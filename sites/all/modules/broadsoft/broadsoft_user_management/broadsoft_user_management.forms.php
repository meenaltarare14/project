<?php

/*---------------------------------------------------------------------------------------
                             broadsoft_user_management
                                    forms
---------------------------------------------------------------------------------------*/

define('RESTRICTED_ACCESS_STR', 'You are not authorized to access this page');
define('UNIMPLEMENTED_YET_STR', 'Sorry, this action is not implemented yet');
define('USER_NOT_FOUND_ON_LDAP_STR', 'This user was not found on the LDAP server');
define('NO_GROUP_FOUND_ON_LDAP_STR', 'No group was found on the LDAP server in this category');

define('ACTION_LOOKUP_USER', 'General - Lookup a User');
define('RESET_USER_PASSWORD', 'General - Reset a User Password');
define('ACTION_DEACTIVATE_USER', 'General - DEactivate a User account');
define('ACTION_REACTIVATE_USER', 'General - REactivate a User account');
define('ACTION_GET_EXTRANET_GROUP_LIST', 'Extranet - Get Group List');
define('ACTION_GET_XCHANGE_GROUP_LIST', 'Xchange - Get Group List');
define('ACTION_ADD_USER_TO_XCHANGE_GROUP', 'Xchange - Add a User to a Group');
define('ACTION_ADD_USER_TO_EXTRANET_GROUP', 'Extranet - Add a User to a Group');
define('ACTION_ADD_USER_TO_CPBX_GROUP', 'CPBX - Add a User to a Group');
define('ACTION_REMOVE_USER_FROM_XCHANGE_GROUP', 'Xchange - Remove a User from a Group');
define('ACTION_REMOVE_USER_FROM_EXTRANET_GROUP', 'Extranet - Remove a User from a Group');
define('ACTION_REMOVE_USER_FROM_CPBX_GROUP', 'CPBX - Remove a User from a Group');
define('ACTION_GRANT_EXTRANET_GROUP_MANAGEMENT_RIGHTS', 'Extranet - Grant Group Management Rights');
define('ACTION_REVOKE_EXTRANET_GROUP_MANAGEMENT_RIGHTS', 'Extranet - Revoke Group Management Rights');

define('ACTION_GRANT_CPBX_CONTENT_EDITOR_RIGHTS', 'cPBX - Grant Content Editor Rights');
define('ACTION_REVOKE_CPBX_CONTENT_EDITOR_RIGHTS', 'cPBX - Revoke Content Editor Rights');
define('ACTION_GRANT_CLOUD_DASHBOARD_CONTENT_EDITOR_RIGHTS', 'cloudDashboard - Grant Content Editor Rights');
define('ACTION_REVOKE_CLOUD_DASHBOARD_CONTENT_EDITOR_RIGHTS', 'cloudDashboard - Revoke Content Editor Rights');

define('ACTION_GRANT_XTENDED_CONTENT_EDITOR_RIGHTS', 'Xtended - Grant Content Editor Rights');
define('ACTION_REVOKE_XTENDED_CONTENT_EDITOR_RIGHTS', 'Xtended - Revoke Content Editor Rights');
define('ACTION_GRANT_INTEROP_CONTENT_EDITOR_RIGHTS', 'Interop - Grant Content Editor Rights');
define('ACTION_REVOKE_INTEROP_CONTENT_EDITOR_RIGHTS', 'Interop - Revoke Content Editor Rights');

define('ACTION_GRANT_XCHANGE_USER_MANAGEMENT_RIGHTS', 'Xchange - Grant User Management Rights');
define('ACTION_REVOKE_XCHANGE_USER_MANAGEMENT_RIGHTS', 'Xchange - Revoke User Management Rights');
define('ACTION_GET_XCHANGE_GROUP_USERS', 'Xchange - List members of a Group');
define('ACTION_GET_EXTRANET_GROUP_USERS', 'Extranet - List members of a Group');
define('ACTION_GRANT_POWER_USER_RIGHTS', 'Admin - Grant Power User Rights');
define('ACTION_REVOKE_POWER_USER_RIGHTS', 'Admin - Revoke Power User Rights');

define('EXTRANET_GROUP_NAME_PREFIX', 'BEN_'); // BEN as in Broadsoft Extra Net
define('CPBX_GROUP_NAME_PREFIX', 'CPBX_');

/** =============================================================================================
*/
function bum_getActionsStr($action='') {
  global $user;
  return '        
    <tr>
      <td><label for="action">Action requested *</label></td>
      <td>
        <select name=action>'.
    /*
    @@@ NOTE: The power user role has too much power here. In the context of THIS specific site (BUMs), we should work with permissions or multiple power user roles
    */
        // ..... general actions .....
        '<option '.(($action==ACTION_LOOKUP_USER)?'selected':'').'>'.ACTION_LOOKUP_USER.
        '<option '.(($action==RESET_USER_PASSWORD)?'selected':'').'>'.RESET_USER_PASSWORD.
        '<option '.(($action==ACTION_DEACTIVATE_USER)?'selected':'').'>'.ACTION_DEACTIVATE_USER.
        '<option '.(($action==ACTION_REACTIVATE_USER)?'selected':'').'>'.ACTION_REACTIVATE_USER.          
        // ..... admin actions .....        
        (in_array(ADMINISTRATOR_ROLE_NAME, $user->roles)?'<option class="select-dash" disabled="disabled">--------</option>':'').
        (in_array(ADMINISTRATOR_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_GRANT_POWER_USER_RIGHTS)?'selected':'').'>'.ACTION_GRANT_POWER_USER_RIGHTS:'').
        (in_array(ADMINISTRATOR_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_REVOKE_POWER_USER_RIGHTS)?'selected':'').'>'.ACTION_REVOKE_POWER_USER_RIGHTS:'').
        // ..... Xchange-related actions .....
        (in_array(XCHANGE_USER_MANAGER_ROLE_NAME, $user->roles)?'<option class="select-dash" disabled="disabled">--------</option>':'').
        (in_array(XCHANGE_USER_MANAGER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_ADD_USER_TO_XCHANGE_GROUP)?'selected':'').'>'.ACTION_ADD_USER_TO_XCHANGE_GROUP:'').
        (in_array(XCHANGE_USER_MANAGER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_REMOVE_USER_FROM_XCHANGE_GROUP)?'selected':'').'>'.ACTION_REMOVE_USER_FROM_XCHANGE_GROUP:'').
        (in_array(XCHANGE_USER_MANAGER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_GET_XCHANGE_GROUP_LIST)?'selected':'').'>'.ACTION_GET_XCHANGE_GROUP_LIST:'').
        (in_array(XCHANGE_USER_MANAGER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_GET_XCHANGE_GROUP_USERS)?'selected':'').'>'.ACTION_GET_XCHANGE_GROUP_USERS:'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_GRANT_XCHANGE_USER_MANAGEMENT_RIGHTS)?'selected':'').'>'.ACTION_GRANT_XCHANGE_USER_MANAGEMENT_RIGHTS:'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_REVOKE_XCHANGE_USER_MANAGEMENT_RIGHTS)?'selected':'').'>'.ACTION_REVOKE_XCHANGE_USER_MANAGEMENT_RIGHTS:'').
        // ..... Extranet-related actions .....
        (in_array(EXTRANET_GROUP_MANAGER_ROLE_NAME, $user->roles)?'<option class="select-dash" disabled="disabled">--------</option>':'').
        (in_array(EXTRANET_GROUP_MANAGER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_ADD_USER_TO_EXTRANET_GROUP)?'selected':'').'>'.ACTION_ADD_USER_TO_EXTRANET_GROUP:'').
        (in_array(EXTRANET_GROUP_MANAGER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_REMOVE_USER_FROM_EXTRANET_GROUP)?'selected':'').'>'.ACTION_REMOVE_USER_FROM_EXTRANET_GROUP:'').
        (in_array(EXTRANET_GROUP_MANAGER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_GET_EXTRANET_GROUP_LIST)?'selected':'').'>'.ACTION_GET_EXTRANET_GROUP_LIST:'').
        (in_array(EXTRANET_GROUP_MANAGER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_GET_EXTRANET_GROUP_USERS)?'selected':'').'>'.ACTION_GET_EXTRANET_GROUP_USERS:'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_GRANT_EXTRANET_GROUP_MANAGEMENT_RIGHTS)?'selected':'').'>'.ACTION_GRANT_EXTRANET_GROUP_MANAGEMENT_RIGHTS:'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_REVOKE_EXTRANET_GROUP_MANAGEMENT_RIGHTS)?'selected':'').'>'.ACTION_REVOKE_EXTRANET_GROUP_MANAGEMENT_RIGHTS:'').
        // ..... cPBX-related actions .....
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option class="select-dash" disabled="disabled">--------</option>':'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_GRANT_CPBX_CONTENT_EDITOR_RIGHTS)?'selected':'').'>'.ACTION_GRANT_CPBX_CONTENT_EDITOR_RIGHTS:'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_REVOKE_CPBX_CONTENT_EDITOR_RIGHTS)?'selected':'').'>'.ACTION_REVOKE_CPBX_CONTENT_EDITOR_RIGHTS:'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_ADD_USER_TO_CPBX_GROUP)?'selected':'').'>'.ACTION_ADD_USER_TO_CPBX_GROUP:'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_REMOVE_USER_FROM_CPBX_GROUP)?'selected':'').'>'.ACTION_REMOVE_USER_FROM_CPBX_GROUP:'').
        
         // ..... Cloud Dashboard - related actions .....
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option class="select-dash" disabled="disabled">--------</option>':'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_GRANT_CLOUD_DASHBOARD_CONTENT_EDITOR_RIGHTS)?'selected':'').'>'.ACTION_GRANT_CLOUD_DASHBOARD_CONTENT_EDITOR_RIGHTS:'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_REVOKE_CLOUD_DASHBOARD_CONTENT_EDITOR_RIGHTS)?'selected':'').'>'.ACTION_REVOKE_CLOUD_DASHBOARD_CONTENT_EDITOR_RIGHTS:'').
        
         // ..... Xtended - Interop - related actions .....
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option class="select-dash" disabled="disabled">--------</option>':'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_GRANT_XTENDED_CONTENT_EDITOR_RIGHTS)?'selected':'').'>'.ACTION_GRANT_XTENDED_CONTENT_EDITOR_RIGHTS:'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_REVOKE_XTENDED_CONTENT_EDITOR_RIGHTS)?'selected':'').'>'.ACTION_REVOKE_XTENDED_CONTENT_EDITOR_RIGHTS:'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option class="select-dash" disabled="disabled">--------</option>':'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_GRANT_INTEROP_CONTENT_EDITOR_RIGHTS)?'selected':'').'>'.ACTION_GRANT_INTEROP_CONTENT_EDITOR_RIGHTS:'').
        (in_array(POWER_USER_ROLE_NAME, $user->roles)?'<option '.(($action==ACTION_REVOKE_INTEROP_CONTENT_EDITOR_RIGHTS)?'selected':'').'>'.ACTION_REVOKE_INTEROP_CONTENT_EDITOR_RIGHTS:'').

        '</select>
      </td>
    </tr>
';
}

/** =============================================================================================
*/
function bum_getGroupActionRootFormStr($action='', $groupname='') {
  if(!user_access(USER_MANAGEMENT_PERMISSION_NAME)) {
    drupal_set_message(RESTRICTED_ACCESS_STR, 'error');
    return;
  }
  $formStr='
    <form name="bum_root_form" method="post" action="BroadSoftUserManagement">
      <table width="450px">
        <! ..................................................> '.
          bum_getActionsStr($action)
          .'
        <! ..................................................>     
        <tr>
          <td><label for="groupname">LDAP Group Name *</label></td>
          <td valign="top">
            <input  type="text" name="groupname" maxlength="80" size="30" value="'.$groupname.'"
          </td>
        </tr>
        <! ..................................................>     
        <tr>
         <td colspan="2" style="text-align:center">
          <input type="submit" value="Submit">
         </td>
        </tr>
      </table>
    </form>
';
  return $formStr;
}

/** =============================================================================================
*/
function bum_getUserSiteActionRootFormStr($email='', $action='') {
  if(!user_access(USER_MANAGEMENT_PERMISSION_NAME)) {
    drupal_set_message(RESTRICTED_ACCESS_STR, 'error');
    return;
  }
  $formStr='
    <form name="bum_root_form" method="post" action="BroadSoftUserManagement">
      <table width="450px">
        <! ..................................................> '.
          bum_getActionsStr($action)
          .'
        <! ..................................................>     
        <tr>
          <td><label for="email">User Email Address *</label></td>
          <td valign="top">
            <input  type="text" name="email" maxlength="80" size="30" value="'.$email.'"
          </td>
        </tr>
        <! ..................................................>     
        <tr>
          <td><label for="sitename">Target Web Site *</label></td>
          <td valign="top">
            <select name=sitename>'.
            // ..... general actions .....
            '<option>Xchange'.
            '<option>Extranet'.
            '<option>cPBX'.
            '<option>cloudDashboard'.
            '<option>Xtended'.
            '<option>Interop'.
            '<option>ALL sites'.
            '</select>
          </td>
        </tr>
        <! ..................................................>     
        <tr>
         <td colspan="2" style="text-align:center">
          <input type="submit" value="Submit">
         </td>
        </tr>
      </table>
    </form>
';
  return $formStr;
}

/** =============================================================================================
*/
function bum_getUserGroupActionRootFormStr($email='', $action='', $groupname='') {
  if(!user_access(USER_MANAGEMENT_PERMISSION_NAME)) {
    drupal_set_message(RESTRICTED_ACCESS_STR, 'error');
    return;
  }
  $formStr='
    <form name="bum_root_form" method="post" action="BroadSoftUserManagement">
      <table width="450px">
        <! ..................................................> '.
          bum_getActionsStr($action)
          .'
        <! ..................................................>     
        <tr>
          <td><label for="email">User Email Address *</label></td>
          <td valign="top">
            <input  type="text" name="email" maxlength="80" size="30" value="'.$email.'"
          </td>
        </tr>
        <! ..................................................>     
        <tr>
          <td><label for="groupname">LDAP Group Name *</label></td>
          <td valign="top">
            <input  type="text" name="groupname" maxlength="80" size="30" value="'.$groupname.'"
          </td>
        </tr>
        <! ..................................................>     
        <tr>
         <td colspan="2" style="text-align:center">
          <input type="submit" value="Submit">
         </td>
        </tr>
      </table>
    </form>
';
  return $formStr;
}

/** =============================================================================================
*/
function bum_getUserActionRootFormStr($email='', $action='') {
  if(!user_access(USER_MANAGEMENT_PERMISSION_NAME)) {
    drupal_set_message(RESTRICTED_ACCESS_STR, 'error');
    return;
  }
  $formStr='
    <form name="bum_root_form" method="post" action="BroadSoftUserManagement">
      <table width="450px">
        <! ..................................................> '.
          bum_getActionsStr($action)
          .'
        <! ..................................................>     
        <tr>
          <td><label for="email">User Email Address *</label></td>
          <td valign="top">
            <input  type="text" name="email" maxlength="80" size="30" value="'.$email.'"
          </td>
        </tr>
        <! ..................................................>     
        <tr>
         <td colspan="2" style="text-align:center">
          <input type="submit" value="Submit">
         </td>
        </tr>
      </table>
    </form>
';
  return $formStr;
}

/** =============================================================================================
*/
function bum_getRootFormStr($email='', $action='') {
  if(!user_access(USER_MANAGEMENT_PERMISSION_NAME)) {
    drupal_set_message(RESTRICTED_ACCESS_STR, 'error');
  }
  $formStr='
    <form name="bum_root_form" method="post" action="BroadSoftUserManagement">
      <table width="450px">
        <! ..................................................> '.
        bum_getActionsStr($action)
        .'
        <! ..................................................>     
        <tr>
         <td colspan="2" style="text-align:center">
          <input type="submit" value="Submit">
         </td>
        </tr>
      </table>
    </form>
';
  return $formStr;
}

?>
