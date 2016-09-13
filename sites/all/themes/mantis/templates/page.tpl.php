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

      <?php print render($customer_groups); ?>

    </div>
  </div>
</section>

<?php $class = 'main'; ?>
<section class="<?php print $class; ?>">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-9 contents-left">
        <?php print render($tabs); ?>
        <?php print $messages; ?>
        <?php print render($page['content']); ?>
      </div></div>
    <div class="col-sm-3 contents-right" id="sidebar">
      <?php print render($page['sidebar']); ?>
      <?php print render($page['sidebar_first']); ?>
    </div>
  </div>
  </div>
</section>
<?php //print render($page['content']); ?>

<footer>
  <div class="container">
    <div class="inner">
                <span class="wrap-btn">
                    <a href="<?php global $base_url; echo $base_url; ?>/modal_forms/nojs/webform/42299" title="Give Feedback" class="btn type-none-background color-green pull-left ctools-use-modal ctools-modal-modal-popup-medium">Give Feedback</a>
                </span>
      <div class="wrap-text">
        <p>© 2016 BroadSoft All Rights Reserved | Build v.1.02</p>
        <p><?php print l('Dev Sitemap','sitemaptmp'); ?></p>
        <a href="#" title="" class="small-logo"></a>
      </div>
    </div>
  </div>
</footer>

<div id="afPopup"></div>
<?php drupal_add_js(drupal_get_path('module', 'broadsoft_ask_flow').'/broadsoft_ask_flow.js'); ?>