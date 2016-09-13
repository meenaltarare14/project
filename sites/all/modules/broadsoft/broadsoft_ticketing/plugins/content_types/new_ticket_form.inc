<?php

$plugin = array(
  'single' => TRUE,
  'title' => t('New ticket form'),
  'description' => t('New ticket form - broadsofr ticketing integration'),
  'category' => t('Broadsoft panes'),
  'edit form' => 'new_ticket_form_edit_render',
  //'admin info' => 'front_banner_pane_admin_info',
  'render callback' => 'new_ticket_form_render',
  'defaults' => array(
    //'wtext' => '',
  ),
  'all contexts' => TRUE,
);

function new_ticket_form_edit_render($form, $form_state) {
  $form['title'] = array(
    '#markup' => t('If user chose \'modal\' in ticketing preferences, a modal form will be returned. Else no content will be returned.'),
  );
  return $form;
}

function new_ticket_form_render($subtype, array $conf = array(), $args, $context) {
  global $user;
  // Render as a block.
  $block = new stdClass();
  $block->module = 'broadsoft_ticketing';
  if(isset($user->data['ticket_preferences']) && $user->data['ticket_preferences'] == 'tab') {
    $block->content = '';
  }
  else {
    $block->content = drupal_get_form('ticketing_modal_form');
  }
  return $block;
}