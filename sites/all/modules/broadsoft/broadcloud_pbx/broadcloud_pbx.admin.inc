<?php 


/** =============================================================================================
*/
function broadcloud_pbx_admin_config_form_submit($form, &$form_state) {
}


/** =============================================================================================
*/
function broadcloud_pbx_admin_config_form($form, &$form_state) {
  $form = array();

  $form['broadcloud_pbx_Enable_Email_Notification'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable Email Notification'),
    '#description' => t('This will enable the email notification to the admin mailing list for creation, update or maintenance on the file attachments for the site'),
    '#default_value' => variable_get('broadcloud_pbx_Enable_Email_Notification', BROADCLOUD_PBX_ENABLE_EMAIL_NOTIFICATION),
    '#required' => FALSE,
  );  
  $form['broadcloud_pbx_Email_Address'] = array(
    '#type' => 'textfield', // or keep plain text & visible 'textfield', of starred with 'password'
    '#title' => t('Email Address'),
    '#default_value' => variable_get('broadcloud_pbx_Email_Address', BROADCLOUD_PBX_EMAIL_ADDRESS),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => TRUE,
  );
  $form['broadcloud_pbx_Notify_On_Creation'] = array(
    '#type' => 'checkbox',
    '#title' => t('Creation notification'),
    '#description' => t('This will send an email when a new file is discovered and uploaded to the site'),
    '#default_value' => variable_get('broadcloud_pbx_Notify_On_Creation', BROADCLOUD_PBX_NOTIFY_ON_CREATION),
    '#required' => FALSE,
  );  
  $form['broadcloud_pbx_Notify_On_Update'] = array(
    '#type' => 'checkbox',
    '#title' => t('Update notification'),
    '#description' => t('This will send an email when a file is updated'),
    '#default_value' => variable_get('broadcloud_pbx_Notify_On_Update', BROADCLOUD_PBX_NOTIFY_ON_UPDATE),
    '#required' => FALSE,
  );  
  $form['broadcloud_pbx_Notify_On_Unmanaged'] = array(
    '#type' => 'checkbox',
    '#title' => t('Unmanaged or filtered out notification'),
    '#description' => t('This will send an email when a file is going to be filered out and will not appear in the unpublished section or update any content. This is a debugging tool.'),
    '#default_value' => variable_get('broadcloud_pbx_Notify_On_Unmanaged', BROADCLOUD_PBX_NOTIFY_ON_UNMANAGED),
    '#required' => FALSE,
  );  
    
  $form['#submit'][] = 'broadcloud_pbx_admin_config_form_submit';  

  return system_settings_form($form);
}

?>