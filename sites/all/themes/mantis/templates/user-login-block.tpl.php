<div class="form-group">
     <?php print $name; ?>
</div>
<div class="form-group">
    <?php print $pass; ?>
    <a href="<?php global $base_url; echo $base_url; ?>/user/password" class="forgot-password">Forgot Password?</a>
</div>
    <?php print $rendered; //hidden elements ?>
    <button type="submit" class="btn btn-green">LogIn</button>
