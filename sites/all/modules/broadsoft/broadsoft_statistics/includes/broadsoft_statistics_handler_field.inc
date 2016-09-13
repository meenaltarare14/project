<?php

/**
 * @file
 *   Views field handler for statistics
 */

/**
 * Views field handler for basic statistics
 *
 * The only thing we're doing here is making sure the field_alias
 * gets set properly, and that none of the sql-specific query functionality
 * gets called.
 */
class broadsoft_statistics_handler_field extends views_handler_field {
  function query() {
    $this->field_alias = $this->real_field;
  }
}

?>