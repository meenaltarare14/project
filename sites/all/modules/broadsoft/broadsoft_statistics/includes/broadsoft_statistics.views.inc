<?php

/**
 * @file
 * Views integration for the accesslogs
 * Ref: https://www.lullabot.com/articles/building-views-query-plugins-part-2
 */
define('FAQ_ACCESS_VIEW_NAME', 'faq_stats_per_month');
define('VIEW_TYPE_NAME', 'Monthly hit statistics - FAQs'); // Name which appears in the add new view dialog
define('TICKETING_AVOIDANCE_TABLE_NAME', 'broadsoft_statistics');
define('TICKETING_AVOIDANCE_STATS_VIEW_NAME', 'ticket_avoidance_metrics');


// FAQ_ACCESS_VIEW_TABLE_NAME : 
// To compute faq views monthly data, we query the accesslog table.
// But there already exists a view connected with accesslog, which does not do what we want at all
// If we specify in our hook_views_data below that we work on accesslog, things break, Drupal gets lost
// Anyway, we are using a hook_query_plugin which does a totally custom accesslog query
// So there is no real need to even mention accesslog, Drupal only requires us to specify an existing db table not used elsewhere. 
// I chose the history table because it is not used elsewhere in views
define('FAQ_ACCESS_VIEW_TABLE_NAME', 'history '); // ^^^ see explanation above ^^^
  
  
/**
 * Implementation of hook_views_plugins().
 * Used for FAQ Monthly View Stats
 */
function broadsoft_statistics_views_plugins() {
  $plugin = array();
  $plugin['query']['broadsoft_statistics_plugin_query'] = array(
    'title' => t(VIEW_TYPE_NAME),
    'help' => t(VIEW_TYPE_NAME),
    'handler' => 'broadsoft_statistics_plugin_query',
  );
  return $plugin;
}

/**
 * Implementation of hook_views_data().
 */
function broadsoft_statistics_views_data() {
  $data = array();

  // --------------------------- Ticketing Avoidance Statistics ------------------------
  // Base data
  $data[TICKETING_AVOIDANCE_TABLE_NAME]['table']['group']  = t('Ticketing Statistics');
  $data[TICKETING_AVOIDANCE_TABLE_NAME]['table']['base'] = array(
    'field' => 'statid', // Primary key
    'title' => 'Broadsoft Ticketing Statistics',
    'help' => 'Broadsoft Ticketing Statistics database table' 
  );
  // real DB field - statid
  $data[TICKETING_AVOIDANCE_TABLE_NAME]['statid'] = array (
    'title' => t( 'Statistic Id' ),
    'help' => t( 'Statistic Unique Id field' ),
    'field' => array (
      'click sortable' => true 
    ),
    'filter' => array (
      'handler' => 'views_handler_filter_numeric' 
    ),
    'sort' => array (
      'handler' => 'views_handler_sort' 
    ) 
  );
  // real DB field - uid
  $data[TICKETING_AVOIDANCE_TABLE_NAME]['uid'] = array (
    'title' => t('User Id'),
    'help' => t('User'),
    'field' => array(
      'handler' => 'views_handler_field_user',
      'click sortable' => true,
    ),
    'relationship' => array(
      'title' => 'User', // relationship destination table
      'base' => 'users',
      'label' => t('user')
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_user_uid',
    ),
    'filter' => array(
      'title' => t('Name'),
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );
  // real DB field - timestamp  
  $data[TICKETING_AVOIDANCE_TABLE_NAME]['timestamp'] = array (
    'title' => t( 'Timestamp' ),
    'help' => t( 'Statistic Timestamp field' ),
    'field' => array (
      'handler' => 'views_handler_field_date',
      'click sortable' => true 
    ),
    'filter' => array (
      'handler' => 'views_handler_filter_date' 
    ),
    'sort' => array (
      'handler' => 'views_handler_sort_date' 
    ) 
  );
  // real DB field - type
  $data[TICKETING_AVOIDANCE_TABLE_NAME]['type'] = array (
    'title' => t( 'Type' ),
    'help' => t( 'Statistic type field' ),
    'field' => array (
      'click sortable' => true 
    ),
    'filter' => array (
      'handler' => 'views_handler_filter_string' 
    ),
    
    'sort' => array (
      'handler' => 'views_handler_sort' 
    ) 
  );
  // real DB field - value    
  $data[TICKETING_AVOIDANCE_TABLE_NAME]['value'] = array (
    'title' => t( 'Encoded value' ),
    'field' => array (
      'click sortable' => false
    ),
    'filter' => array (
      'handler' => 'views_handler_filter_string' 
    ),    
    'sort' => array (
      'handler' => 'views_handler_sort' 
    ) 
  );
  // FAKE DB field - event
  $data[TICKETING_AVOIDANCE_TABLE_NAME]['event'] = array (
    'title' => t( 'Event' ),
    'help' => t( 'Ticketing Event field' ),
    'real field' => 'value', // this is a fake-pseudo-field - linked here to a real DB field
    'field' => array(
      'handler' => 'broadsoft_statistics_event_views_field_handler',
      'click sortable' => true,
    ),
    'filter' => array (
      'handler' => 'views_handler_filter_string' 
    ),    
    'sort' => array (
      'handler' => 'views_handler_sort' 
    ) 
  );
  // FAKE DB field - ticketID
  $data[TICKETING_AVOIDANCE_TABLE_NAME]['ticketID'] = array (
    'title' => t( 'ticketID' ),
    'help' => t( 'Ticketing ticketID field' ),
    'real field' => 'value', // this is a fake-pseudo-field - linked here to a real DB field
    'field' => array(
      'handler' => 'broadsoft_statistics_ticketID_views_field_handler',
      'click sortable' => true,
    ),
    'filter' => array (
      'handler' => 'views_handler_filter_string' 
    ),    
    'sort' => array (
      'handler' => 'views_handler_sort' 
    ) 
  );
              
  // --------------------------- FAQ Monthly View Statistics ------------------------
  // Base data
  $data[FAQ_ACCESS_VIEW_TABLE_NAME]['table']['group']  = t('Broadsoft Ticketing Statistics');
  $data[FAQ_ACCESS_VIEW_TABLE_NAME]['table']['base'] = array(
    'title' => t(VIEW_TYPE_NAME),
    'help' => t('Query access statistics.'),
    'query class' => 'broadsoft_statistics_plugin_query'
  );

  // Fields - year_month YYYYMM
  $data[FAQ_ACCESS_VIEW_TABLE_NAME]['year_month'] = array(
    'title' => t('Year Month (YYYYMM)'),
    'help' => t('YYYYMM'),
    'field' => array(
      'handler' => 'broadsoft_statistics_handler_field',
      'click sortable' => true
    ),
    'sort' => array (
      'handler' => 'views_handler_sort'
    )
  );
  
  // Fields - month MM
  $data[FAQ_ACCESS_VIEW_TABLE_NAME]['month'] = array(
    'title' => t('Month (MM)'),
    'help' => t('MM'),
    'field' => array(
      'handler' => 'broadsoft_statistics_handler_field',
      'click sortable' => true
    ),
    'sort' => array (
      'handler' => 'views_handler_sort'
    )
  );

  // Fields - year YYYY
  $data[FAQ_ACCESS_VIEW_TABLE_NAME]['year'] = array(
    'title' => t('Year (YYYY)'),
    'help' => t('YYYY'),
    'field' => array(
      'handler' => 'broadsoft_statistics_handler_field',
      'click sortable' => true
    ),
    'sort' => array (
      'handler' => 'views_handler_sort'
    )
  );

  // Fields - month str
  $data[FAQ_ACCESS_VIEW_TABLE_NAME]['monthstr'] = array(
    'title' => t('Month (String)'),
    'help' => t('January, Feb...'),
    'field' => array(
      'handler' => 'broadsoft_statistics_handler_field',
      'click sortable' => true
    ),
    'sort' => array (
      'handler' => 'views_handler_sort'
    )
  );

  // Fields - count
  $data[FAQ_ACCESS_VIEW_TABLE_NAME]['count'] = array(
    'title' => t('count'),
    'help' => t('Number of distinct hits for that period'),
    'field' => array(
      'handler' => 'broadsoft_statistics_handler_field',
      'click sortable' => false
    ),
  );
  
  return $data;
}

/**
 * Implementation of hook_views_handlers() DRUPAL 6 SPECIFIC.
 */
function broadsoft_statistics_views_handlers() {
  return array(
    'info' => array(
      'path' => drupal_get_path('module', 'broadsoft_statistics')."/includes",
      ),
    'handlers' => array(
      'broadsoft_statistics_handler_field' => array(
        'parent' => 'views_handler_field',
      ),        
      'broadsoft_statistics_event_views_field_handler' => array(
        'parent' => 'views_handler_field',
      ),        
      'broadsoft_statistics_ticketID_views_field_handler' => array(
        'parent' => 'views_handler_field',
      ),            
    ),
  );
}
