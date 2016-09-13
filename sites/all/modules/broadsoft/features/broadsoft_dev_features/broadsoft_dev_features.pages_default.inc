<?php
/**
 * @file
 * broadsoft_dev_features.pages_default.inc
 */

/**
 * Implements hook_default_page_manager_pages().
 */
function broadsoft_dev_features_default_page_manager_pages() {
  $page = new stdClass();
  $page->disabled = FALSE; /* Edit this to true to make a default page disabled initially */
  $page->api_version = 1;
  $page->name = 'broadsoft_search';
  $page->task = 'page';
  $page->admin_title = 'Broadsoft Search';
  $page->admin_description = '';
  $page->path = 'broadsoft-search/!term';
  $page->access = array();
  $page->menu = array();
  $page->arguments = array(
    'term' => array(
      'id' => 1,
      'identifier' => 'Search term',
      'name' => 'string',
      'settings' => array(
        'use_tail' => 0,
      ),
    ),
  );
  $page->conf = array(
    'admin_paths' => FALSE,
  );
  $page->default_handlers = array();
  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'page_broadsoft_search__panel';
  $handler->task = 'page';
  $handler->subtask = 'broadsoft_search';
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
    'name' => 'panel',
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
  $display->uuid = '3503a1ce-1d18-48ca-8b0d-7b479337ea9d';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-be9f5c93-a680-44f6-8286-2e55e05c719d';
    $pane->panel = 'left';
    $pane->type = 'block';
    $pane->subtype = 'views-c2a77ec859e95e93e0934d1d13c0ac9e';
    $pane->shown = FALSE;
    $pane->access = array();
    $pane->configuration = array(
      'inherit_path' => 0,
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
    $pane->uuid = 'be9f5c93-a680-44f6-8286-2e55e05c719d';
    $display->content['new-be9f5c93-a680-44f6-8286-2e55e05c719d'] = $pane;
    $display->panels['left'][0] = 'new-be9f5c93-a680-44f6-8286-2e55e05c719d';
    $pane = new stdClass();
    $pane->pid = 'new-6a864101-2102-465d-96cd-fa8c229c1255';
    $pane->panel = 'left';
    $pane->type = 'views_panes';
    $pane->subtype = 'broadsoft_content_search-panel_pane_1';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array();
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 1;
    $pane->locks = array();
    $pane->uuid = '6a864101-2102-465d-96cd-fa8c229c1255';
    $display->content['new-6a864101-2102-465d-96cd-fa8c229c1255'] = $pane;
    $display->panels['left'][1] = 'new-6a864101-2102-465d-96cd-fa8c229c1255';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = 'new-6a864101-2102-465d-96cd-fa8c229c1255';
  $handler->conf['display'] = $display;
  $page->default_handlers[$handler->name] = $handler;
  $pages['broadsoft_search'] = $page;

  $page = new stdClass();
  $page->disabled = FALSE; /* Edit this to true to make a default page disabled initially */
  $page->api_version = 1;
  $page->name = 'ticketing_manage_ticket';
  $page->task = 'page';
  $page->admin_title = 'Ticketing Manage Ticket';
  $page->admin_description = 'Manage individual tickets';
  $page->path = 'ticketing/ticket/%ticket_id';
  $page->access = array(
    'plugins' => array(
      0 => array(
        'name' => 'context_exists',
        'settings' => array(
          'exists' => '1',
        ),
        'context' => 'argument_string_1',
        'not' => FALSE,
      ),
    ),
    'logic' => 'and',
  );
  $page->menu = array();
  $page->arguments = array(
    'ticket_id' => array(
      'id' => 1,
      'identifier' => 'Ticket ID',
      'name' => 'string',
      'settings' => array(
        'use_tail' => 0,
      ),
    ),
  );
  $page->conf = array(
    'admin_paths' => FALSE,
  );
  $page->default_handlers = array();
  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'page_ticketing_manage_ticket__panel';
  $handler->task = 'page';
  $handler->subtask = 'ticketing_manage_ticket';
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
    'name' => 'panel',
  );
  $display = new panels_display();
  $display->layout = 'broadsoft_twocol';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'top' => NULL,
      'left' => NULL,
      'right' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = 'a0d2a45f-917f-4093-a10f-56d5e0f99d16';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-b99b9660-5bec-4e75-ad8c-b3dadefa1d70';
    $pane->panel = 'left';
    $pane->type = 'page_messages';
    $pane->subtype = 'page_messages';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array();
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = 'b99b9660-5bec-4e75-ad8c-b3dadefa1d70';
    $display->content['new-b99b9660-5bec-4e75-ad8c-b3dadefa1d70'] = $pane;
    $display->panels['left'][0] = 'new-b99b9660-5bec-4e75-ad8c-b3dadefa1d70';
    $pane = new stdClass();
    $pane->pid = 'new-2f3d2a10-d721-4575-9e31-f99b766361e6';
    $pane->panel = 'left';
    $pane->type = 'existing_ticket_form';
    $pane->subtype = 'existing_ticket_form';
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
    $pane->position = 1;
    $pane->locks = array();
    $pane->uuid = '2f3d2a10-d721-4575-9e31-f99b766361e6';
    $display->content['new-2f3d2a10-d721-4575-9e31-f99b766361e6'] = $pane;
    $display->panels['left'][1] = 'new-2f3d2a10-d721-4575-9e31-f99b766361e6';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $page->default_handlers[$handler->name] = $handler;
  $pages['ticketing_manage_ticket'] = $page;

  return $pages;

}
