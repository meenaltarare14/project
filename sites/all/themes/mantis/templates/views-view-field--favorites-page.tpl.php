<?php

/**
 * @file
 * This template is used to print a single field in a view.
 *
 * It is not actually used in default Views, as this is registered as a theme
 * function which has better performance. For single overrides, the template is
 * perfectly okay.
 *
 * Variables available:
 * - $view: The view object
 * - $field: The field handler object that can process the input
 * - $row: The raw SQL result that can be used
 * - $output: The processed output that will normally be used.
 *
 * When fetching output from the $row, this construct should be used:
 * $data = $row->{$field->field_alias}
 *
 * The above will guarantee that you'll always get the correct data,
 * regardless of any changes in the aliasing that might happen if
 * the view is modified.
 */
?>

<?php
  switch($field->field) {
    case 'title':
      print '<h5>'.$output.'</h5>';
      break;
    case 'body':
      print '<p>'.$output.'</p>';
      break;
    case 'field_radioactivity':
      print '<div class="rating">
                            <span class="active"><i class="fa fa-star-o"></i></span>
                            <span><i class="fa fa-star-o"></i></span>
                            <span><i class="fa fa-star-o"></i></span>
                            <span><i class="fa fa-star-o"></i></span>
                            <span><i class="fa fa-star-o"></i></span>
                          </div>';
      break;
    case 'type':
      $type = strtolower($output);
      switch($type) {
        case 'post':
          $class = 'training-icon';
          break;
        case 'wiki':
          $class = 'faq-icon';
          break;
        case 'post':
          $class = 'community-icon';
          break;
        default:
          $class = 'training-icon';
      }
      print '<div class="'.$class.'"></div>';
      break;
    default:
      print $output;
  }

?>