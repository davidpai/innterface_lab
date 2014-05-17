<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/basic_controller.php';

class Home extends Basic_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    function index()
    {
        $start = microtime(true);
        log_message('debug', 'transfer/dev2lab_app: Start');
        
        $_SESSION['upload']['cancel_url'] = 'home';
        // 關於網站的登入與FB的登入關係
        //  1.網站未登入(沒有建立session cookie)
        //      1.1. FB未登入 => 頁面顯示未登入的狀態，FB login不需動作
        //      1.2. FB已登入 => 前端向FB撈取user data，呼叫後端API，將user data丟給API，後端API進行處理後，產生網站session cookie，建立登入狀態，返回結果給前端，前端顯示頁面為已登入狀態
        //  2.網站已登入()
        //      2.1. FB未登入 => 頁面顯示已登入狀態，FB login不需動作
        //      2.2. FB已登入 => 頁面顯示已登入狀態，FB login不需動作
        // 網站登入與Twitter登入的關係
        //  1.網站未登入(沒有建立session cookie)
        //      1.1. Twitter未登入 => 頁面顯示未登入的狀態，Twitter login不需動作
        //      1.2. Twitter已登入 => 前端呼叫後端API，後端API向Twitter撈取user data更新DB，產生網站session cookie，建立登入狀態，返回結果給前端，前端顯示頁面為已登入狀態
        //  2.網站已登入
        //      2.1. Twitter未登入 => 頁面顯示已登入狀態，Twitter login不需動作
        //      2.2. Twitter已登入 => 頁面顯示已登入狀態，Twitter login不需動作

        $tpl = array();
        $tpl['is_login'] = $this->_is_login();

        if ( $this->_is_login() ) {
            $user = new stdClass();
            $user->id = $_SESSION['login']['user_id'];
            $user->name = isset($_SESSION['login']['user_name']) ? $_SESSION['login']['user_name'] : '';
            $user->picture = isset($_SESSION['login']['user_picture']) ? $_SESSION['login']['user_picture'] : '';
            $tpl['user'] = $user;
        } else {
            $_SESSION['login']['from_url'] = site_url('home');
            $_SESSION['cancel_url'] = site_url('home');
        }
        
        // 從已做好index的tag_screenshot撈出每個tag的screenshot_list，取list陣列中的第一個
        $tag_screenshoot_arr = array();
        $this->db->select();
        $this->db->from('tag_screenshot_index');
        $this->db->order_by('tag', 'ASC');
        $q = $this->db->get();
        foreach ( $q->result() as $row ) {
            $screenshot_list = unserialize($row->screenshot_list);
            $tag_screenshoot_arr[] = array( 'tag' => $row->tag,
                                            'screenshot' => $screenshot_list[0],
                                            );
        }
        
        // 檢查圖片URL，若圖片已不存在，則從陣列裡移除
        $this->load->model('screenshot_model');
        foreach ( $tag_screenshoot_arr as $k => $v ) {
            $screenshot = $v['screenshot'];
            $read = $this->screenshot_model->is_url_exist($screenshot->url);
            if ( $read === FALSE ) {
                unset($tag_screenshoot_arr[$k]);
            }
        }
        
        $tpl['tag_screenshoot_arr'] = $tag_screenshoot_arr;

        /*
        // 搜尋screenshot tag，撈出每一種tag出現次數最高的那個sccreenshot
        // 2013/4/30 搜尋screenshot tag，撈出每一種tag，依tag字母正序排列
        $this->db->select(' screenshot_tag.tag,
                            screenshot.url,
                            screenshot.app_id
                            ');
        $this->db->from('screenshot_tag');
        $this->db->join('screenshot', 'screenshot_tag.screenshot_id=screenshot.id');
        $this->db->group_by('screenshot_tag.tag');
        //$this->db->order_by('screenshot_tag.frequency', 'DESC');
        $this->db->order_by('screenshot_tag.tag', 'ASC');
        $tpl['query'] = $this->db->get();
        */
        
        // user 行為紀錄
        $this->_do_user_session_log();
        $this->_do_user_session_click_log();
        
        $this->load->view('home_new_tpl', $tpl);
    }
}

/* End of file home.php */
/* Location: ./application/controllers/home.php */