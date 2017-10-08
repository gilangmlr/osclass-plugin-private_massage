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
  $message_room = $conn->osc_dbFetchResult("SELECT * FROM %st_message_room WHERE pk_i_message_room_id = %d", DB_TABLE_PREFIX, intval(Params::getParam('message_room_id')));

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
        $messages = $conn->osc_dbFetchResults("SELECT * FROM %st_message WHERE fk_i_message_room_id = %d ORDER BY dt_delivery_time", DB_TABLE_PREFIX, intval(Params::getParam('message_room_id')));
        foreach ($messages as $key => $message) {
          $time = strtotime($message['dt_delivery_time']);

          $my_date = new DateTime($message['dt_delivery_time']);
          if($my_date->format('Y-m-d') === date('Y-m-d')) {
              $message['dt_delivery_time'] = 'today';
              $message['dt_delivery_time'] .= date(' g:', $time);
          } else {
              $message['dt_delivery_time'] = date('n/j g:', $time);
          }

          $message['dt_delivery_time'] .= intval(date('i', $time));
          $message['dt_delivery_time'] .= date(' A', $time);

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
              <img src="<?php echo $image_url ?>" width="256px" />
            <?php } ?>
              <div><?php echo htmlspecialchars($message['s_content']) ?></div>
              <div class="time"><?php echo $message['dt_delivery_time'] ?></div>
            </div>
          </div>
      <?php
        }
      ?>
    </div>
    <form id="formMessage" enctype="multipart/form-data" method="POST">
      <input type="hidden" name="messageRoomId" value="<?php echo intval(Params::getParam('message_room_id')) ?>">
      <input type="hidden" name="senderId" value="<?php echo osc_logged_user_id() ?>">
      <div style="margin: 4px">
        <script src="https://rawgit.com/jackmoore/autosize/master/dist/autosize.min.js"></script>
        <textarea id="messageBox" name="content" rows="2" style="width: 80%; resize: none"></textarea>
        <script>autosize($('#messageBox'))</script>
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
  <div style="margin: 8px; padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
    <input type="number" value="<?php echo (osc_item_price()/1000000) ?>"> <button>Make offer</button>
  </div>
  <div style="clear: both;"></div>
</div>

<script>
  $('#messagesBox').scrollTop($('#messagesBox').get(0).scrollHeight);
  var ajax_url = "<?php echo osc_ajax_plugin_url('private_message/ajax_private_message.php') ?>";
  var upload_url = "<?php echo osc_base_url() . 'oc-content/uploads/private-message/'; ?>";
  var osc_logged_user_id = "<?php echo osc_logged_user_id() ?>";
  var isActive = true;
  var pollTimeout = 60000;

  $().ready(function () {
    // pollServer();
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
            messageRoomId: $('#formMessage input[name=messageRoomId]').val(),
            senderId: $('#formMessage input[name=senderId]').val(),
            content: '',
            mode: 'poll',
            lastMessageId: $(".message-container:last-child").attr('id')
          },
          success: function (messages) {
            pollTimeout = (pollTimeout === 5000)? 60000 : 5000;
            for (var key in messages) {
              if (messages['error']) {
                break;
              }
              pollTimeout = (pollTimeout === 60000)? 5000 : 60000;
              var message = messages[key];
              var className = 'yours';
              if (message["fk_i_sender_id"] === osc_logged_user_id) {
                className = 'mine';
              }
              var image_url = upload_url + message["s_image"];
              var img = (message['s_image'] !== "")? '<img src="'+image_url+'" width="256px" />' : "";
              $('#messagesBox').append($('<div id="' + message["pk_i_message_id"] + '" class="message-container">\
                <div class="message '+className+'">\
                  '+img+'\
                  <div>' + message["s_content"] + '</div>\
                  <div class="time">' + message["dt_delivery_time"] + '</div>\
                </div>\
              </div>'));
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
  $('#formMessage').submit(function(e) {
    e.preventDefault();
    if ($('#messageBox').val().trim() === "") {
      $('#messageBox').val('');
      // return;
    }
    isActive = false;
    $.ajax({
      type: "POST",
      url: ajax_url,
      contentType: false,
      processData: false,
      dataType: 'json',
      data: new FormData(this),
      cache: false,

      success: function(message) {
        var image_url = upload_url + message["s_image"];
        var img = (message['s_image'] !== "")? '<img src="'+image_url+'" width="256px" />' : "";
        $('#messagesBox').append($('<div id="' + message["pk_i_message_id"] + '" class="message-container">\
          <div class="message mine">\
            '+img+'\
            <div>' + message["s_content"] + '</div>\
            <div class="time">' + message["dt_delivery_time"] + '</div>\
          </div>\
        </div>'));
        $('#formMessage')[0].reset();
        $('#messagesBox').animate({
          scrollTop: $('#messagesBox').get(0).scrollHeight
        }, 1000);
        isActive = true;
        pollServer();
      }
    });
  });

  $('#formImage').attr('action', ajax_url);

  $('#inputImage').change(function() {
    $('#formImage').submit();
  });
</script>
