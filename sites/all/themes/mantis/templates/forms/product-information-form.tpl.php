
          <!-- Product Information section -->
          <div class="solutions-block" id="solution-dropdown">
            <div class="inner">
              <?php print render($form['title']); ?>
              <div class="wrap-content">
                <p class="desc">Add preferred solutions to personalize your experience.</p>


                  <div class="white-block" id="saved-solutions">
                    <?php print render($form['saved_solutions']); ?>
                  </div>
                <?php if(isset($form['saved_solutions']['solutions'])): ?>
                  <div class="ticket__submit-line">
                    <button class="ticket__submit btn-orange" id="add-solution">
                      <span class="ticket__submit-text">Add Solution / Version</span>
                    </button>
                  </div>
                <?php endif ?>
                <div id="add-product-solution">
                  <?php print render($form['product_information']['info_product']); ?>
                <div class="ticket__submit-line">
                    <?php print render($form['submit']); ?>
                </div>
                </div>
              </div>
              <?php print drupal_render_children($form); ?>
            </div>
          </div>

