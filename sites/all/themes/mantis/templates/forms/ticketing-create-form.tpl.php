<div class="modal-dialog new-ticket-form">
    <div class="bsModal__container">
      <div class="bsModal__header element-invisible">
        <?php if(isset($form['close'])): ?>
          <?php print render($form['close']); ?>
        <?php else: ?>
          <button type="button" class="bsModal__close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <?php endif; ?>
        <h4 class="bsModal__heading" id="myModalLabel">Submit a new Ticket</h4>
      </div>

      <div class="bsModal__content">

        <?php if(isset($form['error'])): ?>
          <?php print render($form['error']); ?>
        <?php else: ?>

        <div id="ticket" class="ticket">

          <!-- Ticket heading -->
          <h3 class="ticket__heading">
            <span class="ticket__heading-text">ADD NEW TICKET</span>
          </h3>

          <div class="ticket__section ticket-main">

            <!-- Ticket title -->
            <h3 class="ticket__title ticket__title_first">
              <label class="ticket__title-text"><?php print ($form['ticket_title']['#title']); ?></label>
              <?php if($form['ticket_title']['#required']): ?><span class="ticket__title-mandatory">*</span><?php endif; ?>
            </h3>
            <div class="bsInputTextWrapper bsInputTextWrapper_hasRemain">
              <?php print render($form['ticket_title']); ?>
              <div class="bsInputTextRemains">255 left</div>
            </div>

            <!-- Ticket severity tabs -->
            <h3 class="ticket__title">
              <label class="ticket__title-text">Ticket Severity</label>
              <span class="ticket__title-mandatory">*</span>
              <a class="ticket__help btn-ticket-help" data-placement="bottom" data-toggle="popover" data-container="body" data-placement="left" type="button" data-html="true" href="#" id="login"></a>
            </h3>

            <div class="bsTabs">
              <div id="ticket-critical" class="bsTab bsTab_1 bsTab_color_critical">
                <div class="bsTab__icon"><i class="bsIcon bsIcon-critical"></i></div>
                <div class="bsTab__text"><?php print $form['critical']['#title']; ?></div>
                <?php print render($form['critical']); ?>
              </div>
              <div id="ticket-major" class="bsTab bsTab_2 bsTab_color_major">
                <div class="bsTab__icon"><i class="bsIcon bsIcon-major"></i></div>
                <div class="bsTab__text"><?php print $form['major']['#title']; ?> </div>
                <?php print render($form['major']); ?>
              </div>
              <div id="ticket-minor" class="bsTab bsTab_3 bsTab_color_minor">
                <div class="bsTab__icon"><i class="bsIcon bsIcon-minor"></i></div>
                <div class="bsTab__text"><?php print $form['minor']['#title']; ?> </div>
                <?php print render($form['minor']); ?>
              </div>
              <div id="ticket-informational" class="bsTab bsTab_4 bsTab_color_information">
                <div class="bsTab__icon"><i class="bsIcon bsIcon-information"></i></div>
                <div class="bsTab__text"><?php print $form['informational']['#title']; ?> </div>
                <?php print render($form['informational']); ?>
              </div>
            </div>
            <div class="bsTabContents">
              <?php print render($form['severity_tab']); ?>
            </div>

            <!-- Ticket proirity tabs -->
            <div id="priorityAvailability" class="notAvailable">
              <h3 class="ticket__title">
                <label class="ticket__title-text">Ticket Priority</label>
                <a class="ticket__help btn-priority-popover" data-placement="bottom" data-toggle="popover" data-container="body" data-placement="left" type="button" data-html="true" href="#" id="login"></a>
              </h3>

              <div class="bsTabs">
                <div id="ticket-urgent" class="bsTab bsTab_1 bsTab_color_priorityUrgent">
                  <div class="bsTab__icon"><i class="bsIcon bsIcon-urgent"></i></div>
                  <div class="bsTab__text"><?php print $form['urgent']['#title']; ?></div>
                  <?php print render($form['urgent']); ?>
                </div>
                <div id="ticket-high" class="bsTab bsTab_2 bsTab_color_priorityHigh">
                  <div class="bsTab__icon"><i class="bsIcon bsIcon-high"></i></div>
                  <div class="bsTab__text"><?php print $form['high']['#title']; ?> </div>
                  <?php print render($form['high']); ?>
                </div>
                <div id="ticket-normal" class="bsTab bsTab_3 bsTab_color_priorityNormal bsTab_actived">
                  <div class="bsTab__icon"><i class="bsIcon bsIcon-normal"></i></div>
                  <div class="bsTab__text"><?php print $form['normal']['#title']; ?> </div>
                  <?php print render($form['normal']); ?>
                </div>
                <div id="ticket-low" class="bsTab bsTab_4 bsTab_color_priorityLow">
                  <div class="bsTab__icon"><i class="bsIcon bsIcon-low"></i></div>
                  <div class="bsTab__text"><?php print $form['low']['#title']; ?> </div>
                  <?php print render($form['low']); ?>
                </div>
              </div>
            </div>

            <!-- Customer Group -->
            <?php if(isset($form['cg'])): ?>
              <div class="customer-group notAvailable" id="cgroupAvailability">
                <h3 class="ticket__title">
                  <label class="ticket__title-text"><?php print render($form['cg']['#title']); ?></label>
                  <span class="ticket__title-mandatory">*</span>
                </h3>
                <?php print render($form['cg']); ?>
              </div>
            <?php endif ?>
            <!-- close ticket section -->
          </div>

          <!-- Product Information section -->
          <div id="productInformation" class="notAvailable">
            <div class="product-infor-block2 selectcomponent">
              <div class="col-sm-6">
                <h4 class="ticket__title">
                  <label class="ticket__title-text">Product Information<span class="required">*</span></label>
                </h4>
                <?php print render($form['product_information']['product']); ?>
                <?php print render($form['product_information']['component']); ?>
              </div>
              <div class="col-sm-6">
                <div class="problem-category">
                  <h4 class="ticket__title">
                    <label class="ticket__title-text">Problem Category</label>
                  </h4>
                  <?php print render($form['product_information']['category']); ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Ticket Details -->
          <div id="detailsAvailability" class="ticket__section notAvailable">
            <h3 class="ticket__title">
              <label class="ticket__title-text"><?php print $form['ticket_details']['#title']; ?></label>
              <span class="ticket__title-mandatory">*</span>
            </h3>

            <div class="bsTextareaWrapper">
              <?php print render($form['ticket_details']); ?>
            </div>
            <div class="bsInputTextWrapper no-border">
              <?php print render($form['ticket_details_text']); ?>
            </div>

          </div>

          <!-- Attachments and Contact preference tabs -->
          <div id="moreInfoAvailability" class="notAvailable">
            <div role="tablist" aria-multiselectable="true">
              <div role="tab">
                <h3 class="ticket__heading">
                  <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne" class="collapsed">
                    <span class="ticket__heading-text ticket__heading-text_collapsed">Attachments</span>
                  </a>
                </h3>
              </div>
              <div class="collapse" id="collapseOne" role="tabpanel" aria-labelledby="headingOne">
                <div class="ticket__section">
                  <div class="inner">
                    <?php print render($form['attachments']); ?>
                  </div>
                </div>
              </div>
              <div role="tab">
                <h3 class="ticket__heading">
                  <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo" class="collapsed">
                    <span class="ticket__heading-text ticket__heading-text_collapsed">Contact Preferences</span>
                  </a>
                </h3>
              </div>
              <div class="collapse" id="collapseTwo" role="tabpanel" aria-labelledby="headingOne">
                <div class="ticket__section">
                  <p>Contact Preferences</p>

                  <div class="inner">
                    <span class="desc-text">Populated from your profile settings.</span>
                    <div class="col-sm-6">
                      <div class="ticket__section__populated">
                        <form role="form">
                          <div class="form-group">
                            <label for="cus-name"><?php print $form['contact_preferences']['cname']['#title']; ?>
                              <?php if($form['contact_preferences']['cname']['#required']): ?><span class="ticket__title-mandatory">*</span><?php endif; ?>
                            </label>
                            <?php print render($form['contact_preferences']['cname']); ?>
                          </div>
                          <div class="form-group">
                            <label for="cus-contact"><?php print $form['contact_preferences']['contact_num']['#title']; ?>
                              <?php if($form['contact_preferences']['contact_num']['#required']): ?><span class="ticket__title-mandatory">*</span><?php endif; ?>
                            </label>
                            <?php print render($form['contact_preferences']['contact_num']); ?>
                          </div>
                          <div class="form-group">
                            <label for="tracking-numb"><?php print $form['contact_preferences']['ext_tracking_num']['#title']; ?></label>
                            <?php print render($form['contact_preferences']['ext_tracking_num']); ?>
                          </div>

                          <div class="form-group">
                            <?php print render($form['contact_preferences']['in_country']); ?>
                          </div>

                        </form>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="ticket__section__email">
                        <h3 class="ticket__title">
                          <label class="ticket__title-text"><?php print $form['contact_preferences']['recipient_list']['#title']; ?></label>
                        </h3>
                        <?php print render($form['contact_preferences']['recipient_list']); ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Ticket submit button -->
          <div id="submitAvailability" class="notAvailable">
            <div class="ticket__submit-line">
              <?php print render($form['submit']); ?>
            </div>
            <div id="form-errors"></div>
          </div>

        </div>
        <div id="ticket-messages">
          <?php
          if(isset($form['thank_you'])) {
            print render($form['thank_you']);
          }
          ?>
        </div>
        <?php endif; ?>
      </div>
      <!--/bsModal__content-->
    </div>
    <!--/bsModal__container-->
  </div>
  <!-- /.modal-dialog -->
<?php print drupal_render_children($form); ?>