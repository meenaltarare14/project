<?php
  $ticketid = $fieldset['created_tid']['#value'];
  $severity = $fieldset['severity_level']['#value'];
?>
<div class="ticket__section">
  <div class="thankyou">
    <!-- severity not critical success message -->
    <?php if($ticketid != -1 && $severity != 'critical'): ?>
    <h3 class="title">Thank You for Your Ticket</h3>
    <h4 class="sub">Issue <span class="tid"><?php print $ticketid; ?></span> has been created</h4>
    <p>We are reviewing your issue and will respond within 24 hours.</p>
    <div class="ticket__submit-line ticket__submit-line_paddingBottom_77">
      <a href="<?php print base_path().'ticketing/ticket/'.$ticketid; ?>" class="ticket__submit ticket__submit_style_ghost">
        <span class="ticket__submit-text">View Your Ticket</span>
      </a>
      <?php print render($fieldset['close_b']); ?>
      <!-- severity critical success message -->
      <?php elseif($ticketid != -1 && $severity == 'critical'): ?>
      <h3 class="title">Thank You for Your Ticket</h3>
      <h4 class="sub">Issue <span class="tid"><?php print $ticketid; ?></span> has been created</h4>
      <div class="bsTabContents">

        <div class="bsBox bsBox_align_center bsBox_color_critical bsBox_expandTopBottom">

          <h3 class="bsBox__title bsBox__title_color_critical bsBox__title_fontSize_15">Since you indicated an Outage, please call us immediately.</h3>

          <table class="tablePhone">
            <tbody>
            <tr>
              <td class="tablePhone__icon"><i class="fa fa-phone"></i></td>
              <td class="tablePhone__country">US</td>
              <td class="tablePhone__number">+1-240-364-9234</td>
            </tr>
            <tr>
              <td class="tablePhone__icon"><i class="fa fa-phone"></i></td>
              <td class="tablePhone__country">UK</td>
              <td class="tablePhone__number">+44-28-9099-8388</td>
            </tr>
            <tr>
              <td class="tablePhone__icon"><i class="fa fa-phone"></i></td>
              <td class="tablePhone__country">AUS</td>
              <td class="tablePhone__number">+61-2-8424-2996</td>
            </tr>
            </tbody>
          </table>

        </div>
        <!--/bsBox-->

      </div>

    </div>

    <div class="ticket__submit-line">
      <a href="<?php print base_path().'ticketing/ticket/'.$ticketid; ?>" class="ticket__submit ticket__submit_style_ghost">
        <span class="ticket__submit-text">View Your Ticket</span>
      </a>
      <?php print render($fieldset['close_b']); ?>
      <!-- ticket creation failure message -->
      <?php else: ?>
        <h3 class="title">An error Occurred</h3>
        <p style="color:red;">There was an error with ticket creation, please contact support.</p>
      <?php endif; ?>
    </div>

    </div>
  </div>