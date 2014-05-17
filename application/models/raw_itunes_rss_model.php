<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'basic_model.php';

class Raw_itunes_rss_model extends Basic_Model {

	function __construct() 
	{
		parent::__construct();
        $this->table_name = 'raw_itunes_rss';
	}

}

/* End of file raw_itunes_rss_model.php */
/* Location: ./application/models/raw_itunes_rss_model.php */