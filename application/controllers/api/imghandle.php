<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/../basic_controller.php';

class Imghandle extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    function index() 
    {
        redirect('home');
    }
    
    function getInit()
    {
        $limit = (int)$this->input->get('limit', TRUE);
        if ( $limit > 300 ) {
            $limit = 300;
        }
        
        $res = array();
        
        $this->db->select();
        $this->db->from('screenshot');
        $this->db->where(array('s3_url'=>''));
        $this->db->limit($limit);
        $q = $this->db->get();
        foreach ( $q->result() as $row ) {
            $res[] = $row;
        }
        echo json_encode($res);
    }
    
    function update() {
        try {
            $ss_json = $this->input->post('ss_json', TRUE);
            $ss_arr = json_decode($ss_json);
            //var_dump($ss_arr);
            if ( is_array($ss_arr) ) {
                foreach ( $ss_arr as $ss ) {
                    $data = array('s3_url' => $ss->s3_url,
                                  //'fetch_time' => $ss->fetch_time,
                                  //'fetch_status' => $ss->fetch_status, 
                                  );
                    $this->db->update('screenshot', $data, array('id'=>$ss->id));
                }
            }
            echo 'OK';
        } catch (Exception $e) {
            echo 'Fail';
        }
    }
}

/* End of file screenshot.php */
/* Location: ./application/controllers/screenshot.php */