<?php

/** =============================================================================================
  called through API call
*/
function get_ticket_list($params) {
  $ret = NULL;
  $count = 0;
  if($params['format']=='html')
    $ret = BTI_GetTicketTable_HtmlStr($params['state'], $count, $params['numLatest']);
  elseif($params['format']=='json') {
    $retArr = array();
    $retArr['html'] = BTI_GetTicketTable_HtmlStr($params['state'], $count, $params['numLatest']);
    $retArr['count'] = $count;
    drupal_json_output($retArr);
    exit;
  }

  return $ret;
}

/** =============================================================================================*/
function ticketing_landing_page_form($form_state, $form_values) {
  if(blockTicketingOFFLINE())
    return;

  global $user;
  $showPreferencesForm = showPrototype();
  $showAdminForm = showPrototype();
  $showAdvancedForm = false;//@@@showPrototype(); something BREAKS the jql tabs in the advanced form!!

  if(userIsAdministrator()) {
    $ticketingService = getTicketingService();
    list($usec, $sec) = explode(" ", microtime());
    $time1 = ((float)$usec + (float)$sec);
    $jiraIsAlive = $ticketingService->serverIsAlive();
    list($usec, $sec) = explode(" ", microtime());
    $time2 = ((float)$usec + (float)$sec);
    drupal_set_message('Using Jira '.trim(variable_get('ticketing_server', TICKETING_SERVER_ADDRESS)).' - Server status: '.($jiraIsAlive?'Alive':'UNREACHABLE').' [Jira response time: '.number_format($time2-$time1, 3).' seconds]', 'notice');
  }

  // additional access check verification
  if(!(userIsAdministrator()||bsutil_user_has_role(ROLE_TICKETING_ADMIN, $user)||bsutil_user_has_role(ROLE_TICKETING_VIEWER, $user)))  {
    drupal_set_message('You are not allowed to perform this operation', 'error');
    return NULL;
  }

  $form = array();
  $markupID = 0;

  // 3 column table design
  $bkgColor = 'WHITE';
  $form['markup'.$markupID++] = makeMarkupElement('<table border="0" style="background-color:'.$bkgColor.';">');
  // row:
  $form['markup'.$markupID++] = makeMarkupElement('<tr style="background-color:'.$bkgColor.';">');
  // col (row1): CUSTOMER-GROUP ...........

  $AssignedCustomerGroups = AssignedCustomerGroups($user, VIEW_GROUP_ACCESS);
  if(count($AssignedCustomerGroups)==0) {
    drupal_set_message('You are not configured properly to use Xchange ticketing', 'error');
    return NULL;
  } elseif(count($AssignedCustomerGroups)==1) {
    // store on server against user
    $grpNid = key($AssignedCustomerGroups);
    $UserTicketingPreferences = user_data(USER_DATA__TICKETING_PREFERENCES);
    $UserTicketingPreferences['selectedCustomerGroupNid'] = $grpNid;
    user_data(USER_DATA__TICKETING_PREFERENCES, $UserTicketingPreferences);
    $form['markup'.$markupID++] = makeMarkupElement('<td width="33%" style="background-color:'.$bkgColor.';"><div align="left">'); // create empty table col
  } else {
    $gid2titles = array();
    foreach($AssignedCustomerGroups as $gid => $data) {
      $gid2titles[$gid] = $data['title'];
    }

    // store selected or dflt group
    $UserTicketingPreferences = user_data(USER_DATA__TICKETING_PREFERENCES);
    // default: select GET[cg] OR first option
    if(!empty($_GET['cg'])) {
      $UserTicketingPreferences['selectedCustomerGroupNid'] = $_GET['cg'];
    } else {
      reset($AssignedCustomerGroups); // otherwise key() below will return null
      $UserTicketingPreferences['selectedCustomerGroupNid'] = key($AssignedCustomerGroups);
    }
    user_data(USER_DATA__TICKETING_PREFERENCES, $UserTicketingPreferences);
    // create dropdown with customer-group options
    $div_ID = 'CustomerSelect';

    $form['markup'.$markupID++] = makeMarkupElement('<td width="33%" style="background-color:'.$bkgColor.';"><div align="left">');
    $form[$div_ID] = genSelectFormArray(  $markupID,
                                          TICKETING_API_DIRECT_CUSTOMER,
                                          "inner_fieldset",
                                          FORM_FIELD_DIRECT_CUSTOMER,
                                          false,
                                          true,
                                          false,
                                          $gid2titles,
                                          $UserTicketingPreferences['selectedCustomerGroupNid']);
    $js_fct = "
      function ".TICKETING_API_DIRECT_CUSTOMER."_Change(selectObj) {
        var selectIndex = selectObj.selectedIndex;
        var selectValue = selectObj.options[selectIndex].value;
        var desturl = '".bs_get_site_base_path().BASEURL_TICKETING_LANDING."?cg=';
        window.location.href = desturl.concat(selectValue);
      }";

    drupal_add_js($js_fct, 'inline');
  }
  $form['markup'.$markupID++] = makeMarkupElement('</div></td>');

  // col (row2):
  $form['markup'.$markupID++] = makeMarkupElement('<td width="33%" style="background-color:'.$bkgColor.';"><div align="center">');
  if(bsutil_user_has_role(ROLE_TICKETING_EDITOR, $user)) {
    $form['markup'.$markupID] = array(
        '#type' => 'submit',
        '#value' => t('ADD A TICKET'),
        '#submit' => array('BTI_AddTicket'),
        '#attributes' => array('title' => "Opens the form for Ticket Creation"), // mouse over help
      );
    if(array_element('AddTicketTarget', $UserTicketingPreferences) == 'new')
      $form['markup'.$markupID]['#attributes']['onclick'] = 'this.form.target="_blank";return true;';
    $markupID++;
  }
  $form['markup'.$markupID++] = makeMarkupElement('</div></td>');

  // small css adjustments to make buttons & text on this page similar to the jquery-ui tabs...
  drupal_add_css('.form-text {height: 30px;}  ', 'inline');
  drupal_add_css('.form-submit {font-weight: bold;}  ', 'inline');
  drupal_add_css('.form-submit {font-size: 120%;}  ', 'inline');

  $MOHelpStr = "Opens the specified ticket. New ticket format is TAC-NNN while historical tickets can still be referenced by their numerical-only Ticket ID (without the TAC- prefix)";
  drupal_add_js ( 'jQuery(document).ready(function(){
    var idbox = document.getElementById("edit-ticketid");
    idbox.addEventListener("focus", function () {
      this.value = "TAC-";
    });
  });' , 'inline' );
  $form['markup'.$markupID++] = makeMarkupElement('<td width="33%" style="background-color:'.$bkgColor.';"><div align="right">');
  $form['ticketID'] = array(
    '#type' => 'textfield',
    '#size' => 10,
    '#maxlength' => 10,
    '#prefix' => '<div class="container-inline">',
//    '#field_prefix' => 'Ticket ID: ', // to put text in front of textfield. Using placeholder instead...
    '#attributes' => array('title' => $MOHelpStr, 'placeholder' => "Ticket ID"),
    );
  $form['ticketID']['#attributes'] = array('title' => $MOHelpStr, 'placeholder' => "TAC-Ticket ID");
  
  drupal_add_js ( '
      function JSValidateGOButton() {
        var idbox = document.getElementById("edit-ticketid");
        if(idbox.value.match(/^TAC-\d\d*$/)) {
          return true;
        } else {
          if(idbox.value.match(/^\d\d*$/)) { // EV ticket IDs
            return true;
          } else 
            alert("Please enter a valid Ticket ID");
        }
        return false;
      }
  ' , 'inline' );

  $form['Go2Ticket'] = array(
    '#type' => 'submit',
    '#value' => t('Go'),
    '#suffix' => '</div>',
    '#submit' => array('BTI_Go2Ticket'),
    '#attributes' => array(
                      'title' => $MOHelpStr,
                      'onClick' => 'return JSValidateGOButton();',
                      ),
  );
  $form['markup'.$markupID++] = makeMarkupElement('</div></td>');

  $form['markup'.$markupID++] = makeMarkupElement('</tr>');
  $form['markup'.$markupID++] = makeMarkupElement('</table>');

  return $form;
}


/** =============================================================================================
 Get ticket list from EV based on the status: open, closed, pending
 Returns an array of the form:  $retArr[0..n][EV_header_name] = val
 */
function BTI_GetTicketList($originatorID, $customerID, &$retNbTicketsShown, &$retTotNbTicketsInCurTab, $status=NULL) {
  $filterArr = array();
  $statusArr = array('open','pending','closed');
  $filterArr['status'] = array();
  $filterArr['status']['open'] = FALSE;
  $filterArr['status']['pending'] = FALSE;
  $filterArr['status']['closed'] = FALSE;

  if(!is_null($status)){
  	if(in_array($status, $statusArr)){
      $filterArr['status'][preg_replace('/[^a-z\-]/', '', $status)] = TRUE;
  	}elseif($status=='all'){
  	  foreach($statusArr as $status_value){
  	  	$filterArr['status'][$status_value] = TRUE;
  	  }
  	}
  }
  
  $filterArr['severity']= array();
  $filterArr['severity']['critical'] = TRUE;
  $filterArr['severity']['major'] = TRUE;
  $filterArr['severity']['minor'] = TRUE;
  $filterArr['severity']['informational'] = TRUE;

  $ticketingService = getTicketingService();
  $ticketList = $ticketingService->getTicketList($filterArr, $originatorID, $customerID, FALSE, FALSE);

  $retNbTicketsShown = count($ticketList);
  $retTotNbTicketsInCurTab = count($ticketList);
	return $ticketList;
}

/** =============================================================================================
  Params:
  - $state: possible values are 'pending', 'open', 'closed'
  - $numLatest: numerical value. Can be a num. Can be 'ALL'. Can be 'init' when $state==closed
*/
//- tot must be 100
define('pending__ID_WIDTH', '7');
define('pending__TITLE_WIDTH', '30');
define('pending__SEVERITY_WIDTH', '10');
define('pending__CONTACT_WIDTH', '10');
define('pending__DATE_WIDTH', '15'); // count=2
define('pending__STATUS_WIDTH', '13');
//- tot must be 100
define('open__ID_WIDTH', '7');
define('open__TITLE_WIDTH', '40');
define('open__SEVERITY_WIDTH', '10');
define('open__CONTACT_WIDTH', '13');
define('open__DATE_WIDTH', '15'); // count=2
//- tot must be 100
define('closed__ID_WIDTH', '7');
define('closed__TITLE_WIDTH', '40');
define('closed__SEVERITY_WIDTH', '10');
define('closed__CONTACT_WIDTH', '13');
define('closed__DATE_WIDTH', '15'); // count=2

/** =============================================================================================
 */
function BTI_GetTicketTable_HtmlStr($state, $ticketList=NULL, $numLatest=0) {
  global $user;
  $UserTicketingPreferences = user_data(USER_DATA__TICKETING_PREFERENCES);

  $retHtmlStr = '';

  if(!$ticketList) {
    get_cached_group_selection($originatorID, $customerID);
    $ticketList = BTI_GetTicketList($originatorID, $customerID, $retNbTicketsShown, $retTotNbTicketsInCurTab, $state);
  }
  $definedAjaxDIV = FALSE;
  if($state=='closed' && $numLatest=='init') {
    $retHtmlStr .= '<div id="divTicketList">'; // ajax div divTicketList: needed to be defined only one time
    $definedAjaxDIV = TRUE;
  }

  if($ticketList && count($ticketList)) {
    // table headers
    $tid = 'sorttable_'.$state.$numLatest;

    $retHtmlStr .= '<table class="sortable" id="'.$tid.'">';
    $retHtmlStr .= '<tr>';
    switch($state) {
      case 'pending':
      case 'all':
        $retHtmlStr .= '<th width="'.pending__ID_WIDTH.'%">ID</th>';
        if(showOldEVIDCol())
          $retHtmlStr .= '<th width="'.pending__ID_WIDTH.'%">EV ID</th>';
        $retHtmlStr .= '<th width="'.pending__TITLE_WIDTH.'%">Title</th>';
        $retHtmlStr .= '<th width="'.pending__SEVERITY_WIDTH.'%">Severity</th>';
        $retHtmlStr .= '<th width="'.pending__CONTACT_WIDTH.'%">Contact</th>';
        $retHtmlStr .= '<th width="'.pending__DATE_WIDTH.'%">Created</th>';
        $retHtmlStr .= '<th width="'.pending__DATE_WIDTH.'%">Last Updated</th>';
        $retHtmlStr .= '<th width="'.pending__STATUS_WIDTH.'%">Status</th>';
        break;
      case 'open':
        $retHtmlStr .= '<th width="'.open__ID_WIDTH.'%">ID</th>';
        if(showOldEVIDCol())
          $retHtmlStr .= '<th width="'.pending__ID_WIDTH.'%">EV ID</th>';
        $retHtmlStr .= '<th width="'.open__TITLE_WIDTH.'%">Title</th>';
        $retHtmlStr .= '<th width="'.open__SEVERITY_WIDTH.'%">Severity</th>';
        $retHtmlStr .= '<th width="'.open__CONTACT_WIDTH.'%">Contact</th>';
        $retHtmlStr .= '<th width="'.open__DATE_WIDTH.'%">Created</th>';
        $retHtmlStr .= '<th width="'.open__DATE_WIDTH.'%">Last Updated</th>';
        break;
      default: // default = closed
        $retHtmlStr .= '<th width="'.closed__ID_WIDTH.'%">ID</th>';
        if(showOldEVIDCol())
          $retHtmlStr .= '<th width="'.pending__ID_WIDTH.'%">EV ID</th>';
        $retHtmlStr .= '<th width="'.closed__TITLE_WIDTH.'%">Title</th>';
        $retHtmlStr .= '<th width="'.closed__SEVERITY_WIDTH.'%">Severity</th>';
        $retHtmlStr .= '<th width="'.closed__CONTACT_WIDTH.'%">Contact</th>';
        $retHtmlStr .= '<th width="'.closed__DATE_WIDTH.'%">Created</th>';
        $retHtmlStr .= '<th width="'.closed__DATE_WIDTH.'%">Closed</th>';
        break;
    }

    $retHtmlStr .= '</tr>';

    $linkTargetStr = '';
    if(!array_key_exists('Preferences', $UserTicketingPreferences))
      $UserTicketingPreferences['Preferences'] = array(
        'TicketLinkTarget' => 'new', // dflt
        'AddTicketTarget' => 'cur',  // dflt
      );
    if($UserTicketingPreferences['Preferences']['TicketLinkTarget'] == 'new')
      $linkTargetStr = 'target="_blank"';

    // process all table rows
    foreach($ticketList as $i => $row) {
      $hrefStrBeg = '<a href="'.bs_get_site_base_path().BASEURL_ONE_TICKET.'/'.$row['ID'].'" '.$linkTargetStr.'>';
      if($i%2)
        $class = 'class="even"';
      else
        $class = 'class="odd"';
      $retHtmlStr .= '<tr '.$class.'>';
      switch($state) {
        case 'pending':
        case 'all':
          $retHtmlStr .= '<td width="'.pending__ID_WIDTH.'%">'.$hrefStrBeg.$row['ID'].'</a></td>';
          if(showOldEVIDCol())
            $retHtmlStr .= '<td width="'.pending__ID_WIDTH.'%">'.$hrefStrBeg.$row['EV_ID'].'</a></td>';
          $retHtmlStr .= '<td width="'.pending__TITLE_WIDTH.'%">'.$hrefStrBeg.$row['SHORT_DESCR'].'</a></td>';
          $retHtmlStr .= '<td width="'.pending__SEVERITY_WIDTH.'%">'.$hrefStrBeg.$row['SEVERITY_LEVEL'].'</a></td>';
          $retHtmlStr .= '<td width="'.pending__CONTACT_WIDTH.'%">'.$hrefStrBeg.$row['CONTACT_NAME'].'</a></td>';
          $retHtmlStr .= '<td width="'.pending__DATE_WIDTH.'%">'.$hrefStrBeg.$row['DATE_CREATED'].'</a></td>';
          $retHtmlStr .= '<td width="'.pending__DATE_WIDTH.'%">'.$hrefStrBeg.$row['TIMESTAMP'].'</a></td>';
          $retHtmlStr .= '<td width="'.pending__STATUS_WIDTH.'%">'.$hrefStrBeg.$row['STATUS'].'</a></td>';
          break;
        case 'open':
          $retHtmlStr .= '<td width="'.open__ID_WIDTH.'%">'.$hrefStrBeg.$row['ID'].'</a></td>';
          if(showOldEVIDCol())
            $retHtmlStr .= '<td width="'.pending__ID_WIDTH.'%">'.$hrefStrBeg.$row['EV_ID'].'</a></td>';
          $retHtmlStr .= '<td width="'.open__TITLE_WIDTH.'%">'.$hrefStrBeg.$row['SHORT_DESCR'].'</a></td>';
          $retHtmlStr .= '<td width="'.open__SEVERITY_WIDTH.'%">'.$hrefStrBeg.$row['SEVERITY_LEVEL'].'</a></td>';
          $retHtmlStr .= '<td width="'.open__CONTACT_WIDTH.'%">'.$hrefStrBeg.$row['CONTACT_NAME'].'</a></td>';
          $retHtmlStr .= '<td width="'.open__DATE_WIDTH.'%">'.$hrefStrBeg.$row['DATE_CREATED'].'</a></td>';
          $retHtmlStr .= '<td width="'.open__DATE_WIDTH.'%">'.$hrefStrBeg.$row['TIMESTAMP'].'</a></td>';
          break;
        default: // default = closed
          $retHtmlStr .= '<td width="'.closed__ID_WIDTH.'%">'.$hrefStrBeg.$row['ID'].'</a></td>';
          if(showOldEVIDCol())
            $retHtmlStr .= '<td width="'.pending__ID_WIDTH.'%">'.$hrefStrBeg.$row['EV_ID'].'</a></td>';
          $retHtmlStr .= '<td width="'.closed__TITLE_WIDTH.'%">'.$hrefStrBeg.$row['SHORT_DESCR'].'</a></td>';
          $retHtmlStr .= '<td width="'.closed__SEVERITY_WIDTH.'%">'.$hrefStrBeg.$row['SEVERITY_LEVEL'].'</a></td>';
          $retHtmlStr .= '<td width="'.closed__CONTACT_WIDTH.'%">'.$hrefStrBeg.$row['CONTACT_NAME'].'</a></td>';
          $retHtmlStr .= '<td width="'.closed__DATE_WIDTH.'%">'.$hrefStrBeg.$row['DATE_CREATED'].'</a></td>';
          $retHtmlStr .= '<td width="'.closed__DATE_WIDTH.'%">'.$hrefStrBeg.$row['DATE_CLOSED'].'</a></td>';
          break;
      }

      $retHtmlStr .= '</tr>';
    }
    $retHtmlStr .= '</table>';

    // pager if relevant
//    $retHtmlStr .= getTicketListAjaxPager(0, 10); //@@@

// to be better handled when paged    $retHtmlStr .= '<br>Showing '.$retNbTicketsShown.' tickets out of '.$retTotNbTicketsInCurTab.' (for the current tab)';
    $retHtmlStr .= '<script type="text/javascript">
                      var newTableObject = document.getElementById(\''.$tid.'\');
                      sorttable.makeSortable(newTableObject);
                    </script>';
  } else {
    $retHtmlStr .= 'There are no ticket matching this criteria';
  }
  if($definedAjaxDIV) {
    $retHtmlStr .= '</div>'; // ajax div divTicketList
    $retHtmlStr .= '<br>';
    $retHtmlStr .= '<a class="ticketingLink" href="'.bs_get_site_base_path().'support/ticketing/xget/30"><button>Show last 30 days</button></a>';
    $retHtmlStr .= '&nbsp&nbsp';
    $retHtmlStr .= '<a class="ticketingLink" href="'.bs_get_site_base_path().'support/ticketing/xget/180"><button>Show last 180 days</button></a>';
    $retHtmlStr .= '&nbsp&nbsp';
    $retHtmlStr .= '<a class="ticketingLink" href="'.bs_get_site_base_path().'support/ticketing/xget/ALL"><button>Show ALL</button></a>';
  }

  return $retHtmlStr;
}

/** =============================================================================================
 */
function dynamic_ticketing_ajax_get($numLatest) {
  $retStr = BTI_GetTicketTable_HtmlStr('closed', NULL, $numLatest);

  // create a JSON object. The object will contain a property named "tickets" that will be set with the $items variable.
  return drupal_json_output(array('tickets'=>$retStr));
  exit;
}

function BTI_get_html_list($state)
{
  if (strlen($state) == 0) {
    $state = 'closed';
  }
  get_cached_group_selection($originatorID, $customerID);
  $ticketList = BTI_GetTicketList($originatorID, $customerID, $retNbTicketsShown, $retTotNbTicketsInCurTab, $state);
  print drupal_json_encode($ticketList);
}

?>
