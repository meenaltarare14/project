<header class="login-header">
 <div class="logo-broadsoft">
    <a class="navbar-brand" href="<?php print $front_page; ?>"><img src="<?php echo base_path().path_to_theme() ?>/img/logo-login.png" alt="Broadsoft Customer Xchange logo" /></a>
  </div>
</header>
<section class="main-login">
  <div class="container-fluid">
    <div class="row">

    <div class="col-md-6 left-content">
      <h1>
      WELCOME TO
      <br>BROADSOFT <br>
      XCHANGE
      </h1>
      <p>
        Xchange is a holistic and community driven support solution. Access
        documentation, FAQ’s and submit tickets. Ask a question of the
        community and answer a few for community points.
      </p>
    </div>
    <div class="col-md-6 right-content">
     <div class="login-div">
        <h2>LOGIN</h2>
        <?php print $messages; ?>
        <?php print drupal_render($user_login_form); ?>
        <div class="login-footer">
          <div class="col-md-6">
            Do not have access?
          </div>
          <div class="col-md-6">
            <button class="btn btn-orange">Request Access</button>
          </div>
        </div>
      </div>
     
      </div>
    </div>
  </div>
</section>