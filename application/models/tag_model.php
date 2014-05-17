<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'basic_model.php';

class Tag_model extends Basic_Model {

    function __construct() 
    {
        parent::__construct();
        $this->table_name = 'tag';
    }

    /**
     * 從同義字字典中撈出定義好的字詞，做出tag對screenshot的index
     */
    function make_screenshot_index() 
    {
        $this->load->model('synonym_word_model', 'synonym_word');
        $this->load->model('weight_model', 'weight');
        
        $screenshot_arr_list = array();
        $word_list = array();
        $q = $this->synonym_word->get_display_word();
        foreach ( $q->result() as $r ) {
            $result[$r->display_name] = array();
            $screenshot_arr = $this->weight->make_weight_by_tag_or($r->word);
            $result[$r->display_name] = array( 'tag' => $r->display_name,
                                    'frequency' => NULL, 
                                    'screenshot_list' => $screenshot_arr, 
                               );
        }
        return $result;
    }
    /*
    function make_screenshot_index()
    {
        $this->load->model('screenshot_model');
        
        $word_list = array();
        $this->db->select('word, display_name');
        $this->db->from('synonym_word');
        $this->db->where('display_status', 1);
        $this->db->group_by('word');
        $q1 = $this->db->get();
        foreach ( $q1->result() as $row1 ) {
            $word_list[$row1->display_name] = array();
            $word_list[$row1->display_name][$row1->word] = $row1->word;
            $q2 = $this->db->get_where('synonym_synonym', array('word'=>$row1->word));
            foreach ( $q2->result() as $row2 ) {
                $word_list[$row1->display_name][$row2->synonym] = $row2->synonym;
            }
        }
        //var_dump($word_list); exit;
        
        $result = array();
        foreach ( $word_list as $display_name => $synonym_list ) {
            $result[$display_name] = array();
            $screenshot_arr = array();
            foreach ( $synonym_list as $synonym ) {
                $screenshot_arr = array_merge($screenshot_arr, $this->screenshot_model->make_weight_by_keyword($synonym));
            }
            $result[$display_name] = array( 'tag' => $display_name,
                                    'frequency' => NULL, 
                                    'screenshot_list' => $screenshot_arr, 
                               );
        }
        //var_dump($result);
        return $result;
    }
    */
}

/* End of file tag_model.php */
/* Location: ./application/models/tag_model.php */