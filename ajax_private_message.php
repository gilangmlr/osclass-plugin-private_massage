<?php
    require 'vendor/autoload.php';

    use Ramsey\Uuid\Uuid;

    $mode = Params::getParam('mode');
    if (trim(Params::getParam('content')) === "" && isset($_FILES["image"]) && $_FILES["image"]["error"] !== 0 && $mode !== "poll") {
        echo json_encode(["error" => "Content cannot be empty."]);
        if ($mode === "start") {
            header("Location: " . osc_route_url('private-message-start', array('item_id' => intval(Params::getParam('itemId')))));
        }
        exit();
    }
    if (intval(Params::getParam('senderId')) !== osc_logged_user_id()) {
        echo json_encode(["error" => "You are not authorized to use this user."]);
        exit();
    }

    if ($mode === "start") {
        // assume not owner
        $message_room_id = PMModel::newInstance()->createUserMessageRoom(intval(Params::getParam('itemId')));
    } else {
        $message_room_id = intval(Params::getParam('messageRoomId'));
    }

    $message_room = PMModel::newInstance()->getMessageRoomById($message_room_id);

    $item = Item::newInstance()->findByPrimaryKey(intval($message_room['fk_i_item_id']));
    View::newInstance()->_exportVariableToView('item', $item);

    if (intval($message_room['fk_i_buyer_id']) !== osc_logged_user_id() && osc_item_user_id() !== osc_logged_user_id()) {
        echo json_encode(["error" => "You are not authorized to use this message room."]);
        exit();
    }

    if ($mode === 'poll') {
        $messages = PMModel::newInstance()->getMessagesSinceLastMessageId(intval(Params::getParam('messageRoomId')), intval(Params::getParam('lastMessageId')));
        echo json_encode($messages, JSON_UNESCAPED_SLASHES);
        exit();
    }

    $uuid4 = "";
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {
        $uuid4 = Uuid::uuid4()->toString();
        $path = osc_get_preference('upload_path', 'private_message').$uuid4;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $path)) {
        }

    }

    $content = htmlentities(Params::getParam('content'));
    if ($content === "/offer") {
        if (osc_item_user_id() === osc_logged_user_id()) {
            echo json_encode(["error" => "You can not make offer to your own item."]);
            exit();
        }
        $content = "Offered " . (string) osc_format_price( ((float) Params::getParam('price')) * 1000000 );

        $message_id = PMModel::newInstance()->insertMessage(['fk_i_message_room_id' => $message_room_id,
        'fk_i_sender_id' => intval(Params::getParam('senderId')), 's_content' => $content, 's_image' => $uuid4]);
        $message = PMModel::newInstance()->getMessageById($message_id);

        PMModel::newInstance()->insertMessageOffer(['pfk_i_message_offer_id' => $message_id,
        'i_offered_price' => ((int) Params::getParam('price')) * 1000000, 'fk_c_code' => osc_item_currency()]);
        
        if ($mode === "start") {
            header("Location: " . osc_route_url('private-message', array('message_room_id' => $message_room_id)));
            exit();
        }

        echo json_encode($message, JSON_UNESCAPED_SLASHES);
        exit();
    }

    $message_id = PMModel::newInstance()->insertMessage(['fk_i_message_room_id' => $message_room_id,
        'fk_i_sender_id' => intval(Params::getParam('senderId')), 's_content' => $content, 's_image' => $uuid4]);

    $message =PMModel::newInstance()->getMessageById($message_id);

    if ($mode === "start") {
        header("Location: " . osc_route_url('private-message', array('message_room_id' => $message_room_id)));
        exit();
    }

    echo json_encode($message, JSON_UNESCAPED_SLASHES);
?>