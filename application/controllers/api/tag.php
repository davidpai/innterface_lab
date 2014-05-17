<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/../basic_controller.php';

class Tag extends Basic_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * for tag input autocomplete
     **/
    function search()
    {
        try {
            $term = trim($this->input->get('term', TRUE));
            
            if ( $term == '' ) {
                throw new Exception('you need to input some search term');
            }
            
            $this->db->select('tag');
            $this->db->from('tag');
            $this->db->like('tag', $term, 'after');
            $this->db->order_by('frequency', 'desc');
            $q = $this->db->get();
            
            $source = array();
            foreach ( $q->result() as $row ) {
                $source[] = $row->tag;
            }
            
            $data = array( 'error' => 0,
                           'msg' => '',
                           'source' => $source);
            echo json_encode($data);
            
        } catch (Exception $e) {
            
            $data = array( 'error' => 1,
                           'msg' => $e->getMessage() );
            echo json_encode($data);
        }
    }
}

/* End of file tag.php */
/* Location: ./application/controllers/api/tag.php */