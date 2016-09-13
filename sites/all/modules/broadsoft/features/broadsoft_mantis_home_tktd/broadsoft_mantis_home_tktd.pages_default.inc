<?php
/**
 * @file
 * broadsoft_mantis_home_tktd.pages_default.inc
 */

/**
 * Implements hook_default_page_manager_pages().
 */
function broadsoft_mantis_home_tktd_default_page_manager_pages() {
  $page = new stdClass();
  $page->disabled = FALSE; /* Edit this to true to make a default page disabled initially */
  $page->api_version = 1;
  $page->name = 'ticketing_dashboard';
  $page->task = 'page';
  $page->admin_title = 'Ticketing Dashboard';
  $page->admin_description = 'Ticketing Dashboard';
  $page->path = 'ticketing';
  $page->access = array();
  $page->menu = array();
  $page->arguments = array();
  $page->conf = array(
    'admin_paths' => FALSE,
  );
  $page->default_handlers = array();
  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'page_ticketing_dashboard__panel';
  $handler->task = 'page';
  $handler->subtask = 'ticketing_dashboard';
  $handler->handler = 'panel_context';
  $handler->weight = 0;
  $handler->conf = array(
    'title' => 'Dashboard',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'name' => 'panel',
  );
  $display = new panels_display();
  $display->layout = 'broadsoft_twocol';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'top' => NULL,
      'left' => array(
        'clean_markup' => array(
          'region_wrapper' => 'none',
          'additional_region_classes' => '',
          'additional_region_attributes' => '',
          'enable_inner_div' => 0,
          'pane_separators' => 0,
        ),
      ),
      'right' => NULL,
    ),
    'left' => array(
      'style' => 'clean_element',
    ),
  );
  $display->cache = array();
  $display->title = 'Ticketing';
  $display->uuid = '7144d2e7-0c47-4242-a1ed-afdb500aa3fb';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-aefc1abf-2d68-4800-a5f6-4f7839f34147';
    $pane->panel = 'left';
    $pane->type = 'panels_mini';
    $pane->subtype = 'ticketing_tabs';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
      'override_title_heading' => 'h2',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = 'aefc1abf-2d68-4800-a5f6-4f7839f34147';
    $display->content['new-aefc1abf-2d68-4800-a5f6-4f7839f34147'] = $pane;
    $display->panels['left'][0] = 'new-aefc1abf-2d68-4800-a5f6-4f7839f34147';
    $pane = new stdClass();
    $pane->pid = 'new-4031bb95-d20d-4393-b3ba-4332fa377498';
    $pane->panel = 'top';
    $pane->type = 'search_form';
    $pane->subtype = 'search_form';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'type' => 'search_facetapi',
      'form' => 'simple',
      'path_type' => 'default',
      'path' => '',
      'override_prompt' => 0,
      'prompt' => '',
      'context' => 'empty',
      'override_title' => 0,
      'override_title_text' => '',
      'override_title_heading' => 'h2',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => array(
        'clean_markup' => array(
          'pane_wrapper' => 'none',
          'additional_pane_classes' => 'panel-pane',
          'additional_pane_attributes' => '',
          'enable_inner_div' => 0,
          'title_wrapper' => 'h2',
          'title_hide' => 1,
          'content_wrapper' => 'none',
        ),
      ),
      'style' => 'clean_element',
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '4031bb95-d20d-4393-b3ba-4332fa377498';
    $display->content['new-4031bb95-d20d-4393-b3ba-4332fa377498'] = $pane;
    $display->panels['top'][0] = 'new-4031bb95-d20d-4393-b3ba-4332fa377498';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $page->default_handlers[$handler->name] = $handler;
  $pages['ticketing_dashboard'] = $page;

  return $pages;

}
