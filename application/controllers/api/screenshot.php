<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/../basic_controller.php';

class Screenshot extends Basic_Controller {

    private $search_api_url = 'http://itunes.apple.com/lookup?id=%d';

    public function __construct()
    {
        parent::__construct();
    }

    function edit_screenshot_save()
    {
        try {
            $screenshot_json = trim($this->input->post('screenshot_json', TRUE));
            //$screenshot_json = trim($this->input->post('screenshot_json', FALSE));
            
            $json = json_decode($screenshot_json);
            
            if ( ! isset($json->id) ) {
                throw new Exception('You must select a screenshot.');
            }
            $screenshot_id = $json->id;
            
            if ( isset($_SESSION['login']['user_id']) ) {
                $user_id = $_SESSION['login']['user_id'];
            } else {
                throw new Exception('error: no session user_id');
            }
            
            $app_id = null;
            // 判斷App是否已存在DB，若沒有就去Search API撈取App資料回來存入DB
            // 2014-05-02 由於DB已彙整入android，傳進來的 $json->app 有可能是 android 的
            // 其 appPlatformId (也就是這裡的trackId) 並不是數字，而且我們也沒有 
            // fetch android app 的程式，所以這裡要加判斷 $json->app->type 是 iTunes
            // 找來的才去抓取
            if ( isset($json->app->type) && $json->app->type == 'iTunes' ) {
                $this->load->model('raw_search_api_model', 'search_api');
                $r = $this->search_api->fetch_single_app($json->app->appPlatformId);
                if ( isset($r['app_id']) and $r['app_id'] !== false ) {
                    $app_id = $r['app_id'];
                } else {
                    throw new Exception('Fetch app error');
                }
            }
            if ( isset($json->app->type) && $json->app->type == 'DB' && isset($json->app->id) ) {
                $app_id = $json->app->id;
            }

            // 更新screenshot
            $data = array(  'appId'  => $app_id, 
                            'version' => isset($json->version) ? $json->version : NULL, 
                            );
            $this->db->update('screenshot', $data, array('id'=>$screenshot_id));
            
            // 寫入screenshot_tag，更新tag, screenshot_tag, user_tag等table
            if ( ! isset($json->tags) ) {
                throw new Exception('no set tags');
            }

            $tags_arr = explode(',', $json->tags);
            foreach ( $tags_arr as $tag ) {
                $tag = strtolower($tag);
                // 寫入user_screenshot_tag
                $q = $this->db->get_where('user_screenshot_tag', array('user_id' => $user_id, 'screenshot_id' => $screenshot_id, 'tag' => $tag));
                // 已經有的，就不insert新紀錄
                // 如果是新記錄的，才會再去更新tag, screenshot_tag, user_tag等table
                if ( $q->num_rows() > 0 ) {
                } else {
                    $data = array(  'user_id' => $user_id,
                                    'screenshot_id' => $screenshot_id,
                                    'tag' => $tag,
                                    'add_time' => date('Y-m-d H:i:s'), 
                                    );
                    $this->db->insert('user_screenshot_tag', $data);
                    // 寫入tag
                    $q = $this->db->get_where('tag', array( 'tag' => $tag ));
                    if ( $q->num_rows() > 0 ) {
                        $this->db->set('frequency', 'frequency+1', FALSE);
                        $this->db->where('tag', $tag);
                        $this->db->update('tag');
                    } else {
                        $this->db->insert('tag', array( 'tag' => $tag, 'frequency' => '1' ));
                    }
                    // 寫入screenshot_tag
                    $q = $this->db->get_where('screenshot_tag', array( 'screenshot_id' => $screenshot_id, 'tag' => $tag ));
                    if ( $q->num_rows() > 0 ) {
                        $this->db->set('frequency', 'frequency+1', FALSE);
                        $this->db->where(array( 'screenshot_id' => $screenshot_id, 'tag' => $tag ));
                        $this->db->update('screenshot_tag');
                    } else {
                        $this->db->insert('screenshot_tag', array( 'screenshot_id' => $screenshot_id, 'tag' => $tag, 'frequency' => '1' ));
                    }
                    // 寫入user_tag
                    $q = $this->db->get_where('user_tag', array( 'user_id' => $user_id, 'tag' => $tag ));
                    if ( $q->num_rows() > 0 ) {
                        $this->db->set('frequency', 'frequency+1', FALSE);
                        $this->db->where(array( 'user_id' => $user_id, 'tag' => $tag ));
                        $this->db->update('user_tag');
                    } else {
                        $this->db->insert('user_tag', array( 'user_id' => $user_id, 'tag' => $tag, 'frequency' => '1' ));
                    }
                }
            }
            
            // 更新 comment 
            $comment = isset($json->comment) ? trim($json->comment) : '';
            $this->load->model('user_screenshot_comment_model');
            if ( $comment == '' ) {
                $where = array( 'user_id' => $user_id, 
                                'screenshot_id' => $screenshot_id, 
                                );
                $r = $this->user_screenshot_comment_model->delete_by($where);
            } else {
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
                $word_arr = $this->word_process_model->make_word_process($comment);
                $this->user_screenshot_comment_model->save_screenshot_comment_word($screenshot_id, $word_arr);
            }
            
            $data = array(  'error' => 0,
                            'msg' => $screenshot_id,
                            );
            echo json_encode($data);
            
        } catch( Exception $e ) {
            
            $data = array(  'error' => 1,
                            'msg' => $e->getMessage(), 
                            );
            echo json_encode($data);
        }
    }

    function delete_user_screenshot()
    {
        try {
            $screenshot_id = trim($this->input->post('screenshot_id', TRUE));
            
            if ( $screenshot_id == '' ) {
                throw new Exception('error: no screenshot_id');
            }
            
            if ( isset($_SESSION['login']['user_id']) ) {
                $user_id = $_SESSION['login']['user_id'];
            } else {
                throw new Exception('error: no session user_id');
            }
            
            $screenshot = $this->db->get_where('user_screenshot', array('user_id'=>$user_id,'screenshot_id'=>$screenshot_id))->row();
            if ( empty($screenshot) ) {
                throw new Exception('error: no such screenshot');
            }
            
            //1. 刪除screenshot => 會連動刪除screenshot_tag, user_pin, user_screenshot, user_screenshot_tag
            $this->db->delete('screenshot', array('id'=>$screenshot_id));
            
            //2. 刪除實體檔案
            $upload_file = $this->config->item('upload_file');
            $dir = substr($screenshot->file_name, strrpos($screenshot->file_name, '.')-3, 3);
            $target_dir = "{$upload_file}/{$dir}";
            $target_path = "{$target_dir}/{$screenshot->file_name}";
            @unlink($target_path);
            
            $data = array(  'error' => 0, 
                            );
            echo json_encode($data);
            
        } catch( Exception $e ) {
            $data = array(  'error' => 1,
                            'msg' => $e->getMessage(), 
                            );
            echo json_encode($data);
        }
    }
    
    function remove_tags()
    {
        try {
            $screenshot_json = trim($this->input->post('screenshot_json', TRUE));
            
            $json = json_decode($screenshot_json);
            
            if ( ! isset($json->id) ) {
                throw new Exception('You must select a screenshot.');
            }
            $screenshot_id = $json->id;
            
            if ( isset($_SESSION['login']['user_id']) ) {
                $user_id = $_SESSION['login']['user_id'];
            }
            
            if ( ! isset($json->tags) ) {
                throw new Exception('no set tags');
            }
            
            $tags_arr = explode(',', $json->tags);
            foreach ( $tags_arr as $tag ) {
                $tag = strtolower($tag);
                
                // update frequency of tag
                $this->db->select('id, frequency');
                $this->db->from('tag');
                $this->db->where('tag', $tag);
                $q = $this->db->get();
                if ( $q->num_rows() > 0 ) {
                    $row = $q->row();
                    if ( $row->frequency > 1 ) {
                        $this->db->set('frequency', 'frequency-1', FALSE);
                        $this->db->where('id', $row->id);
                        $this->db->update('tag');
                    } else {
                        $this->db->where('id', $row->id);
                        $this->db->delete('tag');
                    }
                }
                
                // update frequency of screenshot_tag
                $this->db->select('id, frequency');
                $this->db->from('screenshot_tag');
                $this->db->where(array( 'screenshot_id' => $screenshot_id, 'tag' => $tag ));
                $q = $this->db->get();
                if ( $q->num_rows() > 0 ) {
                    $row = $q->row();
                    if ( $row->frequency > 1 ) {
                        $this->db->set('frequency', 'frequency-1', FALSE);
                        $this->db->where('id', $row->id);
                        $this->db->update('screenshot_tag');
                    } else {
                        $this->db->where('id', $row->id);
                        $this->db->delete('screenshot_tag');
                    }
                }
                
                if ( isset( $user_id ) ) {
                    // update frequency of user_tag
                    $this->db->select('id, frequency');
                    $this->db->from('user_tag');
                    $this->db->where(array( 'user_id' => $user_id, 'tag' => $tag ));
                    $q = $this->db->get();
                    if ( $q->num_rows() > 0 ) {
                        $row = $q->row();
                        if ( $row->frequency > 1 ) {
                            $this->db->set('frequency', 'frequency-1', FALSE);
                            $this->db->where('id', $row->id);
                            $this->db->update('user_tag');
                        } else {
                            $this->db->where('id', $row->id);
                            $this->db->delete('user_tag');
                        }
                    }
                    
                    // 刪除user_screenshot_tag
                    $this->db->where(array( 'user_id' => $user_id, 'screenshot_id' => $screenshot_id, 'tag' => $tag ));
                    $this->db->delete('user_screenshot_tag');
                }
            }
            
            $data = array(  'error' => 0,
                            'msg' => $json->id,
                            );
            echo json_encode($data);
            
        } catch( Exception $e ) {
            
            $data = array(  'error' => 1,
                            'msg' => $e->getMessage(), 
                            );
            echo json_encode($data);
        }
    }
    
    function add_tags()
    {
        try {
            $screenshot_json = trim($this->input->post('screenshot_json', TRUE));
            
            $json = json_decode($screenshot_json);
            
            if ( ! isset($json->id) ) {
                throw new Exception('You must select a screenshot.');
            }
            $screenshot_id = $json->id;
            
            if ( isset($_SESSION['login']['user_id']) ) {
                $user_id = $_SESSION['login']['user_id'];
            }
            
            if ( ! isset($json->tags) ) {
                throw new Exception('no set tags');
            }
            
            $tags_arr = explode(',', $json->tags);
            foreach ( $tags_arr as $tag ) {
                $tag = strtolower($tag);
                // 寫入tag
                $this->db->set('frequency', 'frequency+1', FALSE);
                $this->db->where('tag', $tag);
                $this->db->update('tag');
                if ( $this->db->affected_rows() == 0 ) {
                    $this->db->insert('tag', array( 'tag' => $tag, 'frequency' => '1' ));
                }
                
                // 寫入screenshot_tag
                $this->db->set('frequency', 'frequency+1', FALSE);
                $this->db->where(array( 'screenshot_id' => $screenshot_id, 'tag' => $tag ));
                $this->db->update('screenshot_tag');
                if ( $this->db->affected_rows() == 0 ) {
                    $this->db->insert('screenshot_tag', array( 'screenshot_id' => $screenshot_id, 'tag' => $tag, 'frequency' => '1' ));
                }
                
                if ( isset( $user_id ) ) {
                    // 寫入user_screenshot_tag
                    $this->db->set('add_time', date('Y-m-d H:i:s'));
                    $this->db->where(array( 'user_id' => $user_id, 'screenshot_id' => $screenshot_id, 'tag' => $tag ));
                    $this->db->update('user_screenshot_tag');
                    if ( $this->db->affected_rows() == 0 ) {
                        $data = array(  'user_id' => $user_id,
                                        'screenshot_id' => $screenshot_id,
                                        'tag' => $tag,
                                        'add_time' => date('Y-m-d H:i:s'), 
                                        );
                        $this->db->insert('user_screenshot_tag', $data);
                        
                        // 寫入user_tag
                        $this->db->set('frequency', 'frequency+1', FALSE);
                        $this->db->where(array( 'user_id' => $user_id, 'tag' => $tag ));
                        $this->db->update('user_tag');
                        if ( $this->db->affected_rows() == 0 ) {
                            $this->db->insert('user_tag', array( 'user_id' => $user_id, 'tag' => $tag, 'frequency' => '1' ));
                            $this->db->insert_id();
                        }
                    }
                }
            }
            
            $data = array(  'error' => 0,
                            'msg' => $json->id,
                            );
            echo json_encode($data);
            
        } catch( Exception $e ) {
            
            $data = array(  'error' => 1,
                            'msg' => $e->getMessage(), 
                            );
            echo json_encode($data);
        }
    }
}

/* End of file app.php */
/* Location: ./application/controllers/api/app.php */