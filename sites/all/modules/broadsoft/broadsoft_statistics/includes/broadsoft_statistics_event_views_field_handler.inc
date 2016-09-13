<?php

/**
 * @file
 *   Views field handler for statistics
 */

/**
 */
class broadsoft_statistics_event_views_field_handler extends views_handler_field {
  function render($values) {
    // $values is actually a DB result row object
    $ret = "";
    $json_object = json_decode("{".$values->broadsoft_statistics_value."}"); // The value was not json_encoded properly in the database so we need to add the brackets.
    if(property_exists($json_object, "event")) {
      $ret = base64_decode($json_object->event);
    }
    return $ret;
  }
}

?>