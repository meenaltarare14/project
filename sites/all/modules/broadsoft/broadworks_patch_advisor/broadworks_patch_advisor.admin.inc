<?php

/**
 * Implementation of admin settings form.
 */
function broadworks_patch_advisor_settings() {
  $form = array();
  $form['advisor_rootdir'] = array(
    '#type' => 'textfield',
    '#title' => t('Storage location'),
    '#default_value' => variable_get('advisor_rootdir', ADVISOR_ROOT_DIR),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t("The storage location of customers tech-supports on disk."),
    '#required' => TRUE,
  );
  $form['advisor_mail_server'] = array(
    '#type' => 'textfield',
    '#title' => t('Mail Server'),
    '#default_value' => variable_get('advisor_mail_server', ADVISOR_MAIL_SERVER_ADDRESS),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t("The patch advisor download mail server."),
    '#required' => TRUE,
  );
  $form['advisor_mail_user'] = array(
    '#type' => 'textfield',
    '#title' => t('Mail User'),
    '#default_value' => variable_get('advisor_mail_user', ADVISOR_MAIL_USER),
    '#size' => 80,
    '#maxlength' => 156,
    '#description' => t("The patch advisor mail server user account."),
    '#required' => TRUE,
  );
  $form['advisor_mail_password'] = array(
    '#type' => 'password',
    '#title' => t('Mail Password'),
    '#default_value' => variable_get('advisor_mail_password', ADVISOR_MAIL_PASSWORD),
    '#size' => 20,
    '#maxlength' => 80,
    '#description' => t("The patch advisor mail server user password."),
    '#required' => TRUE,
  );
  $form['advisor_mail_password2'] = array(
    '#type' => 'password',
    '#title' => t('Mail Password (confirm)'),
    '#default_value' => variable_get('advisor_mail_password2', ADVISOR_MAIL_PASSWORD),
    '#size' => 20,
    '#maxlength' => 80,
    '#description' => t("Please confirm the new password."),
    '#required' => TRUE,
  );

  $new_form = system_settings_form($form);

  $new_form['scan'] = array(
    '#type' => 'submit',
    '#limit_validation_errors' => array(),
    '#value' => t('Save and Scan Customer ID'),
    '#submit' => array('broadworks_patch_advisor_scan_techsupport')
  );

  return $new_form;
}

