<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'basic_model.php';

class App_keyword_model extends Basic_Model {

    function __construct() 
    {
        parent::__construct();
        $this->table_name = 'app_keyword';
    }

}

/* End of file screenshot_model.php */
/* Location: ./application/models/screenshot_model.php */