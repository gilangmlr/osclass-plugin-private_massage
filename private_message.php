<?php
  if (!osc_is_web_user_logged_in()) {
    _e("You must log in or register a new account in order to chat the advertiser", 'bender');
?>
  <p class="contact_button">
    <strong><a href="<?php echo osc_user_login_url(); ?>"><?php _e('Login', 'bender'); ?></a></strong> or <strong><a href="<?php echo osc_register_account_url(); ?>"><?php _e('Register for a free account', 'bender'); ?></a></strong>
  </p>
<?php
    return;
  }
  if (Params::getParam('buyer') != osc_logged_user_id()) {
    _e("This is not your private message!", 'bender');
    return;
  }
?>

<div style="padding: 20px;">
  <div style="padding: 8px; float: left; width: 56%; border: 1px solid rgb(234, 234, 234);">
    Type message here...
  </div>
  <div style="padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
    Buyer id: <?php echo Params::getParam('buyer') ?> <br />
    Seller id: <?php echo Params::getParam('seller') ?> <br />
    Item id: <?php echo Params::getParam('item') ?>
  </div>
  <div style="clear: both;"></div>
</div>
