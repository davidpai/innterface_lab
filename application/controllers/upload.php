<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/basic_controller.php';

class Upload extends Basic_Controller {

    private $search_api_url = 'https://itunes.apple.com/search?term=%s&media=software&limit=100';
    
    public function __construct()
    {
        parent::__construct();
        if ( ! $this->_is_login() ) {
            redirect('login');
        }
    }
    
    function index() 
    {
        $this->history();
    }
    
    // 上傳的screenshot列表畫面
    function history()
    {
        if ( ! isset($_SESSION['login']['user_id']) ) {
            redirect('upload');
        }
        $user_id = $_SESSION['login']['user_id'];
        
        $tpl = array();
        $tpl['cancel_url'] = isset($_SESSION['upload']['cancel_url']) ? $_SESSION['upload']['cancel_url'] : '';
        
        // 撈出曾經上傳過的screenshot
        $this->db->select('app.appName, uss.id, uss.screenshot_id, uss.file_name');
        $this->db->from('user_screenshot AS uss');
        $this->db->join('screenshot AS ss', 'uss.screenshot_id = ss.id', 'left outer');
        $this->db->join('app', 'ss.appId = app.id', 'left outer');
        $this->db->where('uss.user_id', $user_id);
        $q = $this->db->get();

        // user 行為紀錄
        $this->_do_user_session_log();
        $this->_do_user_session_click_log();

        if ( $q->num_rows() > 0 ) {
            $tpl['query'] = $q;
            $this->load->view('upload_history_tpl', $tpl);
        } else {
            $tpl['empty'] = true;
            $this->load->view('upload_empty_tpl', $tpl);
        }
    }

    // 預備上傳畫面
    function add() 
    {
        $tpl = array();
        $tpl['cancel_url'] = '';
        
        if ( ! isset($_SESSION['login']['user_id']) ) {
            redirect('upload');
        }
        $user_id = $_SESSION['login']['user_id'];

        // user 行為紀錄
        $this->_do_user_session_log();
        $this->_do_user_session_click_log();

        $this->load->view('upload_empty_tpl', $tpl);
    }

    // 上傳完成後，跳轉到搜尋App及輸入Tag的畫面
    function tag()
    {
        try {
            if ( ! isset($_SESSION['login']['user_id']) ) {
                redirect('home');
            }
            $user_id = $_SESSION['login']['user_id'];

            $file_name = isset($_SESSION['upload']['file_name']) ? $_SESSION['upload']['file_name'] : '';
            $origin_file_name = isset($_SESSION['upload']['origin_file_name']) ? $_SESSION['upload']['origin_file_name'] : '';
            unset($_SESSION['upload']['file_name']);
            unset($_SESSION['upload']['origin_file_name']);
            
            //** 把檔案從暫存目錄搬到實際存放目錄 **//
            $upload_tmp = $this->config->item('upload_tmp'); // 暫存檔路徑
            $upload_file = $this->config->item('upload_file');  // 實際應該存放的路徑
            $user_screenshot_id_arr = array();
            if ( $file_name != '' && $origin_file_name != '' ) 
            {
                $file_name_array = explode('::', $file_name);
                $origin_file_name_array = explode('::', $origin_file_name);
                    
                for ( $i=0; isset($file_name_array[$i]); $i++ ) 
                {
                    // 決定分類子目錄
                    $dot_pos = (int)strrpos($file_name_array[$i],'.');
                    $dir = substr($file_name_array[$i], $dot_pos-3, 3);
                    $target_dir = "{$upload_file}/{$dir}";
                    if ( !is_dir($target_dir) ) {
                        mkdir($target_dir, 0755);
                    }
                    
                    $source_path = "{$upload_tmp}/{$file_name_array[$i]}";
                    $target_path = "{$target_dir}/{$file_name_array[$i]}";

                    // 決定副檔名
                    $ext = pathinfo($source_path, PATHINFO_EXTENSION);
                    $ext_arr = $this->config->item('file_ext');
                    if ( array_key_exists($ext, $ext_arr) ) {
                        $ext = $ext_arr[$ext];
                    } else {
                        $ext = 'file';
                    }

                    if ( file_exists($source_path) ) 
                    {
                        // 搬移檔案
                        $result = rename($source_path,$target_path);
                        // 寫入 user_screenshot
                        if ( $result ) {
                            $data = array(  'user_id' => $user_id, 
                                            'file_name' => $file_name_array[$i], 
                                            'origin_file_name' => $origin_file_name_array[$i], 
                                            'file_extension' => $ext,
                                            'add_time' => date('Y-m-d H:i:s'), 
                                            );
                            $this->db->insert('user_screenshot', $data);
                            $insert_id = $this->db->insert_id();
                            $user_screenshot_id_arr[$insert_id] = $insert_id;
                        }
                    }
                }
            }
            
            // 將此次上傳的檔案加入 $_SESSION 待處理檔案列表
            if ( ! isset($_SESSION['upload']['user_screenshot_id_arr']) ) {
                $_SESSION['upload']['user_screenshot_id_arr'] = array();
            }
            if ( isset($user_screenshot_id_arr) ) {
                $_SESSION['upload']['user_screenshot_id_arr'] = $_SESSION['upload']['user_screenshot_id_arr'] + $user_screenshot_id_arr;
            }

            // user 行為紀錄
            $this->_do_user_session_log();
            $this->_do_user_session_click_log();

            // 如果 $_SESSION 待處理檔案列表已空，轉回上傳畫面
            // 否則，從 user_screenshot 撈出待處理檔案，如果撈出沒有資料，也次轉入上傳畫面
            if ( empty($_SESSION['upload']['user_screenshot_id_arr']) ) {
                redirect('upload/add');
            } else {
                $tpl = array();
                $this->db->select();
                $this->db->from('user_screenshot');
                $this->db->where_in('id', $_SESSION['upload']['user_screenshot_id_arr']);
                $this->db->order_by('id', 'DESC');
                $q = $this->db->get();
                
                if ( $q->num_rows() > 0 ) {
                    $tpl['query'] = $q;
                    $this->load->view('upload_tpl', $tpl);
                } else {
                    redirect('upload/add');
                }
            }
            
        } catch (Exception $e) {
            
            exit('Opps, we have got something wrong!!');
        }
    }

    // 編輯單一screenshot畫面
    function edit()
    {
        try {
            $screenshot_id = trim($this->input->get('screenshot_id', TRUE));
            
            if ( ! isset($_SESSION['login']['user_id']) ) {
                redirect('upload');
            }
            $user_id = $_SESSION['login']['user_id'];
            
            $tpl = array();
            
            // 撈出screenshot和app的資訊
            $this->db->select(' app.id AS app_id, 
                                app.appPlatformId, 
                                app.appPlatform, 
                                app.appName,
                                app.developer,
                                app.category,
                                app.appIconUrl, 
                                ss.id, ss.url, ss.version');
            $this->db->from('screenshot AS ss');
            $this->db->join('app', 'ss.appId = app.id', 'left outer');
            $this->db->where('ss.id', $screenshot_id);
            $q = $this->db->get();
            if ( $q->num_rows() > 0 ) {
                $screenshot = $q->row();
            } else {
                redirect('home');
            }
            
            // 填入$active_user_screenshot
            $active_user_screenshot = $screenshot;
            $active_user_screenshot->app = array(
                'id' => $screenshot->app_id, 
                'type' => 'DB', 
                'appPlatformId' => $screenshot->appPlatformId, 
                'appPlatform' => $screenshot->appPlatform, 
                'appName' => $screenshot->appName,
                'developer' => $screenshot->developer,
                'category' => $screenshot->category,
                'appIconUrl' => $screenshot->appIconUrl,
            );
            
            // 撈出此user對此screenshot下過的tag
            $user_screenshot_tags = array();
            $q = $this->db->get_where('user_screenshot_tag', array('user_id'=>$user_id,'screenshot_id'=>$screenshot_id));
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $row ) {
                    $user_screenshot_tags[] = $row->tag;
                }
            }
            $active_user_screenshot->tags = implode(',', $user_screenshot_tags);

            // 撈出此user對此screenshot下過的comment
            $this->load->model('user_screenshot_comment_model');
            $row = $this->user_screenshot_comment_model->get_by(array('user_id'=>$user_id,'screenshot_id'=>$screenshot_id))->row();
            $active_user_screenshot->comment = empty($row) ? '' : $row->comment;
            
            //var_dump($active_user_screenshot); exit;
            
            // 填入$user_screenshot_arr
            $user_screenshot_arr = array();
            $user_screenshot_arr[$screenshot->id] = $active_user_screenshot;
            
            $user_screenshot_id_arr = array();
            $user_screenshot_id_arr[$screenshot->id] = $screenshot->id;
            
            if ( ! isset($_SESSION['upload']['user_screenshot_id_arr']) ) {
                $_SESSION['upload']['user_screenshot_id_arr'] = array();
            }
            if ( isset($user_screenshot_id_arr) ) {
                $_SESSION['upload']['user_screenshot_id_arr'] = $_SESSION['upload']['user_screenshot_id_arr'] + $user_screenshot_id_arr;
            }

            // user 行為紀錄
            $this->_do_user_session_log();
            $this->_do_user_session_click_log();

            $tpl['screenshot'] = $screenshot;
            $tpl['active_user_screenshot'] = $active_user_screenshot;
            $tpl['user_screenshot_arr'] = $user_screenshot_arr;
            $this->load->view('upload_edit_tpl', $tpl);

        } catch (Exception $e) {
            exit('Opps, we have got something wrong!!');
        }
    }

    // 搜尋App (以App Name搜尋)
    function search_app() 
    {
        // 搜尋的字詞
        $term = trim($this->input->get('q', TRUE));

        if ( isset($_SESSION['search_app'][$term]) ) {
            $source = $_SESSION['search_app'][$term];
        } else {
            $source = array();
            if ( $term != '' ) {
                // 先撈DB的
                $this->db->select(' id, 
                                    appPlatform, 
                                    appPlatformId,
                                    appName,
                                    category,
                                    developer,
                                    appIconUrl
                                    ');
                $this->db->from('app');
                $this->db->where("MATCH(appName) AGAINST('{$term}')", NULL, FALSE);
                $q = $this->db->get();
                if ( $q->num_rows() > 0 ) {
                    foreach ( $q->result() as $row ) {
                        $source["{$row->id}"] = array( 
                            'id' => $row->id, 
                            'type' => 'DB', 
                            'appPlatform' => $row->appPlatform, 
                            'appPlatformId' => (string)$row->appPlatformId,
                            'appName' => $row->appName,
                            'category' => $row->category,
                            'developer' => $row->developer,
                            'appIconUrl' => $row->appIconUrl, 
                            );
                    }
                }
                // 再找iTunes上的
                $search_api_url = sprintf($this->search_api_url, urlencode($term));
                $raw_data = file_get_contents($search_api_url);
                //echo($raw_data);
                
                if ( FALSE === $raw_data ) {
                } else {
                    $json = @json_decode(trim($raw_data));
                }
                //var_dump($json);
                
                if ( isset($json->results) && is_array($json->results) ) {
                    foreach ( $json->results as $app ) {
                        // iTunes吐回來的資料有時會空的，這裡要先檢查一下
                        if ( isset($app->trackId) ) {
                            // DB已經有的就不放入
                            if ( isset($source["{$app->trackId}"]) ) { 
                            } else {
                                // trackName有搜尋關鍵字的才放入
                                if ( strpos(strtolower($app->trackName), strtolower($term)) === FALSE ) { 
                                } else {
                                    $source["{$app->trackId}"] = array( 
                                        'id' => null, 
                                        'type' => 'iTunes', 
                                        'appPlatform' => 'Apple', 
                                        'appPlatformId' => (string)$app->trackId,
                                        'appName' => $app->trackName,
                                        'category' => $app->primaryGenreName,
                                        'developer' => $app->artistName,
                                        'appIconUrl' => $app->artworkUrl100, 
                                        );
                                }
                            }
                        }
                    }
                }
                //var_dump($source);
            }
        }
        if ( ! empty($source) ) {
            $_SESSION['search_app'][$term] = $source;
        }
        
        $tpl = array();
        $tpl['term'] = $term;
        $tpl['source'] = $source;
        $this->load->view('upload_app_search_tpl', $tpl);
    }
    
    function set_session_var()
    {
        try {
            $file_name = trim($this->input->post('file_name', TRUE));
            $origin_file_name = trim($this->input->post('origin_file_name', TRUE));
            
            if ( $file_name != '' && $origin_file_name != '' ) {
            } else {
                throw new Exception('系統錯誤: 缺少參數file_name, origin_file_name');
            }

            $_SESSION['upload']['file_name'] = $file_name;
            $_SESSION['upload']['origin_file_name'] = $origin_file_name;
            
            $return = array( 'error' => 0,
                             'msg' => '');
            echo json_encode($return);
            
        } catch (Exception $e) {
            
            $return = array( 'error' => 1,
                             'msg' => $e->getMessage());
            echo json_encode($return);
        }
    }
    
    function delete_null()
    {
        try {
            // 刪掉未處理的screenshot => screenshot_id是NULL的
            $upload_file = $this->config->item('upload_file');
            $q = $this->db->get_where('user_screenshot', array('screenshot_id'=>NULL));
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $row ) {
                    $row = $q->row();
                    // 刪除關連的screenshot
                    if ( !empty($row->screenshot_id) ) {
                        $this->db->delete('screenshot', array('id'=>$row->screenshot_id));
                    }
                    // 刪除實體檔案
                    $dir = substr($row->file_name, strrpos($row->file_name, '.')-3, 3);
                    $target_dir = "{$upload_file}/{$dir}";
                    $target_path = "{$target_dir}/{$row->file_name}";
                    @unlink($target_path);
                }
            }
            $this->db->delete('user_screenshot', array('screenshot_id'=>NULL));
                
            unset($_SESSION['upload']['user_screenshot_id_arr']);
            
            $return = array( 'error' => 0,
                             'msg' => '');
            echo json_encode($return);
            
        } catch (Exception $e) {
            
            $return = array( 'error' => 1,
                             'msg' => $e->getMessage());
            echo json_encode($return);
        }
    }
}

/* End of file upload.php */
/* Location: ./application/controllers/upload.php */