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

  $item = Item::newInstance()->findByPrimaryKey(intval(Params::getParam('item_id')));
  View::newInstance()->_exportVariableToView('item', $item);

  if(osc_item_user_id() !== osc_logged_user_id() && intval(Params::getParam('item_id'))) {
    _e("This is not your listing item!");
    return;
  }
?>
<style>
  .message-room {
    margin: 2px;
    padding: 8px;
    border-radius: 4px;
    background-color: rgb(228, 228, 228);
    cursor: pointer;
  }

  .offer-status {
    color: rgb(128, 128, 128);
  }

  .last-message {
    margin: 8px 0;
  }

  .offer-status-box {
    display: inline-block;
    margin: 4px 8px;
    padding: 2px 4px;
    color: white;
    border-radius: 4px;
  }

  .made {
    background-color: rgb(53, 195, 217);
  }

  .accepted {
    background-color: rgb(120, 192, 66);
  }

  .declined {
    background-color: rgb(206, 61, 39);
  }

  .new {
    background-color: rgb(200, 228, 228);
  }
</style>

    <?php
      if (intval(Params::getParam('item_id'))) {
    ?>
      <div style="margin: 2px; padding: 8px;">
        <a href="<?php echo osc_route_url('private-message-list', array('item_id' => null)); ?>">All items</a>
      </div>
    <?php
      }
    ?>

<div id="message-rooms">
  Fetching data...
</div>

<script>
  function getPMList() {
    $.ajax({
      type: "POST",
      url: ajax_url,
      data: {mode: 'getPMList'},
      dataType: 'json',
      cache: false,

      success: function(message) {
        if (message['message_rooms'].length > 0) {
          $('#message-rooms').html('');
        } else {
          $('#message-rooms').html('You do not have any message.');
        }
        for (var key in message['message_rooms']) {
          var message_room = message['message_rooms'][key];
          var newClassName = (parseInt(message_room['unread']) > 0)? ' new' : '';
          var unreadStr = (parseInt(message_room['unread']) > 0)? ' ('+message_room['unread']+')' : '';
          var offerStr = '';
          if (message_room['e_offer_status'] !== 'none') {
            if (message_room['e_offer_status'] === 'made') {
              offerStr += '<span class="offer-status-box made">Offer made</span>';  
            }
            if (message_room['e_offer_status'] === 'accepted') {
              offerStr += '<span class="offer-status-box accepted">Accepted</span>';  
            }
            if (message_room['e_offer_status'] === 'declined') {
              offerStr += '<span class="offer-status-box declined">Declined</span>';  
            }

            offerStr += message_room['name'] + ' offered ' + message_room['formatted_price'];
          } else {
            var ves = (message_room['name'] === 'You')? 've' : 's';
            offerStr += message_room['name'] + 'ha'+ves+' not made an offer on this item yet';
          }
          var image_url = message["osc_resource_url"];
          console.log(message_room);
          var imgStr = (message_room['osc_resource_url'] !== "")? '<img style="float: right;" src="'+message_room['osc_resource_url']+'" width="77px" />' : "";
          var $message_room = $('\
            <div class="message-room'+newClassName+'" data-url="'+message_room['url']+'" onclick="document.location = this.dataset.url">\
              <div style="display: inline-block; width: 10%; vertical-align: top;">\
                '+message_room['name']+'\
              </div>\
              <div style="display: inline-block; width: 78%;">\
                <div class="title">\
                  <strong>'+message_room['s_title']+'</strong> '+unreadStr+'\
                </div>\
                <div class="last-message">\
                  '+message_room['s_content']+'<br />\
                </div>\
                <div class="offer-status">\
                  <small>\
                  '+offerStr+'\
                  </small>\
                </div>\
              </div>\
              <div style="display: inline-block; width: 10%; vertical-align: top;">\
                '+imgStr+'\
              </div>\
            </div>\
          ');

          $('#message-rooms').append($message_room);
        }
        window.setTimeout(function() {
          // getPMList();
        }, 10000);
      }
    });
  }

  getPMList();
</script>
