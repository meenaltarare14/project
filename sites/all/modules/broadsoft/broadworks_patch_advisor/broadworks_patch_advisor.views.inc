<?php

/**
 * Implements hook_views_data().
 */
function broadworks_patch_advisor_views_data() {

  $table = array(
    'broadworks_patch_advisor' => array( // Mydons Custom table
      'table' => array(
        'group' => t('broadworks_patch_advisor'), // Groupname Mydons
        'base' => array(
          'field' => 'tsid', // Primary key
          'title' => 'Tech Support File',
          'help' => 'Tech Support File database table'
        )
      ),
      'tsid' => array(
        'title' => t('Tech Support Id'),
        'help' => t('Tech Support Unique Id field'),
        'field' => array(
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_numeric'
        ),
        'sort' => array(
          'handler' => 'views_handler_sort'
        )
      ),
      'uid' => array(
        'title' => t('User Id'),
        'help' => t('Tech Support User Id field'),
        'field' => array(
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_numeric'
        ),
        'sort' => array(
          'handler' => 'views_handler_sort'
        )
      ),
      'timestamp' => array(
        'title' => t('Timestamp'),
        'help' => t('Tech Support Timestamp field'),
        'field' => array(
          'handler' => 'views_handler_field_date',
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_date'
        ),
        'sort' => array(
          'handler' => 'views_handler_sort_date'
        )
      ),
      'filename' => array(
        'title' => t('Filename'),
        'help' => t('Tech Support Filename field'),
        'field' => array(
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_string'
        ),

        'sort' => array(
          'handler' => 'views_handler_sort'
        )
      ),
      'path' => array(
        'title' => t('File Full Path'),
        'help' => t('Tech Support Full Path field'),
        'field' => array(
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_string'
        ),

        'sort' => array(
          'handler' => 'views_handler_sort'
        )
      ),
      'status' => array(
        'title' => t('File Status'),
        'help' => t('Tech Support File Status field'),
        'field' => array(
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_string'
        ),

        'sort' => array(
          'handler' => 'views_handler_sort'
        )
      ),
      'hostname' => array(
        'title' => t('Hostname'),
        'help' => t('Cluster Hostname field'),
        'field' => array(
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_string'
        ),

        'sort' => array(
          'handler' => 'views_handler_sort'
        )
      ),
      'customerid' => array(
        'title' => t('Customer ID'),
        'help' => t('Salesforce Customer ID field'),
        'field' => array(
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_string'
        ),

        'sort' => array(
          'handler' => 'views_handler_sort'
        )
      ),
      'serviceusage' => array(
        'title' => t('Has Service Usage'),
        'help' => t('Has Service Usage field'),
        'field' => array(
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_numeric'
        ),
        'sort' => array(
          'handler' => 'views_handler_sort'
        )
      ),
      'bwrelease' => array(
        'title' => t('BW Software Release'),
        'help' => t('BW Software Release field'),
        'field' => array(
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_string'
        ),

        'sort' => array(
          'handler' => 'views_handler_sort'
        )
      ),
      'servertype' => array(
        'title' => t('BW Cluster Server Type'),
        'help' => t('BW Cluster Server Type field'),
        'field' => array(
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_string'
        ),

        'sort' => array(
          'handler' => 'views_handler_sort'
        )
      ),
      'os' => array(
        'title' => t('BW Cluster OS'),
        'help' => t('BW Cluster OS field'),
        'field' => array(
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_string'
        ),

        'sort' => array(
          'handler' => 'views_handler_sort'
        )
      ),
      'ticketid' => array(
        'title' => t('Ticket ID'),
        'help' => t('Problem Report Ticket Number'),
        'field' => array(
          'click sortable' => TRUE
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_string'
        ),

        'sort' => array(
          'handler' => 'views_handler_sort'
        )
      ),

    ),
  );
  
  return $table;
}
