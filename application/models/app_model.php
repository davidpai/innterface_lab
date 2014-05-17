<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'basic_model.php';

class App_model extends Basic_Model {

	function __construct() 
	{
		parent::__construct();
        $this->table_name = 'app';
	}

}

/* End of file app_model.php */
/* Location: ./application/models/app_model.php */