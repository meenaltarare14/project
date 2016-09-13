<?php

/**
 * Implementation of hook_views_plugins().
 */
function broadsoft_ticketing_pagination_views_plugins() {
  $plugin = array();
  $plugin['query']['broadsoft_ticketing_pagination_plugin_query'] = array(
    'title' => t('Broadsoft Ticketing Pagination'),
    'help' => t('broadsoft_ticketing_pagination query object.'),
    'handler' => 'broadsoft_ticketing_pagination_plugin_query',
  );
  return $plugin;
}

/**
 * Implementation of hook_views_data().
 */
function broadsoft_ticketing_pagination_views_data() {
  $data = array();

  // Base data
  $data['broadsoft_ticketing_pagination']['table']['group']  = t('Broadsoft Ticketing Pagination');
  $data['broadsoft_ticketing_pagination']['table']['base'] = array(
    'title' => t('Broadsoft Ticketing Pagination'),
    'help' => t('Query JIRA Tickets.'),
    'query class' => 'broadsoft_ticketing_pagination_plugin_query'
  );

  // Fields
  $data['broadsoft_ticketing_pagination']['id'] = array(
    'title' => t('ID'),
    'help' => t('Ticket ID'),
    'field' => array(
      'handler' => 'broadsoft_ticketing_pagination_handler_field',
      'click sortable' => TRUE
    ),
    'filter' => array (
      'handler' => 'views_handler_filter_string'
    ),
    'sort' => array (
      'handler' => 'views_handler_sort'
    )
  );
  
  $data['broadsoft_ticketing_pagination']['ev_id'] = array(
    'title' => t('Ev Id'),
    'help' => t('Extended View Id'),
    'field' => array(
      'handler' => 'broadsoft_ticketing_pagination_handler_field',
          'click sortable' => TRUE
    ),
  );
  
  $data['broadsoft_ticketing_pagination']['title'] = array(
    'title' => t('Title'),
    'help' => t('Task title'),
    'field' => array(
      'handler' => 'broadsoft_ticketing_pagination_handler_field',
      'click sortable' => TRUE
    ),
  );
  
  $data['broadsoft_ticketing_pagination']['severity_level'] = array(
    'title' => t('Severity level'),
    'help' => t('Severity Level'),
    'field' => array(
      'handler' => 'broadsoft_ticketing_pagination_handler_field',
      'click sortable' => TRUE
    ),
  );
    
  $data['broadsoft_ticketing_pagination']['contact'] = array(
    'title' => t('Contact'),
    'help' => t('Contact info'),
    'field' => array(
      'handler' => 'broadsoft_ticketing_pagination_handler_field',
      'click sortable' => TRUE
    ),
    'filter' => array (
        'handler' => 'views_handler_filter_string'
    ),
    'relationship' => array(
        'base' => 'users', // The name of the table to join with.
        'base field' => 'mail', // The name of the field on the joined table.
        'handler' => 'views_handler_relationship',
        'label' => t('Reported by'),
        'title' => t('User Email'),
        'help' => t('The email of the user who reported the item.'),
    ),      
  );
  
  $data['broadsoft_ticketing_pagination']['created_on'] = array(
    'title' => t('Created on'),
    'help' => t('Date created'),
    'field' => array(
      'handler' => 'broadsoft_ticketing_pagination_handler_field',
      'click sortable' => TRUE
    ),
    'sort' => array(
      'handler' => 'views_handler_sort_date',
    )
  );
  
  $data['broadsoft_ticketing_pagination']['updated_on'] = array(
    'title' => t('Updated On'),
    'help' => t('Date updated '),
    'field' => array(
      'handler' => 'broadsoft_ticketing_pagination_handler_field',
      'click sortable' => TRUE
    ),
    'sort' => array(
      'handler' => 'views_handler_sort_date',
    )
  );
  
  $data['broadsoft_ticketing_pagination']['status'] = array(
    'title' => t('Status'),
    'help' => t('Status'),
    'field' => array(
      'handler' => 'broadsoft_ticketing_pagination_handler_field',
      'click sortable' => TRUE
    ),
  );

  return $data;
}

/**
 * Implementation of hook_views_handlers() DRUPAL 6 SPECIFIC.
 */
function broadsoft_ticketing_pagination_views_handlers() {
  return array(
  'info' => array(
    'path' => drupal_get_path('module', 'broadsoft_ticketing_pagination')."/handlers",
    ),
  'handlers' => array(
    'broadsoft_ticketing_pagination_handler_field' => array(
     'parent' => 'views_handler_field',
     ),        
    ),
  );
}