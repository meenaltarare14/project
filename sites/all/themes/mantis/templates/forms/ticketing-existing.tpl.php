<?php if(!isset($form['ticketID']['#value']) && false) {?>
<h2>Invalid Ticket ID</h2>
<?php } else { ?>
<div class="ticket-infor-block ticket">
  <div class="content-ticket-infor">
    <h2 class="title">
      <span><?php print($form['ticketID']['#value']); ?></span>- <?php print($form['#title']); ?>
    </h2>
    <ul class="report-time">
      <li>
        <span>Last Updated :</span>
        <span><?php print gmdate("m/d/Y g:ia", $form['#last_modified']); ?></span>
      </li>
      <li>
        <span>Created on:</span>
        <span><?php print gmdate("m/d/Y g:ia", $form['#created_date']); ?></span>
      </li>
    </ul>
    <?php
    $status = $form['#status'];
    $status_class = '';
    $status_text = '';
    $update_text = 'Update to Pending Customer';
      switch($status) {
        case 'new':
          $status_class = 'color-green';
          $status_text = t('New');
          break;
        case 'open':
          $status_class = 'color-green';
          $status_text = t('Open');
          break;
        case 'closed':
          $status_class = 'color-grey';
          $status_text = t('Closed');
          break;
        case 'pending closure':
          $status_class = 'color-red';
          $status_text = t('Pending Closure');
          break;
        case 'pending customer':
          $status_class = 'color-red';
          $status_text = t('Pending Customer');
          $update_text = 'Update & Return to TAC';
          break;
      }
    $severity = $form['#severity'];
    $severity_icon = '';
    $severity_text = '';
    $severity_bg = '';
    switch($severity) {
      case 'Critical':
        $severity_icon = 'bsIcon-critical';
        $severity_text = 'Critical';
        $severity_bg = 'background-critical';
        break;
      case 'Major':
        $severity_icon = 'bsIcon-major';
        $severity_text = 'Major';
        $severity_bg = 'background-major';
        break;
      case 'Minor':
        $severity_icon = 'bsIcon-minor';
        $severity_text = 'Minor';
        $severity_bg = 'background-minor';
        break;
      case 'Informational':
        $severity_icon = 'bsIcon-information';
        $severity_text = 'Informational';
        $severity_bg = 'background-informational';
        break;
    }
    $priority = $form['#priority'];
    $priority_icon = '';
    $priority_text = '';
    $priority_bg = '';
    switch($priority) {
      case '1 - Urgent':
        $priority_icon = 'bsIcon-urgent';
        $priority_text = 'Urgent';
        $priority_bg = 'button-urgent';
        break;
      case '2 - High':
        $priority_icon = 'bsIcon-high';
        $priority_text = 'High';
        $priority_bg = 'button-high';
        break;
      case '3 - Normal':
        $priority_icon = 'bsIcon-normal';
        $priority_text = 'Normal';
        $priority_bg = 'button-normal';
        break;
      case '4 - Low':
        $priority_icon = 'bsIcon-low';
        $priority_text = 'Low';
        $priority_bg = 'button-low';
        break;
    }
    ?>
    <ul class="list-btn bsTabs">
      <li>
        <button type="button" class="btn type-none-background <?php print $status_class; ?>"><?php print $status_text; ?></button>
      </li>
      <li>
        <button type="button" class="btn <?php print $severity_bg; ?>">
          <div class="bsTab__icon"><i class="bsIcon <?php print $severity_icon; ?>"></i></div>
          <div class="bsTab__text"><?php print $severity_text; ?></div>
        </button>
        <!-- <div class="bsTab">
            <div class="bsTab__icon"><i class="bsIcon bsIcon-major"></i></div>
            <div class="bsTab__text">Major</div>
        </div> -->
      </li>
      <li>
        <button type="button" class="btn <?php print $priority_bg; ?>">
          <div class="bsTab__icon"><i class="bsIcon <?php print $priority_icon; ?>"></i></div>
          <div class="bsTab__text"><?php print $priority_text; ?></div>
        </button>
        <!-- <div class="bsTab bsTab_1 bsTab_color_priorityUrgent">
                        <div class="bsTab__icon"><i class="bsIcon bsIcon-urgent"></i></div>
                        <div class="bsTab__text">Urgent</div>
                    </div> -->
      </li>
    </ul>
    <?php if(!$form['#view_only']): ?>
    <div class="bsTextareaWrapper">
      <?php print render($form['update']); ?>
    </div>
    <ul class="list-btn type-2">
      <li>
        <?php print render($form['send_update']); ?>
      </li>
      <?php if(isset($form['update_pending'])): ?>
      <li>
        <?php print render($form['update_pending']); ?>
      </li>
      <?php endif; ?>
      <li>
        <?php print render($form['update_close']); ?>
      </li>
      <li>
        <?php print render($form['cancel_update']); ?>
      </li>

    </ul>
      <?php endif; ?>
    <div class="show-latest">
      <a href="#" title="" class="show">Show All</a>
      <?php
        //$latest_update = array_pop($form['#updates']);
        $latest = TRUE;
        global $user;
      ?>
  <?php for($i=sizeof($form['#updates'])-1; $i>=0; $i--) { ?>
    <?php if($latest) { ?>
      <div class="yellow-news news">
    <?php } else if($form['#updates'][$i]['evuser'] == $user->name){ ?>
        <div class="grey-news news hidden">
      <?php } else {?>
          <div class="yellow-news news hidden">
            <?php } ?>
        <div class="inner">
          <h3><?php if($latest) print t('LATEST UPDATE').' - '; ?><?php print $form['#updates'][$i]['evuser']; ?> on <?php print date('M d, Y, g:i a', $form['#updates'][$i]['timestamp']); ?>: </h3>
          <p><?php print $form['#updates'][$i]['comment']; ?></p>
        </div>
      </div>
        <?php $latest = FALSE; ?>
  <?php }?>
    </div>
  </div>
  <div role="tablist" aria-multiselectable="true">
    <div role="tab">
      <h3 class="ticket__heading">
        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseAtt" aria-expanded="false" aria-controls="collapseTwo" class="collapsed">
          <span class="ticket__heading-text ticket__heading-text_collapsed">Attachments</span>
        </a>
      </h3>
    </div>
    <div class="collapse" id="collapseAtt" role="tabpanel" aria-labelledby="headingOne" aria-expanded="false" aria-controls="collapseTwo" class="collapsed">
      <div class="ticket__section">
        <div class="list-upload">
          <?php
          if(isset($form['#attachments'])) {
            foreach ($form['#attachments'] as $attachment) {
              $attachmentURL = EncodeAttachmentURL($attachment['fileid'], $attachment['mime'], $attachment['fileSize'], $attachment['fileName'], $form['ticketID']['#value']);
              $preview = getFileForPreview($attachment['fileid'], $attachment['fileName'], $attachment['mime'], $attachment['fileSize']);
              ?>
              <div class="fileUpload">
                <div class="file-preview">
                  <a href="<?php print $attachmentURL; ?>">
                    <img src="<?php print $preview; ?>" alt="<?php print $attachment['fileName']; ?>"/>
                  </a>
                </div>
                <span><i class="fa fa-file-o" aria-hidden="true"></i><a
                    href="<?php print $attachmentURL; ?>"><?php print $attachment['fileName']; ?></a> <span
                    class="size">(<?php print formatSizeUnits($attachment['fileSize']); ?>)</span></span>
              </div>
            <?php
            }
          }
        ?>
          <?php if(!$form['#view_only']): ?>
          <div class="eupload">
            <?php print render($form['attachments']); ?>
          </div>
          <?php print render($form['save_attachments']); ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div role="tab">
      <h3 class="ticket__heading">
        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTic" aria-expanded="false" aria-controls="collapseTwo" class="collapsed">
          <span class="ticket__heading-text ticket__heading-text_collapsed">Ticket Details</span>
        </a>
      </h3>
    </div>
    <div class="collapse" id="collapseTic" role="tabpanel" aria-labelledby="headingOne" aria-expanded="false"  aria-controls="collapseTwo" class="collapsed">
      <div class="ticket__section">
        <div class="ticket-detail-contents">
          <ul>
            <li>
              <span>Ticket Number:</span>
              <span><?php print $form['ticketID']['#value']; ?></span>
            </li>
            <li>
              <span>Title:</span>
              <span><?php print $form['#title']; ?></span>
            </li>
          </ul>
          <ul>
            <li>
              <span>Severity:</span>
              <span><?php print $form['#severity']; ?></span>
            </li>
            <li>
              <span>Priority:</span>
              <span><?php print substr($form['#priority'], 3); ?></span>
            </li>
          </ul>
          <ul>
            <li>
              <span>Description:</span>
              <span><?php print $form['#description']; ?></span>
            </li>
          </ul>
          <ul>
            <li>
              <span>Product:</span>
              <span><?php print $form['#product']; ?></span>
            </li>
            <li>
              <span>Solution:</span>
              <span><?php print $form['#solution']; ?></span>
            </li>
            <li>
              <span>Version:</span>
              <span><?php print $form['#version']; ?></span>
            </li>
            <li>
              <span>Component:</span>
              <span><?php print $form['#component']; ?></span>
            </li>
            <li>
              <span>System Type:</span>
              <span><?php print $form['#system_type']; ?></span>
            </li>
            <li>
              <span>Platform:</span>
              <span><?php print $form['#platform']; ?></span>
            </li>
          </ul>
          <ul>
            <li>
              <span>Customer Contact Name:</span>
              <span><?php print $form['#cname']; ?></span>
            </li>
            <li>
              <span>Customer Phone Number:</span>
              <span><?php print $form['#cphone']; ?></span>
            </li>
            <li>
              <span>In-Country Support:</span>
              <span><?php print $form['#in_country']; ?></span>
            </li>
          </ul>
          <ul>
            <li>
              <span>Emails:</span>
              <span><?php print $form['#emails']; ?></span>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div role="tab">
      <h3 class="ticket__heading">
        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseHis" aria-expanded="false" aria-controls="collapseTwo" class="collapsed">
          <span class="ticket__heading-text ticket__heading-text_collapsed">History</span>
        </a>
      </h3>
    </div>
    <div class="collapse" id="collapseHis" role="tabpanel" aria-labelledby="headingOne" aria-expanded="true">
      <div class="ticket__section">
        <ul class="list-history">
          <li>Date:</li>
          <?php if(isset($form['#created_date'])): ?>
          <li><?php print date('M d, Y, g:i a', $form['#created_date']); ?></li>
          <?php endif ?>
          <?php if(isset($form['#first_response'])): ?>
            <li><?php print date('M d, Y, g:i a', $form['#first_response']); ?></li>
          <?php endif ?>
          <?php if(isset($form['#last_modified'])): ?>
            <li><?php print date('M d, Y, g:i a', $form['#last_modified']); ?></li>
          <?php endif ?>
          <?php if(isset($form['#resolved_date'])): ?>
            <li><?php print date('M d, Y, g:i a', $form['#resolved_date']); ?></li>
          <?php endif ?>
        </ul>
        <ul class="list-history">
          <li>Note:</li>
          <?php if(isset($form['#created_date'])): ?>
            <li><?php print t('Date Opened'); ?></li>
          <?php endif ?>
          <?php if(isset($form['#first_response'])): ?>
            <li><?php print t('Date First Response'); ?></li>
          <?php endif ?>
          <?php if(isset($form['#last_modified'])): ?>
            <li><?php print t('Date Last Modified'); ?></li>
          <?php endif ?>
          <?php if(isset($form['#resolved_date'])): ?>
            <li><?php print t('Date Resolved'); ?></li>
          <?php endif ?>
        </ul>
      </div>
    </div>
  </div>
</div>
  <!-- modal dialogs -->
  <?php print drupal_render($form['modal_confirm']); ?>
  <?php print drupal_render($form['modal_feedback']); ?>
  <?php print drupal_render($form['modal_thankyou']); ?>
  <?php print drupal_render_children($form); ?>
<?php } ?>
