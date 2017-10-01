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
      ?>
          <div id="<?php echo $message['pk_i_message_id']?>" class="message-container">
            <div class="message <?php echo $class ?>">
              <div><?php echo htmlspecialchars($message['s_content']) ?></div>
              <div class="time"><?php echo $message['dt_delivery_time'] ?></div>
            </div>
          </div>
      <?php
        }
      ?>
    </div>
    <div style="margin: 4px">
      <script src="https://rawgit.com/jackmoore/autosize/master/dist/autosize.min.js"></script>
      <textarea id="messageBox" rows="2" style="width: 80%; resize: none"></textarea>
      <script>autosize($('#messageBox'))</script>
      <button id="sendMessageButton">Send</button>
    </div>
    <div style="margin: 4px">
      <input type="file" name="pic" accept="image/*">
    </div>
  </div>
  <div style="margin: 8px; padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
    <a href="<?php echo osc_item_url(); ?>">
      <?php echo osc_item_title() ?>
    </a> <br />
    <br />
    <?php if (osc_count_item_resources()) { ?>
      <a href="<?php echo osc_item_url(); ?>"><img src="<?php echo osc_resource_thumbnail_url(); ?>" width="75px" height="56px" title="" alt="" /></a>
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
  var osc_logged_user_id = "<?php echo osc_logged_user_id() ?>";
  var isActive = true;

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
            messageRoomId: "<?php echo intval(Params::getParam('message_room_id')) ?>",
            senderId: "<?php echo osc_logged_user_id() ?>",
            content: $('#messageBox').val(),
            mode: 'poll',
            lastMessageId: $(".message-container:last-child").attr('id')
          },
          success: function (messages) {
            for (var key in messages) {
              var message = messages[key];
              var className = 'yours';
              if (message["fk_i_sender_id"] === osc_logged_user_id) {
                className = 'mine';
              }
              $('#messagesBox').append($('<div id="' + message["pk_i_message_id"] + '" class="message-container">\
                <div class="message '+className+'">\
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
      }, 5000);
    }
  }
  $('#sendMessageButton').click(function() {
    isActive = false;
    $.ajax({
      type: "POST",
      url: ajax_url,
      dataType: 'json',
      data: {
        messageRoomId: "<?php echo intval(Params::getParam('message_room_id')) ?>",
        senderId: "<?php echo osc_logged_user_id() ?>",
        content: $('#messageBox').val()
      },
      cache: false,

      success: function(message) {
        $('#messagesBox').append($('<div id="' + message["pk_i_message_id"] + '" class="message-container">\
          <div class="message mine">\
            <div>' + message["s_content"] + '</div>\
            <div class="time">' + message["dt_delivery_time"] + '</div>\
          </div>\
        </div>'));
        $('#messageBox').val('');
        $('#messagesBox').animate({
          scrollTop: $('#messagesBox').get(0).scrollHeight
        }, 1000);
        isActive = true;
        pollServer();
      }
    });
  });
</script>
