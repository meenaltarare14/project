<?php
/**
 * @file
 * broadsoft_dev_features.views_default.inc
 */

/**
 * Implements hook_views_default_views().
 */
function broadsoft_dev_features_views_default_views() {
  $export = array();

  $view = new view();
  $view->name = 'broadsoft_content_search';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'search_api_index_ticketing_suggestions';
  $view->human_name = 'Broadsoft Content Search';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Broadsoft Content Search';
  $handler->display->display_options['use_ajax'] = TRUE;
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '10';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['pager']['options']['id'] = '0';
  $handler->display->display_options['pager']['options']['quantity'] = '5';
  $handler->display->display_options['pager']['options']['tags']['first'] = '«';
  $handler->display->display_options['pager']['options']['tags']['previous'] = '‹';
  $handler->display->display_options['pager']['options']['tags']['next'] = '›';
  $handler->display->display_options['pager']['options']['tags']['last'] = '»';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'title' => 'title',
    'field_radioactivity' => 'field_radioactivity',
    'changed' => 'changed',
    'body' => 'title',
    'type' => 'title',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'title' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_radioactivity' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'changed' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'body' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'type' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  /* Field: Indexed Node: Content type */
  $handler->display->display_options['fields']['type']['id'] = 'type';
  $handler->display->display_options['fields']['type']['table'] = 'search_api_index_ticketing_suggestions';
  $handler->display->display_options['fields']['type']['field'] = 'type';
  $handler->display->display_options['fields']['type']['label'] = '';
  $handler->display->display_options['fields']['type']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['type']['link_to_entity'] = 0;
  $handler->display->display_options['fields']['type']['format_name'] = 1;
  /* Field: Indexed Node: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'search_api_index_ticketing_suggestions';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['link_to_entity'] = 1;
  /* Field: Indexed Node: The main body text */
  $handler->display->display_options['fields']['body']['id'] = 'body';
  $handler->display->display_options['fields']['body']['table'] = 'search_api_index_ticketing_suggestions';
  $handler->display->display_options['fields']['body']['field'] = 'body';
  $handler->display->display_options['fields']['body']['label'] = '';
  $handler->display->display_options['fields']['body']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['body']['type'] = 'text_trimmed';
  $handler->display->display_options['fields']['body']['settings'] = array(
    'trim_length' => '100',
  );
  /* Field: Indexed Node: Radioactivity */
  $handler->display->display_options['fields']['field_radioactivity']['id'] = 'field_radioactivity';
  $handler->display->display_options['fields']['field_radioactivity']['table'] = 'search_api_index_ticketing_suggestions';
  $handler->display->display_options['fields']['field_radioactivity']['field'] = 'field_radioactivity';
  $handler->display->display_options['fields']['field_radioactivity']['label'] = 'Rating';
  $handler->display->display_options['fields']['field_radioactivity']['click_sort_column'] = 'radioactivity_energy';
  $handler->display->display_options['fields']['field_radioactivity']['settings'] = array(
    'energy' => '10',
    'type' => 'energy',
    'accuracy' => '100',
  );
  /* Field: Indexed Node: Date changed */
  $handler->display->display_options['fields']['changed']['id'] = 'changed';
  $handler->display->display_options['fields']['changed']['table'] = 'search_api_index_ticketing_suggestions';
  $handler->display->display_options['fields']['changed']['field'] = 'changed';
  $handler->display->display_options['fields']['changed']['label'] = 'Last Updated';
  $handler->display->display_options['fields']['changed']['date_format'] = 'custom';
  $handler->display->display_options['fields']['changed']['custom_date_format'] = 'm/d/Y';
  $handler->display->display_options['fields']['changed']['second_date_format'] = 'privatemsg_current_day';
  $handler->display->display_options['fields']['changed']['link_to_entity'] = 0;
  /* Filter criterion: Search: Fulltext search */
  $handler->display->display_options['filters']['search_api_views_fulltext']['id'] = 'search_api_views_fulltext';
  $handler->display->display_options['filters']['search_api_views_fulltext']['table'] = 'search_api_index_ticketing_suggestions';
  $handler->display->display_options['filters']['search_api_views_fulltext']['field'] = 'search_api_views_fulltext';
  $handler->display->display_options['filters']['search_api_views_fulltext']['operator'] = 'OR';
  $handler->display->display_options['filters']['search_api_views_fulltext']['exposed'] = TRUE;
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['operator_id'] = 'search_api_views_fulltext_op';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['label'] = 'Fulltext search';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['operator'] = 'search_api_views_fulltext_op';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['identifier'] = 'search_api_views_fulltext';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
    4 => 0,
  );
  $handler->display->display_options['filters']['search_api_views_fulltext']['fields'] = array(
    'title' => 'title',
  );

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['exposed_block'] = TRUE;
  $handler->display->display_options['path'] = 'broadsoft-content-search';

  /* Display: Content pane */
  $handler = $view->new_display('panel_pane', 'Content pane', 'panel_pane_1');
  $handler->display->display_options['defaults']['title'] = FALSE;
  $export['broadsoft_content_search'] = $view;

  $view = new view();
  $view->name = 'customer_accounts';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'node';
  $view->human_name = 'Customer Accounts';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Customer Accounts';
  $handler->display->display_options['css_class'] = 'ticketing-content';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '100';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['row_class_special'] = FALSE;
  $handler->display->display_options['style_options']['columns'] = array(
    'title' => 'title',
    'field_cid' => 'field_cid',
    'field_sfdc_id' => 'field_sfdc_id',
    'field_can_access_ticketing' => 'field_can_access_ticketing',
    'field_account_status' => 'field_account_status',
    'changed' => 'changed',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'title' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_cid' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_sfdc_id' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_can_access_ticketing' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_account_status' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'changed' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  /* Field: Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'node';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
  $handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
  /* Field: Content: CID */
  $handler->display->display_options['fields']['field_cid']['id'] = 'field_cid';
  $handler->display->display_options['fields']['field_cid']['table'] = 'field_data_field_cid';
  $handler->display->display_options['fields']['field_cid']['field'] = 'field_cid';
  /* Field: Content: Sales Force Account ID */
  $handler->display->display_options['fields']['field_sfdc_id']['id'] = 'field_sfdc_id';
  $handler->display->display_options['fields']['field_sfdc_id']['table'] = 'field_data_field_sfdc_id';
  $handler->display->display_options['fields']['field_sfdc_id']['field'] = 'field_sfdc_id';
  /* Field: Content: Can Access Ticketing */
  $handler->display->display_options['fields']['field_can_access_ticketing']['id'] = 'field_can_access_ticketing';
  $handler->display->display_options['fields']['field_can_access_ticketing']['table'] = 'field_data_field_can_access_ticketing';
  $handler->display->display_options['fields']['field_can_access_ticketing']['field'] = 'field_can_access_ticketing';
  $handler->display->display_options['fields']['field_can_access_ticketing']['label'] = 'Ticketing Access';
  $handler->display->display_options['fields']['field_can_access_ticketing']['empty'] = 'No';
  /* Field: Content: Account Status */
  $handler->display->display_options['fields']['field_account_status']['id'] = 'field_account_status';
  $handler->display->display_options['fields']['field_account_status']['table'] = 'field_data_field_account_status';
  $handler->display->display_options['fields']['field_account_status']['field'] = 'field_account_status';
  /* Field: Content: Updated date */
  $handler->display->display_options['fields']['changed']['id'] = 'changed';
  $handler->display->display_options['fields']['changed']['table'] = 'node';
  $handler->display->display_options['fields']['changed']['field'] = 'changed';
  $handler->display->display_options['fields']['changed']['date_format'] = 'short';
  $handler->display->display_options['fields']['changed']['second_date_format'] = 'privatemsg_current_day';
  /* Filter criterion: Content: Published */
  $handler->display->display_options['filters']['status']['id'] = 'status';
  $handler->display->display_options['filters']['status']['table'] = 'node';
  $handler->display->display_options['filters']['status']['field'] = 'status';
  $handler->display->display_options['filters']['status']['value'] = 1;
  $handler->display->display_options['filters']['status']['group'] = 1;
  $handler->display->display_options['filters']['status']['expose']['operator'] = FALSE;
  /* Filter criterion: Content: Type */
  $handler->display->display_options['filters']['type']['id'] = 'type';
  $handler->display->display_options['filters']['type']['table'] = 'node';
  $handler->display->display_options['filters']['type']['field'] = 'type';
  $handler->display->display_options['filters']['type']['value'] = array(
    'customer_account' => 'customer_account',
  );
  $handler->display->display_options['filters']['type']['group'] = 1;
  /* Filter criterion: Content: Title */
  $handler->display->display_options['filters']['title']['id'] = 'title';
  $handler->display->display_options['filters']['title']['table'] = 'node';
  $handler->display->display_options['filters']['title']['field'] = 'title';
  $handler->display->display_options['filters']['title']['operator'] = 'contains';
  $handler->display->display_options['filters']['title']['group'] = 1;
  $handler->display->display_options['filters']['title']['exposed'] = TRUE;
  $handler->display->display_options['filters']['title']['expose']['operator_id'] = 'title_op';
  $handler->display->display_options['filters']['title']['expose']['label'] = 'Account Name contains';
  $handler->display->display_options['filters']['title']['expose']['operator'] = 'title_op';
  $handler->display->display_options['filters']['title']['expose']['identifier'] = 'title';
  $handler->display->display_options['filters']['title']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
    4 => 0,
  );
  /* Filter criterion: Content: CID (field_cid) */
  $handler->display->display_options['filters']['field_cid_value']['id'] = 'field_cid_value';
  $handler->display->display_options['filters']['field_cid_value']['table'] = 'field_data_field_cid';
  $handler->display->display_options['filters']['field_cid_value']['field'] = 'field_cid_value';
  $handler->display->display_options['filters']['field_cid_value']['group'] = 1;
  $handler->display->display_options['filters']['field_cid_value']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_cid_value']['expose']['operator_id'] = 'field_cid_value_op';
  $handler->display->display_options['filters']['field_cid_value']['expose']['label'] = '  Account CID is';
  $handler->display->display_options['filters']['field_cid_value']['expose']['operator'] = 'field_cid_value_op';
  $handler->display->display_options['filters']['field_cid_value']['expose']['identifier'] = 'field_cid_value';
  $handler->display->display_options['filters']['field_cid_value']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
    4 => 0,
  );
  /* Filter criterion: Content: Can Access Ticketing (field_can_access_ticketing) */
  $handler->display->display_options['filters']['field_can_access_ticketing_value']['id'] = 'field_can_access_ticketing_value';
  $handler->display->display_options['filters']['field_can_access_ticketing_value']['table'] = 'field_data_field_can_access_ticketing';
  $handler->display->display_options['filters']['field_can_access_ticketing_value']['field'] = 'field_can_access_ticketing_value';
  $handler->display->display_options['filters']['field_can_access_ticketing_value']['group'] = 1;
  $handler->display->display_options['filters']['field_can_access_ticketing_value']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_can_access_ticketing_value']['expose']['operator_id'] = 'field_can_access_ticketing_value_op';
  $handler->display->display_options['filters']['field_can_access_ticketing_value']['expose']['label'] = 'Ticketing Access';
  $handler->display->display_options['filters']['field_can_access_ticketing_value']['expose']['operator'] = 'field_can_access_ticketing_value_op';
  $handler->display->display_options['filters']['field_can_access_ticketing_value']['expose']['identifier'] = 'field_can_access_ticketing_value';
  $handler->display->display_options['filters']['field_can_access_ticketing_value']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
    4 => 0,
  );

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'ticketing/customer-accounts';

  /* Display: CSV Export */
  $handler = $view->new_display('views_data_export', 'CSV Export', 'views_data_export_1');
  $handler->display->display_options['pager']['type'] = 'some';
  $handler->display->display_options['style_plugin'] = 'views_data_export_csv';
  $handler->display->display_options['style_options']['provide_file'] = 1;
  $handler->display->display_options['style_options']['parent_sort'] = 0;
  $handler->display->display_options['style_options']['quote'] = 1;
  $handler->display->display_options['style_options']['trim'] = 0;
  $handler->display->display_options['style_options']['replace_newlines'] = 0;
  $handler->display->display_options['style_options']['newline_token'] = '1';
  $handler->display->display_options['style_options']['header'] = 1;
  $handler->display->display_options['style_options']['keep_html'] = 0;
  $handler->display->display_options['path'] = 'ticketing/customer-accounts/csv';
  $handler->display->display_options['displays'] = array(
    'default' => 0,
    'page' => 0,
  );

  /* Display: XML Export */
  $handler = $view->new_display('views_data_export', 'XML Export', 'views_data_export_2');
  $handler->display->display_options['pager']['type'] = 'some';
  $handler->display->display_options['style_plugin'] = 'views_data_export_xml';
  $handler->display->display_options['style_options']['provide_file'] = 1;
  $handler->display->display_options['style_options']['parent_sort'] = 0;
  $handler->display->display_options['style_options']['transform'] = 1;
  $handler->display->display_options['style_options']['root_node'] = 'customer-accounts';
  $handler->display->display_options['style_options']['item_node'] = 'account';
  $handler->display->display_options['path'] = 'ticketing/customer-accounts/csv';
  $handler->display->display_options['displays'] = array(
    'default' => 0,
    'page' => 0,
  );
  $export['customer_accounts'] = $view;

  return $export;
}
