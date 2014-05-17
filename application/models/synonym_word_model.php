<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'basic_model.php';

class Synonym_word_model extends Basic_Model {

	function __construct() 
	{
		parent::__construct();
        $this->table_name = 'synonym_word';
	}

    function getLanguageList() 
    {
        $language_arr = array();
        $this->db->select('language');
        $this->db->where('language IS NOT NULL AND language != ""');
        $this->db->group_by('language');
        $this->db->order_by('language', 'asc');
        $q = $this->db->get($this->table_name);
        foreach ( $q->result() as $row ) {
            $language_arr[] = $row->language;
        }
        return $language_arr;
    }
    
    function getProfessionList() 
    {
        $profession_arr = array();
        $this->db->select('profession');
        $this->db->where('profession IS NOT NULL AND profession != ""');
        $this->db->group_by('profession');
        $this->db->order_by('profession', 'asc');
        $q = $this->db->get($this->table_name);
        foreach ( $q->result() as $row ) {
            $profession_arr[] = $row->profession;
        }
        return $profession_arr;
    }
    
    function get_synonym($term, $synonym_arr=array()) 
    {
/*
SELECT `word` FROM `synonym_word` WHERE `id` IN (
    SELECT synonym_word_id FROM `synonym_relation` t1 JOIN `synonym_word` t2 ON t1.word_id = t2.id WHERE t2.word = 'activity'
)
*/
        $this->db->select('synonym_word_id');
        $this->db->from('synonym_relation AS t1');
        $this->db->join('synonym_word AS t2', 't1.word_id = t2.id');
        $this->db->where('t2.word', $term);
        $this->db->get();
        $sub_query = $this->db->last_query();
        
        $this->db->select('word');
        $this->db->from('synonym_word');
        $this->db->where('`id` IN (' . $sub_query . ')', null, false);
        $q = $this->db->get();
        foreach ( $q->result() as $row ) {
            $synonym_arr[$row->word] = $row->word;
        }
        return $synonym_arr;
    }
    
    function get_display_word() 
    {
        $this->db->select('word, display_name');
        $this->db->from('synonym_word');
        $this->db->where('display_status', 1);
        $this->db->group_by('word');
        return $this->db->get();
    }
}

/* End of file synonym_word_model.php */
/* Location: ./application/models/synonym_word_model.php */