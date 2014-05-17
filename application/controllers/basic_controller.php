<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Basic_Controller extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('PHPSession');
    }
    
    protected function _is_login()
    {
        if ( isset($_SESSION['login']['user_id']) ) {
            return true;
        }
        return false;
    }

    protected function _do_user_session_log() 
    {
        $now = date('Y-m-d H:i:s');
        $phpsessionid = session_id();
        $this->load->model('user_session_model');
        $q = $this->user_session_model->get_by(array('phpsessionid'=>$phpsessionid));
        if ( $q->num_rows() > 0 ) {
            $_SESSION['user_session']['end_time'] = $now;
            if ( $this->_is_login() ) {
                $_SESSION['user_session']['user_id'] = $_SESSION['login']['user_id'];
                $_SESSION['user_session']['login_time'] = $_SESSION['login']['login_time'];
            }
            $this->user_session_model->update_by($_SESSION['user_session'], array('phpsessionid' => $phpsessionid));
        } else {
            $_SESSION['user_session']['phpsessionid'] = $phpsessionid;
            $_SESSION['user_session']['begin_time'] = $now;
            $_SESSION['user_session']['end_time'] = $now;
            if ( $this->_is_login() ) {
                $_SESSION['user_session']['user_id'] = $_SESSION['login']['user_id'];
                $_SESSION['user_session']['login_time'] = $_SESSION['login']['login_time'];
            }
            $this->user_session_model->add($_SESSION['user_session']);
        }
    }
    
    protected function _do_user_session_click_log($screenshot_id=null) 
    {
        $url = ( $_SERVER['QUERY_STRING'] != '' ) ? current_url().'?'.$_SERVER['QUERY_STRING'] : current_url();
        $screenshot_id = (int)$screenshot_id;
        $now = date('Y-m-d H:i:s');
        $phpsessionid = session_id();
        $this->load->model('user_session_click_model');
        
        $_SESSION['user_session_click']['phpsessionid'] = $phpsessionid;
        $_SESSION['user_session_click']['url'] = $url;
        if ( $this->_is_login() ) {
            $_SESSION['user_session_click']['user_id'] = $_SESSION['login']['user_id'];
        }
        unset($_SESSION['user_session_click']['screenshot_id']);
        if ( $screenshot_id > 0 ) {
            $_SESSION['user_session_click']['screenshot_id'] = $screenshot_id;
        }
        $_SESSION['user_session_click']['add_time'] = $now;
        
        $this->user_session_click_model->add($_SESSION['user_session_click']);
    }
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */