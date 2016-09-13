<?php

$plugin = array(
  'single' => TRUE,
  'title' => t('Ticket search form'),
  'description' => t('Ticket search form - broadsoft ticketing integration'),
  'category' => t('Broadsoft panes'),
  'edit form' => 'ticket_search_form_edit_render',
  'render callback' => 'ticket_search_form_render',
  'defaults' => array(
  ),
  'all contexts' => TRUE,
);

function ticket_search_form_edit_render($form, $form_state) {
  $form['title'] = array(
    '#markup' => t('Ticket Search form'),
  );
  return $form;
}

function ticket_search_form_render($subtype, array $conf = array(), $args, $context) {
  // Render as a block.
  $block = new stdClass();
  $block->module = 'broadsoft_ticketing';
  $block->content = drupal_get_form('ticket_search_form');
  return $block;
}