<div class="container-fluid tab-menu">
  <div class="row">
    <div class="col-md-4">
      <div class="faq-search">
        <?php
        $form['search_api_views_fulltext']['#attributes']['class'][] = 'form-control';
        $form['search_api_views_fulltext']['#attributes']['placeholder'] = t('Search Results');
        $form['search_api_views_fulltext']['#attributes']['type'] = 'search';
        $form['search_api_views_fulltext']['#theme_wrappers'] = array();
        $form['search_api_views_fulltext']['#title_display'] = 'invisible';
        $form['submit']['#attributes']['class'][] = 'element-invisible';
        print render($form['search_api_views_fulltext']);
        ?>
      </div>
    </div>

    <div class="col-md-4">
      <div class="dropdown filter-dropdown wide-dropdown">
        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" aria-haspopup="true" aria-expanded="true">
          Filter
          <span class="caret"></span>
        </button>
        <ul class="dropdown-menu filter-menu" aria-labelledby="dropdownMenu1">
          <div class="row">
            <div class="col-md-12">
              <h5>SEARCH</h5>

              <?php print render($form['search_api_views_fulltext_op']); ?>

            </div>
            </div>
          <li role="separator" class="divider"></li>
          <div class="row">
            <div class="col-md-12 category-checkboxes">
              <h5>CATEGORY</h5>
              <select id="select-all-category">
                <option value="1">Select All</option>
                <option value="0">None</option>
              </select>
              <?php print render($form['type']); ?>

            </div>
          </div>
          <li role="separator" class="divider"></li>

          <div class="row">


            <div class="col-md-6">

                <?php $form['submit']['#attributes']['class'] = array('filter-apply', 'btn-orange', 'form-submit'); print render($form['submit']); ?>

            </div>

          </div>

        </ul>
      </div>
    </div>

    <div class="col-md-4">
      <div class="dropdown sort">
        <?php
          $sb = $form['sort_by']['#value'];
          $or = $form['sort_order']['#value'];
          $sorts = array(
            'search_api_relevanceASC' => 'Sort by: Rating',
            'changedASC' => 'Sort by: Date (New to Old)',
            'changedDESC' => 'Sort by: Date (Old to New)',
            'title2ASC' => 'Sort by: Title (A to Z)',
            'title2DESC' => 'Sort by: Title (Z to A)',
          );
        $form['sort_by']['#attributes']['class'][] = 'element-invisible';
        $form['sort_by']['#title_display'] = 'invisible';
        $form['sort_order']['#attributes']['class'][] = 'element-invisible';
        $form['sort_order']['#title_display'] = 'invisible';
        ?>
        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
          <?php print $sorts[$sb.$or]; ?>
          <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
          <li><a href="#" class="refresh-results" data-cat="search_api_relevance" data-order="ASC">Rating</a></li>
          <li><a href="#" class="refresh-results" data-cat="changed" data-order="ASC">Date (New to Old)</a></li>
          <li><a href="#" class="refresh-results" data-cat="changed" data-order="DESC">Date (Old to New)</a></li>
          <li><a href="#" class="refresh-results" data-cat="title2" data-order="ASC">Title (A to Z)</a></li>
          <li><a href="#" class="refresh-results" data-cat="title2" data-order="DESC">Title (Z to A)</a></li>

        </ul>
      </div>
    </div>
  </div>
</div>
<?php print drupal_render_children($form); ?>
<?php
drupal_add_js("
jQuery(document).ready(function ($) {
  $(document).on('click', '.dropdown.filter-dropdown button.dropdown-toggle', function() {
    $(this).parent().toggleClass('open');
  });
  $('body').on('click', function (e) {
    if (!$('.dropdown.filter-dropdown').is(e.target)
      && $('.dropdown.filter-dropdown').has(e.target).length === 0
      && $('.open').has(e.target).length === 0){
      $('.dropdown.filter-dropdown').removeClass('open');
    }
  });
  $(document).on('click', '.refresh-results', function() {
    var cat = $(this).data('cat');
    var ord = $(this).data('order');
    var txt =  $(this).text();
    $('.form-item-sort-by select').val(cat);
    $('.form-item-sort-order select').val(ord);
    $('.dropdown.sort button').text('Sort by: '+ txt);
    $('.filter-apply.form-submit').click();
  });
  $(document).on('change','select#select-all-category', function() {
    if($(this).val() === '1') checkCategories();
    else uncheckCategories();
  });
  function checkCategories() {
    $('.category-checkboxes .form-item>input').prop('checked', true);
  }
  function uncheckCategories() {
    $('.category-checkboxes .form-item>input').prop('checked', false);
  }
  });", inline);
?>