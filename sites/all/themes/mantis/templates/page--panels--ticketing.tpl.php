<?php
//render page header
print render($page['header']);
?>

<section class="dashboard-breadcrumbs xchange">
  <div class="container-fluid">
    <div class="col-md-4 bread-links">
      <?php print get_breadcrumb_path(); ?>
    </div>
    <div class="col-md-8 bread-action">

      <?php print render($product_info); ?>

      <?php
        global $user;
        if(isset($user->data['ticket_preferences']) && $user->data['ticket_preferences'] == 'tab') {
          $id = 'add-ticket-new';
        }
      else {
        $id = 'add-ticket';
      }
      ?>
      <div class="new-ticket-btn">
        <button type="button" class="btn type-none-background color-green" id="<?php print $id; ?>">Submit a New Ticket
          <span class="glyphicon glyphicon-plus"></span>
        </button>
      </div>

      <?php print render($customer_groups); ?>

    </div>
  </div>
</section>
<?php
$class = 'main';
$url = $path;
if(isset($url)) {
  switch($url) {
    case 'ticketing':
    case 'ticketing/ticket':
      $class = 'main main-ticketing';
      break;
  }
}
?>
<section class="<?php print $class; ?>">
<?php print render($page['content']); ?>
</section>

<footer>
  <div class="container">
    <div class="inner">
                <span class="wrap-btn">
                    <a href="<?php global $base_url; echo $base_url; ?>/modal_forms/nojs/webform/42299" title="Give Feedback" class="btn type-none-background color-green pull-left ctools-use-modal ctools-modal-modal-popup-medium">Give Feedback</a>
                </span>
      <div class="wrap-text">
        <p>© 2016 BroadSoft All Rights Reserved  | Build v.1.02</p>
        <p><?php print l('Dev Sitemap','sitemaptmp'); ?></p>
        <a href="#" title="" class="small-logo"></a>
      </div>
    </div>
  </div>
</footer>
<script type="text/javascript">
  jQuery(window).load(function(){
    jQuery('#add-ticket').click(function() {
      jQuery('#contentRating').modal('show');
    });
    jQuery('#add-ticket-new').click(function() {
      var tic = window.open('<?php print base_path(); ?>ticketing/new-ticket', '_blank');
      if (tic) {
        tic.focus();
      } else {
        //Browser has blocked it
        alert('Please allow popups for this website');
      }
    });
  });
</script>

<div id="afPopup"></div>
<?php drupal_add_js(drupal_get_path('module', 'broadsoft_ask_flow').'/broadsoft_ask_flow.js'); ?>