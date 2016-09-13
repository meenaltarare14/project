<?php

/** =============================================================================================
*/
function BTI_add_toggleCB_js() {
  //xhReq.open("GET", "'.bs_get_site_base_path().'support/ticketing/xusrmgmtsave/"+jsonstr, false);
  drupal_add_js('
    function toggleCB(uid, gid, mode) {
      document.getElementById("saving_notice_"+uid+"_"+gid+"_"+mode).style.display = "block";
      var context = { \'uid\' : uid, \'gid\' : gid, \'mode\' : mode, \'action\' : \'toggle\'  };
      var xhReq = new XMLHttpRequest();
      var jsonstr = urlencode(JSON.stringify(context));
      xhReq.open("GET", "'.base_path().'support/ticketing/xusrmgmtsave/"+jsonstr, false);
      xhReq.send(null);
      var serverResponse = xhReq.responseText;
      json_obj = JSON.parse(xhReq.responseText);      
      document.getElementById("saving_notice_"+uid+"_"+gid+"_"+mode).style.display = "none";
      // if removing view permission, also remove edit one if was set
      if(mode=="view" && !document.getElementById("cb_"+uid+"_"+gid+"_view").checked && document.getElementById("cb_"+uid+"_"+gid+"_edit").checked) {
        document.getElementById("cb_"+uid+"_"+gid+"_edit").checked = false;
        toggleCB(uid, gid, "edit");
      }
      // if adding edit, also grant view if was not set
      if(mode=="edit" && document.getElementById("cb_"+uid+"_"+gid+"_edit").checked && !document.getElementById("cb_"+uid+"_"+gid+"_view").checked) {
        document.getElementById("cb_"+uid+"_"+gid+"_view").checked = true;
        toggleCB(uid, gid, "view");
      }
      // if adding notify, also grant view if was not set
      if(mode=="notify" && document.getElementById("cb_"+uid+"_"+gid+"_notify").checked && !document.getElementById("cb_"+uid+"_"+gid+"_view").checked) {
        document.getElementById("cb_"+uid+"_"+gid+"_view").checked = true;
        toggleCB(uid, gid, "view");
      }
      return false;
    }
  ', 'inline');
}

/** =============================================================================================
 * Manage users while in account context
*/
function BTI_manage_account_users() {
  $debugNoticeTxt = "";
  $retHtml = "";
  global $user;
  if(!(bsutil_user_has_role(ROLE_TICKETING_ADMIN, $user) || bsutil_user_has_role(ROLE_EXTERNAL_ACCOUNT_ADMINISTRATOR, $user)) && false)
    return "You do not have access to this feature";
      
  if(isset($_GET['display']) && $_GET['display']=='GF') {
    $groupFirst = true;
    $debugNoticeTxt .= "display: group first<br>";
  } else {
    $groupFirst = false;
    $debugNoticeTxt .= "display: accounts first<br>";
  }
  
  // ------- DATA GATHERING
  // these are the 2 main display-driving arrays
  $groupArr = array();
  $userArr = array();
  $userArrTmp = array(); // will get sorted by email before building final userArr
  
  $init_account_nids = array();
  if(curNodeIsCustomerAccount()) {
    $init_account_nids[arg(1)] = arg(1) ; 
    $debugNoticeTxt .= "(working on account nid: ".arg(1).")<br>";
    $node = node_load(arg(1));
  } else {
    $queryStr = "SELECT * FROM {ticketing_account_membership} WHERE uid='".$user->uid."'";
    $result = db_query($queryStr);
  	while($row = $result->fetchAssoc())
      $init_account_nids[$row['aid']] = $row['aid']; 
  }
  
  $account_nids = array();

  // process all initial accounts to gather their account trees
  foreach($init_account_nids as $account_nid) {
    $result = db_query("SELECT n.title as title FROM {node} n WHERE n.nid=:anid", array(':anid' => $account_nid));
    if($row = $result->fetchAssoc())
      $account_nids[$account_nid]['title'] = $row['title'];
    
    // if has indirect accounts, process them all
    $result = db_query("SELECT n.title as title, cfic.field_indirect_customers_target_id as field_indirect_customers_nid FROM {field_data_field_indirect_customers} cfic, {node} n WHERE cfic.field_indirect_customers_target_id=n.nid AND cfic.entity_id=:anid", array(':anid' => $account_nid));
    while($row = $result->fetchAssoc()) {
      $debugNoticeTxt .= "(adding indirect: ".$row['field_indirect_customers_nid'].")<br>";
      $account_nids[$row['field_indirect_customers_nid']]['title'] = $row['title'];
    }
  }
    
  // build group list based on all accounts
  foreach($account_nids as $anid => $account) {
    $debugNoticeTxt .= "(analysing account: ".$anid.")<br>";
    $result = db_query("SELECT n.title as title, ctcg.entity_id as nid, ctcg.field_notification_list_value as notif FROM {field_data_field_notification_list} ctcg, {node} n, {field_data_field_customer_account} ca WHERE n.nid=ctcg.entity_id AND ca.entity_id=n.nid AND ca.field_customer_account_target_id=:anid", array(':anid' => $anid));
    while($row = $result->fetchAssoc()) {
      $gid = $row['nid'];
      $debugNoticeTxt .= "(adding group: ".$row['title'].")<br>";
      $groupArr[$gid]['account name'] = $account['title'];
      $groupArr[$gid]['account nid'] = $anid;
      $groupArr[$gid]['group name'] = $row['title'];
      $groupArr[$gid]['group nid'] = $gid;
      $groupArr[$gid]['notif'] = $row['notif'];
      $groupArr[$gid]['display'] = '('.$account['title'].')<br>'.$row['title'];
      
      // complement user data: add any other associated user (IS, TAC etc) to cur group
      $result2 = db_query("SELECT tgm.uid as uid, u.mail as mail FROM {ticketing_group_membership} tgm, {users} u WHERE tgm.gid=:gid AND u.uid=tgm.uid", array(':gid' => $gid));
      while($row2 = $result2->fetchAssoc())
        $userArrTmp[$row2['mail']] = $row2['uid'];
    }
  }
    
  // build domain list
  foreach($account_nids as $anid => $account) {
    $query = "SELECT field_email_domains_value FROM {field_data_field_email_domains} WHERE entity_id = '".$anid."'";
    $result = db_query($query);
    if($db_account_row = $result->fetchAssoc()) {
      $domainList = explode(',', $db_account_row['field_email_domains_value']);
      foreach($domainList as $domain) {
        $domain = trim(strip_tags($domain));
        if(strlen($domain))
          $email_domains[$domain] = $domain;
      }
    }
  }

  // build user list: based on account email domain settings + any other associated user (IS, TAC etc)
  foreach($email_domains as $email_domain) {
    $result = db_query("SELECT uid, mail FROM {users} WHERE mail like :pat", array(':pat' => '%@'.preg_replace('/.*@/', '', $email_domain)));
    while($row = $result->fetchAssoc())
      $userArrTmp[$row['mail']] = $row['uid'];
  }
  
  // gather existing user-group associations
  uksort($userArrTmp, "strnatcasecmp"); // case insensitive sorting by email
  foreach($userArrTmp as $mail => $uid) {
    $userArr[$uid]['display'] = $mail;
    $result = db_query("SELECT gid, mode, notify FROM {ticketing_group_membership} WHERE uid=:uid", array(':uid' => $uid));
    while($row = $result->fetchAssoc()) {
      $userArr[$uid]['display'] = $mail;
      $userArr[$uid][$row['gid']]['notify'] = ($userArr[$uid][$row['gid']]['notify'] || $row['notify']);
      $userArr[$uid][$row['gid']]['view'] = ($userArr[$uid][$row['gid']]['view'] || ($row['mode']=='view'));
      $userArr[$uid][$row['gid']]['edit'] = ($userArr[$uid][$row['gid']]['edit'] || ($row['mode']=='edit'));
    }
  }
  
  BTI_add_toggleCB_js();

  // ------- GENERATE DISPLAY
  // control region
  // toggle display button
  drupal_add_js('
    function applyFilter() {
      var trs = document.getElementsByTagName("TR");
      var re = new RegExp(document.getElementById("filterString").value, "gi");
      var re_lvl1 = new RegExp("^lvl1_", "gi");
      var count = 0;
      for(var i = 0; i < trs.length; i++) {
        if(trs[i].id != null) {
          if(trs[i].id.match(re_lvl1)) {
            if(trs[i].id.match(re)) {
              trs[i].style.display = "table-row";
              if(count++ % 2 === 0)
                trs[i].className = "odd";
              else
                trs[i].className = "even";
            } else
              trs[i].style.display = "none";
          }
        }
      }
      return false;
    }
    ', 'inline');
  drupal_add_js('misc/form.js'); // required for collapsible fieldsets to work
  drupal_add_js('misc/collapse.js');
  
  // group default notification region
  drupal_add_js('
    function saveNotifListSettings() {
      // build json object sent to server
      var paramArr = {"notifLists":[], "notifGids":[], "action":"saveNotifLists"};
      
      var el = document.getElementsByClassName("notifListVals");
      for (var j=el.length-1; j>=0; j--) {
        var id = el[j].id.replace("notifList_", "");
        paramArr["notifGids"].push(id);
        paramArr["notifLists"].push(el[j].value);
      }

      var jsonstr = urlencode(JSON.stringify(paramArr));
      var xhReq = new XMLHttpRequest();
      xhReq.open("GET", "'.base_path().'support/ticketing/xusrmgmtsave/"+jsonstr, false);
      xhReq.send(null);
      var serverResponse = xhReq.responseText;
      json_obj = JSON.parse(xhReq.responseText);
      
      alert(json_obj["response"]);
    }
  ', 'inline');
  $retHtml .= ' <fieldset class=" collapsible">
                <legend>Default Notification Lists</legend>
                ';
  $retHtml .= ' <form>';
  $retHtml .= 'The Default Notification List specifies non-Xchange user emails which will be added to all new tickets created against the associated Group.';
  $retHtml .= ' It should be used mainly for mailing lists.<br><br>';
  $retHtml .= ' All Xchange users associated to a Group and having the <i>Notifications</i> setting checked, along with the emails specified in the Default Notification List for that Group, will be added to the Notification List of new Tickets created against a given Group.<br><br>';
  $retHtml .= ' For individual Xchange users, we recommend checking their <i>Notifications</i> setting instead of putting their emails in the Default Notification List.<br><br>';
  $retHtml .= ' <table>';

  $retHtml .= '<tr>';
  $retHtml .= '<th width="40%">(Account)<br>Group</th>';
  $retHtml .= '<th width="60%">Default Notification list</th>';
  $retHtml .= '</tr>';
  $trClassId = 0;
  foreach($groupArr as $Id => $Item) {
    $trClassId++;
    $TRStr = 'grpNotif_'.preg_replace('/[^\w@.]/', '', preg_replace('/<br>/', '', $Item['display']));
    $TRid = 0;
    $retHtml .= '<tr style="display:table-row;" id="'.$TRStr.$TRid++.'" class="'.($trClassId%2==0?'even':'odd').'">';
    $retHtml .= '<td>'.$Item['display'].'</td>';
    $retHtml .= '<td>';
    $retHtml .= '<input type="text" class="notifListVals" maxlength="1000" size="80" value="'.BTI_text2csvList($Item['notif']).'" id="notifList_'.$Id.'" title="comma-separated email list">';
    $retHtml .= '</td>';
    $retHtml .= '</tr>';
  }
  $retHtml .= ' </table>';
        
  $retHtml .= '<center>';
  $retHtml .= '<input class="form-submit" type="button" onclick="saveNotifListSettings()" value="Save Notification List settings">';
  $retHtml .= '</center>';

  $retHtml .= ' </form>';  
  $retHtml .= ' </fieldset>';

  // Display and Filtering region
  $retHtml .= ' <fieldset class=" collapsible">
                <legend>Display and Filtering</legend>
                ';
  $retHtml .= ' <form action="'.preg_replace('/\?.*/', '', urldecode(request_uri())).'?'.($groupFirst?'display=AF':'display=GF').'" method = "post">
                  <input type="submit" value="Switch to '.($groupFirst?'User':'Group').'-First Display" title="Toggles Group or User first Displays."/>
                </form>';  
  $retHtml .= '<div>';
  $retHtml .= 'Filter by '.($groupFirst?'Account/Group':'User').': ';
  $retHtml .= '<input type="text" value="" id="filterString"/ onkeyup="applyFilter();" title="Start typing to narrow down elements based on '.($groupFirst?'Account/Group':'User').' name">';
  $retHtml .= '</div>';
    
  $retHtml .= ' </fieldset>';

  $retHtml .= '<table border=1>';
  $trClassId = 0;
  $viewTitleStr = "User has permission to View all tickets of the Group";
  $editTitleStr = "User has permission to Create or Edit all tickets of the Group";
  $notifTitleStr = "User will get notified when any new or existing non-closed ticket of that Group is updated";
  if($groupFirst) {
    $Lvl1Arr = $groupArr;
    $Lvl2Arr = $userArr;
    $retHtml .= '<tr>';
    $retHtml .= '<th>(Account)<br>Group</th>';
    $retHtml .= '<th>User Email</th>';
    $retHtml .= '<th title="'.$viewTitleStr.'">View<br>Tickets</th>';
    $retHtml .= '<th title="'.$editTitleStr.'">Edit<br>Tickets</th>';
    $retHtml .= '<th title="'.$notifTitleStr.'">Notifications</th>';
    $retHtml .= '</tr>';
  } else {
    $Lvl1Arr = $userArr;
    $Lvl2Arr = $groupArr;
    $retHtml .= '<tr>';
    $retHtml .= '<th>User Email</th>';
    $retHtml .= '<th>(Account)<br>Group</th>';
    $retHtml .= '<th title="'.$viewTitleStr.'">View<br>Tickets</th>';
    $retHtml .= '<th title="'.$editTitleStr.'">Edit<br>Tickets</th>';
    $retHtml .= '<th title="'.$notifTitleStr.'">Notifications</th>';
    $retHtml .= '</tr>';
  }
  $nbRows = count($Lvl2Arr); 

  foreach($Lvl1Arr as $Lvl1Id => $Lvl1Item) {
    $trClassId++;
    $TRStr = 'lvl1_'.preg_replace('/[^\w@.]/', '', preg_replace('/<br>/', '', $Lvl1Item['display']));
    $TRid = 0;
    $retHtml .= '<tr style="display:table-row;" id="'.$TRStr.$TRid++.'" class="'.($trClassId%2==0?'even':'odd').'">';
    $retHtml .= '<td rowspan='.$nbRows.'>'.$Lvl1Item['display'].'</td>';
    $tmpTR = "";
    foreach($Lvl2Arr as $Lvl2Id => $Lvl2Item) {
      $retHtml .= $tmpTR;
      $retHtml .= '<td>'.$Lvl2Item['display'].'</td>';

      if($groupFirst) {
        $gid = $Lvl1Id;
        $uid = $Lvl2Id;
      } else {
        $gid = $Lvl2Id;
        $uid = $Lvl1Id;
      }
      $retHtml .= '<td><input type="checkbox" '.($userArr[$uid][$gid]['view']?'checked':'').' onchange="toggleCB('.$uid.', '.$gid.', \'view\')" id="cb_'.$uid.'_'.$gid.'_view" title="'.$viewTitleStr.'"><div style="display:none;" id="saving_notice_'.$uid.'_'.$gid.'_view"><i> Saving...</i></div></td>';
      $retHtml .= '<td><input type="checkbox" '.($userArr[$uid][$gid]['edit']?'checked':'').' onchange="toggleCB('.$uid.', '.$gid.', \'edit\')" id="cb_'.$uid.'_'.$gid.'_edit" title="'.$editTitleStr.'"><div style="display:none;" id="saving_notice_'.$uid.'_'.$gid.'_edit"><i> Saving...</i></div></td>';
      $retHtml .= '<td><input type="checkbox" '.($userArr[$uid][$gid]['notify']?'checked':'').' onchange="toggleCB('.$uid.', '.$gid.', \'notify\')"  id="cb_'.$uid.'_'.$gid.'_notify" title="'.$notifTitleStr.'"><div style="display:none;" id="saving_notice_'.$uid.'_'.$gid.'_notify"><i> Saving...</i></div></td>';
      $tmpTR = '</tr><tr style="display:table-row;" id="'.$TRStr.$TRid++.'" class="'.($trClassId%2==0?'even':'odd').'">';
    }
    $retHtml .= '</tr>';
  }
  $retHtml .= '</table>';  
  
  if(userIsAdministrator()) {
    drupal_set_message($debugNoticeTxt, 'notice');
  }
  
  return $retHtml;
}

/** =============================================================================================
*/
function BTI_administer_users($form, &$form_state) {
  // just a page holder - empty page. A block exists to display actual content.
  //print BTI_manage_account_users();
}

/** =============================================================================================
*/
function BTI_manage_users($form, &$form_state) {
  $minChar = 5;
  global $user;
  $form = array();
  $markupID = 0; // using auto-numbering for markup elements
  
  $showStep1 = TRUE;
  $showStep2 = FALSE;
  $showStep3 = FALSE;
  
  $curUrl = urldecode($_GET['q']);
  $userFilterStr = NULL;
  $userSelectStr = NULL;
  if(preg_match('/filter=/', $curUrl)) {
    $showStep1 = TRUE;
    $showStep2 = TRUE;
    $showStep3 = FALSE;
    $userFilterStr = preg_replace('/.*filter=/', '', $curUrl);
  }
  if(preg_match('/selectedUsers=/', $curUrl)) {
    $userSelectStr = preg_replace('/.*selectedUsers=/', '', $curUrl);
    $showStep1 = FALSE;
    $showStep2 = FALSE;
    $showStep3 = TRUE;
  }

  if($showStep2)
    $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('<center><h1 class="title"><i>Step 2 of 3: User Selection</i></h1></center>');
  elseif($showStep1)
    $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('<center><h1 class="title"><i>Step 1 of 3: User Filtering</i></h1></center>');
  else
    $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('<center><h1 class="title"><i>Step 3 of 3: Account/Group Association</i></h1></center>');
  
  //......................................................................................................................
  $div_ID = 'UserSelection';
  $form[$div_ID] = array(
    '#type' => 'fieldset',
    '#title' => t('User Filtering & Selection'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,    
  );
  
  if($showStep1) { // ..........
    $form[$div_ID]['userFilter'] = array(
      '#type' => 'textfield', 
      '#title' => t('User Filtering'),
      '#description' => t('Show users whose email contain (examples: \'joe@abc.com\' or \'@acme.org\')'),
      '#size' => 64, 
      '#maxlength' => 64, 
      '#default_value' => $userFilterStr,
      '#required' => FALSE,
    );
    $form[$div_ID]['FilterUser'] = array(
      '#type' => 'submit',
      '#value' => t('Step 1: Filter Users'),
      '#submit' => array('BTI_UserSettings__filter'),
    );
  } else {
    $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('<div>Selected user(s) is/are:<br>');
    $userList = explode(',', $userSelectStr);
    $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('<ul class="bullets">');
    // bs_get_site_base_path()
    foreach($userList as $uid) {
      $editUser = user_load($uid);
      $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('<li><b><a target="_blank" href="'.bs_get_site_base_path().'user/'.$editUser->uid.'">'.$editUser->mail.'</a></b> <a target="_blank" href="/support/ticketing/manage_users/selectedUsers='.$editUser->uid.'">(see current Ticketing Settings)</a></li>');
      $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('<input type="hidden" class="selectedUsers" value="'.$editUser->uid.'" id="'.$editUser->mail.'">');
    }
    $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('</ul>');
    $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('</div>');
    
    $form[$div_ID]['Reset'] = array(
      '#type' => 'submit',
      '#value' => t('Reset User filtering & selection - back to Step 1'),
      '#submit' => array('BTI_UserSettings__reset'),
    );
  }
  
  if($userFilterStr && strlen(trim($userFilterStr))<5) {
    drupal_set_message('You must enter at least '.$minChar.' characters as the filter string', 'warning');
    return $form;
  }  
  
  if($showStep2) { // ..........
    $userList = NULL;
    if($userFilterStr && strlen(trim($userFilterStr))>=5)
      $userList = BTI_getUserList(trim($userFilterStr));
    $userListOptionArr = array();
    if($userList) {
      foreach($userList as $uid => $email)
        // '.bs_get_site_base_path().'
        $userListOptionArr[$uid] = t($email.'<a target="_blank" href="/support/ticketing/manage_users/selectedUsers='.$uid.'"> (see current Ticketing Settings)</a></li>');
      if(count($userListOptionArr)>1) {
        $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('<input type="button" id="userselectall" class="form-submit" value="Select ALL Users below" onclick="
          var button = document.getElementById(\'userselectall\');
          var newval = true;
          if(button.value==\'Select ALL Users below\') {
            button.value=\'Unselect ALL Users below\';
          } else {
            button.value=\'Select ALL Users below\';
            newval = false;
          }
          var el = document.getElementsByClassName(\'form-checkbox\');
          for (var j=el.length-1; j>=0; j--) { el[j].checked = newval; }
        " />');
      }
      $form[$div_ID]['SelectUsers'] = array(
        '#type' => 'submit',
        '#value' => t('Step 2: Proceed with Selected User(s)'),
        '#submit' => array('BTI_UserSettings__select'),
        '#attributes' => array('title' => "Continue to Step 3: Account/Group Associations with the Selected User(s)"), // mouse over help
      );
      $form[$div_ID]['userList'] = array(
        '#type' => 'checkboxes', 
        '#title' => t('Select one or more users to which you wish to apply a Ticketing Setting'), 
        '#options' => $userListOptionArr,
      );
    }
  }

  if($showStep3) {
    //......................................................................................................................
    $div_ID = 'AdminSetting';
    $form[$div_ID] = array(
      '#type' => 'fieldset',
      '#title' => t('External Account Administrator Setting'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    // only if 1 user selected - make sure $userSelectStr is unique
    $userList = explode(',', $userSelectStr);
    if(count($userList)!=1) {
      $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('<div>External Account Administrator can only be set if a single user is selected (you currently selected '.count($userList).' users)</div>');
    } else {
      BTI_add_toggleCB_js();
      $editUser = user_load($userList[0]);
      $uid = $userList[0];
      $gid = '9999'; // irrelevant
      $cbStr = '<div>';
      $cbStr .= '<input type="checkbox" '.(BTI_User_is_EAA($uid)?'checked':'').' onchange="toggleCB('.$uid.', '.$gid.', \'EAA\')">';
      $cbStr .= " Is External Account Administrator <i>(grants limited user management rights for users associated on <b>all Accounts</b> ".$editUser->mail." is associated to)</i><br>";
      $cbStr .= "(Will take effect after ".$editUser->mail." has logged out and back in)<br>";
      $cbStr .= '<div style="display:none;" id="saving_notice_'.$uid.'_'.$gid.'_EAA"><i> Saving...</i></div>';
      $cbStr .= '</div>';
      $form[$div_ID]['markup'.$markupID++] = makeMarkupElement($cbStr);
    }
  }

  //......................................................................................................................
  $div_ID = 'AccountGroupSelection';
  $form[$div_ID] = array(
    '#type' => 'fieldset',
    '#title' => t('Account/Group Association'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  if(!$showStep3) {
    $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('<div>Please filter & select user(s) in steps 1 & 2 above</div>');
  } else {
    $form[$div_ID]['AccountFilter'] = array(
      '#type' => 'textfield',
      '#title' => t('Account Filtering'),
      '#description' => t('Enter part of the account name or CID (3 chars minimum) to dynamically display accounts/groups.'),
      '#size' => 32, 
      '#required' => FALSE,
    );
    
  $IDMOStr='ID - refers to the string value known to Jira and available in Jira dropdowns';
    
  $JSFct = "
      function refreshAccounts(filterStr) {
	      var xhReq = new XMLHttpRequest();
        
        var selectedUsersArr = [];
        var el = document.getElementsByClassName('selectedUsers');
        for (var j=el.length-1; j>=0; j--) {
          selectedUsersArr.push(el[j].value);
        }

        var context = { 'filteringStr' : filterStr, 'selectedUsers' : selectedUsersArr };
        var jsonstr = urlencode(JSON.stringify(context));
	      xhReq.open(\"GET\", '".base_path()."support/ticketing/xusrmgmt/'+jsonstr, false);
	      xhReq.send(null);
	      var serverResponse = xhReq.responseText;
	      json_obj = JSON.parse(xhReq.responseText);

        var partnerHtml = '<table style=\"border-collapse: collapse;\" border=\"1\"><tr><th>Partner Account <a title=\"".$IDMOStr."\">IDs*</a></th><th>INDirect Account <a title=\"".$IDMOStr."\">IDs*</a></th><th>Group <a title=\"".$IDMOStr."\">IDs*</a></th><th width=\"5%\">View</th><th width=\"5%\">Edit</th></tr>';
        var nbPartners=0, oeclass='';
        for (var key in json_obj['partners']) {
          if(nbPartners++%2==0)
            oeclass = 'even';
          else
            oeclass = 'odd';
          partnerHtml += GetPartnerAccountHTMLStr(json_obj['partners'][key], oeclass, key);
        }
        if(nbPartners==0) {
          partnerHtml += '<tr><td>'+json_obj['msg']['partners']+'</td></tr>';
        }
        partnerHtml += '</table>';
     	  jQuery('#divPartnerAccountsUserMgmt').html(partnerHtml);

        var directHtml = '<table border=\"1\"><tr><th>Direct Account <a title=\"".$IDMOStr."\">IDs*</a></th><th>Group <a title=\"".$IDMOStr."\">IDs*</a></th><th width=\"5%\">View</th><th width=\"5%\">Edit</th></tr>';
        var nbDirects=0;
        oeclass='';
        for (var key in json_obj['directs']) {
          if(nbDirects++%2==0)
            oeclass = 'even';
          else
            oeclass = 'odd';
          directHtml += GetNonPartnerAccountHTMLStr(json_obj['directs'][key], oeclass, true, key, '');
        }
        if(nbDirects==0) {
          directHtml += '<tr><td>'+json_obj['msg']['directs']+'</td></tr>';
        }
        directHtml += '</table>';    
     	  jQuery('#divDirectAccountsUserMgmt').html(directHtml);
        
        if(nbDirects==0 && nbPartners==0) {
       	  jQuery('#divTopButtonUserMgmt').html('');
       	  jQuery('#divBottomButtonUserMgmt').html('');
        } else {
          buttonStr = '<center>';
          buttonStr += '<input class=\"form-submit\" type=\"button\" onclick=\"saveSettings(\'set\')\" value=\"Set Ticketing Settings\" title=\"For all selected user(s), the ticketing settings will be SET TO EXACTLY the assigned accounts & checked groups in this form, REPLACING any existing settings.\">&nbsp';
          buttonStr += '<input class=\"form-submit\" type=\"button\" onclick=\"saveSettings(\'add\')\" value=\"Add Ticketing Settings\" title=\"For all selected user(s), the ticketing settings (assigned accounts & checked groups) will be ADDED to existing settings.\">';";
          
  $JSFct .= "  
      buttonStr += '<input class=\"form-submit\" type=\"button\" onclick=\"saveSettings(\'clone\')\" value=\"Set Ticketing Settings *to YOURSELF*\" title=\"For yourself ".$user->mail.", the ticketing settings will be SET TO EXACTLY the assigned accounts & checked groups in this form, REPLACING any existing settings.\">&nbsp';
    ";

  $JSFct .= "  
          buttonStr += '</center>';
       	  jQuery('#divTopButtonUserMgmt').html(buttonStr);
       	  jQuery('#divBottomButtonUserMgmt').html(buttonStr);
        }
      }
      (function ($) {
      $(document).ready(function() {
        $('#edit-accountfilter').keyup(function() {
          var s = $('#edit-accountfilter').val();
          setTimeout(function() { 
            if($('#edit-accountfilter').val() == s){
              refreshAccounts($('#edit-accountfilter').val());
            } 
          }, 500); // 0.5 sec delay to check.
        }); // End of  keyup function
      });
      })(jQuery);// End of document.ready
    ";
    drupal_add_js($JSFct, 'inline');
    
    drupal_add_js('
      window.onload = function() {
        refreshAccounts("");
      };
    ', 'inline');
    
    drupal_add_js('
      function GetPartnerAccountHTMLStr(partnerAccountData, oeclass, nid) {
        var retStr = "<tr class=\""+oeclass+"\">";
        hidden_value = 0;
        select_value = "select";
        bkg_col = "";
        if(partnerAccountData["assigned"]) {
          hidden_value = 1;
          select_value = "unselect";
          bkg_col = "style=\"background-color:lightblue\"";
        } 
        FunctionalHtml = "";
        if(!partnerAccountData["functional"])
          FunctionalHtml = "style=\"border:2px solid #f00;\"";
        retStr += "<td "+FunctionalHtml+" rowspan=\""+partnerAccountData["groupNb"]+"\" id=\"tdp"+nid+"\" "+bkg_col+">";
        retStr += "<input type=\"hidden\" class=\"accountvals\" value=\""+hidden_value+"\" id=\"valp"+nid+"\" name=\""+nid+"\">";
        retStr += "<a href=\"'.bs_get_site_base_path().'node/"+nid+"\" target=\"_blank\">"+partnerAccountData["title"]+"</a>";
        retStr += "<input type=\"hidden\" id=\""+nid+"\" name=\""+partnerAccountData["title"]+"\">";        
        retStr += "<br><input id=\"selectp"+nid+"\" class=\"form-submit\" type=\"button\" onclick=\"selectGroups(\'p\', "+nid+")\" value=\""+select_value+"\" title=\"This will check all Groups under this Account\">";
        retStr += "</td>";
        var nb=0;
        // indirects
        // manage tr elements outside the call to GetNonPartnerAccountHTMLStr
        for (var key in partnerAccountData["indirects"]) {
          if(nb!=0)
            retStr += "<tr class=\""+oeclass+"\">";
          retStr += GetNonPartnerAccountHTMLStr(partnerAccountData["indirects"][key], oeclass, false, key, nid);
          nb++;
        }
        return retStr;
      }
      function GetNonPartnerAccountHTMLStr(directAccountData, oeclass, addBegTr, nid, partnerNid) {
        var retStr = "";
        if(addBegTr)
          retStr += "<tr class=\""+oeclass+"\">";
        hidden_value = 0;
        select_value = "select";
        bkg_col = "";
        if(directAccountData["assigned"]) {
          hidden_value = 1;
          select_value = "unselect";
          bkg_col = "style=\"background-color:lightblue\"";
        } 
        FunctionalHtml = "";
        if(!directAccountData["functional"])
          FunctionalHtml = "style=\"border:2px solid #f00;\"";
        retStr += "<td "+FunctionalHtml+" rowspan=\""+directAccountData["groupNb"]+"\" id=\"tdi"+nid+"\" class=\"tdi"+partnerNid+"\" "+bkg_col+">";
        retStr += "<input type=\"hidden\" class=\"accountvals vali"+partnerNid+"\" value=\""+hidden_value+"\" id=\"vali"+nid+"\" name=\""+nid+"\">";
        retStr += "<a href=\"'.bs_get_site_base_path().'node/"+nid+"\" target=\"_blank\">"+directAccountData["title"]+"</a>";
        retStr += "<input type=\"hidden\" id=\""+nid+"\" name=\""+directAccountData["title"]+"\">";        
        retStr += "<br><input id=\"selecti"+nid+"\" type=\"button\" class=\"selectp"+partnerNid+" form-submit\" onclick=\"selectGroups(\'i\', "+nid+")\" value=\""+select_value+"\" title=\"This will check all Groups under this Account\">";
        // groups
        var g=0;
        for (var key in directAccountData["groups"]) {
          retStr += "<input type=\"hidden\" id=\""+key+"\" name=\""+directAccountData["groups"][key]["title"]+"\">";                  
          if(g++!=0)
            retStr += "</tr><tr class=\""+oeclass+"\">";
          var cpbx_str = "";
          if(directAccountData["groups"][key]["product_category"]==1)
            cpbx_str = "&nbsp&nbsp<FONT color=\"green\"><b><i>CPBX</i></b></FONT>";
          retStr += "<td><a href=\"'.bs_get_site_base_path().'node/"+key+"\" target=\"_blank\">"+directAccountData["groups"][key]["title"]+"</a>"+cpbx_str+"</td>";
          cbclass = "cbi"+nid;
          if(partnerNid!=0)
            cbclass += " cbp"+partnerNid;
          checkedStr = "";
          if(directAccountData["groups"][key]["view"])
            checkedStr = "checked=true";
          retStr += "<td><input type=\"checkbox\" name=\""+key+"\" class=\"cbview "+cbclass+"\" "+checkedStr+" onchange=\"toggleCheckbox("+nid+")\"></td>";
          checkedStr = "";
          if(directAccountData["groups"][key]["edit"])
            checkedStr = "checked=true";
          retStr += "<td><input type=\"checkbox\" name=\""+key+"\" class=\"cbedit "+cbclass+"\" "+checkedStr+" onchange=\"toggleCheckbox("+nid+")\"></td>";
        }
        retStr += "</tr>";
        return retStr;
      }
      function toggleCheckbox(anid) {
        var pi = "i";
        document.getElementById("val"+pi+anid).value = "1";
        document.getElementById("select"+pi+anid).value = "unselect";
        document.getElementById("td"+pi+anid).style.backgroundColor = "lightblue";
      }
      function selectGroups(pi, anid) {
        var newcbval = true;
        if(document.getElementById("val"+pi+anid).value=="1") {
          newcbval = false;
          document.getElementById("val"+pi+anid).value = "0";
          document.getElementById("select"+pi+anid).value = "select";
          document.getElementById("td"+pi+anid).style.backgroundColor = "";
        } else {
          document.getElementById("val"+pi+anid).value = "1";
          document.getElementById("select"+pi+anid).value = "unselect";
          document.getElementById("td"+pi+anid).style.backgroundColor = "lightblue";
        }         
        if(pi=="p") {
          var el = document.getElementsByClassName("vali"+anid);
          for (var j=el.length-1; j>=0; j--)
            el[j].value = "0";
          var el = document.getElementsByClassName("tdi"+anid);
          for (var j=el.length-1; j>=0; j--)
            el[j].style.backgroundColor = "";
          var el = document.getElementsByClassName("selectp"+anid);
          for (var j=el.length-1; j>=0; j--) {
            if(newcbval)
              el[j].type = "hidden";
            else
              el[j].type = "button";
            el[j].value = "select";
            el[j].disabled = newcbval;
          }
        }
        var el = document.getElementsByClassName("cb"+pi+anid);
        for (var j=el.length-1; j>=0; j--) {
          el[j].checked = newcbval;
        }        
      }
      function saveSettings(action) {
        // in one loop:
        // - build json object sent to server
        // - build summary message for user confirmation
        var summaryStr = "Do you wish to proceed with the following action(s)?\r\n\r\n";
        var checkedElementArr = {"assignAccounts":[], "viewGroups":[], "editGroups":[], "selectedUsers":[], "action":action};
        if(action=="clone") {
          summaryStr += "DELETE current ticketing settings and SET to the following:\r\n\r\n";
          summaryStr += "Impacted Users:\r\n";
          summaryStr += "- '.$user->mail.'\r\n";
          checkedElementArr["selectedUsers"].push("'.$user->uid.'");
        } else {
          if(action=="set")
            summaryStr += "DELETE current ticketing settings and SET to the following:\r\n\r\n";
          else
            summaryStr += "ADD the following ticketing settings:\r\n\r\n";
          summaryStr += "Impacted Users:\r\n";
          var el = document.getElementsByClassName("selectedUsers");
          for (var j=el.length-1; j>=0; j--) {
            checkedElementArr["selectedUsers"].push(el[j].value);
            summaryStr += "- "+el[j].id+"\r\n";
          }
        }
        
        summaryStr += "\r\nAssociating Accounts:\r\n";
        var el = document.getElementsByClassName("accountvals");
        for (var j=el.length-1; j>=0; j--) {
          if(el[j].value=="1") {
            checkedElementArr["assignAccounts"].push(el[j].name);
            summaryStr += "-  "+document.getElementById(el[j].name).name+"\r\n";
          }
        }
        summaryStr += "\r\nAssociating Groups:\r\n";
        var el = document.getElementsByClassName("cbview");
        for (var j=el.length-1; j>=0; j--) {
          if(el[j].checked) {
            checkedElementArr["viewGroups"].push(el[j].name);
            summaryStr += "- (view) "+document.getElementById(el[j].name).name+"\r\n";
          }
        }
        var el = document.getElementsByClassName("cbedit");
        for (var j=el.length-1; j>=0; j--) {
          if(el[j].checked) {
            checkedElementArr["editGroups"].push(el[j].name);
            summaryStr += "- (edit) "+document.getElementById(el[j].name).name+"\r\n";
          }
        }

        confirmed = false;
        confirmed = confirm(summaryStr)
        if(confirmed) {
          var jsonstr = urlencode(JSON.stringify(checkedElementArr));
  	      var xhReq = new XMLHttpRequest();
  	      xhReq.open("GET", "'.base_path().'support/ticketing/xusrmgmtsave/"+jsonstr, false);
  	      xhReq.send(null);
  	      var serverResponse = xhReq.responseText;
  	      json_obj = JSON.parse(xhReq.responseText);
          
          alert(json_obj["response"]);
        }       
      }
    ', 'inline');
    // ..
    $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('<div class="accountDivs" id="divTopButtonUserMgmt"></div><br>'); 

    $div_IDII = 'AccountGroupSelection_Directss';
    $form[$div_ID][$div_IDII] = array(
      '#type' => 'fieldset',
      '#title' => t('Direct Accounts'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    $form[$div_ID][$div_IDII]['markup'.$markupID++] = makeMarkupElement('<br><div class="accountDivs" id="divDirectAccountsUserMgmt"></div>'); // whole table is dynamic
      
    $div_IDII = 'AccountGroupSelection_Partners';
    $form[$div_ID][$div_IDII] = array(
      '#type' => 'fieldset',
      '#title' => t('Partner Accounts'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    $form[$div_ID][$div_IDII]['markup'.$markupID++] = makeMarkupElement('<div class="accountDivs" id="divPartnerAccountsUserMgmt"></div>'); // whole table is dynamic

    $form[$div_ID]['markup'.$markupID++] = makeMarkupElement('<br><div class="accountDivs" id="divBottomButtonUserMgmt"></div>'); 
    // ..    
  }

  return $form;
}

/** =============================================================================================
*/
function BTI_User_is_EAA($uid) {
  $editUser = user_load($uid);
  //profile_load_profile($editUser);

  $isEAA = '0'; // dflt
  if(property_exists($editUser, 'profile_is_EAA')) 
    $isEAA = $editUser->profile_is_EAA;
  
  return ($isEAA!='0');
}

/** =============================================================================================
*/
function BTI_Toggle_Notify($uid, $gid) {
  // call API_RemoveUserFromALLEmailNotificationLists - it was originally called from ajax API
  global $user;

  // add or remove? check current DB status
  $currentlyNotified = FALSE;
  $queryStr = "SELECT notify from {ticketing_group_membership} WHERE uid='".$uid."' AND gid='".$gid."'";
  $result = db_query($queryStr);
	while($row = $result->fetchAssoc())
    $currentlyNotified = ($currentlyNotified || ($row['notify']==1));
  
  if($currentlyNotified)
    AdjustUserNotificationLists($user->uid, $uid, $gid, FALSE);
  else
    AdjustUserNotificationLists($user->uid, $uid, $gid, TRUE);
}

/** =============================================================================================
*/
function BTI_Toggle_EAA($uid) {
  $editUser = user_load($uid);
  profile_load_profile($editUser);
  $updatedFields['profile_is_EAA'] = (BTI_User_is_EAA($uid)?'0':'1'); // inverse value i.e. toggle
  profile_save_profile($updatedFields, $editUser, 'Ticketing');
}

/** =============================================================================================
*/
function BTI_UpdateNotificationLists($paramObj) {
  foreach($paramObj->notifGids as $arrId => $gid) {
    db_query("UPDATE content_type_customer_group SET field_notification_list_value='".$paramObj->notifLists[$arrId]."' WHERE nid='".$gid."'");
    bs_touchNode($gid);
    BTI_log_group_notif_change($gid);
  }
}

/** =============================================================================================
*/
function BTI_Toggle_GroupAssignment($paramObj) {
  $queryStr = "SELECT * FROM {ticketing_group_membership} WHERE uid='".$paramObj->uid."' AND gid='".$paramObj->gid."' AND mode='".$paramObj->mode."'";
  $result = db_query($queryStr);
  $r = $result->fetchAssoc();
  $isAlreadyAssigned = ($r!=NULL || $r != false);
  if($isAlreadyAssigned) {
    revokeGroup($paramObj->gid, $paramObj->uid, (($paramObj->mode == "view") ? VIEW_GROUP_ACCESS : EDIT_GROUP_ACCESS));
  }
  else {
    grantGroup($paramObj->gid, $paramObj->uid, (($paramObj->mode == "view") ? VIEW_GROUP_ACCESS : EDIT_GROUP_ACCESS));
  }
}

/** =============================================================================================
*/
function dynamic_ticketing_ajax_user_management_save($jsonStr) {
  global $user;
  $resArray = array();
  $resArray['response'] = 'Unknown action or request'; // default server response
  $paramObj = json_decode($jsonStr);
  
  if($paramObj->action=="toggle") {
    if($paramObj->mode=="notify") {
      if(userIsAdministrator()||bsutil_user_has_role(ROLE_TICKETING_ADMIN, $user)||bsutil_user_has_role(ROLE_EXTERNAL_ACCOUNT_ADMINISTRATOR, $user)) {
        BTI_Toggle_Notify($paramObj->uid, $paramObj->gid);
        $resArray['response'] = 'Request processed'; 
      } else {
        $resArray['response'] = 'You do not have the permission to perform this action'; 
      }
    } elseif($paramObj->mode=="EAA") {
      if(userIsAdministrator()||bsutil_user_has_role(ROLE_TICKETING_ADMIN, $user)) {
        BTI_Toggle_EAA($paramObj->uid);
        $resArray['response'] = 'Request processed'; 
      } else {
        $resArray['response'] = 'You do not have the permission to perform this action'; 
      }
    } elseif($paramObj->mode=="view" || $paramObj->mode=="edit") {
      if(userIsAdministrator()||bsutil_user_has_role(ROLE_TICKETING_ADMIN, $user)||bsutil_user_has_role(ROLE_EXTERNAL_ACCOUNT_ADMINISTRATOR, $user)) {
        BTI_Toggle_GroupAssignment($paramObj);
        $resArray['response'] = 'Request processed'; 
      } else {
        $resArray['response'] = 'You do not have the permission to perform this action'; 
      }
    } 
  } elseif($paramObj->action=="saveNotifLists") {
    if(userIsAdministrator()||bsutil_user_has_role(ROLE_TICKETING_ADMIN, $user)||bsutil_user_has_role(ROLE_EXTERNAL_ACCOUNT_ADMINISTRATOR, $user)) {
      BTI_UpdateNotificationLists($paramObj);
      $resArray['response'] = 'Notification Lists have been updated'; 
    } else {
      $resArray['response'] = 'You do not have the permission to perform this action'; 
    }
  } else {
    if($paramObj->action=="set" || $paramObj->action=="clone") {
      foreach(array_values($paramObj->selectedUsers) as $uid) {
        revokeGroup(0, $uid);
        unassignAccount(0, $uid);
      }    
    }
    foreach(array_values($paramObj->selectedUsers) as $uid) {
      foreach(array_values($paramObj->assignAccounts) as $anid)
        assignAccount($anid, $uid);
      foreach(array_values($paramObj->viewGroups) as $gnid)
        grantGroup($gnid, $uid, VIEW_GROUP_ACCESS);
      foreach(array_values($paramObj->editGroups) as $gnid)
        grantGroup($gnid, $uid, EDIT_GROUP_ACCESS);
    }
    
    BTI_UpdateUserRoles($uid);
    
    $resArray['response'] = 'The settings have been saved';
  }
  
  return drupal_json_output($resArray);
  exit;
}

/** =============================================================================================
*/
function dynamic_ticketing_ajax_user_management($jsonStr) {
  $resArray = array();
  $resArray['msg']['partners'] = $resArray['msg']['directs'] = 'No Data to show';
  
  $MAX_NB_ACCOUNTS = 25;
  $MIN_FILTER_LEN = 2;

  $paramObj = json_decode($jsonStr);
  
  $selectedUserUID = NULL;
  $filteringStr = NULL;
  
  if(is_array($paramObj->selectedUsers) && count($paramObj->selectedUsers)==1)
    $selectedUserUID = $paramObj->selectedUsers[0];

  // only process if filter is long enough or for specific user
  if(strlen($paramObj->filteringStr)>=1 && strlen($paramObj->filteringStr)<$MIN_FILTER_LEN) {
    $resArray['msg']['partners'] = $resArray['msg']['directs'] = 'Filter string is too short (min '.$MIN_FILTER_LEN.' characters)';
    if(!$selectedUserUID) {
      $resArray['error'] = 'Filter string is too short (min '.$MIN_FILTER_LEN.' characters)';
      return drupal_json($resArray);
      exit;
    }
  } else {
    $filteringStr = $paramObj->filteringStr;
  }

  $resArray['directs'] = array();
  $resArray['partners'] = array();

  // build temp objects    
  $accounts = array();
  $indirects = array();
  $isIndirect = array();
  $partners = array();  
  $assignedGroups = array();  
  $assignedAccounts = array();  

  // doing a first query for accounts only - otherwise group-less account will not be fetched and will cause display issue
//  $queryStr = "select n.nid as anid, n.title as atitle from node n, content_type_customer_account ca where n.type='customer_account' and n.nid=ca.nid";
  $queryStr = db_select('node', 'n')
    ->fields('n', array("nid", "type", "title"))
    ->condition('type', 'customer_account', '=');
  $result = $queryStr->execute();
	while($row = $result->fetchAssoc()) {
    $accounts[$row['nid']] = jsonify(BTI_get_exportable_account_name_CID($row['nid']));
  }
  // ... now include groups
//  $queryStr = "select n.nid as anid, cg.nid as gnid, cg.field_product_category_value, n.title as atitle, n2.title as gtitle, ic.field_indirect_customers_nid from node n, node n2, content_type_customer_group cg, content_field_indirect_customers ic, content_type_customer_account ca where n.type='customer_account' and n.nid=ic.nid and n.nid=ca.nid and cg.field_customer_account_nid=n.nid and cg.nid=n2.nid";
//  $queryStr = db_select('node', 'n')
//    ->condition('n.type', 'customer_account', '=');
//  $queryStr->join('field_data_field_product_category', 'fpc', 'n.nid = fpc.entity_id');
//    $queryStr->join('field_data_field_indirect_customers', 'fic', 'n.nid = fic.entity_id');
//    $queryStr->join('field_data_field_customer_account', 'fca', 'fca.field_customer_account_target_id = n.nid');
//    $queryStr->join('node', 'n2', 'n2.nid = fca.entity_id');
//    $queryStr->addField('n', 'nid', 'anid');
//    $queryStr->addField('fca', 'entity_id', 'gnid');
//    $queryStr->addField('fpc', 'field_product_category_value');
//    $queryStr->addField('n', 'title', 'atitle');
//    $queryStr->addField('n2', 'title', 'gtitle');
//    $queryStr->addField('fic', 'entity_id', 'field_indirect_customers_nid');
//  //D7 query
//  $queryStr = "select n.nid as anid, fca.entity_id as gnid, fpc.field_product_category_value, n.title as atitle, n2.title as gtitle, fic.entity_id as field_indirect_customers_nid from node n, node n2, field_data_field_product_category fpc, field_data_field_indirect_customers fic, field_data_field_customer_account fca where n.type='customer_account' and n.nid=fic.entity_id and n2.nid=fpc.entity_id and fca.field_customer_account_target_id=n.nid and fca.entity_id=n2.nid";
//  $result = db_query($queryStr);
//  $accountArr = array();
//  $groupArr = array();
//	while($row = $result->fetchAssoc()) {
//    if($row['field_indirect_customers_nid']) {
//      $indirects[$row['anid']][] = $row['field_indirect_customers_nid'];
//      $isIndirect[$row['field_indirect_customers_nid']] = TRUE;
//      $partners[$row['anid']] = $row['anid'];
//    }
//    $groupArr[$row['anid']][$row['gnid']]['GName'] = $row['gtitle'];
//    $groupArr[$row['anid']][$row['gnid']]['product_category'] = $row['field_product_category_value'];
//  }

  $queryStr = db_select('node', 'n');
  $queryStr->addField('n', 'nid', 'anid');
  $queryStr->addField('n', 'title', 'atitle');
  $queryStr->addField('n2', 'title', 'gtitle');
  $queryStr->addField('n2', 'nid', 'gnid');
  $queryStr->addField('fpc', 'field_product_category_value', 'field_product_category_value');
  $queryStr->addField('ic', 'field_indirect_customers_target_id', 'field_indirect_customers_nid');
  $queryStr->leftJoin("field_data_field_indirect_customers", "ic", "ic.bundle = 'customer_account' AND ic.entity_id = n.nid");
  $queryStr->leftJoin("field_data_field_customer_account", "cg", "cg.bundle = 'customer_group' AND cg.field_customer_account_target_id = n.nid");
  $queryStr->leftJoin("node", "n2", "n2.type = 'customer_group' AND n2.nid = cg.entity_id");
  $queryStr->leftJoin("field_data_field_product_category", "fpc", "fpc.bundle = 'customer_group' AND fpc.entity_id = cg.entity_id");
  $queryStr->condition("n.type", "customer_account");
  $result = $queryStr->execute();

  $accountArr = array();
  $groupArr = array();
  foreach ($result as $row) {
    if ($row->field_indirect_customers_nid) {
      $indirects[$row->anid][] = $row->field_indirect_customers_nid;
      $isIndirect[$row->field_indirect_customers_nid] = TRUE;
      $partners[$row->anid] = $row->anid;
    }
    $groupArr[$row->anid][$row->gnid]['GName'] = $row->gtitle;
    $groupArr[$row->anid][$row->gnid]['product_category'] = $row->field_product_category_value;
  }
  // approach is:
  // --- first: gather data of account/groups based on required filtering
  // --- second: add data of existing associations (if for single user, otherwise becomes fuzzy...)
  if($selectedUserUID) {
    $queryStr = "SELECT * FROM {ticketing_group_membership} WHERE uid='".$selectedUserUID."'";
    $result = db_query($queryStr);
  	while($row = $result->fetchAssoc())
      $assignedGroups[$row['mode']][$row['gid']] = TRUE;
    $queryStr = "SELECT * FROM {ticketing_account_membership} WHERE uid='".$selectedUserUID."'";
    $result = db_query($queryStr);
  	while($row = $result->fetchAssoc())
      $assignedAccounts[$row['aid']] = TRUE;
  }
  foreach($accounts as $anid => $title) {
    $keep = FALSE;
    if($filteringStr && preg_match('/'.$filteringStr.'/i', $title)) 
      $keep = TRUE;
    if($assignedAccounts[$anid])
      $keep = TRUE;
    if(array_key_exists($anid, $partners)) {      
      // is partner
      // check if filtering applies to indirects
      if($filteringStr) {
        foreach(array_values(array_unique($indirects[$anid])) as $inid) {
          if(preg_match('/'.$filteringStr.'/i', $accounts[$inid])) 
            $keep = TRUE;
        }
      }
      $nbGroups = 0;
      foreach(array_values(array_unique($indirects[$anid])) as $inid) {
        // starting a new indirect
        if(array_key_exists($inid, $groupArr)) {
          $nbGroups += count($groupArr[$inid]);
          if(!$keep) { // check if assigned
            foreach($groupArr[$inid] as $gnid => $groupData) {
              if($assignedGroups['view'][$gnid] || $assignedGroups['edit'][$gnid])
                $keep = TRUE;
            }
          }
        } else
          $nbGroups++; // group-less account! Data issue, but keep display ok
      }

      // redo it all - only if keeping this whole partner
      if($keep) foreach(array_values(array_unique($indirects[$anid])) as $inid) {
        // starting a new indirect
        if(array_key_exists($inid, $groupArr)) {            
          foreach($groupArr[$inid] as $gnid => $groupData) {                
            $resArray['partners'][$anid]['indirects'][$inid]['groups'][$gnid]['title'] = jsonify(BTI_get_exportable_group_name($gnid));
            $resArray['partners'][$anid]['indirects'][$inid]['groups'][$gnid]['product_category'] = $groupData['product_category'];
            $resArray['partners'][$anid]['indirects'][$inid]['groups'][$gnid]['view'] = $assignedGroups['view'][$gnid];
            $resArray['partners'][$anid]['indirects'][$inid]['groups'][$gnid]['edit'] = $assignedGroups['edit'][$gnid];
          }
        }
        $resArray['partners'][$anid]['indirects'][$inid]['groupNb'] = (count($groupArr[$inid])>0?count($groupArr[$inid]):1); // if no group, data issue, but keep display ok
        $resArray['partners'][$anid]['indirects'][$inid]['title'] = jsonify(BTI_get_exportable_account_name_CID($inid));
        $resArray['partners'][$anid]['indirects'][$inid]['assigned'] = ($assignedAccounts[$inid]==true);
        $resArray['partners'][$anid]['indirects'][$inid]['functional'] = isAccountEnabledANDActive($inid);
      }        
      if($keep) $resArray['partners'][$anid]['title'] = jsonify(BTI_get_exportable_account_name_CID($anid));
      if($keep) $resArray['partners'][$anid]['groupNb'] = $nbGroups;
      if($keep) $resArray['partners'][$anid]['assigned'] = ($assignedAccounts[$anid]==true);
      if($keep) $resArray['partners'][$anid]['functional'] = isAccountEnabledANDActive($anid);
    } elseif($isIndirect[$anid]) {
        // is INdirect... will be part of partner sub-data
    }  else {
      // is direct
      if(array_key_exists($anid, $groupArr)) {
        if(!$keep) { // check if assigned
          foreach($groupArr[$anid] as $gnid => $groupData) {
            if($assignedGroups['view'][$gnid] || $assignedGroups['edit'][$gnid])
              $keep = TRUE;
          }
        }
        if($keep) {
          foreach($groupArr[$anid] as $gnid => $groupData) {
            $resArray['directs'][$anid]['groups'][$gnid]['title'] = jsonify(BTI_get_exportable_group_name($gnid));
            $resArray['directs'][$anid]['groups'][$gnid]['product_category'] = $groupData['product_category'];
            $resArray['directs'][$anid]['groups'][$gnid]['view'] = $assignedGroups['view'][$gnid];
            $resArray['directs'][$anid]['groups'][$gnid]['edit'] = $assignedGroups['edit'][$gnid];
          }
          $resArray['directs'][$anid]['groupNb'] = (count($groupArr[$anid])>0?count($groupArr[$anid]):1); // if no group, data issue, but keep display ok
          $resArray['directs'][$anid]['functional'] = isAccountEnabledANDActive($anid);
        }
      } 
      if($keep) $resArray['directs'][$anid]['title'] = jsonify(BTI_get_exportable_account_name_CID($anid));
      if($keep) $resArray['directs'][$anid]['assigned'] = ($assignedAccounts[$anid]==true);
    }
  }

	$nbAccount = (is_array($resArray['directs'])?count($resArray['directs']):0);
	$nbAccount += (is_array($resArray['partners'])?count($resArray['partners']):0);
  if($nbAccount>$MAX_NB_ACCOUNTS) {
    $resArray = array(); // flush everything
    $resArray['directs'] = array();
    $resArray['partners'] = array();
    $resArray['msg']['partners'] = $resArray['msg']['directs'] = 'Maximum nb of result ('.$MAX_NB_ACCOUNTS.') has been reached (current filter yields '.$nbAccount.' results) - please filter more...';
  }

  
  // create a JSON object. The object will contain a property named "SearchResults" that will be set with the $items variable.
  return drupal_json_output($resArray);
  exit;
}

/** ============================================================================================= */
function GetUIDFromEmail($email) {
  $query = db_select('users', 'u')
    ->fields('u', array('uid'))
    ->condition('mail', $email, '=');
  $result = $query->execute();
  
	if($row = $result->fetchAssoc()) {
    return $row['uid'];
	}    
  return NULL;
}

/** ============================================================================================= */
function BTI_getUserList($userFilterStr) {
  $userList = NULL;
  global $user;
  $query = db_select('users', 'u')
    ->fields('u', array('uid', 'mail'))
    ->condition('mail', '%' . db_like($userFilterStr) . '%', 'LIKE');

  $result = $query->execute();
  
	while($row = $result->fetchAssoc()) {
    $userList[$row['uid']] = $row['mail'];
	}    
  return $userList;
}

/** ============================================================================================= */
function BTI_UserSettings__reset($form, &$form_state) {
  $form_state['redirect'] = BASEURL_MANAGE_USER;
}

/** ============================================================================================= */
function BTI_UserSettings__filter($form, &$form_state) {
  $filterVal = $form_state['values']['userFilter'];
  $form_state['redirect'] = BASEURL_MANAGE_USER.'/filter='.urlencode($filterVal);
}

/** ============================================================================================= */
function BTI_UserSettings__select($form, &$form_state) {
  $userUidList = '';
  foreach($form_state['values']['userList'] as $uid => $val) {
    if($val) {
      if(strlen($userUidList))
        $userUidList .= ',';
      $userUidList .= $uid;
    }
  }
  $form_state['redirect'] = BASEURL_MANAGE_USER.'/selectedUsers='.$userUidList;
}

/** ============================================================================================= */
function assignAccount($account_nid, $uid) {
  db_query("INSERT IGNORE INTO {ticketing_account_membership} (uid, aid) VALUES (:uid, :aid)", array(":uid" => $uid, ":aid" => $account_nid));
}

/** ============================================================================================= 
if $account_nid is 0 : revokes ALL accounts for this user
*/
function unassignAccount($account_nid, $uid) {
  if($account_nid) {
    db_query("DELETE FROM {ticketing_account_membership} WHERE uid = :uid AND aid = :aid", array(":uid" => $uid, ":aid" => $account_nid));
  } else {
    db_query("DELETE FROM {ticketing_account_membership} WHERE uid = :uid", array(":uid" => $uid));
  }
}

/** ============================================================================================= */
function grantGroup($group_nid, $uid, $accessMode) {
  db_query("INSERT IGNORE INTO {ticketing_group_membership} (uid, gid, mode) VALUES (:uid, :gid, :mode)", array(":uid" => $uid, ":gid" => $group_nid, ":mode" => $accessMode));
  BTI_log_user_group_assign($uid, $group_nid, $accessMode);
  // assign associated account
  $result = db_query("SELECT field_customer_account_target_id FROM {field_data_field_customer_account} WHERE entity_id = :nid", array(":nid" => $group_nid));
  if($row = $result->fetchAssoc())
    assignAccount($row['field_customer_account_nid'], $uid);
}

/** ============================================================================================= 
if $group_nid is 0 : revokes ALL groups for this user
*/
function revokeGroup($group_nid, $uid, $accessMode=NULL) {
  if($group_nid) {
    if($accessMode)
      db_query("DELETE FROM {ticketing_group_membership} WHERE uid=:uid AND gid=:gid AND mode=:mode", array(":uid" => $uid, ":gid" => $group_nid, ":mode" => $accessMode));
    else
      db_query("DELETE FROM {ticketing_group_membership} WHERE uid=:uid AND gid=:gid", array(":uid" => $uid, ":gid" => $group_nid));
  } else {
    db_query("DELETE FROM {ticketing_group_membership} WHERE uid=:uid", array(":uid" => $uid));
  }
  
  // detect dangling account associations i.e. with no more underlying group association for the user
  $result = db_query("SELECT aid FROM {ticketing_account_membership} WHERE uid=:uid", array(":uid" => $uid));
	while($row = $result->fetchAssoc()) {
    if($row['aid'] == 0) continue;
    // is the user associated to a group under that account?
    //$result2 = db_query("SELECT gid FROM {ticketing_group_membership} tgm, {content_type_customer_group} ctcc WHERE tgm.uid=:uid AND tgm.gid=ctcc.nid AND ctcc.field_customer_account_nid=:raid", array(":uid" => $uid, ":raid" => $row['aid']));
    $result2 = db_query("SELECT gid FROM {ticketing_group_membership} tgm, {field_data_field_customer_account} fca WHERE tgm.uid=:uid AND tgm.gid=fca.entity_id AND fca.field_customer_account_target_id=:raid", array(":uid" => $uid, ":raid" => $row['aid']));
    if($row2 = $result2->fetchAssoc()) {
      // group association still exists
    } else {
      unassignAccount($row['aid'], $uid);
    }
  }
 
  // if no more group association - revoke ticketing roles otherwise inconsistent
  $result = db_query("SELECT * FROM {ticketing_group_membership} WHERE uid=:uid", array(":uid" => $uid));
  if($result->fetchAssoc()==NULL) {
    revokeRole(getRidFromRoleName(ROLE_TICKETING_VIEWER), $uid);
    revokeRole(getRidFromRoleName(ROLE_TICKETING_EDITOR), $uid);
    revokeRole(getRidFromRoleName(ROLE_EXTERNAL_ACCOUNT_ADMINISTRATOR), $uid);
  }  
  BTI_log_user_group_unassign($uid, $group_nid, $accessMode);
}

?>
