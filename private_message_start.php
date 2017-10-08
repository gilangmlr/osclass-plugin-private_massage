<?php
  $conn = getConnection();
  $message_room = $conn->osc_dbFetchResult("SELECT * FROM %st_message_room WHERE fk_i_item_id = %d AND fk_i_buyer_id = %d",
    DB_TABLE_PREFIX, intval(Params::getParam('item_id')), osc_logged_user_id());
  if(!isset($message_room['pk_i_message_room_id'])) {
    $conn->osc_dbExec("INSERT INTO %st_message_room (fk_i_item_id, fk_i_buyer_id) VALUES (%d, %d)",
      DB_TABLE_PREFIX, intval(Params::getParam('item_id')), osc_logged_user_id());
    $message_room_id = $conn->get_last_id();
  } else {
    $message_room_id = $message_room['pk_i_message_room_id'];
  }

  header("Location: " . osc_route_url('private-message', array('message_room_id' => $message_room_id)));
  exit();
?>