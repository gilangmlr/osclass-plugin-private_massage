<script>
  var content = "<h2><?php _e("Chat publisher", 'bender'); ?></h2>";
  <?php if( osc_item_is_expired () ) { ?>
    content += '<p>' +
          "<?php _e("The listing is expired. You can't chat the publisher.", 'bender'); ?>" +
      '</p>';
  <?php } else if( ( osc_logged_user_id() == osc_item_user_id() ) && osc_logged_user_id() != 0 ) { ?>
    content += '<p>' +
          "<?php _e("It's your own listing, you can't chat the publisher.", 'bender'); ?>" +
      '</p>';
  <?php } else if( !osc_is_web_user_logged_in() ) { ?>
    content += '<p>' +
          '<?php _e("You must log in or register a new account in order to chat the advertiser", 'bender'); ?>' +
      '</p>' +
      '<p class="contact_button">' +
          '<strong><a href="<?php echo osc_user_login_url(); ?>"><?php _e('Login', 'bender'); ?></a></strong>' + ' or ' +
          '<strong><a href="<?php echo osc_register_account_url(); ?>"><?php _e('Register for a free account', 'bender'); ?></a></strong>' +
      '</p>';
  <?php } else { ?>
    var redirectLocation = '<?php echo osc_route_url('private-message', array('id' => 'i' . osc_item_id() . 'u' .osc_logged_user_id())); ?>';
    content += '<button id="chatWithSeller" onClick="window.location.href='+"'"+redirectLocation+"'"+'">Chat with seller</button>';
  <?php } ?>
  $('#contact').html(content);
</script>