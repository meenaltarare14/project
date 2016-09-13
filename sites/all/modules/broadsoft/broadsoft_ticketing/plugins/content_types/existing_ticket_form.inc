<?php

$plugin = array(
  'single' => TRUE,
  'title' => t('Existing ticket form'),
  'description' => t('Existing ticket form - broadsofr ticketing integration'),
  'category' => t('Broadsoft panes'),
  'edit form' => 'existing_ticket_form_edit_render',
  //'admin info' => 'front_banner_pane_admin_info',
  'render callback' => 'existing_ticket_form_render',
  'defaults' => array(
    //'wtext' => '',
  ),
  'all contexts' => TRUE,
);

function existing_ticket_form_edit_render($form, $form_state) {
  $form['title'] = array(
    '#markup' => t('%ticket from url will be used as argument to this form'),
  );
  return $form;
}

function existing_ticket_form_render($subtype, array $conf = array(), $args, $context) {
  // Render as a block.
  $block = new stdClass();
  $block->module = 'broadsoft_ticketing';
  $block->content = drupal_get_form('ticketing_existing_form', $context['argument_string_1']->data);
  return $block;
}