<?php
/**
 * @file
 * broadsoft_mantis_home_tktd.panels_default.inc
 */

/**
 * Implements hook_default_panels_mini().
 */
function broadsoft_mantis_home_tktd_default_panels_mini() {
  $export = array();

  $mini = new stdClass();
  $mini->disabled = FALSE; /* Edit this to true to make a default mini disabled initially */
  $mini->api_version = 1;
  $mini->name = 'front_page_content_top';
  $mini->category = 'Front page';
  $mini->admin_title = 'Front Page Content Top';
  $mini->admin_description = '';
  $mini->requiredcontexts = array();
  $mini->contexts = array();
  $mini->relationships = array();
  $display = new panels_display();
  $display->layout = 'onecol_reset_clean';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'left' => array(
        'clean_markup' => array(
          'region_wrapper' => 'div',
          'additional_region_classes' => 'row wrap-info-dashboard',
          'additional_region_attributes' => '',
          'enable_inner_div' => 1,
          'pane_separators' => 0,
        ),
      ),
      'right' => NULL,
      'middle' => array(
        'clean_markup' => array(
          'region_wrapper' => 'div',
          'additional_region_classes' => 'col-sm-12',
          'additional_region_attributes' => '',
          'enable_inner_div' => 0,
          'pane_separators' => 0,
        ),
      ),
    ),
    'left' => array(
      'style' => 'clean_element',
    ),
    'middle' => array(
      'style' => 'clean_element',
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = '7f0ce70e-1187-4b2d-8f64-045ea6ec264e';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-dd4832a6-6ca7-4b3f-bb1a-b272e9c04ac8';
    $pane->panel = 'middle';
    $pane->type = 'views';
    $pane->subtype = 'following_content';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_pager_settings' => 1,
      'use_pager' => 0,
      'nodes_per_page' => '4',
      'pager_id' => '0',
      'offset' => '0',
      'more_link' => 1,
      'feed_icons' => 0,
      'panel_args' => 0,
      'link_to_view' => 0,
      'args' => '',
      'url' => '',
      'display' => 'default',
      'override_title' => 1,
      'override_title_text' => 'Recent Activity',
      'override_title_heading' => 'h3',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => array(
        'clean_markup' => array(
          'pane_wrapper' => 'div',
          'additional_pane_classes' => 'pull-left contents-info popular-content',
          'additional_pane_attributes' => '',
          'enable_inner_div' => 0,
          'title_wrapper' => 'h3',
          'title_hide' => 0,
          'content_wrapper' => 'none',
        ),
      ),
      'style' => 'clean_element',
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = 'dd4832a6-6ca7-4b3f-bb1a-b272e9c04ac8';
    $display->content['new-dd4832a6-6ca7-4b3f-bb1a-b272e9c04ac8'] = $pane;
    $display->panels['middle'][0] = 'new-dd4832a6-6ca7-4b3f-bb1a-b272e9c04ac8';
    $pane = new stdClass();
    $pane->pid = 'new-9508706d-d3b0-400b-9a62-a375b1b07511';
    $pane->panel = 'middle';
    $pane->type = 'views';
    $pane->subtype = 'following_content';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_pager_settings' => 1,
      'use_pager' => 0,
      'nodes_per_page' => '4',
      'pager_id' => '0',
      'offset' => '0',
      'more_link' => 1,
      'feed_icons' => 0,
      'panel_args' => 0,
      'link_to_view' => 0,
      'args' => '',
      'url' => '',
      'display' => 'default',
      'override_title' => 1,
      'override_title_text' => 'Recently Answered Questions',
      'override_title_heading' => 'h3',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => array(
        'clean_markup' => array(
          'pane_wrapper' => 'div',
          'additional_pane_classes' => 'panel-pane pull-right contents-info recently-answered-questions',
          'additional_pane_attributes' => '',
          'enable_inner_div' => 0,
          'title_wrapper' => 'h3',
          'title_hide' => 0,
          'content_wrapper' => 'none',
        ),
      ),
      'style' => 'clean_element',
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 1;
    $pane->locks = array();
    $pane->uuid = '9508706d-d3b0-400b-9a62-a375b1b07511';
    $display->content['new-9508706d-d3b0-400b-9a62-a375b1b07511'] = $pane;
    $display->panels['middle'][1] = 'new-9508706d-d3b0-400b-9a62-a375b1b07511';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $mini->display = $display;
  $export['front_page_content_top'] = $mini;

  $mini = new stdClass();
  $mini->disabled = FALSE; /* Edit this to true to make a default mini disabled initially */
  $mini->api_version = 1;
  $mini->name = 'ticketing_tabs';
  $mini->category = '';
  $mini->admin_title = 'Ticketing Tabs';
  $mini->admin_description = 'Ticketing Tabs for dashboard';
  $mini->requiredcontexts = array();
  $mini->contexts = array();
  $mini->relationships = array();
  $display = new panels_display();
  $display->layout = 'broadsoft_tabs';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'tab2' => NULL,
      'tab3' => NULL,
      'tab4' => NULL,
      'tab1' => NULL,
    ),
    'tab1' => array(
      'style' => '-1',
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = '7e809b27-496c-413f-b91a-58b34704c7bd';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-3559ecea-8932-43f4-b358-2b303cc68b70';
    $pane->panel = 'tab1';
    $pane->type = 'views';
    $pane->subtype = 'test_tickets';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_pager_settings' => 0,
      'use_pager' => 1,
      'nodes_per_page' => '10',
      'pager_id' => '0',
      'offset' => '0',
      'more_link' => 0,
      'feed_icons' => 0,
      'panel_args' => 0,
      'link_to_view' => 0,
      'args' => '',
      'url' => '',
      'display' => 'block_1',
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
    $pane->uuid = '3559ecea-8932-43f4-b358-2b303cc68b70';
    $display->content['new-3559ecea-8932-43f4-b358-2b303cc68b70'] = $pane;
    $display->panels['tab1'][0] = 'new-3559ecea-8932-43f4-b358-2b303cc68b70';
    $pane = new stdClass();
    $pane->pid = 'new-2d30e39e-bb83-4ceb-bd77-d912fb7ee472';
    $pane->panel = 'tab2';
    $pane->type = 'views';
    $pane->subtype = 'test_tickets';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_pager_settings' => 0,
      'use_pager' => 1,
      'nodes_per_page' => '10',
      'pager_id' => '0',
      'offset' => '0',
      'more_link' => 0,
      'feed_icons' => 0,
      'panel_args' => 0,
      'link_to_view' => 0,
      'args' => '',
      'url' => '',
      'display' => 'block_2',
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
    $pane->uuid = '2d30e39e-bb83-4ceb-bd77-d912fb7ee472';
    $display->content['new-2d30e39e-bb83-4ceb-bd77-d912fb7ee472'] = $pane;
    $display->panels['tab2'][0] = 'new-2d30e39e-bb83-4ceb-bd77-d912fb7ee472';
    $pane = new stdClass();
    $pane->pid = 'new-0b526bb1-3dd2-4614-81c9-329086a54858';
    $pane->panel = 'tab3';
    $pane->type = 'views';
    $pane->subtype = 'test_tickets';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_pager_settings' => 0,
      'use_pager' => 1,
      'nodes_per_page' => '0',
      'pager_id' => '0',
      'offset' => '0',
      'more_link' => 0,
      'feed_icons' => 0,
      'panel_args' => 0,
      'link_to_view' => 0,
      'args' => '',
      'url' => '',
      'display' => 'block_3',
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
    $pane->uuid = '0b526bb1-3dd2-4614-81c9-329086a54858';
    $display->content['new-0b526bb1-3dd2-4614-81c9-329086a54858'] = $pane;
    $display->panels['tab3'][0] = 'new-0b526bb1-3dd2-4614-81c9-329086a54858';
    $pane = new stdClass();
    $pane->pid = 'new-ab770fcc-ab38-4380-8b7d-157c9ee8dc11';
    $pane->panel = 'tab4';
    $pane->type = 'views';
    $pane->subtype = 'test_tickets';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_pager_settings' => 0,
      'use_pager' => 1,
      'nodes_per_page' => '0',
      'pager_id' => '0',
      'offset' => '0',
      'more_link' => 0,
      'feed_icons' => 0,
      'panel_args' => 0,
      'link_to_view' => 0,
      'args' => '',
      'url' => '',
      'display' => 'block_4',
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
    $pane->uuid = 'ab770fcc-ab38-4380-8b7d-157c9ee8dc11';
    $display->content['new-ab770fcc-ab38-4380-8b7d-157c9ee8dc11'] = $pane;
    $display->panels['tab4'][0] = 'new-ab770fcc-ab38-4380-8b7d-157c9ee8dc11';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $mini->display = $display;
  $export['ticketing_tabs'] = $mini;

  return $export;
}
