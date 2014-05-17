<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'basic_model.php';

class Screenshot_tag_model extends Basic_Model {

    function __construct() 
    {
        parent::__construct();
        $this->table_name = 'screenshot_tag';
    }

    function get_by_screenshot($screenshot_id)
    {
        $screenshot_id = (int)$screenshot_id;
        
        $return_arr = array();
        $this->db->select();
        $this->db->from($this->table_name);
        $this->db->where('screenshot_id', $screenshot_id);
        $this->db->order_by('frequency', 'desc');
        $q = $this->db->get();
        if ( $q->num_rows() > 0 ) {
            foreach ( $q->result() as $row ) {
                $return_arr[$row->id] = $row;
            }
        }
        return $return_arr;
    }
}

/* End of file screenshot_tag_model.php */
/* Location: ./application/models/screenshot_tag_model.php */