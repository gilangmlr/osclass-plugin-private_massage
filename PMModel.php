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
    }

?>