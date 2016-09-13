<?php
/**
 * @file
 * online_broadworks_patch_advisor.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function online_broadworks_patch_advisor_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "page_manager" && $api == "pages_default") {
    return array("version" => "1");
  }
}
