<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/basic_controller.php';

class Pinboard extends Basic_Controller {

    public function __construct()
    {
        parent::__construct();
        if ( ! $this->_is_login() ) {
            redirect('login');
        }
    }

    function index()
    {
        $tpl = array();
        
        if ( ! isset($_SESSION['login']['user_id']) ) {
            redirect('home');
        }
        $user_id = $_SESSION['login']['user_id'];

        if ( isset($_SESSION['cancel_url']) && trim($_SESSION['cancel_url']) != '' ) {
            $tpl['cancel_url'] = $_SESSION['cancel_url'];
            unset($_SESSION['cancel_url']);
        }
        
        // 撈出曾經pin過的screenshot
        $this->db->select('app.appName, ss.url, ss.id');
        $this->db->from('user_pin');
        $this->db->join('screenshot AS ss', 'user_pin.screenshot_id = ss.id');
        $this->db->join('app', 'ss.appId = app.id');
        $this->db->where('user_pin.user_id', $user_id);
        $this->db->order_by('user_pin.update_time', 'desc');
        $tpl['query'] = $this->db->get();
        
        // user 行為紀錄
        $this->_do_user_session_log();
        $this->_do_user_session_click_log();
        
        $this->load->view('pinboard_tpl', $tpl);
    }
}

/* End of file upload.php */
/* Location: ./application/controllers/upload.php */