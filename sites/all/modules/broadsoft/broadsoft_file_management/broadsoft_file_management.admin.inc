<?php 


/**
 *
 * @param type $form
 * @param type $form_state
 */
function broadsoft_file_management_admin_config_form_submit($form, &$form_state) {
  /* interesting to look at form arrays
   drupal_set_message("I am inside submit handler. Printed form values below");
  drupal_set_message("<pre>" . print_r($form_state['values'], 1) . "</pre>");
  drupal_set_message("Printing Form Structure Below !");
  drupal_set_message("<pre>" . print_r($form, 1) . "</pre>");
  */
  $discovery = !empty($form_state['values']['broadsoft_file_management_enable_discovery']);

  //////////////////////////////////////
  // Nodes
  if($discovery) {
    $process1file = $form_state['values']['broadsoft_file_management_process1file'];
    $process1dir = $form_state['values']['broadsoft_file_management_process1dir'];
    variable_set('broadsoft_file_management_enable_discovery', true);
    
    if( !empty($process1file) ) {
      drupal_set_message("Discovering ".$process1file );
      broadsoft_file_management_process1file($process1file);
      
    }

    if( !empty($process1dir) ) {
      drupal_set_message("Discovering ".$process1dir);
      broadsoft_file_management_process1dir($process1dir);
    
    }
  }
  variable_set('broadsoft_file_management_enable_discovery', false);
}


/**
 *
 * @param type $form
 * @param type $form_state
 * @return type
 */
function broadsoft_file_management_admin_config_form($form, &$form_state) {
  $form = array();
  $form['broadsoft_file_management_process1file'] = array(
      '#type' => 'textfield',
      '#title' => t('Discover 1 file (full path)'),
      '#default_value' => variable_get('broadsoft_content_process1file', BROADSOFT_FILE_MANAGEMENT_PROCESS_1_FILE),
      '#size' => 80,
      '#maxlength' => 250,
      '#required' => false,
  );
  $form['broadsoft_file_management_process1dir'] = array(
      '#type' => 'textfield',
      '#title' => t('Discover 1 directory (full path)'),
      '#default_value' => variable_get('broadsoft_content_process1file', BROADSOFT_FILE_MANAGEMENT_PROCESS_1_DIR),
      '#size' => 80,
      '#maxlength' => 250,
      '#required' => false,
  );

  $form['broadsoft_file_management_enable_discovery'] = array(
      '#type' => 'checkbox',
      '#title' => t('Activate the Discovery for one time'),
      '#description' => t('Will execute once a discovery for a file or a directory.'),
      '#default_value' => variable_get('broadsoft_file_management_enable_discovery', FALSE),
      '#required' => FALSE,
  );
  $form['#submit'][] = 'broadsoft_file_management_admin_config_form_submit';

  return system_settings_form($form);
}

?>