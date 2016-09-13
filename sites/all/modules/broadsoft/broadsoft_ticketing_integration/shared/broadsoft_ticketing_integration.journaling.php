<?php
  
/*------------------------------------------------------------------------------
                          Ticketing Journaling
                          
1) types of events:
  TICKETING_USER_CONFIG_ASSIGN_GROUP_EVENT : modifying user-account-group associations (ROLE_EXTERNAL_ACCOUNT_ADMINISTRATOR level)
  TICKETING_USER_CONFIG_UNASSIGN_GROUP_EVENT : modifying user-account-group associations (ROLE_EXTERNAL_ACCOUNT_ADMINISTRATOR level)

  TICKETING_USER_CONFIG_EMAIL_NOTIF_UNREGISTER_EVENT : removing a user from ALL email notification lists

  TICKETING_ACCOUNT_CONFIG_EVENT : modifying accounts and groups nodes (content editor level)

  TICKETING_SERVICE_CONFIG_EVENT : modifying the ticketing module config (site admin level)

  TICKETING_TICKET_UPDATE : end user updating a ticket
  
  TICKETING_TICKET_CREATION_START_EVENT : user initiates ticket creation (form displayed)
  TICKETING_TICKET_CREATION_SELF_HELP_EXPLORING_EVENT : user follows a self help link
  TICKETING_TICKET_CREATION_SELF_HELP_CONFIRM_EVENT : user confirms that a self help suggestion prevented ticket creation
  TICKETING_TICKET_CREATION : user creating a ticket

------------------------------------------------------------------------------*/
define("TICKETING_STAT_TYPE",  "Ticketing");

define("TICKETING_USER_CONFIG_ASSIGN_GROUP_EVENT", "UserConfigAssignGroup");
define("TICKETING_USER_CONFIG_UNASSIGN_GROUP_EVENT", "UserConfigUNAssignGroup");
define("TICKETING_USER_CONFIG_EMAIL_NOTIF_REMOVAL_EVENT", "UserConfigEmailNotifUnregister");
define("TICKETING_USER_CONFIG_EMAIL_NOTIF_ADD_EVENT", "UserConfigEmailNotifRegister");

define("TICKETING_TICKET_UPDATE_EVENT", "TicketUpdate"); 
define("TICKETING_TICKET_CLOSURE_EVENT", "TicketClosure"); 
define("TICKETING_TICKET_CREATION_EVENT", "TicketCreation"); 
define("TICKETING_TICKET_CREATION_START_EVENT", "AddTicketStart"); 
define("TICKETING_TICKET_CREATION_SELF_HELP_EXPLORING_EVENT", "SelfHelpExploring"); 
define("TICKETING_TICKET_CREATION_SELF_HELP_CONFIRM_EVENT", "SelfHelpUserConfirm"); 

define("TICKETING_JIRA_CONNECTION_FAILURE_EVENT", "JiraConnectionFailure"); 

define("FIELD_EVENT", "event"); 
define("FIELD_TICKET_ID", "ticketID"); 
define("FIELD_USER_EMAIL", "user"); 
define("FIELD_SELF_HELP_NODE_REF", "destination"); 
define("FIELD_TARGET_USER_EMAIL", "targetUser"); 
define("FIELD_TARGET_ACCOUNT", "AccountName"); 
define("FIELD_TARGET_ACCOUNT_NID", "AccountNid"); 
define("FIELD_TARGET_GROUP", "GroupName"); 
define("FIELD_TARGET_GROUP_NID", "GroupNid"); 
define("FIELD_ACCESS_MODE", "accessMode"); 

/** ============================================================================================= */
function BTI_log_group_notif_change($gid) {
  global $user;
  $editUser = user_load($targetUserUID);
  $fields = array(
    FIELD_EVENT => TICKETING_ACCOUNT_CONFIG_EVENT,
    FIELD_USER_EMAIL => $user->mail,
    FIELD_TARGET_GROUP => BTI_get_exportable_group_name($gid),
    FIELD_TARGET_GROUP_NID => $gid,
  );
  broadsoft_statistics_addStat(TICKETING_STAT_TYPE, $fields);
}

/** ============================================================================================= */
function BTI_log_user_group_assign($targetUserUID, $targetGroupNid, $accessMode) {
  global $user;
  $editUser = user_load($targetUserUID);
  $accountNid = BTI_getAccountFromGroup($targetGroupNid);
  $fields = array(
    FIELD_EVENT => TICKETING_USER_CONFIG_ASSIGN_GROUP_EVENT,
    FIELD_USER_EMAIL => $user->mail,
    FIELD_TARGET_USER_EMAIL => $editUser->mail,
    FIELD_TARGET_ACCOUNT => BTI_get_exportable_account_name($accountNid),
    FIELD_TARGET_ACCOUNT_NID => $accountNid,
    FIELD_TARGET_GROUP => BTI_get_exportable_group_name($targetGroupNid),
    FIELD_TARGET_GROUP_NID => $targetGroupNid,
    FIELD_ACCESS_MODE => $accessMode,
  );
  broadsoft_statistics_addStat(TICKETING_STAT_TYPE, $fields);
}

/** ============================================================================================= */
function BTI_log_user_group_unassign($targetUserUID, $targetGroupNid, $accessMode) {
  global $user;
  $editUser = user_load($targetUserUID);
  $accountNid = BTI_getAccountFromGroup($targetGroupNid);
  $fields = array(
    FIELD_EVENT => TICKETING_USER_CONFIG_UNASSIGN_GROUP_EVENT,
    FIELD_USER_EMAIL => $user->mail,
    FIELD_TARGET_USER_EMAIL => $editUser->mail,
    FIELD_TARGET_ACCOUNT => BTI_get_exportable_account_name($accountNid),
    FIELD_TARGET_ACCOUNT_NID => $accountNid,
    FIELD_TARGET_GROUP => BTI_get_exportable_group_name($targetGroupNid),
    FIELD_TARGET_GROUP_NID => $targetGroupNid,
    FIELD_ACCESS_MODE => $accessMode,
  );
  broadsoft_statistics_addStat(TICKETING_STAT_TYPE, $fields);
}

/** ============================================================================================= */
function BTI_log_user_group_email_notif_register($targetUserMail, $targetGroupNid) {
  global $user;
  $accountNid = BTI_getAccountFromGroup($targetGroupNid);
  $fields = array(
    FIELD_EVENT => TICKETING_USER_CONFIG_EMAIL_NOTIF_ADD_EVENT,
    FIELD_USER_EMAIL => $user->mail,
    FIELD_TARGET_USER_EMAIL => $targetUserMail,
    FIELD_TARGET_ACCOUNT => BTI_get_exportable_account_name($accountNid),
    FIELD_TARGET_ACCOUNT_NID => $accountNid,
    FIELD_TARGET_GROUP => BTI_get_exportable_group_name($targetGroupNid),
    FIELD_TARGET_GROUP_NID => $targetGroupNid,
  );
  broadsoft_statistics_addStat(TICKETING_STAT_TYPE, $fields);
}

/** ============================================================================================= */
function BTI_log_user_group_email_notif_unregister($targetUserMail, $targetGroupNid) {
  global $user;
  $accountNid = BTI_getAccountFromGroup($targetGroupNid);
  $fields = array(
    FIELD_EVENT => TICKETING_USER_CONFIG_EMAIL_NOTIF_REMOVAL_EVENT,
    FIELD_USER_EMAIL => $user->mail,
    FIELD_TARGET_USER_EMAIL => $targetUserMail,
    FIELD_TARGET_ACCOUNT => BTI_get_exportable_account_name($accountNid),
    FIELD_TARGET_ACCOUNT_NID => $accountNid,
    FIELD_TARGET_GROUP => BTI_get_exportable_group_name($targetGroupNid),
    FIELD_TARGET_GROUP_NID => $targetGroupNid,
  );
  broadsoft_statistics_addStat(TICKETING_STAT_TYPE, $fields);
}

/** ============================================================================================= */
function BTI_log_ticket_creation_start() {
  global $user;
  $fields = array(
    FIELD_EVENT => TICKETING_TICKET_CREATION_START_EVENT,
    FIELD_USER_EMAIL => $user->mail,
  );
  broadsoft_statistics_addStat(TICKETING_STAT_TYPE, $fields);
}

/** ============================================================================================= */
function BTI_log_ticket_creation($ticketID) {
  global $user;
  $fields = array(
    FIELD_EVENT => TICKETING_TICKET_CREATION_EVENT,
    FIELD_USER_EMAIL => $user->mail,
    FIELD_TICKET_ID => $ticketID,
  );
  broadsoft_statistics_addStat(TICKETING_STAT_TYPE, $fields);
}

/** ============================================================================================= */
function BTI_log_ticket_closure($ticketID) {
  global $user;
  $fields = array(
    FIELD_EVENT => TICKETING_TICKET_CLOSURE_EVENT,
    FIELD_USER_EMAIL => $user->mail,
    FIELD_TICKET_ID => $ticketID,
  );
  broadsoft_statistics_addStat(TICKETING_STAT_TYPE, $fields);
}

/** ============================================================================================= */
function BTI_log_ticket_update($ticketID) {
  global $user;
  $fields = array(
    FIELD_EVENT => TICKETING_TICKET_UPDATE_EVENT,
    FIELD_USER_EMAIL => $user->mail,
    FIELD_TICKET_ID => $ticketID,
  );
  broadsoft_statistics_addStat(TICKETING_STAT_TYPE, $fields);
}

/** ============================================================================================= */
function BTI_log_self_help_exploring($dest) {
  global $user;
  $fields = array(
    FIELD_EVENT => TICKETING_TICKET_CREATION_SELF_HELP_EXPLORING_EVENT,
    FIELD_USER_EMAIL => $user->mail,
    FIELD_SELF_HELP_NODE_REF => $dest,
  );
  broadsoft_statistics_addStat(TICKETING_STAT_TYPE, $fields);
}

/** ============================================================================================= */
function BTI_log_jira_connection_failure() {
  global $user;
  $fields = array(
    FIELD_EVENT => TICKETING_JIRA_CONNECTION_FAILURE_EVENT,
    FIELD_USER_EMAIL => $user->mail,
  );
  broadsoft_statistics_addStat(TICKETING_STAT_TYPE, $fields);
}

/** ============================================================================================= */
function BTI_log_self_help_confirm($answerNid) {
  global $user;
  $fields = array(
    FIELD_EVENT => TICKETING_TICKET_CREATION_SELF_HELP_CONFIRM_EVENT,
    FIELD_USER_EMAIL => $user->mail,
    FIELD_SELF_HELP_NODE_REF => $answerNid,
  );
  broadsoft_statistics_addStat(TICKETING_STAT_TYPE, $fields);
}

?>