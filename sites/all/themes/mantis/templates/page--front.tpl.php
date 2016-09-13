<?php
//render page header
print render($page['header']);
?>

<section class="main-banner banner-Xchange">
  
  <?php print render($page['slider']); ?>

  <div class="container">
    <div class="row">
      <div class="col-xs-12 search-box">
        <h1 class="heading-title">WELCOME TO THE BROADSOFT XCHANGE CUSTOMER PORTAL</h1>
        <?php print render($broadsoft_files_search); ?>
        <ul class="list-itm">
          <li>
            <a href="documentation" title="Documentation">
              <span class="icon-products">Documentation</span>
              <span>Documentation</span>
            </a>
          </li>
          <li>
            <a href="#" title="KB & FAQs">
              <span class="icon-FAQs">KB & FAQs</span>
              <span>KB & FAQs</span>
            </a>
          </li>
          <li>
            <a href="#" title="Community">
              <span class="icon-community-forum">Community</span>
              <span>Community</span>
            </a>
          </li>
          <li>
            <a href="patchadvisor" title="Downloads">
              <span class="icon-downloads">Downloads</span>
              <span>Downloads</span>
            </a>
          </li>
          <li>
            <a href="#" title="Training">
              <span class="icon-trainings">Training</span>
              <span>Training</span>
            </a>
          </li>

          <li>
            <a href="<?php print base_path(); ?>ticketing" title="Ticketing">
              <span class="icon-ticketing">Ticketing</span>
              <span>Ticketing</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</section>
<section class="hm-notification">
  <div class="container">

    <div class="row">


      <div class="col-md-12 ">        
        <?php print render($page['announcments']); ?>        
      </div>
      </div>
  </div>
</section>
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

<?php print render($page['content']); ?>

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
<script type="text/javascript">
  jQuery(window).load(function(){
    jQuery('#add-ticket').click(function() {
      jQuery('#contentRating').modal('show');
    });
  });
</script>
<div id="afPopup"></div>
<?php drupal_add_js(drupal_get_path('module', 'broadsoft_ask_flow').'/broadsoft_ask_flow.js'); ?>
