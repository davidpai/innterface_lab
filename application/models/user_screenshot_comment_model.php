<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'basic_model.php';

class User_screenshot_comment_model extends Basic_Model {

	function __construct() 
	{
		parent::__construct();
        $this->table_name = 'user_screenshot_comment';
	}

    public function make_screenshot_comment_word() 
    {
        $this->load->model('word_process_model');
        
        $this->db->truncate('screenshot_comment_word');
        
        $q = $this->get_all();
        foreach ( $q->result() as $row ) {
            $word_arr = $this->word_process_model->make_word_process($row->comment);
            $this->save_screenshot_comment_word($row->screenshot_id, $word_arr);
        }
    }
    
    public function save_screenshot_comment_word($screenshot_id, $word_arr) 
    {
        if ( is_array($word_arr) && count($word_arr) > 0 ) {
        } else {
            return false;
        }
        $screenshot_id = (int)$screenshot_id;
        
        foreach ( $word_arr as $word => $frequency ) {
            $this->db->set('frequency', 'frequency+'.$frequency, FALSE);
            $this->db->where(array('screenshot_id'=>$screenshot_id, 'word'=>$word));
            $r = $this->db->update('screenshot_comment_word');
            //var_dump($this->db->affected_rows());
            if ( $this->db->affected_rows() == 0 ) {
                $data = array(  'screenshot_id' => $screenshot_id, 
                                'word' => $word, 
                                'frequency' => $frequency, 
                                );
                $this->db->insert('screenshot_comment_word', $data);
            }
        }
    }
}

/* End of file user_screenshot_comment_model.php */
/* Location: ./application/models/user_screenshot_comment_model.php */