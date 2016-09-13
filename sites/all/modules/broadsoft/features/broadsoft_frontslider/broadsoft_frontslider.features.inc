<?php
/**
 * @file
 * broadsoft_frontslider.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function broadsoft_frontslider_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_node_info().
 */
function broadsoft_frontslider_node_info() {
  $items = array(
    'homepage_slider' => array(
      'name' => t('Homepage slider'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}
