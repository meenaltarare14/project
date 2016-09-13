<?php
/**
 * @file
 * broadsoft_dev_features.pm_existing_pages.inc
 */

/**
 * Implements hook_pm_existing_pages_info().
 */
function broadsoft_dev_features_pm_existing_pages_info() {
  $export = array();

  $pm_existing_page = new stdClass();
  $pm_existing_page->api_version = 1;
  $pm_existing_page->name = 'search_page';
  $pm_existing_page->label = 'Search Page';
  $pm_existing_page->context = '';
  $pm_existing_page->paths = 'broadsoft-content-search';
  $export['search_page'] = $pm_existing_page;

  return $export;
}
