<?php

/**
 * @file
 */

define('TERMID_FAQ', '3');

/**
 */
class broadsoft_statistics_plugin_query extends views_plugin_query {

  function query($get_count = FALSE) {  }

  function execute(&$view) {
    //obtain the results form DB
    $view->result = GetAccessLogPerMonth(array(TERMID_FAQ));
  }
  
  function add_orderby() { }
}
