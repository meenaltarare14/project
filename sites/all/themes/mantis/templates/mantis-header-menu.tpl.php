<?php
/*
 * Page top bar
 */
?>
<nav class="navbar navbar-inverse navbar-fixed-top">
  <div class="container">
    <div class="row">
      <div class="navbar-header">
        <!-- <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button> -->
        <a class="navbar-brand" href="<?php print $items['front_page']; ?>" title="<?php print t('Home'); ?>" rel="home">
          <img src="<?php print $items['logo']; ?>" alt="<?php print t('Home'); ?>" />
        </a>
         <ul class="list-inline list-btn header-icons header-menu-notify">
              <li class="notification" data-toggle="dropdown">
                  <!-- <a href="javascript:void(0);" id="notify-drop-btn"  onclick="jQuery('#notify-drop2').toggleClass('open');">  <img src="<?php echo base_path().path_to_theme() ?>/img/icon-notification.png" alt="Notification" /></a>-->
                  <a href="javascript:void(0);" id="notify-drop-btn" >  <img src="<?php echo base_path().path_to_theme() ?>/img/icon-notification.png" alt="Notification" /></a>
              </li>
              <div id="notify-drop" class="dropdown-menu hide col-md-4" >
                  <h6>Notifications:</h6>
                  <div class="notifications-list">
                      <div class="notification-item">
                          <span class="icon-FAQs"></span>
                          <div class="notification-title">Updated to TAC-17613</div>
                          <div class="notification-description">New Update from slarplree@broadsoft.com</div>
                          <div class="notification-ago">15 minutes ago</div>
                          <a href=""><img src="<?php global $base_url; echo $base_url; ?>/sites/all/themes/mantis/img/cross.png"></a>
                      </div> <!-- / .notification -->
                      <div class="notification-item">
                          <span class="icon-community-forum"></span>
                          <div class="notification-title">Dave Replied to your comment</div>
                          <div class="notification-description">Checkout the Documentation to troubleshoot your issue...</div>
                          <div class="notification-ago">48 minutes ago</div>
                          <a href=""><img src="<?php global $base_url; echo $base_url; ?>/sites/all/themes/mantis/img/cross.png"></a>
                      </div> <!-- / .notification -->
                      <div class="notification-item">
                          <span class="icon-downloads"></span>
                          <div class="notification-title">Xchange Portal V2.75 Released</div>
                          <div class="notification-description">New release Planned for next weeek.</div>
                          <div class="notification-ago">2 days ago</div>
                          <a href=""><img src="<?php global $base_url; echo $base_url; ?>/sites/all/themes/mantis/img/cross.png"></a>
                      </div> <!-- / .notification -->
                      <div class="notification-item">
                          <span class="icon-products"></span>
                          <div class="notification-title">Broadsoft BCOSS 23.spz now available</div>
                          <div class="notification-description">Update service pack avaibale for BCOSS.</div>
                          <div class="notification-ago">3 days ago</div>
                          <a href=""><img src="<?php global $base_url; echo $base_url; ?>/sites/all/themes/mantis/img/cross.png"></a>
                      </div> <!-- / .notification -->
                      <div class="notification-item">
                          <span class="icon-FAQs"></span>
                          <div class="notification-title">Updated to TAC-17613</div>
                          <div class="notification-description">New Update from slarplree@broadsoft.com</div>
                          <div class="notification-ago">15 minutes ago</div>
                          <a href=""><img src="<?php global $base_url; echo $base_url; ?>/sites/all/themes/mantis/img/cross.png"></a>
                      </div> <!-- / .notification -->
                      <div class="notification-item">
                          <span class="icon-community-forum"></span>
                          <div class="notification-title">Dave Replied to your comment</div>
                          <div class="notification-description">Checkout the Documentation to troubleshoot your issue...</div>
                          <div class="notification-ago">48 minutes ago</div>
                          <a href=""><img src="<?php global $base_url; echo $base_url; ?>/sites/all/themes/mantis/img/cross.png"></a>
                      </div> <!-- / .notification -->
                      <div class="notification-item">
                          <span class="icon-downloads"></span>
                          <div class="notification-title">Xchange Portal V2.75 Released</div>
                          <div class="notification-description">New release Planned for next weeek.</div>
                          <div class="notification-ago">2 days ago</div>
                          <a href=""><img src="<?php global $base_url; echo $base_url; ?>/sites/all/themes/mantis/img/cross.png"></a>
                      </div> <!-- / .notification -->
                      <div class="notification-item">
                          <span class="icon-products"></span>
                          <div class="notification-title">Broadsoft BCOSS 23.spz now available</div>
                          <div class="notification-description">Update service pack avaibale for BCOSS.</div>
                          <div class="notification-ago">3 days ago</div>
                          <a href=""><img src="<?php global $base_url; echo $base_url; ?>/sites/all/themes/mantis/img/cross.png"></a>
                      </div> <!-- / .notification -->
                  </div>
              </div>
          <li class="person">
            <?php
            global $user;
            if($user->uid!='0') {
            ?>
            <a href="#">
              <?php } else { ?>
              <a href="<?php print base_path(); ?>user/login">
                <?php } ?>
                <!--<img data-toggle="tooltip" data-placement="bottom"  data-container="body" title="Click to logout" src="<?php print base_path().path_to_theme(); ?>/img/icon-person.png" alt="Person" />-->
                <img src="<?php print base_path().path_to_theme(); ?>/img/icon-person.png" alt="Person" />
              </a>
              <?php
              if($user->uid!='0') {
                ?>
                <ul class="list-sub">
                  <?php
                    if(isset($items['masquerade'])):?>
                      <li><a href="#" data-toggle="modal" data-target="#masquerade">Customer View Mode</a></li>
                  <?php endif; ?>
                  <li><a href="<?php print base_path(); ?>user/<?php echo $user->uid; ?>/edit">Settings</a></li>
                  <?php
                    if(in_array('administrator', $user->roles)){ ?>
                      <li><a href="<?php print base_path(); ?>administration">Administration</a></li>
                  <?php  } ?>
                  <li><a href="<?php print base_path(); ?>user/logout?destination=user/login">Logout</a></li>
                </ul>
              <?php } ?>
          </li>
          <li class="humburger">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<?php
  /*
   * Page top menu
   */
?>
<section class="current-sub-nav <?php print $items['active_menu_class']; ?>">

  <div class="col-md-6">
    <?php print render($items['search_form']); ?>
  </div>
  <a href="<?php print base_path(); ?>documentation" class="col-md-1 nav-item products-div">
    <img src="<?php echo base_path().path_to_theme() ?>/img/icon-products-new.png" alt="documentation" />
  </a>
  <a href="<?php print base_path(); ?>" class="col-md-1 nav-item faq-div">
    <img src="<?php echo base_path().path_to_theme() ?>/img/icon-FAQs-new.png" alt="KB & FAQs" />
  </a>
  <a href="<?php print base_path(); ?>" class="col-md-1 nav-item community-div">
    <img src="<?php echo base_path().path_to_theme() ?>/img/icon-community-forum-new.png" alt="community" />
  </a>
  <a href="<?php print base_path(); ?>support/broadworks/softwaredistribution/patchadvisor" class="col-md-1 nav-item download-div">
    <img src="<?php echo base_path().path_to_theme() ?>/img/icon-downloads-new.png" alt="downloads" />
  </a>
  <a href="<?php print base_path(); ?>" class="col-md-1 nav-item training-div">
    <img src="<?php echo base_path().path_to_theme() ?>/img/icon-trainings-new.png" alt="training" />
  </a>
  <div class="dropdown">
    <a href="#" class="col-md-1 nav-item ticketing-div" id="ticketingMenu" data-toggle="dropdown">
      <img src="<?php echo base_path().path_to_theme() ?>/img/icon-ticketing-new.png" alt="ticketing" />
    </a>
    <ul class="dropdown-menu dropdown-menu-right ticketing-menu" aria-labelledby="ticketingMenu">
      <li><a href="<?php print base_path(); ?>ticketing">Dashboard</a></li>
      <li><a href="<?php print base_path(); ?>ticketing/customer-accounts">Customer Accounts</a></li>
      <li><a href="<?php print base_path(); ?>support/ticketing/manage_users">Manage Ticketing Users</a></li>
      <li><a href="<?php print base_path(); ?>ticketing/broadsoft-ticketing-feedback">Ticketing System Feedback</a></li>
      <li><a href="<?php print base_path(); ?>support/ticketing/resync">Re-Synchronize Ticketing Options</a></li>
      <li><a href="<?php print base_path(); ?>support/ticketing/administer_users">Administer Users</a></li>
    </ul>
  </div>

</section>

<?php
  // Modal form for customer mode
?>
<?php  if(isset($items['masquerade'])): ?>
  <?php if(isset($items['masquerade-warning'])): ?>
      <div class="customer-mode-warning"><?php print $items['masquerade-warning']; ?> <a href="#" data-toggle="modal" data-target="#masquerade">Switch back</a></div>
    <?php endif; ?>
  <div id="masquerade" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog bsModal__dialog__article">
      <div class="bsModal__container ">
        <div class="bsModal__header">
          <button type="button" class="bsModal__close" data-dismiss="modal" aria-hidden="true">&times;  </button>
          <h4 class="bsModal__heading" id="myModalLabel">Customer Mode</h4>
        </div>
        <div class="bsModal__content">
          <?php print render($items['masquerade']); ?>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
