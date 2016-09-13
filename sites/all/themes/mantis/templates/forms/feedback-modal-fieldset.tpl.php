<div class="modal fade" id="tacClosed" tabindex="-1" role="dialog" aria-labelledby="tacClosed" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="bsModal__container">
      <div class="bsModal__header">
        <?php print render($fieldset['fb_dismiss']); ?>
        <h4 class="bsModal__heading" id="myModalLabel"><?php if(isset($fieldset['ticid']['#value'])) print($fieldset['ticid']['#value']); ?> has been closed</h4>
      </div>
      <div class="bsModal__content">

        <div class="ticket">

          <h3 class="ticket__heading">
            <span class="ticket__heading-text">We value your feedback</span>
          </h3>

          <div class="ticket__section">
            <p>Please give us feedback on our support. Help us imporve and add points to your community score...</p>
          </div>
          <div class="ticket__section ticket__section__satisfaction">
            <h4>Please rate your overall satisfaction :</h4>
          <?php print render($fieldset['satisfaction']); ?>
          </div>

          <div class="ticket__section ticket__section__comment">
            <h4>Comments</h4>
            <ul>
              <li>Why did you choose this rating?</li>
              <li>What did we do well, or could have done better?</li>
            </ul>
            <?php print render($fieldset['comments']); ?>
          </div>

          <div class="ticket__submit-line">
            <?php print render($fieldset['no_thanks']); ?>
            <?php print render($fieldset['fb_submit']); ?>
            </div>

        </div>

      </div>
      <!--/bsModal__content-->
    </div>
    <!--/bsModal__container-->
  </div>
  <!-- /.modal-dialog -->
</div>