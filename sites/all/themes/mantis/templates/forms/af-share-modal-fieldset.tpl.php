<div class="modal" id="shareModal" tabindex="-1" role="dialog" aria-labelledby="shareModal" aria-hidden="true">
  <div class="vertical-alignment-helper">
    <div class="modal-dialog bsModal__dialog__article">
      <div class="bsModal__container ">
        <div class="bsModal__header">
          <button type="button" class="bsModal__close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="bsModal__heading" id="myModalLabel">Share Article</h4>
        </div>
        <div class="bsModal__content">

          <div class="ticket">
            <?php if(!isset($fieldset['error'])): ?>
            <div class="wrap-content">
              <div class="dropdown select-style">
                <?php
                  print render($fieldset['groups']);
                ?>
              </div>
              <p class="text">Share with colleagues to gain community points...</p>
            </div>
            <div class="list-email">
              <?php
              print render($fieldset['emails']);
              ?>
            </div>
            <div class="ticket__section ticket__section__comment">
              <p class="ticket__section__text">Message</p>
              <?php
                print render($fieldset['message']);
              ?>
            </div>

            <div class="ticket__submit-line">
              <?php
              print render($fieldset['send_message']);
              ?>
            </div>
            <?php else: ?>
            <div class="error"><?php print render($fieldset['error']); ?></div>
            <?php endif; ?>
          </div>

        </div>
        <!--/bsModal__content-->
      </div>
      <!--/bsModal__container-->
    </div>
  </div>
</div>