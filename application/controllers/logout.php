<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/basic_controller.php';

class Logout extends Basic_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    function index() 
    {
        unset($_SESSION['login']);
        unset($_SESSION['upload']);
        unset($_SESSION['oauth']);
        unset($_SESSION['access_token']);
        unset($_SESSION['user_session']);
        unset($_SESSION['user_session_click']);
        unset($_SESSION['search_app']);
        session_regenerate_id(TRUE);
        redirect('home');
    }
}

/* End of file logout.php */
/* Location: ./application/controllers/logout.php */