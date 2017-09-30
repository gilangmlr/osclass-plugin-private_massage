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

  $conn = getConnection() ;
  $message_room = $conn->osc_dbFetchResult("SELECT * FROM %st_message_room WHERE pk_i_message_room_id = %d AND fk_i_buyer_id = %d", DB_TABLE_PREFIX, intval(Params::getParam('message_room_id')), osc_logged_user_id());
  if(!isset($message_room['pk_i_message_room_id'])) {
    _e("This is not your private message!");
    return;
  }
  $item = Item::newInstance()->findByPrimaryKey(intval($message_room['fk_i_item_id']));
  View::newInstance()->_exportVariableToView('item', $item);
?>

<div style="padding: 20px;">
  <div style="margin: 8px; float: left; width: 56%; border: 1px solid rgb(234, 234, 234);">
    <div style="overflow: auto; margin: 4px; height: 512px; border: 1px solid rgb(234, 234, 234);">
    </div>
    <div style="margin: 4px">
      <script src="https://rawgit.com/jackmoore/autosize/master/dist/autosize.min.js"></script>
      <textarea id="messageBox" style="width: 80%; resize: none"></textarea>
      <script>autosize($('#messageBox'))</script>
      <button id="sendMessageButton">Send</button>
    </div>
    <div style="margin: 4px">
      <input type="file" name="pic" accept="image/*">
    </div>
  </div>
  <div style="margin: 8px; padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
    <b>Title:</b> <br />
    <?php echo osc_item_title() ?> <br />
    <br />
    <b>Description:</b> <br />
    <?php echo osc_item_description() ?> <br />
    <br />
    <b>Price:</b> <br />
    <?php echo osc_item_formated_price(); ?> <br />
  </div>
  <div style="margin: 8px; padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
    <input type="number" value="<?php echo (osc_item_price()/1000000) ?>"> <button>Make offer</button>
  </div>
  <div style="clear: both;"></div>
</div>

<script>
  var ajax_url = "<?php echo osc_ajax_plugin_url('private_message/ajax_private_message.php') ?>";
  $('#sendMessageButton').click(function() {
      $.ajax({
        type: "POST",
        url: ajax_url,
        data: {content: $('#messageBox').val()},
        cache: false,

        success: function(data) {
          alert(data);
        }
      });
  });
</script>
