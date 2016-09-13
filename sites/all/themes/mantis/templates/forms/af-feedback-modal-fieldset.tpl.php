<div class="modal" id="afFbForm" tabindex="-1" role="dialog" aria-labelledby="afFbForm" aria-hidden="true">
  <div class="vertical-alignment-helper">
    <div class="modal-dialog bsModal__dialog__article">
      <div class="bsModal__container ">
        <div class="bsModal__header">
          <button type="button" class="bsModal__close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="bsModal__heading" id="myModalLabel">Did this answer your question?</h4>
        </div>
        <div class="bsModal__content">

          <div class="ticket">


            <div class="ticket__section ticket__section__comment">
              <p class="ticket__section__text">Let us know how we can improve this article.</p>

              <?php
                print render($fieldset['fb_text']);
              ?>
            </div>

            <div class="ticket__submit-line">
              <?php
                print render($fieldset['fb_cancel']);
                print render($fieldset['fb_submit']);
              ?>
            </div>
          </div>

        </div>
        <!--/bsModal__content-->
      </div>
      <!--/bsModal__container-->
    </div>
  </div>
</div>