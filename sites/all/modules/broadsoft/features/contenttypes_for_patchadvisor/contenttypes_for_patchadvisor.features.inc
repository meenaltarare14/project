<?php
/**
 * @file
 * contenttypes_for_patchadvisor.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function contenttypes_for_patchadvisor_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_node_info().
 */
function contenttypes_for_patchadvisor_node_info() {
  $items = array(
    'broadsoft_software' => array(
      'name' => t('Broadsoft Software'),
      'base' => 'node_content',
      'description' => t('A node that can hold a generic software file, with it\'s md5 value. To be used only for software that cannot be associated to some other software type.

Examples: 
- Broadworks preUpgradeCheck script files
- upgrade tools'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'broadworks_patch' => array(
      'name' => t('Broadworks Patch'),
      'base' => 'node_content',
      'description' => t('A software patch for the BroadWorks Product.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}
