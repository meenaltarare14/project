<div class="modal fade confirm-popup" id="confirmClose" tabindex="-1" role="dialog" aria-labelledby="contentRating" aria-hidden="true" data-backdrop="static" data-keyboard="false" data-dismiss="modal">
  <div class="vertical-alignment-helper">
    <div class="modal-dialog vertical-align-center">
      <div class="modal-content">
        <div class="bsModal__container">
          <div class="bsModal__header">
            <button type="button" class="bsModal__close" data-dismiss="modal" aria-hidden="true">&times;</button>
          </div>
          <div class="bsModal__content">
            <div class="confirm">
              <div class="confirm__section">
                <div>
                  <h3 class="title">Are You Sure?</h3>
                  <p>Closing this ticket will indicate you this ticket is resoloved and requires no additional response.</p>
                  <div class="confirm__submit-line">
                    <button type="button" id="confirm-cancel" data-dismiss="modal" aria-hidden="true">
                      No Keep the ticket
                    </button>
                    <?php
                      print render($fieldset['confirm']);
                    ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>