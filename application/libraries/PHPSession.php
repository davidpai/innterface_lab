<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PHPSession {
	private $_my_session;

    public function __construct()
    {
		$CI =& get_instance();

		$this->_my_session = $CI->config->item('my_session');

		if ( isset($this->_my_session['save_path']) ) {
			session_save_path($this->_my_session['save_path']);
		}
		if ( isset($this->_my_session['name']) ) {
			session_name($this->_my_session['name']);
		}
		if ( isset($this->_my_session['gc_probability']) ) {
			ini_set('session.gc_probability', $this->_my_session['gc_probability']);
		}
		if ( isset($this->_my_session['gc_divisor']) ) {
			ini_set('session.gc_divisor', $this->_my_session['gc_divisor']);
		}
		if ( isset($this->_my_session['gc_maxlifetime']) ) {
			ini_set('session.gc_maxlifetime', $this->_my_session['gc_maxlifetime']);
		}

		$cookie_params = session_get_cookie_params();
		$cookie_lifetime = isset($this->_my_session['cookie_lifetime']) ? $this->_my_session['cookie_lifetime'] : $cookie_params['lifetime'] ;
		$cookie_path = isset($this->_my_session['cookie_path']) ? $this->_my_session['cookie_path'] : $cookie_params['path'] ;
		$cookie_domain = isset($this->_my_session['cookie_domain']) ? $this->_my_session['cookie_domain'] : $cookie_params['domain'] ;

		session_set_cookie_params($cookie_lifetime, $cookie_path, $cookie_domain);

		session_start();
    }
}

/* End of file PHPSession.php */
/* Location: ./application/libraries/PHPSession.php */