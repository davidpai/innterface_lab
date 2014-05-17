<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/basic_controller.php';

class Test extends Basic_Controller {

    public function __construct()
    {
        parent::__construct();
    }

	public function index()
	{
		echo 'OK!';
	}
}

/* End of file test.php */
/* Location: ./application/controllers/test.php */