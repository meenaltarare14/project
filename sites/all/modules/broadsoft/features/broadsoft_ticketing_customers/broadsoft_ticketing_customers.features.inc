<?php
/**
 * @file
 * broadsoft_ticketing_customers.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function broadsoft_ticketing_customers_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_node_info().
 */
function broadsoft_ticketing_customers_node_info() {
  $items = array(
    'customer_account' => array(
      'name' => t('Customer Account'),
      'base' => 'node_content',
      'description' => t('A Customer Company Account, mapping to a Sales Force Account'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'customer_group' => array(
      'name' => t('Customer Group'),
      'base' => 'node_content',
      'description' => t('A Customer Company Group, aka sub-organization'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}
