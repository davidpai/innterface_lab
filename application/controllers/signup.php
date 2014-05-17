<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/basic_controller.php';

class Signup extends Basic_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    function index() 
    {
        // user 行為紀錄
        $this->_do_user_session_log();
        $this->_do_user_session_click_log();
        
        $tpl = array(   'is_login' => $this->_is_login(),
                        );
        $this->load->view('signup_tpl', $tpl);
    }
}

/* End of file signup.php */
/* Location: ./application/controllers/signup.php */