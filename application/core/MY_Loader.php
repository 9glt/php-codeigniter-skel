<?php

class MY_Loader extends CI_Loader
{
    /* overloaded methods */



    public function database($params = '', $return = false, $query_builder = null)
    {
        $ci =& get_instance();

        if ($return === false && $query_builder === null && isset($ci->db) && is_object($ci->db) && !empty($ci->db->conn_id)) {
            return false;
        }

        require_once(BASEPATH . 'database/DB.php');

        $dba =& DB($params, $query_builder);

        $driver = config_item('subclass_prefix') . 'DB_' . $dba->dbdriver . '_driver';
        $file = APPPATH . 'core/' . $driver . '.php';
        if (file_exists($file) === true && is_file($file) === true) {
            require_once($file);
            
            $dbo = new $driver(get_object_vars($dba));
            $db = & $dbo;
        }
        if ($return === true) {
            return $db;
        }

        $ci->db = '';
        $ci->db = $db;
        $ci->db->load_driver();

        return $this;
    }
}
