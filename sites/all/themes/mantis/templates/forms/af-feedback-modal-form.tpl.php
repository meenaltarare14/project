<div class="modal" id="tacClosed" tabindex="-1" role="dialog" aria-labelledby="tacClosed" aria-hidden="true">
  <div class="vertical-alignment-helper">
    <div class="modal-dialog bsModal__dialog__article rating">
      <div class="bsModal__container ">
        <div class="bsModal__header">
          <button type="button" class="bsModal__close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="bsModal__heading" id="myModalLabel">Did this answer your question?</h4>
        </div>
        <div class="bsModal__content">

          <div class="ticket">

            <div class="ticket__section">
              <p class="ticket__section__text">Contribute to our community by rating this doc and increase your ratings!</p>
            </div>

            <div class="ticket__submit-line">
              <?php
                print render($form['yes']);
                print render($form['no']);
              ?>
            </div>
          </div>

        </div>
        <!--/bsModal__content-->
      </div>
      <!--/bsModal__container-->
    </div>
  </div>
  <?php print drupal_render_children($form); ?>
</div>