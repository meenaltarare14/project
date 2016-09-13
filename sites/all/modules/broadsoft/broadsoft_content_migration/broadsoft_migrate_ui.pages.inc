<?php


/**
 * Form for managing migration groups.
 */
function broadsoft_content_migration_dashboard($form, &$form_state) {
  drupal_set_title(t('BroadSoft Content Migration dashboard'));

  $form = array();

  $old_database_options = array('xchange', 'cpbx');
  
  $form['db'] = array(
    '#type' => 'select',
    '#options' => drupal_map_assoc($old_database_options),
    '#required' => TRUE,
    '#title' => 'Select site',
    '#description' => 'Chose which site content is to be migrated',
  );

  $content_type_options = array(
    'broadworks_patch' => 'Broadworks Patch',
    'broadsoft_software' => 'Broadsoft Software'
  );

  $form['content_type'] = array(
    '#type' => 'select',
    '#options' => $content_type_options,
    '#required' => TRUE,
    '#title' => 'Content Type',
    '#description' => 'Chose which content type is to be migratesd',
  );

  $process_info_option = array(
    'create' => 'Create',
    'update' => 'Update'
  );

  $form['single_node'] = array(
    '#type' => 'textfield',
    '#title' => 'Update single node',
    '#description' => 'Chose old xchange nid to update the corresponding commons node',
  );
  
  $form['process_info'] = array(
    '#type' => 'select',
    '#options' => $process_info_option,
    '#required' => TRUE,
    '#title' => 'Action',
    '#description' => 'Chose whether you want to import new content or update old content.'
  );


  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Start Migration'
  );

  return $form;
}

function broadsoft_content_migration_dashboard_submit($form, $form_state) {
//  (
//  [db] => xchange
//  [content_type] => broadworks_patch
//  [process_info] => create
//);
  $values = $form_state['values'];
  broadsoft_batch_migrate($values);
}