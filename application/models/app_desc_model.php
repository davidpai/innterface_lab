<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'basic_model.php';

class App_desc_model extends Basic_Model {

	function __construct() 
	{
		parent::__construct();
        $this->table_name = 'app_desc';
	}

}

/* End of file app_desc_model.php */
/* Location: ./application/models/app_desc_model.php */