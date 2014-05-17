<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'basic_model.php';

class User_session_click_model extends Basic_Model {

	function __construct() 
	{
		parent::__construct();
        $this->table_name = 'user_session_click';
	}

}

/* End of file user_session_click_model.php */
/* Location: ./application/models/user_session_click_model.php */