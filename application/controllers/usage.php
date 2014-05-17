<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/basic_controller.php';

class Usage extends Basic_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    function index() 
    {
        if ( isset($_SESSION['usage']) ) {
            $this->user_total();
        } else {
            $pass = trim($this->input->post('pass', TRUE));
            
            if ( $pass != '' && $pass === $this->config->item('usage_pass') ) {
                session_regenerate_id(true);
                $_SESSION['usage'] = true;
                redirect('usage/user_total');
                exit;
            }
            $this->load->view('usage/login_tpl');
        }
    }
    
    function user_total() 
    {
        if ( !isset($_SESSION['usage']) ) {
            unset($_SESSION['usage']);
            redirect('usage');
            exit;
        }
        $sql = "
SELECT 
    t1.*, 
    t2.duration_minutes, 
    t2.duration_seconds, 
    t3.amount_of_uploads, 
    t4.amount_of_tags, 
    t5.amount_of_clicks
FROM (
    SELECT 
        id AS user_id, 
        first_name, 
        last_name, 
        email, 
        fb_name, 
        fb_email, 
        twitter_name 
    FROM `user` 
) AS t1 
LEFT OUTER JOIN 
(
    SELECT 
        user_id, 
        FLOOR(SUM(UNIX_TIMESTAMP(end_time)-UNIX_TIMESTAMP(login_time))/60) AS duration_minutes, 
        CEIL(SUM(UNIX_TIMESTAMP(end_time)-UNIX_TIMESTAMP(login_time))%60) AS duration_seconds
    FROM `user_session` 
    WHERE user_id IS NOT NULL 
    GROUP BY user_id
) AS t2 
ON t1.user_id = t2.user_id 
LEFT OUTER JOIN 
(
    SELECT
     user_id, COUNT(id) AS amount_of_uploads 
    FROM `user_screenshot` 
    GROUP BY user_id
) AS t3 
ON t1.user_id = t3.user_id 
LEFT OUTER JOIN 
(
    SELECT 
     user_id, COUNT(tag) AS amount_of_tags 
    FROM `user_screenshot_tag` 
    GROUP BY user_id 
) AS t4 
ON t1.user_id = t4.user_id 
LEFT OUTER JOIN 
(
    SELECT 
        user_id, COUNT(screenshot_id) AS amount_of_clicks 
    FROM `user_session_click` 
    WHERE user_id IS NOT NULL AND screenshot_id IS NOT NULL 
    GROUP BY user_id 
) AS t5 
ON t1.user_id = t5.user_id
        ";
        $r = $this->db->query($sql);
        $user_arr = array();
        foreach ($r->result() as $row) {
            $user = $this->_build_user_info($row);
            $user->duration_hours = 0;
            $user->duration_minutes = $row->duration_minutes;
            if ( $row->duration_minutes >= 60 ) {
                $user->duration_hours = floor($row->duration_minutes/60);
                $user->duration_minutes = $row->duration_minutes%60;
            }
            $user->duration_seconds = $row->duration_seconds;
            $user->amount_of_uploads = $row->amount_of_uploads;
            $user->amount_of_tags = $row->amount_of_tags;
            $user->amount_of_clicks = $row->amount_of_clicks;
            
            $user_arr[$row->user_id] = $user;
        }
        $tpl = array();
        $tpl['user_arr'] = $user_arr;
        $this->load->view('usage/user_total_tpl', $tpl);
    }
    
    private function _build_user_info($row) 
    {
        $user = new stdClass();
        $user->id = isset($row->user_id) ? $row->user_id : $row->id;
        $user->name = null;
        $user->email = null;
        if ( $row->first_name != '' ) {
            $user->name = $row->first_name;
        }
        if ( $row->last_name != '' ) {
            $user->name .= ' ' . $row->last_name;
        }
        if ( $row->email != '' ) {
            $user->email = $row->email;
        }
        $user->loginType = 'innterface';
        if ( $row->fb_name != '' ) {
            $user->name = $row->fb_name;
            $user->email = $row->fb_email;
            $user->loginType = 'Facebook';
        }
        if ( $row->twitter_name != '' ) {
            $user->name = $row->twitter_name;
            $user->email = null;
            $user->loginType = 'Twitter';
        }
        return $user;
    }
    
    function user_daily() 
    {
        if ( !isset($_SESSION['usage']) ) {
            unset($_SESSION['usage']);
            redirect('usage');
            exit;
        }
        $user_id = (int)trim($this->input->get('user_id', TRUE));
        $year_month = trim($this->input->get('year_month', TRUE));
        
        if ( $user_id <= 0 ) {
            $user_id = null;
        }
        if ( preg_match('/\d{4}\d{2}/i', $year_month) === 0 ) {
            $year_month = date('Ym');
        }
        
        $tpl = array();
        $tpl['user_id'] = $user_id;
        $tpl['year_month'] = $year_month;
        
        $this->load->model('user_model');
        $user_all_arr = array();
        $q = $this->user_model->get_all();
        foreach ( $q->result() as $row ) {
            $user = $this->_build_user_info($row);
            $user_all_arr[$user->id] = $user;
        }
        $tpl['user_all_arr'] = $user_all_arr;
        
        /*
        function cmp($a, $b) {
            if ( $a->name == $b->name ) {
                return 0;
            }
            return ($a->name < $b->name) ? -1 : 1;
        }
        usort($user_all_arr, "cmp");
        */

        $month_arr = array();
        for ($i=0; $i<24; $i++) {
            $time = mktime(12,0,0,1+$i,1,2013);
            $month_value = date('Y / m', $time);
            $month_key = date('Ym', $time);
            $month_arr[$month_key] = $month_value;
        }
        $tpl['month_arr'] = $month_arr;
        
        $daily_arr = array();
        $year = (int)substr($year_month, 0, 4);
        $month = (int)substr($year_month, 4, 2);
        $month_end_day = date('j', mktime(12,0,0,$month+1,0,$year));
        for ($i=1; $i<=$month_end_day; $i++) {
            $date = date('Y-m-d', mktime(12,0,0,$month,$i,$year));
            $daily = new stdClass();
            $daily->login_date = date('Y-m-d (D)', mktime(12,0,0,$month,$i,$year));
            $daily->duration_minutes = null;
            $daily->duration_seconds = null;
            $daily->amount_of_uploads = 0;
            $daily->amount_of_tags = 0;
            $daily->amount_of_clicks = 0;
            $daily_arr[$date] = $daily;
        }
        
        // duration of login
        $sql = "
SELECT 
    user_id, 
    DATE(login_time) AS date, 
    FLOOR(SUM(UNIX_TIMESTAMP(end_time)-UNIX_TIMESTAMP(login_time))/60) AS duration_minutes, 
    CEIL(SUM(UNIX_TIMESTAMP(end_time)-UNIX_TIMESTAMP(login_time))%60) AS duration_seconds
FROM `user_session` 
WHERE user_id = ".$this->db->escape($user_id)." AND DATE_FORMAT(login_time, '%Y%m') = ".$this->db->escape($year_month)." 
GROUP BY DATE_FORMAT(login_time, '%Y%m%d')
        ";
        $q = $this->db->query($sql);
        foreach ( $q->result() as $row ) {
            $daily = $daily_arr[$row->date];
            $daily->duration_minutes = $row->duration_minutes;
            $daily->duration_seconds = $row->duration_seconds;
            if ( $row->duration_minutes >= 60 ) {
                $daily->duration_hours = floor($row->duration_minutes/60);
                $daily->duration_minutes = $row->duration_minutes%60;
            }
        }

        // amount of uploads
        $sql = "
SELECT
    user_id, 
    DATE(add_time) AS date, 
    COUNT(id) AS amount_of_uploads 
FROM `user_screenshot` 
WHERE user_id = ".$this->db->escape($user_id)." AND DATE_FORMAT(add_time, '%Y%m') = ".$this->db->escape($year_month)." 
GROUP BY DATE_FORMAT(add_time, '%Y%m%d')
        ";
        $q = $this->db->query($sql);
        foreach ( $q->result() as $row ) {
            $daily = $daily_arr[$row->date];
            $daily->amount_of_uploads = $row->amount_of_uploads;
        }
        
        // amount of tags
        $sql = "
SELECT 
    user_id, 
    DATE(add_time) AS date, 
    COUNT(tag) AS amount_of_tags 
FROM `user_screenshot_tag` 
WHERE user_id = ".$this->db->escape($user_id)." AND DATE_FORMAT(add_time, '%Y%m') = ".$this->db->escape($year_month)."  
GROUP BY DATE_FORMAT(add_time, '%Y%m%d')
        ";
        $q = $this->db->query($sql);
        foreach ( $q->result() as $row ) {
            $daily = $daily_arr[$row->date];
            $daily->amount_of_tags = $row->amount_of_tags;
        }
        
        // amount of clicks
        $sql = "
SELECT 
    user_id, 
    DATE(add_time) AS date, 
    COUNT(screenshot_id) AS amount_of_clicks 
FROM `user_session_click` 
WHERE user_id = ".$this->db->escape($user_id)." AND screenshot_id IS NOT NULL AND DATE_FORMAT(add_time, '%Y%m') = ".$this->db->escape($year_month)."
GROUP BY DATE_FORMAT(add_time, '%Y%m%d')
        ";
        $q = $this->db->query($sql);
        foreach ( $q->result() as $row ) {
            $daily = $daily_arr[$row->date];
            $daily->amount_of_clicks = $row->amount_of_clicks;
        }
        
        $tpl['daily_arr'] = $daily_arr;
        $this->load->view('usage/user_daily_tpl', $tpl);
    }
    
    function user_daily_detail() 
    {
        $user_id = (int)trim($this->input->get('user_id', TRUE));
        $set_date = trim($this->input->get('set_date', TRUE));
        
        $tpl = array();
        $tpl['user_id'] = $user_id;
        $tpl['set_date'] = $set_date;
        
        if ( $user_id <= 0 ) {
            $user_id = null;
        }
        if ( preg_match('/\d{4}-\d{2}-\d{2}/i', $set_date) === 0 ) {
            $set_date = date('Y-m-d');
        }
        list($year, $month, $day) = explode('-', $set_date);
        $year = (int)$year;
        $month = (int)$month;
        $day = (int)$day;

        $this->load->model('user_model');
        $user_all_arr = array();
        $q = $this->user_model->get_all();
        foreach ( $q->result() as $row ) {
            $user = $this->_build_user_info($row);
            $user_all_arr[$user->id] = $user;
        }
        $tpl['user_all_arr'] = $user_all_arr;
        
        $daily_arr = array();
        $month_end_day = date('j', mktime(12,0,0,$month+1,0,$year));
        for ($i=1; $i<=$month_end_day; $i++) {
            $date = date('Y-m-d', mktime(12,0,0,$month,$i,$year));
            $daily = new stdClass();
            $daily->login_date = date('Y-m-d (D)', mktime(12,0,0,$month,$i,$year));
            $daily_arr[$date] = $daily;
        }
        $tpl['daily_arr'] = $daily_arr;
        
        $all_arr = array();
        $sql = "
SELECT screenshot_id, add_time  
FROM `user_screenshot` 
WHERE user_id = ".$this->db->escape($user_id)." AND DATE_FORMAT(add_time, '%Y-%m-%d') = ".$this->db->escape($set_date)." 
ORDER BY add_time ASC
        ";
        $q = $this->db->query($sql);
        $upload_arr = array();
        foreach ( $q->result() as $row ) {
            $upload_arr[] = $row;
            $all_item = new stdClass();
            $all_item->type = 'uploads';
            $all_item->screenshot_id = $row->screenshot_id;
            $all_item->tag = null;
            $all_item->url = null;
            $all_item->query_term = null;
            $all_item->add_time = $row->add_time;
            $all_arr[] = $all_item;
        }
        $tpl['upload_arr'] = $upload_arr;
        
        $sql = "
SELECT screenshot_id, tag, add_time  
FROM `user_screenshot_tag` 
WHERE user_id = ".$this->db->escape($user_id)." AND DATE_FORMAT(add_time, '%Y-%m-%d') = ".$this->db->escape($set_date)." 
ORDER BY add_time ASC
        ";
        $q = $this->db->query($sql);
        $tag_arr = array();
        foreach ( $q->result() as $row ) {
            $tag_arr[] = $row;
            $all_item = new stdClass();
            $all_item->type = 'tags';
            $all_item->screenshot_id = $row->screenshot_id;
            $all_item->tag = $row->tag;
            $all_item->url = null;
            $all_item->query_term = null;
            $all_item->add_time = $row->add_time;
            $all_arr[] = $all_item;
        }
        $tpl['tag_arr'] = $tag_arr;
        
        $sql = "
SELECT screenshot_id, query_term, url, add_time  
FROM `user_session_click` 
WHERE screenshot_id IS NOT NULL AND user_id = ".$this->db->escape($user_id)." AND DATE_FORMAT(add_time, '%Y-%m-%d') = ".$this->db->escape($set_date)." 
ORDER BY add_time ASC
        ";
        $q = $this->db->query($sql);
        $click_arr = array();
        foreach ( $q->result() as $row ) {
            $click_arr[] = $row;
            $all_item = new stdClass();
            $all_item->type = 'tags';
            $all_item->screenshot_id = $row->screenshot_id;
            $all_item->tag = null;
            $all_item->query_term = $row->query_term;
            $all_item->url = $row->url;
            $all_item->add_time = $row->add_time;
            $all_arr[] = $all_item;
        }
        $tpl['click_arr'] = $click_arr;
        
        function cmp($a, $b) {
            if ($a->add_time == $b->add_time) {
                return 0;
            }
            return ($a->add_time < $b->add_time) ? -1 : 1;
        }
        usort($all_arr, "cmp");
        $tpl['all_arr'] = $all_arr;
        
        $this->load->view('usage/user_daily_detail_tpl', $tpl);
    }
    
    function logout() 
    {
        unset($_SESSION['usage']);
        session_regenerate_id(true);
        redirect('usage');
    }
}

/* End of file signup.php */
/* Location: ./application/controllers/signup.php */