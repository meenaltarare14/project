<!--<div class="documentation-filter">
  <div class="row">


    <div class="col-md-6">
      <div class="input-group add-on search-doc">
        <?php
        $form['keys']['#attributes']['class'][] = 'form-control';
        $form['keys']['#attributes']['placeholder'] = t('Search');
        $form['keys']['#attributes']['type'] = 'text';
        $form['keys']['#theme_wrappers'] = array();
        $form['keys']['#title_display'] = 'invisible';
        print render($form['keys']);
        ?>
        <div class="input-group-btn">
          <button class="btn btn-default" type="submit" style="position: relative"><i class="glyphicon glyphicon-search"></i>
          <?php
          $form['submit']['#value'] = "";
          $form['submit']['#attributes']['style'] = array('position: absolute; left: 0; right: 0; top: 0; bottom: 0; border: none; opacity: 0; width: 100%; height: 100%;');
          print render($form['submit']);
          ?>
          </button>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="btn-group filter-doc">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Filter <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <li><a href="#">Action</a></li>
          <li><a href="#">Another action</a></li>
          <li><a href="#">Something else here</a></li>
          <li role="separator" class="divider"></li>
          <li><a href="#">Separated link</a></li>
        </ul>
      </div>
    </div>
    <div class="col-md-3">
      <div class="btn-group sort-doc">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Action <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <li><a href="#">Action</a></li>
          <li><a href="#">Another action</a></li>
          <li><a href="#">Something else here</a></li>
          <li role="separator" class="divider"></li>
          <li><a href="#">Separated link</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php print drupal_render_children($form); ?>
-->