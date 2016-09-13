<ul class="filterbar__chk filterbar__chk__status chk_<?php print $checkboxes['#name']; ?> col-sm-6">
  <?php if(isset($checkboxes['#title'])): ?>
  <li class="head"><?php print $checkboxes['#title']; ?></li>
  <?php endif ?>
  <?php
    foreach($checkboxes['#options'] as $key => $option) {
      print '<li class="checkbox">'.render($checkboxes[$key]).'</li>';
    }
  ?>
</ul>