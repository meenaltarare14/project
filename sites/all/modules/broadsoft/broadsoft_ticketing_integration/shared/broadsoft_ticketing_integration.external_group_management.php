<?php  

/** ============================================================================================= 
*/
function AdjustUserFromEmailNotificationListsInExistingTickets($adminUserObj, $targetUserObj, $targetGroupNID, $add=TRUE) {
  // allow if ticketing admin
  if(!(userIsAdministrator()||bsutil_user_has_role(ROLE_TICKETING_ADMIN, $adminUserObj)||bsutil_user_has_role(ROLE_EXTERNAL_ACCOUNT_ADMINISTRATOR, $adminUserObj))) {
    return 0;
  }
  
  $ticketingService = getTicketingService();
  
  $paramArr = array();  
  $paramArr['filters'] = getDfltFilterArray();
  $paramArr['filters']['status']['open'] = TRUE;    // get all non-closed tickets for this group  
  $paramArr['filters']['status']['pending'] = TRUE; // get all non-closed tickets for this group  
   
  $paramArr['fields'][TICKET_FIELD__EMAIL_LIST] = TRUE;

  $nbUpdatedTickets = 0;
  
  // for each ticket, update email notification list field if required
  $ticketList = $ticketingService->getTicketList2($targetGroupNID, $paramArr);
  foreach($ticketList as $ticket) {
    if(preg_match('/'.$targetUserObj->mail.'/', $ticket[TICKET_FIELD__EMAIL_LIST])) {
      if(!$add) {
        $updateArr[Ticket::getEmailNotificationListTag()] = urlencode(BTI_text2csvList(preg_replace('/'.$targetUserObj->mail.'/', '', $ticket[TICKET_FIELD__EMAIL_LIST])));
        $ticketingService->update_ticket($ticket['ID'], '', $updateArr); // no update text
        $nbUpdatedTickets++;
      }
    } else {
      // not in the ticket notif list
      if($add) {
        $updateArr[Ticket::getEmailNotificationListTag()] = urlencode(BTI_text2csvList($targetUserObj->mail.','.$ticket[TICKET_FIELD__EMAIL_LIST]));
        $ticketingService->update_ticket($ticket['ID'], '', $updateArr); // no update text
        $nbUpdatedTickets++;
      }
    }
  }
  watchdog('ticketing', 'Unregistered user '.$targetUserObj->mail.' from '.$nbUpdatedTickets.' existing tickets', NULL, WATCHDOG_NOTICE);
}

/** ============================================================================================= 
*/
function API_RemoveUserFromALLEmailNotificationLists($params) {
  if(!isXchange()) 
    return 0;

  AdjustUserNotificationLists(  array_element('adminUserUID', $params),
                                array_element('targetUserUID', $params),
                                array_element('targetGroupNID', $params),
                                FALSE);
}
/** ============================================================================================= 
*/
function AdjustUserNotificationLists($adminUserUID, $targetUserUID, $targetGroupNID, $add=TRUE) {
  if(!isXchange()) 
    return 0;
  
  if(!$adminUserUID || !$targetUserUID || !$targetGroupNID) {
    return 0;
  }

  $adminUserObj = user_load($adminUserUID);
  if(!(userIsAdministrator()||bsutil_user_has_role(ROLE_TICKETING_ADMIN, $adminUserObj)||bsutil_user_has_role(ROLE_EXTERNAL_ACCOUNT_ADMINISTRATOR, $adminUserObj))) {
    return 0;
  }
  
  $targetUserObj = user_load(array('uid' => $targetUserUID));

  // adjust default group notification list
  if(isXchange()) {
    // Loading node ignoring cache : IT IS the solution
    $node = node_load($targetGroupNID, NULL, TRUE);
    if($add) {
      // nothing to do
    } else {
      if($notif_list = $node->field_notification_list[0]['value']) {
        $new_list = BTI_text2csvList(preg_replace('/'.$targetUserObj->mail.'/', '', $notif_list));
        $node->field_notification_list = array(0 => array('value' => $new_list));
        $node = node_submit($node);
        node_save($node);
      }
    }
  }
  // adjust notify attribute for that User-Group
  $queryStr = "UPDATE {ticketing_group_membership} SET notify='".($add?'1':'0')."' WHERE uid='".$targetUserUID."' AND gid='".$targetGroupNID."'";
  $result = db_query($queryStr);
  
  // adjust existing tickets
  AdjustUserFromEmailNotificationListsInExistingTickets($adminUserObj, $targetUserObj, $targetGroupNID, $add);
  
  // add journaling entry
  if($add)
    BTI_log_user_group_email_notif_unregister($targetUserObj->mail, $targetGroupNID);
  else
    BTI_log_user_group_email_notif_register($targetUserObj->mail, $targetGroupNID);
  
}
/** ============================================================================================= 
  Temporary UI: quick TAC access @@@ remove fct?
*/
function GenerateUserEmailNotificationManagementGUI() {
  global $user;
  $retHTML = "";
  
  $targetUserUID = NULL;
  if(preg_match('/selectedUsers/', $_GET['q'])) {
    $targetUserUID = preg_replace('/.*selectedUsers=/', '', $_GET['q']);
  }
  
  if(!$targetUserUID || preg_match('/\D/', $targetUserUID)) {  
    return 0;
  }
  
  $targetUserObj = user_load(array('uid' => $targetUserUID));
  
  // list all groups assigned - both Xchange and CPBX
  $retHTML .= "<h4>This Tool enables TAC to unregister the current user from existing and future Email Notification Lists in Xchange and Jira. It will evolve and be integrated in the customer group manager UI (coming shortly).</h4>";
  $retHTML .= "<br>";
  $retHTML .= "<table>";
  $retHTML .= "<tr><th>Customer Account</th><th>Customer Group</th><th>Action</th></tr>";
  $result = db_query("SELECT distinct tgm.gid, n.title, cg.field_customer_account_nid, cg.field_in_country_support_options_value, cg.field_target_client_options_value FROM {ticketing_group_membership} tgm, {node} n, {content_type_customer_group} cg WHERE cg.nid=n.nid AND tgm.uid='".$targetUserObj->uid."' AND tgm.gid=n.nid ORDER BY n.title");
	while($CustGroupRow = db_fetch_array($result)) {
    $retHTML .= "<tr>";
    $jira_originator = BTI_get_exportable_group_name($CustGroupRow['gid']);
    $jira_customer = BTI_get_exportable_account_name_CID($CustGroupRow['field_customer_account_nid']);
    $MOText = "This will remove ".$targetUserObj->mail." from (1) ".$jira_originator."'s default email notification list, (2) email notification lists for existing non-closed and future Jira tickets of Group ".$jira_originator;
    $params = array('adminUserUID' => $user->uid,
                    'targetUserUID' => $targetUserUID,
                    'targetGroupNID' => $CustGroupRow['gid']
              );
    $buttonHTML = '<div>';
    $buttonHTML .= api_get_simple_button( 'Unregister '.$targetUserObj->mail.' from Email Lists of '.$jira_originator, 
                                          'API_RemoveUserFromALLEmailNotificationLists', 
                                          $params, 
                                          $MOText);
    $buttonHTML .= '</div>';
    $retHTML .= "<td>".$jira_customer."</td><td>".$jira_originator."</td><td>".$buttonHTML."</td>";
    $retHTML .= "</tr>";
  }
  $retHTML .= "</table>";
  
  return $retHTML;
}
?>