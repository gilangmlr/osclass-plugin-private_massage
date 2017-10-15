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
                throw new Exception( "Error importSQL::PMModel<br>".$file );
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

        public function getUserMessageRooms()
        {
            $this->dao->select();
            $this->dao->from(DB_TABLE_PREFIX . 't_message_room');
            $this->dao->join(DB_TABLE_PREFIX . 't_item', 'fk_i_item_id = pk_i_id' , 'INNER');
            $this->dao->where('fk_i_buyer_id', osc_logged_user_id());
            $this->dao->orWhere('fk_i_user_id', osc_logged_user_id());

            $result = $this->dao->get();
            if( !$result ) {
                return array();
            }

            return $result->result();
        }

        public function getUserMessageRoomsByItemId($itemId)
        {
            $this->dao->select();
            $this->dao->from(DB_TABLE_PREFIX . 't_message_room');
            $this->dao->join(DB_TABLE_PREFIX . 't_item', 'fk_i_item_id = pk_i_id' , 'INNER');
            $this->dao->where('fk_i_item_id', $itemId);
            $this->dao->Where('fk_i_user_id', osc_logged_user_id());

            $result = $this->dao->get();
            if( !$result ) {
                return array();
            }

            return $result->result();
        }

        public function getMessageRoomById($id)
        {
            $this->dao->select();
            $this->dao->from(DB_TABLE_PREFIX . 't_message_room');
            $this->dao->where('pk_i_message_room_id', $id);

            $result = $this->dao->get();
            if( !$result ) {
                return array();
            }

            return $result->row();
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
            $this->dao->Where('fk_i_message_room_id', $messageRoomId);
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
    }

?>