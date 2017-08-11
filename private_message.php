<?php
  if (!osc_is_web_user_logged_in()) {
    _e("You must log in or register a new account in order to chat the advertiser");
?>
  <p class="contact_button">
    <strong><a href="<?php echo osc_user_login_url(); ?>"><?php _e('Login'); ?></a></strong> or <strong><a href="<?php echo osc_register_account_url(); ?>"><?php _e('Register for a free account'); ?></a></strong>
  </p>
<?php
    return;
  }
  if (Params::getParam('buyer') != osc_logged_user_id()) {
    _e("This is not your private message!");
    return;
  }
?>

<div style="padding: 20px;">
  <div style="margin: 8px; float: left; width: 56%; border: 1px solid rgb(234, 234, 234);">
    <div style="overflow: auto; margin: 4px; height: 512px; border: 1px solid rgb(234, 234, 234);">
    </div>
    <div style="margin: 4px">
      <script src="https://rawgit.com/jackmoore/autosize/master/dist/autosize.min.js"></script>
      <textarea id="message-box" style="width: 80%; resize: none"></textarea>
      <script>autosize($('#message-box'))</script>
      <button>Send</button>
    </div>
    <div style="margin: 4px">
      <input type="file" name="pic" accept="image/*">
    </div>
  </div>
  <div style="margin: 8px; padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
    Buyer id: <?php echo Params::getParam('buyer') ?> <br />
    Seller id: <?php echo Params::getParam('seller') ?> <br />
    Item id: <?php echo Params::getParam('item') ?> <br />
  </div>
  <div style="margin: 8px; padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
    <input type="number" value="500000"> <button>Make offer</button>
  </div>
  <div style="clear: both;"></div>
</div>
