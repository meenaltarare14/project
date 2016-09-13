<?php

/*---------------------------------------------------------------------------------------
                             broadsoft_user_management                                  
---------------------------------------------------------------------------------------*/

define("LINE_SEPARATOR", "<b>============================================</b><br>");

/** =============================================================================================
*/
function bum_handle_LDAP_GUI() {
  $action = (empty($_REQUEST['action'])?'':$_REQUEST['action']);
  $email = (empty($_REQUEST['email'])?'':$_REQUEST['email']);
  $groupname = (empty($_REQUEST['groupname'])?'':$_REQUEST['groupname']);
  $sitename = (empty($_REQUEST['sitename'])?'':$_REQUEST['sitename']);
  
  if(!$action) {
    return bum_getRootFormStr($email, $action);
  }
  
  $LDAPServer = new LDAPIntegration();  

  switch($action) {
    // ............................................................................
    case ACTION_LOOKUP_USER: 
    // ............................................................................
      if(!$email) {
        drupal_set_message('Please specify an email', 'warning');
        return bum_getUserActionRootFormStr($email, $action);
      }
      if(!preg_match('/.+@[^@]+\.[^@]{1,}$/', $email)) {
        drupal_set_message('The email provided is not a valid email string', 'error');
        return  bum_getUserActionRootFormStr($email, $action);
      }
      $LDAPUserData = $LDAPServer->getLDAPUserDataFromEmail($email);
      $html = bum_getRootFormStr($email, $action);
      if($LDAPUserData) {
        $html  .= LINE_SEPARATOR;
        $html  .= "<b>LDAP User data for ".$email.":</b><br>";
        $html  .= LINE_SEPARATOR;
        $html  .= bum_getUserDataStr($LDAPUserData);
        $html  .= LINE_SEPARATOR;
        $html  .= "<b>LDAP Groups ".$email." is a member of:</b><br>";
        $html  .= LINE_SEPARATOR;
        $LDAPGroupList = $LDAPServer->getLDAPGroupList(); 
        if($LDAPGroupList) {
          $userDN = $LDAPServer->getUserDnFromEmail($email);
          foreach($LDAPGroupList as $key => $val) {
            $LDAPGroupName = preg_replace('/,.*/', '', preg_replace('/.*cn=/', '', $val));
            if(!empty($LDAPGroupName) && $LDAPServer->userIsInGroup($LDAPGroupName, $userDN)) {
              // ADMIN internal groups: using brackets [] for site name
              if(preg_match('/ADMIN_/', $LDAPGroupName)) {
                if(preg_match('/ADMIN_cPBX_/', $LDAPGroupName) )
                  $html  .= preg_replace('/ADMIN_cPBX_/', '[cPBX] ', $LDAPGroupName)."<br>";
                elseif(preg_match('/ADMIN_Extranet_/', $LDAPGroupName))
                  $html  .= preg_replace('/ADMIN_Extranet_/', '[Extranet] ', $LDAPGroupName)."<br>";
                elseif(preg_match('/ADMIN_BEN_/', $LDAPGroupName))
                  $html  .= preg_replace('/ADMIN_BEN_/', '[Extranet] ', $LDAPGroupName)."<br>";
                elseif(preg_match('/ADMIN_Xchange_/', $LDAPGroupName))
                  $html  .= preg_replace('/ADMIN_Xchange_/', '[Xchange] ', $LDAPGroupName)."<br>";
                elseif(preg_match('/ADMIN_cloudDashboard_/', $LDAPGroupName))
                  $html  .= preg_replace('/ADMIN_cloudDashboard_/', '[cloudDashboard] ', $LDAPGroupName)."<br>";
                elseif(preg_match('/ADMIN_Xtended_/', $LDAPGroupName))
                  $html  .= preg_replace('/ADMIN_Xtended_/', '[xtended] ', $LDAPGroupName)."<br>";
                elseif(preg_match('/ADMIN_Interop_/', $LDAPGroupName))
                  $html  .= preg_replace('/ADMIN_Interop_/', '[interop] ', $LDAPGroupName)."<br>";
                else
                  $html  .= preg_replace('/ADMIN_/', '[All Sites] ', $LDAPGroupName)."<br>";
              } elseif(preg_match('/'.CPBX_GROUP_NAME_PREFIX.'(.*)/', $LDAPGroupName, $matches)) {
                  $html  .= '[cPBX] '.$matches[1]."<br>";
              } else {
                // not ADMIN internal groups: using parenthesis () for site name
                if(preg_match('/'.EXTRANET_GROUP_NAME_PREFIX.'/', $LDAPGroupName))
                  $html  .= preg_replace('/'.EXTRANET_GROUP_NAME_PREFIX.'/', '(Extranet) ', $LDAPGroupName)."<br>";
                else
                  $html  .= '(Xchange) '.$LDAPGroupName."<br>";
              }
            }
          }
        }
        return $html;
      } else
        drupal_set_message(USER_NOT_FOUND_ON_LDAP_STR.' ('.$email.')', 'error');
        return '';
      break;      
    // ............................................................................
    case RESET_USER_PASSWORD:
    // ............................................................................
      if(!$email) {
        drupal_set_message('Please specify an email', 'warning');
        return bum_getUserActionRootFormStr($email, $action, $groupname);
      }
      if(!preg_match('/.+@[^@]+\.[^@]{1,}$/', $email)) {
        drupal_set_message('The email provided is not a valid email string', 'error');
        return bum_getUserActionRootFormStr($email, $action, $groupname);
      }
      if($password = $LDAPServer->resetPassword($email)) {
        bum_SendPasswordResetEmail($email, $password);
        drupal_set_message('Password has been reset to '.$password.' for User account ('.$email.'). An email has been sent to the user with the new credentials.');
      } else
        drupal_set_message('Error while resetting password for the User account ('.$email.')', 'error');
      return bum_getRootFormStr($email, $action);
      break;          
    // ............................................................................
    case ACTION_DEACTIVATE_USER:
    // ............................................................................
      if(!$email) {
        drupal_set_message('Please specify an email', 'warning');
        return bum_getUserActionRootFormStr($email, $action, $groupname);
      }
      if(!preg_match('/.+@[^@]+\.[^@]{1,}$/', $email)) {
        drupal_set_message('The email provided is not a valid email string', 'error');
        return bum_getUserActionRootFormStr($email, $action, $groupname);
      }
      $groupname = DEACTIVATED_USERS_GROUP_NAME__LDAP;
      $LDAPServer->createGroup($groupname); 
      if($LDAPServer->grantLDAPGroupMembership($email, $groupname))
        drupal_set_message('User account ('.$email.') has been DEActivated');
      else
        drupal_set_message('Error while deactivating the User account ('.$email.')', 'error');
      return bum_getRootFormStr($email, $action);
      break;          
    // ............................................................................
    case ACTION_REACTIVATE_USER:
    // ............................................................................
      if(!$email) {
        drupal_set_message('Please specify an email', 'warning');
        return bum_getUserActionRootFormStr($email, $action, $groupname);
      }
      if(!preg_match('/.+@[^@]+\.[^@]{1,}$/', $email)) {
        drupal_set_message('The email provided is not a valid email string', 'error');
        return bum_getUserActionRootFormStr($email, $action, $groupname);
      }
      $groupname = DEACTIVATED_USERS_GROUP_NAME__LDAP;
      $LDAPServer->createGroup($groupname); 
      if($LDAPServer->revokeLDAPGroupMembership($email, $groupname))
        drupal_set_message('User account ('.$email.') has been REActivated');
      else
        drupal_set_message('Error while REActivating the User account ('.$email.')', 'error');
      return bum_getRootFormStr($email, $action);
      break;          
    // ............................................................................
    case ACTION_GET_XCHANGE_GROUP_USERS: 
    case ACTION_GET_EXTRANET_GROUP_USERS: 
    // ............................................................................
      if(!$groupname) {
        drupal_set_message('Please specify a group name', 'warning');
        return bum_getGroupActionRootFormStr($action, $groupname);
      }
      // action-specific: group-mangling
      $internal_groupname = NULL;
      switch($action) {
        case ACTION_GET_XCHANGE_GROUP_USERS:  $internal_groupname=$groupname;                             break;
        case ACTION_GET_EXTRANET_GROUP_USERS: $internal_groupname=EXTRANET_GROUP_NAME_PREFIX.$groupname;  break;
      }

      // check if group exists in the first place      
      $data = $LDAPServer->getLDAPGroupList($internal_groupname);
      if(!$data) {
        drupal_set_message('Group '.$groupname.' does not exist', 'error');
        // does not exist on LDAP
        return bum_getRootFormStr($email, $action);
      } else {
        $LDAPUserList = $LDAPServer->getLDAPGetUsersInGroup($internal_groupname);
        $html = bum_getRootFormStr($email, $action);

        $html  .= LINE_SEPARATOR;
        $html  .= "<b>LDAP User **CNs** in group ".$groupname.":</b><br>";
        $html  .= LINE_SEPARATOR;
        if($LDAPUserList) {
          foreach($LDAPUserList as $key => $val) {
            if(!empty($val))
              $html  .= $val."<br>";
          }
        } else {
          $html  .= "Group ".$groupname." is empty<br>";
        }

        return $html;
      }

      break;
    // ............................................................................
    case ACTION_GET_XCHANGE_GROUP_LIST:
    // ............................................................................
      $html = bum_getRootFormStr($email, $action);
      $html .= bum_getXchangeGroupListDataStr(NULL);
      return $html;
      break;
    // ............................................................................
    case ACTION_GET_EXTRANET_GROUP_LIST:
    // ............................................................................
      $html = bum_getRootFormStr($email, $action);
      $LDAPGroupList = $LDAPServer->getLDAPGroupList(EXTRANET_GROUP_NAME_PREFIX); 
      if($LDAPGroupList)
        $html .= bum_getGroupListDataStr($LDAPGroupList, EXTRANET_GROUP_NAME_PREFIX);
      else
        drupal_set_message(NO_GROUP_FOUND_ON_LDAP_STR, 'warning');
      return $html;
      break;

    // ............................................................................
    // ALL Grant & revoke actions for USER-PROVIDED groups - grouping because all same processing...
    // ............................................................................
    case ACTION_ADD_USER_TO_XCHANGE_GROUP:
    case ACTION_REMOVE_USER_FROM_XCHANGE_GROUP:
    case ACTION_ADD_USER_TO_EXTRANET_GROUP:
    case ACTION_REMOVE_USER_FROM_EXTRANET_GROUP:
    case ACTION_ADD_USER_TO_CPBX_GROUP:
    case ACTION_REMOVE_USER_FROM_CPBX_GROUP:
      // ............................................................................
      if(!$email) {
        drupal_set_message('Please specify an email', 'warning');
        return bum_getUserGroupActionRootFormStr($email, $action, $groupname);
      }
      if(!preg_match('/.+@[^@]+\.[^@]{1,}$/', $email)) {
        drupal_set_message('The email provided is not a valid email string', 'error');
        return bum_getUserGroupActionRootFormStr($email, $action, $groupname);
      }
      if(!$groupname) {
        drupal_set_message('Please specify a group name', 'warning');
        return bum_getUserGroupActionRootFormStr($email, $action, $groupname);
      }
      
      // action-specific: group-mangling + grant-vs-revoke
      $internal_groupname = NULL;
      $grant = FALSE; // default action is revoke
      switch($action) {
        case ACTION_ADD_USER_TO_XCHANGE_GROUP:                $internal_groupname=$groupname;                             $grant=TRUE;    break;
        case ACTION_REMOVE_USER_FROM_XCHANGE_GROUP:           $internal_groupname=$groupname;                             $grant=FALSE;   break;
        case ACTION_ADD_USER_TO_EXTRANET_GROUP:               $internal_groupname=EXTRANET_GROUP_NAME_PREFIX.$groupname;  $grant=TRUE;    break;
        case ACTION_REMOVE_USER_FROM_EXTRANET_GROUP:          $internal_groupname=EXTRANET_GROUP_NAME_PREFIX.$groupname;  $grant=FALSE;   break;
        case ACTION_ADD_USER_TO_CPBX_GROUP:                   $internal_groupname=CPBX_GROUP_NAME_PREFIX.$groupname;      $grant=TRUE;    break;
        case ACTION_REMOVE_USER_FROM_CPBX_GROUP:              $internal_groupname=CPBX_GROUP_NAME_PREFIX.$groupname;      $grant=FALSE;   break;
      }
            
      $LDAPServer->createGroup($internal_groupname); // if first-time group-referencing, group might not exist yet on LDAP... create it
      if($grant) {
        if($LDAPServer->grantLDAPGroupMembership($email, $internal_groupname))
          drupal_set_message('User ('.$email.') has been added to group ('.$groupname.')');
        else
          drupal_set_message('Error while adding the User ('.$email.') to group ('.$groupname.')', 'error');
      } else {
        if($LDAPServer->revokeLDAPGroupMembership($email, $internal_groupname))
          drupal_set_message('User ('.$email.') has been removed from group ('.$groupname.')');
        else
          drupal_set_message('Error while removing the User ('.$email.') from group ('.$groupname.')', 'error');          
      }

      return bum_getRootFormStr($email, $action);
      break;          

    // ............................................................................
    // ALL Grant & revoke actions for Site-specific - grouping because all same processing...
    // ............................................................................
    case ACTION_GRANT_POWER_USER_RIGHTS:
    case ACTION_REVOKE_POWER_USER_RIGHTS:
    // ............................................................................
      if(!$email) {
        drupal_set_message('Please specify an email', 'warning');
        return bum_getUserSiteActionRootFormStr($email, $action);
      }
      if(!preg_match('/.+@[^@]+\.[^@]{1,}$/', $email)) {
        drupal_set_message('The email provided is not a valid email string', 'error');
        return bum_getUserSiteActionRootFormStr($email, $action);
      }
      if(!$sitename) {
        drupal_set_message('Please specify a site name', 'warning');
        return bum_getUserSiteActionRootFormStr($email, $action);
      }
      // using an array to diminish error: if, one day, we add a new site option, 
      // it will get processed in the revoke (below) automatically
      $internalNames =  array(  'Xchange' => BUM_XCHANGE_POWER_USERS__LDAP,
                                'Extranet' => BUM_EXTRANET_POWER_USERS__LDAP,
                                'cPBX' => BUM_CPBX_POWER_USERS__LDAP,
                                'cloudDashboard' => BUM_CLOUD_DASHBOARD_POWER_USERS__LDAP,
                                'Xtended' => BUM_XTENDED_POWER_USERS__LDAP,
                                'Interop' => BUM_INTEROP_POWER_USERS__LDAP,
                                'ALL sites' => BUM_ALL_SITES_POWER_USERS__LDAP,
                        );
      if(!array_key_exists($sitename, $internalNames)) 
        drupal_set_message('Unknown site ('.$sitename.') - code update required');
      else {
        $internal_sitegroupname = $internalNames[$sitename];
      
        $grant = FALSE; // default action is revoke
        if($action==ACTION_GRANT_POWER_USER_RIGHTS)
          $grant=TRUE;
              
        if($grant) {
          $LDAPServer->createGroup($internal_sitegroupname); // if first-time site-referencing, group might not exist yet on LDAP... create it
          if($LDAPServer->grantLDAPGroupMembership($email, $internal_sitegroupname))
            drupal_set_message('User ('.$email.') is now a power user for ('.$sitename.')');      
          else
            drupal_set_message('Error while adding the User ('.$email.') to group ('.$internal_sitegroupname.')', 'error');
        } else {
          if($sitename=='ALL sites') {
            foreach($internalNames as $sitename => $internal_sitegroupname) {
              $LDAPServer->createGroup($internal_sitegroupname); // if first-time site-referencing, group might not exist yet on LDAP... create it
              if($LDAPServer->revokeLDAPGroupMembership($email, $internal_sitegroupname))
                drupal_set_message('User ('.$email.') is no longer a power user for ('.$sitename.')');      
              else
                drupal_set_message('Error while removing the User ('.$email.') from group ('.$internal_sitegroupname.')', 'error');          
            }
          } else {
            $LDAPServer->createGroup($internal_sitegroupname); // if first-time site-referencing, group might not exist yet on LDAP... create it
            if($LDAPServer->revokeLDAPGroupMembership($email, $internal_sitegroupname))
              drupal_set_message('User ('.$email.') is no longer a power user for ('.$sitename.')');      
            else
              drupal_set_message('Error while removing the User ('.$email.') from group ('.$sitename.')', 'error');          
          }
        }
      }
      return bum_getRootFormStr($email, $action);
      break;          

    // ............................................................................
    // ALL Grant & revoke actions for HARD-CODED groups - grouping because all same processing...
    // ............................................................................
    case ACTION_GRANT_EXTRANET_GROUP_MANAGEMENT_RIGHTS:
    case ACTION_REVOKE_EXTRANET_GROUP_MANAGEMENT_RIGHTS:
    case ACTION_GRANT_XCHANGE_USER_MANAGEMENT_RIGHTS:
    case ACTION_REVOKE_XCHANGE_USER_MANAGEMENT_RIGHTS:
    case ACTION_GRANT_CPBX_CONTENT_EDITOR_RIGHTS:
    case ACTION_REVOKE_CPBX_CONTENT_EDITOR_RIGHTS:
    case ACTION_GRANT_CLOUD_DASHBOARD_CONTENT_EDITOR_RIGHTS:
    case ACTION_REVOKE_CLOUD_DASHBOARD_CONTENT_EDITOR_RIGHTS:
    case ACTION_GRANT_XTENDED_CONTENT_EDITOR_RIGHTS:
    case ACTION_REVOKE_XTENDED_CONTENT_EDITOR_RIGHTS:
    case ACTION_GRANT_INTEROP_CONTENT_EDITOR_RIGHTS:
    case ACTION_REVOKE_INTEROP_CONTENT_EDITOR_RIGHTS:
    // ............................................................................
      if(!$email) {
        drupal_set_message('Please specify an email', 'warning');
        return bum_getUserActionRootFormStr($email, $action);
      }
      if(!preg_match('/.+@[^@]+\.[^@]{1,}$/', $email)) {
        drupal_set_message('The email provided is not a valid email string', 'error');
        return bum_getUserActionRootFormStr($email, $action);
      }
      // action-specific: group and grant-vs-revoke
      $groupname = NULL;
      $grant = FALSE; // default action is revoke
      switch($action) {
        case ACTION_GRANT_EXTRANET_GROUP_MANAGEMENT_RIGHTS:       $groupname=BUM_EXTRANET_GROUP_MANAGERS__LDAP;     $grant=TRUE;    break;
        case ACTION_REVOKE_EXTRANET_GROUP_MANAGEMENT_RIGHTS:      $groupname=BUM_EXTRANET_GROUP_MANAGERS__LDAP;     $grant=FALSE;   break;
        
        case ACTION_GRANT_XCHANGE_USER_MANAGEMENT_RIGHTS:         $groupname=BUM_XCHANGE_USER_MANAGERS__LDAP;       $grant=TRUE;    break;
        case ACTION_REVOKE_XCHANGE_USER_MANAGEMENT_RIGHTS:        $groupname=BUM_XCHANGE_USER_MANAGERS__LDAP;       $grant=FALSE;   break;
        
        case ACTION_GRANT_CPBX_CONTENT_EDITOR_RIGHTS:             $groupname=BUM_CPBX_CONTENT_EDITOR__LDAP;     $grant=TRUE;    break;
        case ACTION_REVOKE_CPBX_CONTENT_EDITOR_RIGHTS:            $groupname=BUM_CPBX_CONTENT_EDITOR__LDAP;     $grant=FALSE;    break;
        
        case ACTION_GRANT_CLOUD_DASHBOARD_CONTENT_EDITOR_RIGHTS:  $groupname=BUM_CLOUD_DASHBOARD_CONTENT_EDITOR__LDAP;     $grant=TRUE;    break;
        case ACTION_REVOKE_CLOUD_DASHBOARD_CONTENT_EDITOR_RIGHTS: $groupname=BUM_CLOUD_DASHBOARD_CONTENT_EDITOR__LDAP;     $grant=FALSE;    break;
        
        case ACTION_GRANT_XTENDED_CONTENT_EDITOR_RIGHTS:             $groupname=BUM_XTENDED_CONTENT_EDITOR__LDAP;     $grant=TRUE;    break;
        case ACTION_REVOKE_XTENDED_CONTENT_EDITOR_RIGHTS:            $groupname=BUM_XTENDED_CONTENT_EDITOR__LDAP;     $grant=FALSE;    break;
        
        case ACTION_GRANT_INTEROP_CONTENT_EDITOR_RIGHTS:             $groupname=BUM_INTEROP_CONTENT_EDITOR__LDAP;     $grant=TRUE;    break;
        case ACTION_REVOKE_INTEROP_CONTENT_EDITOR_RIGHTS:            $groupname=BUM_INTEROP_CONTENT_EDITOR__LDAP;     $grant=FALSE;    break;
        
      }
      
      if($groupname) {
        $LDAPServer->createGroup($groupname); // if first-time group-referencing, group might not exist yet on LDAP... create it
        if($grant) {
          if($LDAPServer->grantLDAPGroupMembership($email, $groupname))
            drupal_set_message('User ('.$email.') has been added to group ('.$groupname.')');
          else
            drupal_set_message('Error while adding the User ('.$email.') to group ('.$groupname.')', 'error');
        } else {
          if($LDAPServer->revokeLDAPGroupMembership($email, $groupname))
            drupal_set_message('User ('.$email.') has been removed from group ('.$groupname.')');
          else
            drupal_set_message('Error while removing the User ('.$email.') from group ('.$groupname.')', 'error');          
        }
      }      
      return bum_getRootFormStr($email, $action);
      break;          
      
    // ............................................................................
    default:
      drupal_set_message(UNIMPLEMENTED_YET_STR, 'error');
      return bum_getRootFormStr($email, $action);
  }
}

/** =============================================================================================
*/
function bum_getUserDataStr($LDAPDataArr, $html=TRUE) {
  $eol = ($html?"<br>":"\n");
  $resStr = "";
  foreach($LDAPDataArr as $key => $val) {
    if(preg_match('/[a-zA-Z]/', $key)) { // discard numeric keys
      // some fields to discard or hide
      switch($key) {
        case 'objectclass':
        case 'userpassword':
        case 'count':
          break;
        // add specific hover text for important fields only
        case 'mail':
          $resStr .= '<div title="mail is the user identifier for login">'.($html?"<B>":"").$key.($html?"</B>":"")." => ".$val[0].'</div>'.$eol;
          break;
        case 'uidnumber':
          $resStr .= '<div title="uidnumber is the unique Drupal ID for a single user, across sites">'.($html?"<B>":"").$key.($html?"</B>":"")." => ".$val[0].'</div>'.$eol;
          break;
        case 'dn':
          $resStr .= '<div title="dn is the LDAP identity for this user">'.($html?"<B>":"").$key.($html?"</B>":"")." => ".$val.'</div>'.$eol; // dn is not an array, no clue why
          break;
        case 'description':
          $resStr .= $key.$eol;
          foreach($val as $i => $v)
            if(preg_match('/[a-zA-Z]/', $v)) // filter out garbage
              $resStr .= ' - '.$v.$eol;
          $resStr .= $eol;
          break;
        default:          
          $resStr .= $key." => ".$val[0].$eol;
      }
    }
  }
  return $resStr;
}

/** =============================================================================================
*/
function bum_getGroupListDataStr($LDAPDataArr, $nameFilter='', $html=TRUE) {
  $eol = ($html?"<br>":"\n");
  $resStr = LINE_SEPARATOR;
  $resStr .= "This is the list of Extranet LDAP group names:".$eol;
  $resStr .= LINE_SEPARATOR;
  foreach($LDAPDataArr as $key => $val) {
    if(!empty($val)) {
      $LDAPGroupName = preg_replace('/,.*/', '', preg_replace('/.*cn=/', '', $val));
      $LDAPGroupName = preg_replace('/'.$nameFilter.'/', '', $LDAPGroupName);
      $resStr .= $LDAPGroupName.$eol;
    }
  }
  return $resStr;
}

/** =============================================================================================
* This function uses a hard-coded list for now as there is currently no way to identify Xchange groups from other groups...
*/
function bum_getXchangeGroupListDataStr($LDAPDataArr, $html=TRUE) {
  $eol = ($html?"<br>":"\n");
  $resStr = LINE_SEPARATOR;
  $resStr .= "This is the list of Xchange LDAP group names:".$eol;
  $resStr .= LINE_SEPARATOR;

  $resStr .= 'internal'.$eol;
  $resStr .= 'writedocuments'.$eol; 
  $resStr .= 'ChannelPartners'.$eol; 
  $resStr .= 'SystemPartners'.$eol;  
  $resStr .= 'InteropPartners'.$eol; 
//  $resStr .= 'Manager'.$eol; // that is the User Manager Right itself...
  $resStr .= 'Software'.$eol; 
  $resStr .= 'M6Customers'.$eol; 
  $resStr .= 'PSCustomers'.$eol; 
  $resStr .= 'SynergyCustomers'.$eol; 
  $resStr .= 'LimitedUser'.$eol; 
  $resStr .= 'Prospect'.$eol; 

  return $resStr;
}

/* ======================================================================= */
function bs_SendEmail($subject, $body, $rcpt, $cc, $fileName = NULL) {
	if($fileName)
		return bs_SendEmailWithAttachment($subject, $body, $rcpt, $cc, $fileName);
	else {
	  // apparently this is not the purest way to send an email in Drupal, see ref above
	  $message = array(
	    'to'    => $rcpt,
	    'subject' => t($subject),
	    'body'  => t($body),
	    'headers' => array('From' => "xchange@broadsoft.com"),
	  );

	  if(!empty($cc)) 
	    $message['headers']['Cc'] = $cc;

//	  drupal_mail_send($message);
    drupal_mail('broadsoft_user_management', 'broadsoft_ldap', $rcpt, language_default(), $message, 'xchange@broadsoft.com', TRUE);
	  return 0;
	}
}

/* ======================================================================= */
function bs_SendEmailWithAttachment($subject, $body, $rcpt, $cc, $fileName) {
  $bound_text = md5(uniqid(time()));
  $bound = "--".$bound_text."\r\n";
  $bound_last = "--".$bound_text."--\r\n";

  $headers =   "From: BroadSoft Xchange <xchangesupport@broadsoft.com>";
  if(!empty($cc)) {
    $headers .= "\r\n";
    $headers .= "Cc: ".$cc;
  }
  $headers .= "\r\n";
  $headers .=  "MIME-Version: 1.0\r\n"
    ."Content-Type: multipart/mixed; boundary=\"$bound_text\"";

  $message  = "If you can see this MIME than your client doesn't accept MIME types!\r\n"
    .$bound
    ."Content-Type: text/plain; charset=us-ascii\"\r\n"
    ."Content-Transfer-Encoding: 7bit\r\n\r\n"
    .$body
    ."\r\n"
    .$bound;

  $file = file_get_contents($fileName);

  $message .=  "Content-Type: application/msword; name=\"".$fileName."\"\r\n"
    ."Content-Transfer-Encoding: base64\r\n"
    ."Content-disposition: attachment; file=\"".$fileName."\"\r\n"
    ."\r\n"
    .chunk_split(base64_encode($file))
    .$bound_last;

  return mail($rcpt, $subject, $message, $headers);
}


?>
