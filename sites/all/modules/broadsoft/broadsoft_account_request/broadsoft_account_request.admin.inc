<?php
/** =============================================================================================
*/
function broadsoft_account_request_settings() {
  $form = array();
  // ........... Gmail SMTP Settings ...........
   $form['broadsoft_gmail_smtp'] = array(
    '#type' => 'fieldset',
    '#title' => t('SMTP'),
    '#description' => t("Settings to send emails through gmail SMTP servers"),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['broadsoft_gmail_smtp']['broadsoft_gmail_smtp_host'] = array(
    '#type' => 'textfield',
    '#title' => t('gmail SMTP Host'),
    '#default_value' => variable_get('broadsoft_gmail_smtp_host', 'smtp.gmail.com'),
    '#description' => t("The default gmail smtp host."),
    '#required' => FALSE,
  );
  $form['broadsoft_gmail_smtp']['broadsoft_gmail_smtp_port'] = array(
    '#type' => 'textfield',
    '#title' => t('gmail SMTP Port'),
    '#default_value' => variable_get('broadsoft_gmail_smtp_port', 587),
    '#description' => t("The default gmail smtp port."),
    '#required' => FALSE,
  );
  $form['broadsoft_gmail_smtp']['broadsoft_gmail_smtp_username'] = array(
    '#type' => 'textfield',
    '#title' => t('gmail SMTP User'),
    '#description' => t("This is the user required to send emails from gmail. Has to be a valid broadsoft account."),
    '#default_value' => variable_get('broadsoft_gmail_smtp_username', 'xchange@broadsoft.com'),
    '#required' => FALSE,
  );
  $form['broadsoft_gmail_smtp']['broadsoft_gmail_smtp_fromname'] = array(
    '#type' => 'textfield',
    '#title' => t('gmail SMTP From Name'),
    '#description' => t("the Name displayed when sending an email instead of the actual email."),
    '#default_value' => variable_get('broadsoft_gmail_smtp_fromname', 'BroadSoft Xchange'),
    '#required' => FALSE,
  );
  $form['broadsoft_gmail_smtp']['broadsoft_gmail_smtp_password'] = array(
    '#type' => 'textfield',
    '#title' => t('gmail SMTP Password'),
    '#description' => t("The password matching the user configured in the previous field."),
    '#default_value' => variable_get('broadsoft_gmail_smtp_password', 'xChAng3!'),
    '#required' => FALSE,
  );

  /* General Settings */
  $form['broadsoft_general'] = array(
    '#type' => 'fieldset',
    '#title' => t('General'),
    '#description' => t('General settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['broadsoft_general']['broadsoft_general_rejected_domains'] = array(
    '#type' => 'textarea',
    '#title' => t('Rejected domains'),
    '#description' => t("User email domain names(comma or newline separated) that should be rejected during user registration."),
    '#default_value' => variable_get('broadsoft_general_rejected_domains', "gmail.com,yahoo.com,hotmail.com,ymail.com,email.com,yahoo.com.uk,hotmail.ru,msn.com"),
    '#required' => FALSE,
  );

  return system_settings_form($form);
}