<?php
  $item = Item::newInstance()->findByPrimaryKey(intval(Params::getParam('item_id')));
  View::newInstance()->_exportVariableToView('item', $item);
?>

<div style="padding: 20px;">
  <div style="margin: 8px; float: left; width: 56%; border: 1px solid rgb(234, 234, 234);">
    <div id="messagesBox" style="overflow: auto; margin: 4px; height: 512px; border: 1px solid rgb(234, 234, 234);">
    </div>
    <form id="formMessage" enctype="multipart/form-data" method="POST" action="<?php echo osc_ajax_plugin_url('private_message/ajax_private_message.php') ?>">
      <input type="hidden" id="messageRoomId" name="messageRoomId" value="<?php echo intval(Params::getParam('message_room_id')) ?>">
      <input type="hidden" id="senderId" name="senderId" value="<?php echo osc_logged_user_id() ?>">
      <input type="hidden" id="itemId" name="itemId" value="<?php echo osc_item_id() ?>">
      <input type="hidden" name="mode" value="start">
      <div style="margin: 4px">
        <textarea id="messageBox" name="content" rows="2" style="width: 80%; resize: none"></textarea>
        <input type="submit" id="sendMessageButton" value="Send" disabled>
      </div>
      <div style="margin: 4px">
        <input id="inputImage" type="file" name="image" accept="image/*" />
      </div>
    </form>
  </div>
  <div style="margin: 8px; padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
    <a href="<?php echo osc_item_url(); ?>">
      <?php echo osc_item_title() ?>
    </a> <br />
    <br />
    <?php if (osc_count_item_resources()) { ?>
      <a href="<?php echo osc_item_url(); ?>"><img src="<?php echo osc_resource_url(); ?>" width="256px" height="56px" title="" alt="" /></a>
    <?php } else { ?>
      <img src="<?php echo osc_current_web_theme_url('images/no_photo.gif'); ?>" title="" alt="" />
    <?php } ?> <br />
    <br />
    <b>Description:</b> <br />
    <?php echo osc_item_description() ?> <br />
    <br />
    <b>Price:</b> <br />
    <?php echo osc_item_formated_price(); ?> <br />
  </div>
  <?php if (osc_item_user_id() !== osc_logged_user_id()) { ?>
    <form method="POST" action="<?php echo osc_ajax_plugin_url('private_message/ajax_private_message.php') ?>">
      <input type="hidden" name="content" value="/offer" />
      <input type="hidden" name="messageRoomId" value="<?php echo intval(Params::getParam('message_room_id')) ?>">
      <input type="hidden" name="senderId" value="<?php echo osc_logged_user_id() ?>">
      <input type="hidden" name="itemId" value="<?php echo osc_item_id() ?>">
      <input type="hidden" name="mode" value="start">
      <div style="margin: 8px; padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
        <input id="offerPrice" type="number" value="<?php echo (osc_item_price()/1000000) ?>"> <button id="offerButton">Make offer</button>
      </div>
    <div style="clear: both;"></div>
  </form>
  <?php } ?>
</div>
<script type="text/javascript">
  $('#messageBox').change(function() {
    if ($('#messageBox').val() === "") {
      $("#sendMessageButton").prop('disabled', true);
    } else {
      $("#sendMessageButton").prop('disabled', false);
    }
  });
  $('#inputImage').change(function() {
    if ($(this).val() !== "") {
      $("#sendMessageButton").prop('disabled', false);
    } else if ($('#messageBox').val() === "") {
      $("#sendMessageButton").prop('disabled', true);
    }
  });
  $("#formMessage").submit(function(e) {
    if ($('#messageBox').val() === "" && $("#inputImage").val() === "") {
      e.preventDefault();
    }
  });
</script>
<script src="https://rawgit.com/jackmoore/autosize/master/dist/autosize.min.js"></script>
<script>autosize($('#messageBox'))</script>
