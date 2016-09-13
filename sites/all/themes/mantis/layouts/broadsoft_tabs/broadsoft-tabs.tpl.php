<?php
/**
 * @file
 * Template for a 2 column panel layout.
 *
 * This template provides a two column panel display layout, with
 * each column roughly equal in width.
 *
 * Variables:
 * - $id: An optional CSS id to use for the layout.
 * - $content: An array of content, each item in the array is keyed to one
 *   panel of the layout. This layout supports the following sections:
 *   - $content['left']: Content in the left column.
 *   - $content['right']: Content in the right column.
 */
?>
<?php
//to avoid warnings at panel edit page
if(!isset($tab1_title)) $tab1_title = '';
if(!isset($tab2_title)) $tab2_title = '';
if(!isset($tab3_title)) $tab3_title = '';
if(!isset($tab4_title)) $tab4_title = '';
$tab1 = preg_replace('@[^a-z0-9-]+@','-', strtolower($tab1_title));
$tab2 = preg_replace('@[^a-z0-9-]+@','-', strtolower($tab2_title));
$tab3 = preg_replace('@[^a-z0-9-]+@','-', strtolower($tab3_title));
$tab4 = preg_replace('@[^a-z0-9-]+@','-', strtolower($tab4_title));
drupal_add_js('jQuery(document).ready(function ($) {
$("#'.$tab1.'-count").text($("#'.$tab1.'-results").text());
$("#'.$tab2.'-count").text($("#'.$tab2.'-results").text());
$("#'.$tab3.'-count").text($("#'.$tab3.'-results").text());
$("#'.$tab4.'-count").text($("#'.$tab4.'-results").text());
});',
  array('type' => 'inline', 'scope' => 'footer'));
$preferences = drupal_get_form('ticketing_preferences_form');
?>
<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
  <li role="presentation" class="active"><a href="#action" aria-controls="action" role="tab" data-toggle="tab"><?php print $tab1_title; ?> <span id="<?php print $tab1?>-count" class="count">-</span></a></li>
  <li role="presentation"><a href="#open" aria-controls="open" role="tab" data-toggle="tab"><?php print $tab2_title; ?> <span id="<?php print $tab2?>-count" class="count">-</span></a></li>
  <li role="presentation"><a href="#closed" aria-controls="closed" role="tab" data-toggle="tab"><?php print $tab3_title; ?> <span id="<?php print $tab3?>-count" class="count">-</span></a></li>
  <li role="presentation"><a href="#mytickets" aria-controls="mytickets" role="tab" data-toggle="tab"><?php print $tab4_title; ?> <span id="<?php print $tab4?>-count" class="count">-</span></a></li>
  <li class="settings"><a href="#preferences" aria-controls="preferences" role="tab" data-toggle="tab">
      <i class="fa fa-cog" aria-hidden="true"></i></a>
  </li>
</ul>


<!-- Tab panes -->
<div class="tab-content ticketing-content">
  <div role="tabpanel" class="tab-pane active" id="action">
    <?php print $content['tab1']; ?>
  </div>
  <div role="tabpanel" class="tab-pane" id="open">
    <?php print $content['tab2']; ?>
  </div>
  <div role="tabpanel" class="tab-pane" id="closed">
    <?php print $content['tab3']; ?>
  </div>
  <div role="tabpanel" class="tab-pane" id="mytickets">
    <?php print $content['tab4']; ?>
  </div>
  <div role="tabpanel" class="tab-pane" id="preferences">
    <?php print render($preferences); ?>
  </div>
</div>