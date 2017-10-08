<script>
  var content = "<h2><?php _e("Chat publisher"); ?></h2>";
  <?php if( osc_item_is_expired () ) { ?>
    content += '<p>' +
          "<?php _e("The listing is expired. You can't chat the publisher."); ?>" +
      '</p>';
  <?php } else if( !osc_is_web_user_logged_in() ) { ?>
    content += '<p>' +
          '<?php _e("You must log in or register a new account in order to chat the advertiser"); ?>' +
      '</p>' +
      '<p class="contact_button">' +
          '<strong><a href="<?php echo osc_user_login_url(); ?>"><?php _e('Login'); ?></a></strong>' + ' or ' +
          '<strong><a href="<?php echo osc_register_account_url(); ?>"><?php _e('Register for a free account'); ?></a></strong>' +
      '</p>';
  <?php } else {
    $conn = getConnection();
    if (osc_logged_user_id() === osc_item_user_id()) { ?>
      var redirectLocation = '<?php echo osc_route_url('private-message-list', array('item_id' => osc_item_id())); ?>';
      content += '<button id="chatWithSeller" onClick="window.location.href='+"'"+redirectLocation+"'"+'">View Chats</button>';
    <?php } else {
    ?>
      var redirectLocation = '<?php echo osc_route_url('private-message-start', array('item_id' => osc_item_id())); ?>';
      content += '<button id="chatWithSeller" onClick="window.location.href='+"'"+redirectLocation+"'"+'">Chat with Seller</button>';
    <?php } ?>
  <?php } ?>
  $('#contact').html(content);

  <?php if( osc_users_enabled() && osc_is_web_user_logged_in() ) { ?>
    $('#header .nav li:nth-child(1)').before($('<li style="margin-right: 16px;"><a href="<?php echo osc_route_url('private-message-list', array('item_id' => null)); ?>">Private Message</a></li>'));
  <?php } ?>
</script>