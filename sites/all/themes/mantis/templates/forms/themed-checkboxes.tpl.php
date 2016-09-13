<ul>
  <?php
    foreach($checkboxes['#options'] as $key => $option) {
      $checkboxes[$key]['attributes']['class'][] = 'styled';
      print '<li><div class="mf-checkbox">'.render($checkboxes[$key]).'</div></li>';
    }
  ?>
</ul>