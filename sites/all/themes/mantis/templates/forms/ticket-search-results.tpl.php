<?php
$ticket = $markup['#markup'];
$item_class = '';
$status = '';
switch($ticket['STATUS']) {
  case 'pending customer':
  case 'pending closure':
    $item_class = 'action-required-status';
    $status = t('Action Required');
    break;
  case 'new':
  case 'open':
    $item_class = 'open-status';
  $status = t('Open');
    break;
  case 'closed':
    $item_class = 'closed-status';
    $status = t('Closed');
    break;
}
$severity_class = '';
switch($ticket['SEVERITY_LEVEL']) {
  case 'Critical':
    $severity_class = 'hexagon';
    break;
  case 'Major':
    $severity_class = 'fa fa-angle-double-up';
    break;
  case 'Minor':
    $severity_class = 'fa fa-angle-up';
    break;
  case 'Informational':
    $severity_class = 'fa fa-minus';
    break;
}
$created = DateTime::createFromFormat('m/d/y h:i A', $ticket['DATE_CREATED']);
$created_date = $created->format('m/d/Y');
?>
<div class="item <?php print $item_class; ?>">
  <div class="inner">
    <ul>
      <li><a href="<?php print base_path(); ?>ticketing/ticket/<?php print $ticket['ID']; ?>"><i class="<?php print $severity_class; ?>"></i><?php print $ticket['ID']; ?></a></li>
      <li class="color-text"><?php print $status; ?></li>
      <li>Created : <?php print $created_date; ?></li>
    </ul>
    <div class="text-block">
      <h4><?php print $ticket['SHORT_DESCR']; ?></h4>
      <p><?php print $ticket['DESCRIPTION']; ?></p>
    </div>
  </div>
</div>