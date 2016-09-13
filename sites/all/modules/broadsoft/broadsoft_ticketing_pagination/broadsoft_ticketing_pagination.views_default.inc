<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function broadsoft_ticketing_pagination_views_default_views(){
  $view = new view;
  $view->name = BROADSOFT_TICKETING_VIEW;
  $view->description = 'Broadsoft Ticketing pagination view showing tickets from JIRA';
  $view->tag = 'jira_tickets';
  $view->base_table = 'broadsoft_ticketing_pagination';
  $view->human_name = 'Jira Tickets View';
  if (isD6()){
    $view->core = 6;
  } else {
    $view->core = 7;
  }
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Page */
  $handler = $view->new_display('default', 'Page', 'default');
  $handler->display->display_options['title'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['use_ajax'] = TRUE;
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '25';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'id' => 'id',
    'title' => 'title',
    'contact' => 'contact',
    'severity_level' => 'severity_level',
    'created_on' => 'created_on',
    'updated_on' => 'updated_on',
    'status' => 'status',
  );
$handler->display->display_options['style_options']['default'] = 'created_on';
$handler->display->display_options['style_options']['info'] = array(
  'id' => array(
    'sortable' => 1,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
  ),
  'title' => array(
    'sortable' => 1,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
  ),
  'contact' => array(
    'sortable' => 1,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
  ),
  'severity_level' => array(
    'sortable' => 1,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
  ),
  'created_on' => array(
    'sortable' => 1,
    'click sortable' => 0,
    'default_sort_order' => 'desc',
    'align' => '',
    'separator' => '',
  ),
  'updated_on' => array(
    'sortable' => 1,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
  ),
  'status' => array(
    'sortable' => 1,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
  ),
);
$handler->display->display_options['style_options']['override'] = 0;
$handler->display->display_options['style_options']['sticky'] = 1;
  $handler->display->display_options['style_options']['empty_table'] = 0;
  /* Field: Broadsoft Ticketing Pagination: ID */
  $handler->display->display_options['fields']['id']['id'] = 'id';
  $handler->display->display_options['fields']['id']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['id']['field'] = 'id';
  $handler->display->display_options['fields']['id']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['id']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['id']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['id']['alter']['external'] = 0;
  $handler->display->display_options['fields']['id']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['id']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['id']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['id']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['id']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['id']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['id']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['id']['alter']['html'] = 0;
  $handler->display->display_options['fields']['id']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['id']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['id']['hide_empty'] = 0;
  $handler->display->display_options['fields']['id']['empty_zero'] = 0;
  $handler->display->display_options['fields']['id']['hide_alter_empty'] = 1;
  /* Field: Broadsoft Ticketing Pagination: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['title']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['title']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['title']['alter']['external'] = 0;
  $handler->display->display_options['fields']['title']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['title']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['title']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['title']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['title']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['title']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['title']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['title']['alter']['html'] = 0;
  $handler->display->display_options['fields']['title']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['title']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['title']['hide_empty'] = 0;
  $handler->display->display_options['fields']['title']['empty_zero'] = 0;
  $handler->display->display_options['fields']['title']['hide_alter_empty'] = 1;
  /* Field: Broadsoft Ticketing Pagination: Contact */
  $handler->display->display_options['fields']['contact']['id'] = 'contact';
  $handler->display->display_options['fields']['contact']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['contact']['field'] = 'contact';
  $handler->display->display_options['fields']['contact']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['external'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['contact']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['contact']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['html'] = 0;
  $handler->display->display_options['fields']['contact']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['contact']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['contact']['hide_empty'] = 0;
  $handler->display->display_options['fields']['contact']['empty_zero'] = 0;
  $handler->display->display_options['fields']['contact']['hide_alter_empty'] = 1;
  /* Field: Broadsoft Ticketing Pagination: Severity level */
  $handler->display->display_options['fields']['severity_level']['id'] = 'severity_level';
  $handler->display->display_options['fields']['severity_level']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['severity_level']['field'] = 'severity_level';
  $handler->display->display_options['fields']['severity_level']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['external'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['severity_level']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['severity_level']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['html'] = 0;
  $handler->display->display_options['fields']['severity_level']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['severity_level']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['severity_level']['hide_empty'] = 0;
  $handler->display->display_options['fields']['severity_level']['empty_zero'] = 0;
  $handler->display->display_options['fields']['severity_level']['hide_alter_empty'] = 1;
  /* Field: Broadsoft Ticketing Pagination: Created on */
  $handler->display->display_options['fields']['created_on']['id'] = 'created_on';
  $handler->display->display_options['fields']['created_on']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['created_on']['field'] = 'created_on';
  $handler->display->display_options['fields']['created_on']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['external'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['created_on']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['created_on']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['html'] = 0;
  $handler->display->display_options['fields']['created_on']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['created_on']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['created_on']['hide_empty'] = 0;
  $handler->display->display_options['fields']['created_on']['empty_zero'] = 0;
  $handler->display->display_options['fields']['created_on']['hide_alter_empty'] = 1;
  /* Field: Broadsoft Ticketing Pagination: Updated On */
  $handler->display->display_options['fields']['updated_on']['id'] = 'updated_on';
  $handler->display->display_options['fields']['updated_on']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['updated_on']['field'] = 'updated_on';
  $handler->display->display_options['fields']['updated_on']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['external'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['updated_on']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['updated_on']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['html'] = 0;
  $handler->display->display_options['fields']['updated_on']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['updated_on']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['updated_on']['hide_empty'] = 0;
  $handler->display->display_options['fields']['updated_on']['empty_zero'] = 0;
  $handler->display->display_options['fields']['updated_on']['hide_alter_empty'] = 1;
  /* Field: Broadsoft Ticketing Pagination: Status */
  $handler->display->display_options['fields']['status']['id'] = 'status';
  $handler->display->display_options['fields']['status']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['status']['field'] = 'status';
  $handler->display->display_options['fields']['status']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['status']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['status']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['status']['alter']['external'] = 0;
  $handler->display->display_options['fields']['status']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['status']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['status']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['status']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['status']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['status']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['status']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['status']['alter']['html'] = 0;
  $handler->display->display_options['fields']['status']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['status']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['status']['hide_empty'] = 0;
  $handler->display->display_options['fields']['status']['empty_zero'] = 0;
  $handler->display->display_options['fields']['status']['hide_alter_empty'] = 1;

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', BROADSOFT_TICKETING_VIEW_DISPLAY);
  $handler->display->display_options['defaults']['pager'] = FALSE;
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '25';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['pager']['options']['id'] = '0';
  $handler->display->display_options['pager']['options']['expose']['items_per_page_options_all'] = 0;
  $handler->display->display_options['defaults']['style_plugin'] = FALSE;
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'id' => 'id',
    'title' => 'title',
    'contact' => 'contact',
    'severity_level' => 'severity_level',
    'created_on' => 'created_on',
    'updated_on' => 'updated_on',
    'status' => 'status',
  );
  $handler->display->display_options['style_options']['default'] = 'created_on';
  $handler->display->display_options['style_options']['info'] = array(
    'id' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'title' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'contact' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'severity_level' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'created_on' => array(
      'sortable' => 1,
      'click sortable' => 0,
      'default_sort_order' => 'desc',
      'align' => '',
      'separator' => '',
    ),
    'updated_on' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'status' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
  );
  $handler->display->display_options['style_options']['override'] = 0;
  $handler->display->display_options['style_options']['sticky'] = 1;
  $handler->display->display_options['style_options']['empty_table'] = 0;
  $handler->display->display_options['defaults']['style_options'] = FALSE;
  $handler->display->display_options['defaults']['row_plugin'] = FALSE;
  $handler->display->display_options['defaults']['row_options'] = FALSE;
  $handler->display->display_options['defaults']['footer'] = FALSE;
  /* Footer: Global: Text area */
  $handler->display->display_options['footer']['area']['id'] = 'area';
  $handler->display->display_options['footer']['area']['table'] = 'views';
  $handler->display->display_options['footer']['area']['field'] = 'area';
  $handler->display->display_options['footer']['area']['empty'] = TRUE;
  $handler->display->display_options['footer']['area']['content'] = '<?php
    ticketingFooter();
  ?>';
  $handler->display->display_options['footer']['area']['format'] = '3';
  $handler->display->display_options['footer']['area']['tokenize'] = 0;
  $handler->display->display_options['defaults']['fields'] = FALSE;
  /* Field: Broadsoft Ticketing Pagination: ID */
  $handler->display->display_options['fields']['id']['id'] = 'id';
  $handler->display->display_options['fields']['id']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['id']['field'] = 'id';
  $handler->display->display_options['fields']['id']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['id']['alter']['make_link'] = 1;
  $handler->display->display_options['fields']['id']['alter']['path'] = '/support/ticketing/ticket/[id]';
  $handler->display->display_options['fields']['id']['alter']['absolute'] = 1;
  $handler->display->display_options['fields']['id']['alter']['external'] = 0;
  $handler->display->display_options['fields']['id']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['id']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['id']['alter']['target'] = '_blank';
  $handler->display->display_options['fields']['id']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['id']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['id']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['id']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['id']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['id']['alter']['html'] = 0;
  $handler->display->display_options['fields']['id']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['id']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['id']['hide_empty'] = 0;
  $handler->display->display_options['fields']['id']['empty_zero'] = 0;
  $handler->display->display_options['fields']['id']['hide_alter_empty'] = 1;
  /* Field: Broadsoft Ticketing Pagination: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['title']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['title']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['title']['alter']['external'] = 0;
  $handler->display->display_options['fields']['title']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['title']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['title']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['title']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['title']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['title']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['title']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['title']['alter']['html'] = 0;
  $handler->display->display_options['fields']['title']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['title']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['title']['hide_empty'] = 0;
  $handler->display->display_options['fields']['title']['empty_zero'] = 0;
  $handler->display->display_options['fields']['title']['hide_alter_empty'] = 1;
  /* Field: Broadsoft Ticketing Pagination: Contact */
  $handler->display->display_options['fields']['contact']['id'] = 'contact';
  $handler->display->display_options['fields']['contact']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['contact']['field'] = 'contact';
  $handler->display->display_options['fields']['contact']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['external'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['contact']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['contact']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['contact']['alter']['html'] = 0;
  $handler->display->display_options['fields']['contact']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['contact']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['contact']['hide_empty'] = 0;
  $handler->display->display_options['fields']['contact']['empty_zero'] = 0;
  $handler->display->display_options['fields']['contact']['hide_alter_empty'] = 1;
  /* Field: Broadsoft Ticketing Pagination: Severity level */
  $handler->display->display_options['fields']['severity_level']['id'] = 'severity_level';
  $handler->display->display_options['fields']['severity_level']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['severity_level']['field'] = 'severity_level';
  $handler->display->display_options['fields']['severity_level']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['external'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['severity_level']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['severity_level']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['severity_level']['alter']['html'] = 0;
  $handler->display->display_options['fields']['severity_level']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['severity_level']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['severity_level']['hide_empty'] = 0;
  $handler->display->display_options['fields']['severity_level']['empty_zero'] = 0;
  $handler->display->display_options['fields']['severity_level']['hide_alter_empty'] = 1;
  /* Field: Broadsoft Ticketing Pagination: Created on */
  $handler->display->display_options['fields']['created_on']['id'] = 'created_on';
  $handler->display->display_options['fields']['created_on']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['created_on']['field'] = 'created_on';
  $handler->display->display_options['fields']['created_on']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['external'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['created_on']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['created_on']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['created_on']['alter']['html'] = 0;
  $handler->display->display_options['fields']['created_on']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['created_on']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['created_on']['hide_empty'] = 0;
  $handler->display->display_options['fields']['created_on']['empty_zero'] = 0;
  $handler->display->display_options['fields']['created_on']['hide_alter_empty'] = 1;
  /* Field: Broadsoft Ticketing Pagination: Updated On */
  $handler->display->display_options['fields']['updated_on']['id'] = 'updated_on';
  $handler->display->display_options['fields']['updated_on']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['updated_on']['field'] = 'updated_on';
  $handler->display->display_options['fields']['updated_on']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['external'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['updated_on']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['updated_on']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['updated_on']['alter']['html'] = 0;
  $handler->display->display_options['fields']['updated_on']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['updated_on']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['updated_on']['hide_empty'] = 0;
  $handler->display->display_options['fields']['updated_on']['empty_zero'] = 0;
  $handler->display->display_options['fields']['updated_on']['hide_alter_empty'] = 1;
  /* Field: Broadsoft Ticketing Pagination: Status */
  $handler->display->display_options['fields']['status']['id'] = 'status';
  $handler->display->display_options['fields']['status']['table'] = 'broadsoft_ticketing_pagination';
  $handler->display->display_options['fields']['status']['field'] = 'status';
  $handler->display->display_options['fields']['status']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['status']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['status']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['status']['alter']['external'] = 0;
  $handler->display->display_options['fields']['status']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['status']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['status']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['status']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['status']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['status']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['status']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['status']['alter']['html'] = 0;
  $handler->display->display_options['fields']['status']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['status']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['status']['hide_empty'] = 0;
  $handler->display->display_options['fields']['status']['empty_zero'] = 0;
  $handler->display->display_options['fields']['status']['hide_alter_empty'] = 1;
  $handler->display->display_options['path'] = 'support/tickets';
  
  /* Display: Data export */
  $handler = $view->new_display('views_data_export', 'Data export', 'views_data_export_1');
  $handler->display->display_options['defaults']['access'] = FALSE;
  $handler->display->display_options['access']['type'] = 'role';
  $handler->display->display_options['access']['role'] = array(
    37 => 37,
    35 => 35,
    38 => 38,
  );
  $handler->display->display_options['pager']['type'] = 'some';
  $handler->display->display_options['style_plugin'] = 'views_data_export_csv';
  $handler->display->display_options['style_options']['provide_file'] = 1;
  $handler->display->display_options['style_options']['filename'] = '%view%timestamp-full.csv';
  $handler->display->display_options['style_options']['parent_sort'] = 0;
  $handler->display->display_options['style_options']['quote'] = 1;
  $handler->display->display_options['style_options']['trim'] = 0;
  $handler->display->display_options['style_options']['replace_newlines'] = 0;
  $handler->display->display_options['style_options']['header'] = 1;
  $handler->display->display_options['path'] = 'support/ticketing/export';
  $handler->display->display_options['displays'] = array(
    'page' => 'page',
    'page' => 0,
  );

  $views[$view->name] = $view;
  return $views;
}
