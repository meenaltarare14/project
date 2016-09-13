<section class="searchbar">
  <h4 class="searchbar__header">Find a ticket</h4>
  <div class="searchbar__container">
    <?php print render($form['search_term']); ?>
    <i class="icon-searchbar">&nbsp;</i>
  </div>
</section>
<section class="filterbar">
  <?php print render($form['filter_button']); ?>
  <div class="filterbar__expand">
    <?php print render($form['status']); ?>

    <?php print render($form['severity']); ?>

    <?php print render($form['product']); ?>

    <?php print render($form['attachments']); ?>

    <div class="filterbar__date btop">
      <div class="head">Created Between:</div>
                  <span class="start">
                    <?php print render($form['start_date']); ?>
                  </span>
      <span class="plus">+</span>
                  <span class="end">
                    <?php print render($form['end_date']); ?>
                  </span>
    </div>

    <div class="filterbar__action btop">
      <?php print render($form['default_filter']); ?>
    </div>

  </div>

</section>
<div class="filterbar__action out">
  <?php print render($form['submit']); ?>
  </div>
<div class="load-ov hide"><img src="<?php print base_path().drupal_get_path('theme', 'mantis'); ?>/img/loading-bar.gif"/></div>

<section class="search-results">
  <?php print render($form['search_results']); ?>
</section>
<?php print drupal_render_children($form); ?>