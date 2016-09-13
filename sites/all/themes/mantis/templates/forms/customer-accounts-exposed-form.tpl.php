<div class="container-fluid tab-menu">
  <div class="row">


    <div class="col-md-3">
        <?php
        $form['title']['#attributes']['class'][] = 'form-control';
        $form['title']['#attributes']['placeholder'] = t('Account Name (contains)');
        $form['title']['#attributes']['type'] = 'search';
        $form['title']['#theme_wrappers'] = array();
        $form['title']['#title_display'] = 'invisible';
        print render($form['title']);
        ?>
    </div>

    <div class="col-md-3">
      <?php
      $form['field_cid_value']['#attributes']['class'][] = 'form-control';
      $form['field_cid_value']['#attributes']['placeholder'] = t('Account CID is');
      $form['field_cid_value']['#attributes']['type'] = 'search';
      $form['field_cid_value']['#theme_wrappers'] = array();
      $form['field_cid_value']['#title_display'] = 'invisible';
      print render($form['field_cid_value']);
      ?>
    </div>

    <div class="col-md-3">
      <div class="dropdown">
      <?php
      $form['field_can_access_ticketing_value']['#attributes']['class'][] = 'form-control';
      $form['field_can_access_ticketing_value']['#options']['All'] = t('Ticketing Access (any)');
      $form['field_can_access_ticketing_value']['#attributes']['class'] = array('btn', 'btn-default');
      $form['field_can_access_ticketing_value']['#theme_wrappers'] = array();
      $form['field_can_access_ticketing_value']['#title_display'] = 'invisible';
      print render($form['field_can_access_ticketing_value']);
      ?>
      </div>
    </div>

    <div class="col-md-2">
      <?php
      $form['submit']['#attributes']['class'] = array('apply');
      print render($form['submit']);
      ?>
    </div>

    <div class="col-md-1">
      <div class="d-link"><?php print l('csv', 'ticketing/customer-accounts/csv', array('query' => drupal_get_query_parameters())); ?></div>
      <div class="d-link"><?php print l('xml', 'ticketing/customer-accounts/xml', array('query' => drupal_get_query_parameters())); ?></div>
    </div>

    </div>
  </div>

<?php print drupal_render_children($form); ?>