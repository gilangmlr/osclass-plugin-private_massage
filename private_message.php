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

  $conn = getConnection();
  $message_room = PMModel::newInstance()->getMessageRoomById(intval(Params::getParam('message_room_id')));

  $item = Item::newInstance()->findByPrimaryKey(intval($message_room['fk_i_item_id']));
  View::newInstance()->_exportVariableToView('item', $item);

  if(intval($message_room['fk_i_buyer_id']) !== osc_logged_user_id() && osc_item_user_id() !== osc_logged_user_id()) {
    _e("This is not your private message!");
    return;
  }
?>
<style type="text/css">
  .message-container {
    clear: both;
  }

  .message {
    display: inline-block;
    margin: 2px;
    padding: 8px;
    border-radius: 4px;
  }

  .message img {
    margin-bottom: 4px;
  }

  .mine {
    float: right;
    margin-left: 32px;
    background-color: rgb(200, 228, 228);
  }

  .yours {
    margin-right: 32px;
    background-color: rgb(228, 228, 228);
  }

  .time {
    float: right;
    margin-top: 4px;
    font-size: .7em;
    color: rgb(128, 128, 128);
  }
</style>

<div style="padding: 20px;">
  <div style="margin: 8px; float: left; width: 56%; border: 1px solid rgb(234, 234, 234);">
    <div id="messagesBox" style="overflow: auto; margin: 4px; height: 512px; border: 1px solid rgb(234, 234, 234);">
      <?php
        $messages = $messages = PMModel::newInstance()->getAllMessages(intval(Params::getParam('message_room_id')));
        foreach ($messages as $key => $message) {
          if (intval($message['fk_i_sender_id']) === osc_logged_user_id()) {
            $class = 'mine';
          } else {
            $class = 'yours';
          }

          $image_url = "";
          if ($message['s_image'] !== "") {
            $image_url = osc_base_url() . 'oc-content/uploads/private-message/' . $message['s_image'];
          }
      ?>
          <div id="<?php echo $message['pk_i_message_id']?>" class="message-container">
            <div class="message <?php echo $class ?>">
            <?php if ($image_url !== "") { ?>
              <a href="<?php echo $image_url ?>"><img src="<?php echo $image_url ?>" width="256px" /></a>
            <?php } ?>
              <div><?php
                if (strpos($message['s_content'], 'Offered') !== false) {
                  echo '<b>' . $message['s_content'] . '</b>';
                } else {
                  echo $message['s_content'];
                }
              ?></div>
              <div class="time"><?php echo $message['dt_delivery_time'] ?></div>
            </div>
          </div>
      <?php
        }
      ?>
    </div>
    <form id="formMessage" enctype="multipart/form-data" method="POST">
      <input type="hidden" id="messageRoomId" name="messageRoomId" value="<?php echo intval(Params::getParam('message_room_id')) ?>">
      <input type="hidden" id="senderId" name="senderId" value="<?php echo osc_logged_user_id() ?>">
      <input type="hidden" id="itemId" name="itemId" value="<?php echo osc_item_id() ?>">
      <div style="margin: 4px">
        <textarea id="messageBox" name="content" rows="2" style="width: 80%; resize: none"></textarea>
        <input type="submit" id="sendMessageButton" value="Send">
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
    <div style="margin: 8px; padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
      <input id="offerPrice" type="number" value="<?php echo (osc_item_price()/1000000) ?>"> <button id="offerButton">Make offer</button>
    </div>
    <div style="clear: both;"></div>
  <?php } else if ($message_room['e_offer_status'] === 'made') { ?>
    <div style="margin: 8px; padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
      <div style="display: flex; justify-content: center; margin-bottom: 8px;">
        <input id="offerPrice" type="hidden" value="<?php echo $message_room['i_offered_price'] / 1000000; ?>">
        <input style="text-align: center;" type="text" value="<?php echo osc_format_price((float) $message_room['i_offered_price'], $message_room['currency_description']); ?>" disabled>
      </div>
      <div id="offerActionButtons" style="display: flex; justify-content: center;">
        <button style="margin-right: 8px" id="acceptButton">Accept</button> <button id="declineButton">Declice</button>
      </div>
    </div>
    <div style="clear: both;"></div>
  <?php } ?>
</div>

<script>
  $('#messagesBox').scrollTop($('#messagesBox').get(0).scrollHeight);
  var ajax_url = "<?php echo osc_ajax_plugin_url('private_message/ajax_private_message.php') ?>";
  var upload_url = "<?php echo osc_base_url() . 'oc-content/uploads/private-message/'; ?>";
  var osc_logged_user_id = "<?php echo osc_logged_user_id() ?>";
  var isActive = true;
  var pollTimeout = 5000;
  var waitingTrial = 0;

  var messageRoomId = $("#messageRoomId").val();
  var senderId = $("#senderId").val();
  var itemId = $("#itemId").val();

  $().ready(function () {
    pollServer();
  });

  function pollServer()
  {
    if (isActive)
    {
      window.setTimeout(function () {
        $.ajax({
          url: ajax_url,
          type: "POST",
          dataType: "json",
          data: {
            messageRoomId: messageRoomId,
            senderId: senderId,
            content: '',
            mode: 'poll',
            lastMessageId: $(".message-container:last-child").attr('id')
          },
          success: function (messages) {
            var newMessage = false;
            for (var key in messages) {
              if (messages['error']) {
                alert(JSON.stringify(messages));
                break;
              }
              newMessage = true;
              var message = messages[key];
              var className = 'yours';
              if (message["fk_i_sender_id"] === osc_logged_user_id) {
                className = 'mine';
              }
              appendMessage(message, className);
              if (message['s_content'].indexOf('Offered') !== -1) {
                $('#offerActionButtons').show();
              }
            }
            if (newMessage) {
              waitingTrial = 0;
              pollTimeout = 5000;
            } else {
              waitingTrial++;
              if (waitingTrial === 60) {
                pollTimeout = 60000;
              }
            }
            if (messages.length > 0) {
              $('#messagesBox').animate({
                scrollTop: $('#messagesBox').get(0).scrollHeight
              }, 1000);
            }
            pollServer();
          },
          error: function () {
          }});
      }, pollTimeout);
    }
  }

  function appendMessage(message, className) {
    var image_url = upload_url + message["s_image"];
    var img = (message['s_image'] !== "")? '<a href="'+image_url+'"><img src="'+image_url+'" width="256px" /></a>' : "";
    var string = message['s_content'],
        substring = "Offered";
    var content = string.indexOf(substring) !== -1? "<b>" + message['s_content'] + "</b>" : message['s_content'];
    $('#messagesBox').append($('<div id="' + message["pk_i_message_id"] + '" class="message-container">\
      <div class="message '+className+'">\
        '+img+'\
        <div>' + content + '</div>\
        <div class="time">' + message["dt_delivery_time"] + '</div>\
      </div>\
    </div>'));
  }

  function sendMessage(data, ct, pd) {
    $.ajax({
      type: "POST",
      url: ajax_url,
      contentType: ct,
      processData: pd,
      dataType: 'json',
      data: data,
      cache: false,

      success: function(message) {
        if (message['error']) {
          alert(JSON.stringify(message));
          return;
        }
        appendMessage(message, 'mine');
        $('#formMessage')[0].reset();
        $('#messagesBox').animate({
          scrollTop: $('#messagesBox').get(0).scrollHeight
        }, 1000);
        isActive = true;
        pollServer();
      }
    });
  }

  $('#formMessage').submit(function(e) {
    e.preventDefault();
    if ($('#messageBox').val().trim() === "") {
      $('#messageBox').val('');
      // return;
    }
    isActive = false;
    sendMessage(new FormData(this), false, false);
  });

  $('#formImage').attr('action', ajax_url);

  $('#inputImage').change(function() {
    $('#formImage').submit();
  });

  var data = {
    messageRoomId: messageRoomId,
    senderId: senderId,
    itemId: itemId
  }

  <?php if (osc_item_user_id() !== osc_logged_user_id()) { ?>
    $('#offerButton').click(function() {
      data.content = "/offer";
      data.price = $('#offerPrice').val();

      sendMessage(data);
    });
  <?php } else if ($message_room['e_offer_status'] === 'made') { ?>
    $('#acceptButton').click(function() {
      data.content = "/accept";
      data.price = $('#offerPrice').val();

      sendMessage(data);

      $('#offerActionButtons').hide();
    });

    $('#declineButton').click(function() {
      data.content = "/decline";
      data.price = $('#offerPrice').val();

      sendMessage(data);

      $('#offerActionButtons').hide();
    });
  <?php } ?>
</script>
<script src="https://rawgit.com/jackmoore/autosize/master/dist/autosize.min.js"></script>
<script>
  $(function() {
    autosize($('#messageBox'));
  });
</script>
