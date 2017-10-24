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

  if (intval(Params::getParam('item_id'))) {
    $message_rooms = PMModel::newInstance()->getUserMessageRoomsByItemId(intval(Params::getParam('item_id')));
  } else {
    $message_rooms = PMModel::newInstance()->getUserMessageRooms();
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

    <?php
      if (count($message_rooms) === 0) {
    ?>
    You do not have any message.
    <?php
      }
    ?>

<?php
  foreach ($message_rooms as $key => $message_room) {
    $field = 'i_seller_unread';
    if (intval($message_room['fk_i_buyer_id']) === osc_logged_user_id()) {
        $field = 'i_buyer_unread';
    }
    $new = '';
    if (intval($message_room[$field]) > 0) {
      $new = 'new';
    }
?>
  <div class="message-room <?php echo $new ?>" data-url="<?php echo osc_route_url('private-message', array('message_room_id' => $message_room['pk_i_message_room_id'])); ?>">
    <div>
      <div class="title">
        <strong><?php echo $message_room['s_title'] ?></strong> <?php
          if ($new) {
           echo '(' . $message_room[$field] . ')';
          }
         ?>
      </div>
      <?php
        if ($message_room['s_content'] !== NULL) {
      ?>
        <div class="last-message">
          <?php echo $message_room['s_content'] ?><br />
        </div>
      <?php 
        } 
      ?>
      <div class="offer-status">
        <small><?php
          if (intval($message_room['fk_i_buyer_id']) === osc_logged_user_id()) {
            $name = 'You';
          } else {
            $name = $message_room['s_name'];
          }
          if ($message_room['e_offer_status'] !== 'none') {
            if ($message_room['e_offer_status'] === 'made') {
              echo '<span class="offer-status-box made">Offer made</span>';  
            }
            if ($message_room['e_offer_status'] === 'accepted') {
              echo '<span class="offer-status-box accepted">Accepted</span>';  
            }
            if ($message_room['e_offer_status'] === 'declined') {
              echo '<span class="offer-status-box declined">Declined</span>';  
            }
            $offered_price = osc_format_price((float) $message_room['i_offered_price'], $message_room['currency_description']);
            if (intval($message_room['fk_i_buyer_id']) === osc_logged_user_id()) {
              echo "$name offered $offered_price";
            } else {
              echo "$name offered $offered_price";
            }
          } else {
            echo "$name have not made an offer on this item yet";
          }
        ?></small>
      </div>
    </div>
  </div>
<?php
  }
?>

<script>
  $(".message-room").click(function() {
    document.location = this.dataset.url;
  });
</script>
