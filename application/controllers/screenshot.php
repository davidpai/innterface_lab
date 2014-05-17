<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/basic_controller.php';

class Screenshot extends Basic_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    function index() 
    {
        $id = (int)trim($this->input->get('id', TRUE));
        $keyword = trim($this->input->get('keyword', TRUE));
        
        // clickCount + 1
        $this->db->set('clickCount','clickCount+1', FALSE);
        $this->db->where('id', $id);
        $this->db->update('screenshot');
        
        $tpl = array();
        $tpl['is_login'] = false;
        $tpl['id'] = $id;
        $tpl['keyword'] = $keyword;
        
        if ( $this->_is_login() ) {
            $tpl['is_login'] = true;
        } else {
            $return_url = site_url('search').'?q='.urlencode($keyword);
            $tpl['return_url'] = $return_url;
            /*
            if ( isset($_SESSION['layer3']) ) {
                $_SESSION['layer3']['trigger'] = true;
                $_SESSION['layer3']['id'] = $id;
                $_SESSION['layer3']['url'] = site_url('screenshot')."?id=".urlencode($id)."&keyword=".urlencode($keyword);
                $_SESSION['login']['from_url'] = site_url('search')."?q=".urlencode($_SESSION['layer3']['q'])."&m=".urlencode($_SESSION['layer3']['m'])."&p=".urlencode($_SESSION['layer3']['p']);
            } else {
                $_SESSION['login']['from_url'] = site_url('screenshot')."?id=".urlencode($id)."&keyword=".urlencode($keyword);
            }
            */
        }

        // 撈出screenshot和app的資料
        $this->db->select(' app.id AS app_id,
                            app.appIconUrl,
                            app.appName,
                            app.appPlatform, 
                            app.appViewUrl, 
                            app.developer,
                            app.category,
                            app.version AS app_version, 
                            app.averageUserRating,
                            app.userRatingCount, 
                            ss.url,
                            ss.id, 
                            ss.version AS ss_version, 
                            ss.clickCount,
                            ss.pinCount, 
                            ss.likeCount, 
                            ss.dislikeCount
                            ');
        $this->db->from('screenshot AS ss');
        $this->db->join('app', 'ss.appId = app.id', 'left outer');
        $this->db->where('ss.id', $id);
        $screenshot = $this->db->get()->row();

        if ( isset($_SESSION['login']['user_id']) ) {
            $user_id = $_SESSION['login']['user_id'];
            $tpl['user_id'] = $user_id;
        }
        
        // 撈出這個screenshot的所有tag
        $this->load->model('screenshot_tag_model');
        $tag_arr = array();
        $screenshot_tag_arr = $this->screenshot_tag_model->get_by_screenshot($id);
        foreach ( $screenshot_tag_arr as $screenshot_tag ) {
            $tag_arr[] = $screenshot_tag->tag;
        }
        $screenshot->tag_list = implode(',',$tag_arr);
        $tpl['tags'] = $tag_arr;
        
        // 這個user是否 like 這張screenshot
        $screenshot->like = false;
        if ( isset($user_id) ) {
            $q = $this->db->get_where('user_screenshot_like', array('user_id'=>$user_id, 'screenshot_id'=>$id));
            if ( $q->num_rows() > 0 ) {
                $screenshot->like = true;
            }
        }
        
        // 這個user是否 dislike 這張screenshot
        $screenshot->dislike = false;
        if ( isset($user_id) ) {
            $q = $this->db->get_where('user_screenshot_dislike', array('user_id'=>$user_id, 'screenshot_id'=>$id));
            if ( $q->num_rows() > 0 ) {
                $screenshot->dislike = true;
            }
        }

        // 這個user是否pin過這張screenshot
        $screenshot->pin = false;
        if ( isset($user_id) ) {
            $q = $this->db->get_where('user_pin', array('user_id'=>$user_id, 'screenshot_id'=>$id));
            if ( $q->num_rows() > 0 ) {
                $screenshot->pin = true;
            }
        }
        $tpl['screenshot'] = $screenshot;
        
        // 這個user對這張screenshot下的tag
        $tpl['user_screenshot_tag_list'] = "";
        if ( isset($user_id) ) {
            $user_screenshot_tag_arr = array();

            $this->db->select('tag');
            $this->db->from('user_screenshot_tag');
            $this->db->where(array('user_id'=>$user_id, 'screenshot_id'=>$id));
            $this->db->group_by('tag');
            $q = $this->db->get();
            foreach ( $q->result() as $row ) {
                $user_screenshot_tag_arr[] = $row->tag;
            }
            $tpl['user_screenshot_tag_list'] = implode(',',$user_screenshot_tag_arr);
        }
        
        // 這個user對這張screenshot下的comment
        if ( isset($user_id) ) {
            $this->load->model('user_screenshot_comment_model');
            $tpl['user_screenshot_comment'] = null;
            $tpl['user_screenshot_comment'] = $this->user_screenshot_comment_model->get_by(array('user_id'=>$user_id, 'screenshot_id'=>$id))->row();
        }
        
        
        // Relate Screenshot 什麼樣叫相關? 相關的程度如何計算?
        // 1. 同一個App?
        // 2. 相同Tag的其他screenshot
        // 3. App類別相同?
        
        // 2. 相同Tag的其他screenshot
        // 定義相關截圖的容器
        $tpl['relate_screenshot_arr'] = array();

        //撈出有這些tag的screenshot，並依frequency從高->低排列
        if ( count($tag_arr) > 0 ) {
            $this->load->model('screenshot_tag_model');
            $this->db->select('app.appName, ss.id, ss.url, st.tag');
            $this->db->from('screenshot_tag AS st');
            $this->db->join('screenshot AS ss', 'st.screenshot_id = ss.id');
            $this->db->join('app', 'ss.appId = app.id', 'left outer');
            $this->db->where('st.screenshot_id !=', $id);
            $this->db->where_in('st.tag', $tag_arr);
            $this->db->order_by('st.frequency', 'desc');
            $query = $this->db->get();
            foreach ( $query->result() as $row ) {
                $screenshot_tag_db_arr = $this->screenshot_tag_model->get_by_screenshot($row->id);
                $screenshot_tag_arr = array();
                foreach ( $screenshot_tag_db_arr as $screenshot_tag ) {
                    $screenshot_tag_arr[] = $screenshot_tag->tag;
                }
                $row->tag_list = implode(',',$screenshot_tag_arr);
                $tpl['relate_screenshot_arr'][$row->id] = $row;
            }
        }

        // user 行為紀錄
        $this->_do_user_session_log();
        $this->_do_user_session_click_log($id);
        
        $this->load->view('screenshot_new_tpl', $tpl);
    }

    public function ajaxPostComment() 
    {
        try {
            $comment = trim($this->input->post('comment', TRUE));
            $screenshot_id = (int)$this->input->post('screenshot_id', TRUE);
            
            if ( isset($_SESSION['login']['user_id']) ) {
                $user_id = $_SESSION['login']['user_id'];
            } else {
                throw new Exception('Please login first.');
            }
            
            $this->load->model('user_screenshot_comment_model');
            $data = array(  'user_id' => $user_id, 
                            'screenshot_id' => $screenshot_id, 
                            'comment' => $comment, 
                            'add_time' => date('Y-m-d H:i:s'), 
                            );
            $where = array( 'user_id' => $user_id, 
                            'screenshot_id' => $screenshot_id, 
                            );
            $r = $this->user_screenshot_comment_model->update_by($data, $where);
            if ( $r > 0 ) {
            } else {
                $r = $this->user_screenshot_comment_model->add($data);
            }
            
            // 拆解comment為字詞，並計算出現頻率，寫入screenshot_comment_word table
            // **這裡我們並未防止作弊情形，也就是: 
            // 如果有人在comment裡面重複寫同一個字，或同樣的comment內容重複一直save，會使那個字的frequency飆高，影響到weight
            // 如何防止惡意字詞堆疊，這樣再想想
            $this->load->model('word_process_model');
            $this->load->model('user_screenshot_comment_model');
            $word_arr = $this->word_process_model->make_word_process($comment);
            $this->user_screenshot_comment_model->save_screenshot_comment_word($screenshot_id, $word_arr);
            
            $result = array('error' => 0, 
                            'msg' => $r, 
                            );
            echo json_encode($result);
            
        } catch (Exception $e) {
        
            $result = array('error' => 1, 
                            'msg' => $e->getMessage(), 
                            );
            echo json_encode($result);
        }
    }
}

/* End of file screenshot.php */
/* Location: ./application/controllers/screenshot.php */