<?php
    if (intval(Params::getParam('senderId')) !== osc_logged_user_id()) {
        echo json_encode(["error" => "You are not authorized to use this user."]);
        exit();
    }

    $conn = getConnection();

    function formatDatetime($message) {
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

        return $message;
    }

    $message_room = $conn->osc_dbFetchResult("SELECT * FROM %st_message_room WHERE pk_i_message_room_id = %d", DB_TABLE_PREFIX, intval(Params::getParam('messageRoomId')));

    $item = Item::newInstance()->findByPrimaryKey(intval($message_room['fk_i_item_id']));
    View::newInstance()->_exportVariableToView('item', $item);

    if (intval($message_room['fk_i_buyer_id']) !== osc_logged_user_id() && osc_item_user_id() !== osc_logged_user_id()) {
        var_dump(intval($message_room['fk_i_buyer_id']) !== osc_logged_user_id(), osc_item_user_id() !== osc_logged_user_id());
        echo json_encode(["error" => "You are not authorized to use this message room."]);
        exit();
    }

    if (Params::getParam('mode') === 'poll') {
        $messages = $conn->osc_dbFetchResults("SELECT * FROM %st_message WHERE pk_i_message_id > %d AND fk_i_message_room_id = %d ORDER BY dt_delivery_time", DB_TABLE_PREFIX, intval(Params::getParam('lastMessageId')), intval(Params::getParam('messageRoomId')));
        $messages = array_map("formatDatetime", $messages);
        if (count($messages) > 48) {
            echo json_encode(['error'=> 'something wrong', 'sql'=> "SELECT * FROM oc_t_message WHERE pk_i_message_id > ".intval(Params::getParam('lastMessageId'))." AND fk_i_message_room_id = ".intval(Params::getParam('messageRoomId'))." ORDER BY dt_delivery_time"]);
        } else {
            echo json_encode($messages, JSON_UNESCAPED_SLASHES);
        }
        exit();
    }
    
    $conn->osc_dbExec("INSERT INTO %st_message (fk_i_message_room_id, fk_i_sender_id, s_content) VALUES (%d, %d, '%s')", DB_TABLE_PREFIX, intval(Params::getParam('messageRoomId')), intval(Params::getParam('senderId')), Params::getParam('content'));
    $message_id = $conn->get_last_id();
    $message = $conn->osc_dbFetchResult("SELECT * FROM %st_message WHERE pk_i_message_id = %d", DB_TABLE_PREFIX, $message_id);

    $message = formatDatetime($message);

    echo json_encode($message, JSON_UNESCAPED_SLASHES);
?>