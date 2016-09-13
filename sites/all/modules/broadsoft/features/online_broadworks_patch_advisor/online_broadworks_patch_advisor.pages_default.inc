<?php
/**
 * @file
 * online_broadworks_patch_advisor.pages_default.inc
 */

/**
 * Implements hook_default_page_manager_pages().
 */
function online_broadworks_patch_advisor_default_page_manager_pages() {
  $page = new stdClass();
  $page->disabled = FALSE; /* Edit this to true to make a default page disabled initially */
  $page->api_version = 1;
  $page->name = 'broadsoft_patch_advisor';
  $page->task = 'page';
  $page->admin_title = 'Broadsoft Patch advisor';
  $page->admin_description = 'Patch advisor';
  $page->path = 'patchadvisor';
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
  $handler->name = 'page_broadsoft_patch_advisor__panel_context_c941bf36-ae80-4d5f-8569-1c84b8130ed2';
  $handler->task = 'page';
  $handler->subtask = 'broadsoft_patch_advisor';
  $handler->handler = 'panel_context';
  $handler->weight = 0;
  $handler->conf = array(
    'title' => 'Panel',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'name' => '',
  );
  $display = new panels_display();
  $display->layout = 'broadsoft_twocol_fw';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'left' => NULL,
      'right' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = '51453f66-083e-4cf2-abd6-169d3f30bc03';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-58c4dd56-9bc5-41e3-87a7-d800deea3a41';
    $pane->panel = 'left';
    $pane->type = 'block';
    $pane->subtype = 'broadworks_patch_advisor-patch_advisor';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 1,
      'override_title_text' => 'Online Broadworks Patch Advisor',
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
    $pane->uuid = '58c4dd56-9bc5-41e3-87a7-d800deea3a41';
    $display->content['new-58c4dd56-9bc5-41e3-87a7-d800deea3a41'] = $pane;
    $display->panels['left'][0] = 'new-58c4dd56-9bc5-41e3-87a7-d800deea3a41';
    $pane = new stdClass();
    $pane->pid = 'new-2ce21c10-abbc-4894-af90-7c80f0093d7e';
    $pane->panel = 'right';
    $pane->type = 'views';
    $pane->subtype = 'test_view';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_pager_settings' => 0,
      'use_pager' => 0,
      'nodes_per_page' => '5',
      'pager_id' => '0',
      'offset' => '0',
      'more_link' => 0,
      'feed_icons' => 0,
      'panel_args' => 0,
      'link_to_view' => 0,
      'args' => '',
      'url' => '',
      'display' => 'default',
      'override_title' => 1,
      'override_title_text' => 'My Favorites',
      'override_title_heading' => 'h3',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '2ce21c10-abbc-4894-af90-7c80f0093d7e';
    $display->content['new-2ce21c10-abbc-4894-af90-7c80f0093d7e'] = $pane;
    $display->panels['right'][0] = 'new-2ce21c10-abbc-4894-af90-7c80f0093d7e';
    $pane = new stdClass();
    $pane->pid = 'new-446c6b62-63ce-4d00-a93a-3820fe919959';
    $pane->panel = 'right';
    $pane->type = 'views';
    $pane->subtype = 'test_view';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_pager_settings' => 0,
      'use_pager' => 0,
      'nodes_per_page' => '5',
      'pager_id' => '0',
      'offset' => '0',
      'more_link' => 0,
      'feed_icons' => 0,
      'panel_args' => 0,
      'link_to_view' => 0,
      'args' => '',
      'url' => '',
      'display' => 'default',
      'override_title' => 1,
      'override_title_text' => 'Recently Viewed ',
      'override_title_heading' => 'h2',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 1;
    $pane->locks = array();
    $pane->uuid = '446c6b62-63ce-4d00-a93a-3820fe919959';
    $display->content['new-446c6b62-63ce-4d00-a93a-3820fe919959'] = $pane;
    $display->panels['right'][1] = 'new-446c6b62-63ce-4d00-a93a-3820fe919959';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $page->default_handlers[$handler->name] = $handler;
  $pages['broadsoft_patch_advisor'] = $page;

  return $pages;

}
