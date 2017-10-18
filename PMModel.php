<?php

    class PMModel extends DAO
    {

        private static $instance;

        public static function newInstance()
        {
            if( !self::$instance instanceof self ) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        function __construct()
        {
            parent::__construct();
        }
        
        public function import($file)
        {
            $path = osc_plugin_resource($file);
            $sql = file_get_contents($path);

            if(! $this->dao->importSQL($sql) ){
                $errorLevel = $this->dao->getErrorLevel();
                $errorDesc = $this->dao->getErrorDesc();
                throw new Exception( "Error importSQL::PMModel<br>".$file . ", errorLevel: $errorLevel, errorDesc: $errorDesc");
            }
        }

        public function install()
        {
            $this->import('private_message/struct_install.sql');
        }

        public function uninstall()
        {
            $this->import('private_message/struct_uninstall.sql');
        }

        public function _getUserMessageRooms($id = '', $itemId = '')
        {
            if ($id === '') {
                $this->dao->select('mr.*, u.s_name, id.s_title, mrs.*, m.s_content, m.dt_delivery_time, mo.*, c.s_description AS currency_description, mis.*');
                $this->dao->from(DB_TABLE_PREFIX . 't_message_room AS mr');
                $this->dao->join(DB_TABLE_PREFIX . 't_item AS i', 'i.pk_i_id = mr.fk_i_item_id' , 'INNER');
                $this->dao->join(DB_TABLE_PREFIX . 't_item_description AS id', 'id.fk_i_item_id = mr.fk_i_item_id' , 'INNER');
                $this->dao->join(DB_TABLE_PREFIX . 't_message_item_status AS mis', 'mis.pfk_i_item_id = mr.fk_i_item_id' , 'INNER');
                $this->dao->join(DB_TABLE_PREFIX . 't_user AS u', 'u.pk_i_id = mr.fk_i_buyer_id' , 'INNER');
                $this->dao->join(DB_TABLE_PREFIX . 't_message_room_status AS mrs', 'mr.pk_i_message_room_id = mrs.pfk_i_message_room_id' , 'INNER');
                $this->dao->join(DB_TABLE_PREFIX . 't_message AS m', 'm.pk_i_message_id = mrs.fk_i_last_message_id' , 'LEFT');
                $this->dao->join(DB_TABLE_PREFIX . 't_message_offer AS mo', 'mo.pfk_i_message_offer_id = mrs.fk_i_message_offer_id' , 'LEFT');
                $this->dao->join(DB_TABLE_PREFIX . 't_currency AS c', 'c.pk_c_code = mo.fk_c_code' , 'INNER');
                if ($itemId !== '') {
                    $osc_logged_user_id = osc_logged_user_id();
                    $this->dao->where("mr.fk_i_item_id = $itemId AND (i.fk_i_user_id = $osc_logged_user_id OR mr.fk_i_buyer_id = $osc_logged_user_id)");
                } else {
                    $this->dao->where('mr.fk_i_buyer_id', osc_logged_user_id());
                    $this->dao->orWhere('i.fk_i_user_id', osc_logged_user_id());
                }
            } else {
                $this->dao->select();
                $this->dao->from(DB_TABLE_PREFIX . 't_message_room AS mr');
                $this->dao->join(DB_TABLE_PREFIX . 't_message_item_status AS mis', 'mis.pfk_i_item_id = mr.fk_i_item_id' , 'INNER');
                $this->dao->join(DB_TABLE_PREFIX . 't_message_room_status AS mrs', 'mr.pk_i_message_room_id = mrs.pfk_i_message_room_id' , 'INNER');
                $this->dao->join(DB_TABLE_PREFIX . 't_message_offer AS mo', 'mo.pfk_i_message_offer_id = mrs.fk_i_message_offer_id' , 'LEFT');
                $this->dao->where('pk_i_message_room_id', $id);
            }

            $result = $this->dao->get();
            if( !$result ) {
                return array();
            }

            if ($id === '') {
                return $result->result();
            } else {
                return $result->row();
            }
        }

        public function getUserMessageRooms()
        {
            return $this->_getUserMessageRooms('');
        }

        public function getUserMessageRoomsByItemId($itemId)
        {
            return $this->_getUserMessageRooms('', $itemId);
        }

        public function getUserMessageRoomByItemId($itemId)
        {
            $results = $this->_getUserMessageRooms('', $itemId);

            if (count($results) > 0) {
                return $results[0];
            } else {
                $this->dao->select();
                $this->dao->from(DB_TABLE_PREFIX . 't_message_item_status');
                $this->dao->where('pfk_i_item_id', $itemId);

                $result = $this->dao->get();
                if( !$result ) {
                    return array();
                }

                return $result->row();
            }
        }

        public function getMessageRoomById($id)
        {
            return $this->_getUserMessageRooms($id);
        }

        public function createUserMessageRoom($itemId)
        {
            $aSet = array();
            $aSet['fk_i_item_id'] = $itemId;
            $aSet['fk_i_buyer_id'] = osc_logged_user_id();
            $success = $this->dao->insert(DB_TABLE_PREFIX . 't_message_room', $aSet);
            if ($success) {
                return $this->dao->insertedId();
            }
            return 0;
        }

        public function formatDatetime($message) {
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

        public function insertMessage($aMessage)
        {
            $success = $this->dao->insert(DB_TABLE_PREFIX . 't_message', $aMessage);
            if ($success) {
                return $this->dao->insertedId();
            }
            return 0;
        }

        public function getMessageById($id)
        {
            $this->dao->select();
            $this->dao->from(DB_TABLE_PREFIX . 't_message');
            $this->dao->where('pk_i_message_id', $id);

            $result = $this->dao->get();
            if( !$result ) {
                return array();
            }

            return $message = $result->row();
            return $this->formatDatetime($message);
        }

        public function _getMessages($messageRoomId, $lastMessageId = 0)
        {
            $this->dao->select();
            $this->dao->from(DB_TABLE_PREFIX . 't_message');
            $this->dao->where('fk_i_message_room_id', $messageRoomId);
            $this->dao->where('pk_i_message_id >', $lastMessageId);
            $this->dao->orderBy('dt_delivery_time');

            $result = $this->dao->get();
            if( !$result ) {
                return array();
            }

            $messages = $result->result();
            return array_map([$this, 'formatDatetime'], $messages);
        }

        public function getAllMessages($messageRoomId)
        {
            return $this->_getMessages($messageRoomId);
        }

        public function getMessagesSinceLastMessageId($messageRoomId, $lastMessageId)
        {
            return $this->_getMessages($messageRoomId, $lastMessageId);
        }

        public function insertMessageOffer($aMessageOffer)
        {
            $success = $this->dao->insert(DB_TABLE_PREFIX . 't_message_offer', $aMessageOffer);
            if ($success) {
                return $this->dao->insertedId();
            }
            return 0;
        }

        public function _statusOffer($messageRoomId, $status)
        {
            $this->dao->from(DB_TABLE_PREFIX . 't_message_room_status');
            $this->dao->set(['e_offer_status' => $status]);
            $this->dao->where('pfk_i_message_room_id', $messageRoomId);
            return $this->dao->update();
        }        

        public function acceptOffer($messageRoomId)
        {
            return $this->_statusOffer($messageRoomId, 'accepted');
        }

        public function declineOffer($messageRoomId)
        {
            return $this->_statusOffer($messageRoomId, 'declined');
        }
    }

?>