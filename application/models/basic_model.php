<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Basic_model extends CI_Model {

    protected $table_name = '';
    protected $id_field = 'id';
    
    function __construct()
    {
        parent::__construct();
    }

    function get_all() 
    {
        return $this->db->get($this->table_name);
    }
    
    function get_by_id($id) 
    {
        return $this->db->get_where($this->table_name, array($this->id_field=>$id));
    }
    
    function get_by($where) 
    {
        return $this->db->get_where($this->table_name, $where);
    }
    
    function add($data) 
    {
        $result = $this->db->insert($this->table_name, $data);
        if ( $result ) {
            return $this->db->insert_id();
        } else {
            return $result;
        }
    }

    function update_by_id($data, $id) 
    {
        $result = $this->db->update($this->table_name, $data, array($this->id_field=>$id));
        if ( $result ) {
            return $this->db->affected_rows();
        } else {
            return $result;
        }
    }
    
    function update_by($data, $where) 
    {
        $result = $this->db->update($this->table_name, $data, $where);
        if ( $result ) {
            return $this->db->affected_rows();
        } else {
            return $result;
        }
    }
    
    function delete_by_id($id) 
    {
        $this->db->delete($this->table_name, array($this->id_field=>$id));
    }
    
    function delete_by($where) 
    {
        $this->db->delete($this->table_name, $where);
    }
    
    function get_in_id($id_array) 
    {
        $this->db->where_in($this->id_field, $id_array);
        return $this->db->get($this->table_name);
    }
    
    function update_in_id($data, $id_array) 
    {
        $this->db->where_in($this->id_field, $id_array);
        $this->db->update($this->table_name, $data);
    }
}

/* End of file raw_itunes_rss_model.php */
/* Location: ./application/models/raw_itunes_rss_model.php */