<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/basic_controller.php';

class Login extends Basic_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    function index() 
    {
        // user 行為紀錄
        $this->_do_user_session_log();
        $this->_do_user_session_click_log();
        
        $return_url = trim($this->input->get_post('r', TRUE));
        $_SESSION['login']['from_url'] = $return_url;
        //$this->make_from_url();
        $tpl = array(   'is_login' => $this->_is_login(),
                        );
        $this->load->view('login_tpl', $tpl);
    }
    
    function twitter()
    {
        try {
            $oauth_verifier = trim($this->input->get_post('oauth_verifier', TRUE));
            
            $this->load->library('tmhOAuth/tmhOAuth', array(
                'timezone'        => date_default_timezone_get(), 
                'consumer_key'    => '4Mb0DRY94BrjLl7dpvBmA',
                'consumer_secret' => 'cXoSTTOxgembIWignd3JOp1JpnlCe0EBa6NuZZnYaM',
            ), 'oauth');
            
            // We already got some credentials stored
            if ( isset($_SESSION['access_token']) ) {
                $this->twitter_verify();
            }
            // 3. Called back by Twitter
            elseif ( $oauth_verifier !== '' ) {
                
                $this->oauth->config['user_token']  = $_SESSION['oauth']['oauth_token'];
                $this->oauth->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];
                
                // 4. Convert request token to access token
                $code = $this->oauth->request('POST', $this->oauth->url('oauth/access_token', ''), array(
                    'oauth_verifier' => $oauth_verifier
                ));
                
                if ( 200 !== $code ) {
                    throw new Exception('Sorry, We got some problem when connecting to Twitter. Please return to previous page.');
                }
                
                $_SESSION['access_token'] = $this->oauth->extract_params($this->oauth->response['response']);
                $this->twitter_verify();
                
            } else {
                
                // 1.obtain a request token
                $params = array(
                    'oauth_callback' => site_url('login/twitter')
                );
    
                $code = $this->oauth->request('POST', $this->oauth->url('oauth/request_token', ''), $params);
                
                if ( 200 !== $code ) {
                    throw new Exception('Sorry, We got some problem when connecting to Twitter. Please return to previous page.');
                }
                
                // 2.redirect user to Twitter's authenticate page
                $_SESSION['oauth'] = $this->oauth->extract_params($this->oauth->response['response']);
                $authurl = $this->oauth->url("oauth/authenticate", '') . "?oauth_token={$_SESSION['oauth']['oauth_token']}";
                redirect($authurl, 'location', 301);
            }
            
        } catch (Exception $e) {
            
            exit($e->getMessage());
        }
    }
    
    private function twitter_verify()
    {
        try {
            if ( isset($_SESSION['access_token']) ) {
                
                $this->oauth->config['user_token']  = $_SESSION['access_token']['oauth_token'];
                $this->oauth->config['user_secret'] = $_SESSION['access_token']['oauth_token_secret'];

                $code = $this->oauth->request('GET', $this->oauth->url('1.1/account/verify_credentials'));

                if ( 200 !== $code ) {
                    unset($_SESSION['oauth']);
                    unset($_SESSION['access_token']);
                    throw new Exception('Sorry, We got some problem when connecting to Twitter. Please return to previous page.');
                }

                $resp = json_decode($this->oauth->response['response']);
                
                $this->load->model('user_model');
                $user = $this->user_model->get_by(array('twitter_id'=>$resp->id))->row();
                if ( ! empty($user) ) {
                    $data = array(  'oauth_provider'    => 'Twitter',
                                    'oauth_token'       => $_SESSION['access_token']['oauth_token'],
                                    'oauth_token_secret'=> $_SESSION['access_token']['oauth_token_secret'], 
                                    'twitter_name'      => $resp->name,
                                    'twitter_picture'   => $resp->profile_image_url,
                                    'last_login_time'   => date('Y-m-d H:i:s'), 
                                    );
                    $result = $this->user_model->update_by($data, array('id'=>$user->id));
                } else {
                    $data['twitter_id'] = $resp->id;
                    $data['add_time'] = date('Y-m-d H:i:s');
                    $user = new stdClass();
                    $user->id = $this->user_model->add($data);
                }

                session_regenerate_id(TRUE);
                $_SESSION['login']['user_id'] = $user->id;
                $_SESSION['login']['user_name'] = $resp->name;
                $_SESSION['login']['user_picture'] = $resp->profile_image_url;
                $_SESSION['login']['login_time'] = date('Y-m-d H:i:s');
                if ( isset($_SESSION['login']['from_url']) ) {
                    redirect($_SESSION['login']['from_url'], 'location', 301);
                } else {
                    redirect('home', 'location', 301);
                }
            }
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    private function make_from_url()
    {
        $controller = trim($this->input->get_post('c', TRUE));
        $method = trim($this->input->get_post('m', TRUE));
        $query_string = trim($this->input->get_post('qs', TRUE));
        
        $uri = '';
        if ( $controller != '' ) {
            $uri .= $controller;
            if ( $method != '' ) {
                $uri .= '/'.$method;
            }
        }
        $site_url = '';
        if ( $uri != '' ) {
            $site_url = site_url($uri);
            if ( $query_string != '' ) {
                $site_url .= '?'.$query_string;
            }
        }
        if ( $site_url != '' ) {
            $_SESSION['login']['from_url'] = $site_url;
        }
    }
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */