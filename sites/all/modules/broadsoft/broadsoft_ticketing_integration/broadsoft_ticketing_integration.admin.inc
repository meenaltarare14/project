<?php

/** =============================================================================================
*/
function broadsoft_ticketing_integration_settings() {
  $form = array();

  // ........... MAINTENANCE ...........
  $form['maintenance'] = array(
    '#type' => 'fieldset',
    '#title' => t('Maintenance Settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['maintenance']['ticketing_is_online'] = array(
    '#type' => 'checkbox',
    '#title' => t('Ticketing Service is ONLINE'),
    '#default_value' => variable_get('ticketing_is_online', TRUE),
    '#description' => t("When NOT online, only site and Ticketing admins will be able to access Ticketing-related pages."),
    '#required' => FALSE,
  );
  $form['maintenance']['ticketing_offline_msg'] = array(
    '#type' => 'textarea',
    '#title' => t('Offline Message'),
    '#default_value' => variable_get('ticketing_offline_msg', ''),
    '#description' => t("Message to be displayed in a block to the users while the Ticketing service is offline."),
    '#required' => FALSE,
  );
  $form['maintenance']['ticketing_JIRA_monitoring_autoMaintenanceMode'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable Automatic Maintenance mode'),
    '#description' => t("If checked, Ticketing UI will go in/out of maintenance based on the status of the Ticketing System Server. When the ticketing system is unreachable, the Offline Message will be displayed to the users."),
    '#default_value' => variable_get('ticketing_JIRA_monitoring_autoMaintenanceMode', FALSE),
    '#required' => FALSE,
  );

  // ........... TICKETING SYSTEM REGION ...........
  $form['ticketing_system'] = array(
    '#type' => 'fieldset',
    '#title' => t('Ticketing System Connection & Monitoring Settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
/*
  $form['ticketing_system']['ticketing_system'] = array(
    '#type' => 'radios',
    '#title' => t('Ticketing System'),
    '#default_value' => variable_get('ticketing_system', 0),
    '#options' => array(0 => t('ExtraView'), 1 => t('Jira')),
    '#description' => t("When changing system settings, DO NOT FORGET to Re-synchronize Ticketing Options!"),
    '#required' => TRUE,
  );
*/
  $form['ticketing_system']['ticketing_server'] = array(
    '#type' => 'textfield',
    '#title' => t('Ticketing System Server'),
    '#default_value' => variable_get('ticketing_server', TICKETING_SERVER_ADDRESS),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t("Server IP Adress. For JIRA sandbox (https://jira-sb.broadsoft.com or http://10.8.9.2:8888)"),
    '#required' => TRUE,
  );
  $form['ticketing_system']['ticketing_user'] = array(
    '#type' => 'textfield',
    '#title' => t('Ticketing System User'),
    '#default_value' => variable_get('ticketing_user', TICKETING_USER),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t("Username."),
    '#required' => TRUE,
  );
  $form['ticketing_system']['ticketing_password'] = array(
    '#type' => 'textfield',
    '#title' => t('Ticketing System Password'),
    '#default_value' => variable_get('ticketing_password', TICKETING_PASSWORD),
    '#size' => 20,
    '#maxlength' => 80,
    '#description' => t("Password."),
    '#required' => FALSE,
  );
  $form['ticketing_system']['ticketing_JIRA_monitoring_run_frequency'] = array(
    '#type' => 'textfield',
    '#title' => t('Ticketing Server Monitoring Frequency'),
    '#default_value' => variable_get('ticketing_JIRA_monitoring_run_frequency', 0),
    '#size' => 20,
    '#maxlength' => 20,
    '#description' => t("Frequency at which the Ticketing Server will be monitored. Set 0 to disable monitoring. Granularity: 5 min minimum (300 sec.). Unit: seconds."),
    '#required' => TRUE,
  );
  $form['ticketing_system']['ticketing_JIRA_monitoring_notificationList'] = array(
    '#type' => 'textfield',
    '#title' => t('Monitoring Notification Recipients'),
    '#default_value' => variable_get('ticketing_JIRA_monitoring_notificationList', ''),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t("These emails will be notified along with xchangesupport@broadsoft.com. Separate emails with a comma."),
    '#required' => FALSE,
  );
  $form['ticketing_system']['ticketing_JIRA_monitoring_monirotNOW'] =
    makeMarkupElement('<div>'.api_get_simple_button('Force Monitoring NOW', 'forceMonitorTicketing', array(), 'Ticketing System will be monitored now, even if not scheduled as such').' - Make sure you first SAVE CONFIGURATION if you edited settings!</div>');

  // ........... CACHE MANAGEMENT ...........
  $form['ticketing_cache_management'] = array(
    '#type' => 'fieldset',
    '#title' => t('Manage Closed Tickets Cache'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['ticketing_cache_management']['ticketing_system_cache_enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable Cache'),
    '#default_value' => variable_get('ticketing_system_cache_enabled', FALSE),
    '#required' => FALSE,
  );
  $form['ticketing_cache_management']['ticketing_cache_dir'] = array(
    '#type' => 'textfield',
    '#title' => t('Cache Files Storage location'),
    '#default_value' => variable_get('ticketing_cache_dir', '/bw/broadworks/xchangeTicketingCache'),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t("The storage location of ticket cache on disk."),
    '#required' => TRUE,
  );

  // ........... FIELD RESTRICTIONS REGION ...........
  $form['field_restrictions'] = array(
    '#type' => 'fieldset',
    '#title' => t('Field Restrictions Settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['field_restrictions']['ticketing_field_title_max_len'] = array(
    '#type' => 'textfield',
    '#title' => t('Max Len: Title'),
    '#default_value' => variable_get('ticketing_field_title_max_len', TICKETING_FIELD_TITLE_MAX_LEN),
    '#size' => 20,
    '#maxlength' => 20,
    '#description' => t("The maximum length of a ticket title (in characters)."),
    '#required' => TRUE,
  );
  $form['field_restrictions']['ticketing_field_description_max_len'] = array(
    '#type' => 'textfield',
    '#title' => t('Max Len: Description & User Comment'),
    '#default_value' => variable_get('ticketing_field_description_max_len', TICKETING_FIELD_DESCRIPTION_MAX_LEN),
    '#size' => 20,
    '#maxlength' => 20,
    '#description' => t("The maximum length of a ticket description & user comment (in characters)."),
    '#required' => TRUE,
  );
  $form['field_restrictions']['ticketing_field_user_comment_required'] = array(
    '#type' => 'checkbox',
    '#title' => t('User Comment Required'),
    '#default_value' => variable_get('ticketing_field_user_comment_required', TICKETING_FIELD_USER_COMMENT_REQUIRED),
    '#description' => t("Forces the user to input a comment every time he modifies the ticket (fields or status)."),
    '#required' => FALSE,
  );
  $form['field_restrictions']['show_old_EVID_column'] = array(
    '#type' => 'checkbox',
    '#title' => t('(Only if on Jira) Show old ExtraView ID column in ticket List tabs'),
    '#default_value' => variable_get('show_old_EVID_column', TRUE),
    '#required' => FALSE,
  );

  // ........... FILE UPLOAD REGION ...........
  $form['attachments'] = array(
    '#type' => 'fieldset',
    '#title' => t('File Attachment Settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['attachments']['ticketing_upload_dir'] = array(
    '#type' => 'textfield',
    '#title' => t('Temporary Storage location'),
    '#default_value' => variable_get('ticketing_upload_dir', TICKETING_UPLOAD_DIR),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t("The storage location of customers uploaded attachments on disk."),
    '#required' => TRUE,
  );
  $form['attachments']['ticketing_upload_max_size_mb'] = array(
    '#type' => 'textfield',
    '#title' => t('Maximum File Attachment Size'),
    '#default_value' => variable_get('ticketing_upload_max_size_mb', TICKETING_UPLOAD_MAX_SIZE_MB),
    '#size' => 20,
    '#maxlength' => 20,
    '#description' => t("in Mb."),
    '#required' => TRUE,
  );
  $form['attachments']['ticketing_upload_allowed_extensions'] = array(
    '#type' => 'textfield',
    '#title' => t('File Extension Restriction'),
    '#default_value' => variable_get('ticketing_upload_allowed_extensions', TICKETING_UPLOAD_ALLOWED_EXTENSIONS),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t("The list of space-separated allowed file extensions for customers uploaded attachments. Put ALL to enable all file extensions."),
    '#required' => TRUE,
  );
  $form['attachments']['ticketing_upload_max_attachment_nb'] = array(
    '#type' => 'textfield',
    '#title' => t('File Upload Quantity Restriction'),
    '#default_value' => variable_get('ticketing_upload_max_attachment_nb', TICKETING_UPLOAD_MAX_ATTACHMENT_NB),
    '#size' => 20,
    '#maxlength' => 20,
    '#description' => t("The maximum number of attachments supported at ticket creation time."),
    '#required' => TRUE,
  );

  // ........... SELF HELP ...........
  $form['selfHelp'] = array(
    '#type' => 'fieldset',
    '#title' => t('Self Help'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['selfHelp']['ticketing_max_self_help_items_shown'] = array(
    '#type' => 'textfield',
    '#title' => t('Max nb of help suggestion items in Self Help area'),
    '#default_value' => variable_get('ticketing_max_self_help_items_shown', TICKETING_MAX_SELF_HELP_ITEMS_SHOWN),
    '#size' => 5,
    '#maxlength' => 5,
    '#description' => t("To disable Self Help, set this field to 0"),
    '#required' => TRUE,
  );

  // ........... MISC ...........
  $form['other'] = array(
    '#type' => 'fieldset',
    '#title' => t('Other'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['other']['ticketing_critical_contact_TAC_msg'] = array(
    '#type' => 'textfield',
    '#title' => t('Critical Ticket Creation Popup Message'),
    '#default_value' => variable_get('ticketing_critical_contact_TAC_msg', 'If you are experiencing a calling outage, after creating this ticket, please call us immediately at +1-866-398-9101 or +44-2079930262 so we can assist you.  If this issue is not related to an outage, please specify severity=major and we\'ll look at the issue and get back to you.'),
    '#size' => 80,
    '#maxlength' => 800,
    '#description' => t('Popup displayed message when severity=critical is selected, informing user to contact TAC'),
    '#required' => TRUE,
  );
  $form['other']['ticketing_embed_xchange_uid_into_updates'] = array(
    '#type' => 'checkbox',
    '#title' => t('Embed encoded xchange user id into Ticket Updates'),
    '#default_value' => variable_get('ticketing_embed_xchange_uid_into_updates', TRUE),
    '#description' => t("Adds an encoded value into ticket updated, to be parsed out on Xchange before viewing the ticket update history. Enables to identify and display which Xchange User did the update."),
    '#required' => FALSE,
  );
  $form['other']['ticketing_portal_is_cpbx'] = array(
    '#type' => 'checkbox',
    '#title' => t('Is this portal dealing with BroadCloud PBX tickets?'),
    '#default_value' => variable_get('ticketing_portal_is_cpbx', FALSE),
    '#description' => t("This determines the dropdown options a user will be presented with, plus the account/groups he will see on this portal. Leave unchecked if for any other product (including BroadCloud)."),
    '#required' => FALSE,
  );
  $form['other']['ticketing_xchange_API_server'] = array(
    '#type' => 'textfield',
    '#title' => t('Xchange Server to query for misc. API calls'),
    '#default_value' => variable_get('ticketing_xchange_API_server', TICKETING_API_SERVER),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t('Without this, ticketing will not work on a secondary ticketing portal. E.g. https://xchange.broadsoft.com/php/xchange or http://10.1.1.33/php/xchange2'),
    '#required' => TRUE,
  );
  $form['other']['ticketing_xchange_API_token'] = array(
    '#type' => 'textfield',
    '#title' => t('Token for Xchange API authentication'),
    '#default_value' => variable_get('ticketing_xchange_API_token', TICKETING_API_TOKEN),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t('Without this, ticketing will not work on a secondary ticketing portal'),
    '#required' => TRUE,
  );
  $form['other']['ticketing_xchange_API_usercheck'] = array(
    '#type' => 'textfield',
    '#title' => t('User for Xchange API checks'),
    '#default_value' => variable_get('ticketing_xchange_API_usercheck', TICKETING_API_USERCHECK),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t('This is the user used in API checks'),
    '#required' => TRUE,
  );
  $form['other']['ticketing_xchange_server_1'] = array(
    '#type' => 'textfield',
    '#title' => t('Xchange Server 1 to query for misc. API calls'),
    '#default_value' => variable_get('ticketing_xchange_server_1'),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t('First URL to check'),
    '#required' => FALSE,
  );
  $form['other']['ticketing_xchange_server_2'] = array(
    '#type' => 'textfield',
    '#title' => t('Xchange Server 2 to query for misc. API calls'),
    '#default_value' => variable_get('ticketing_xchange_server_2'),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t('Second URL to check'),
    '#required' => FALSE,
  );

  $form['other']['broadsoft_site_ID'] = array(
    '#type' => 'textfield',
    '#title' => t('Xchange Broadsoft Site ID'),
    '#default_value' => variable_get('broadsoft_site_ID'),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t('Site ID to be passed to the API call'),
    '#required' => FALSE,
  );

  return system_settings_form($form);
}

?>