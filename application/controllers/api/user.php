<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/../basic_controller.php';

class User extends Basic_Controller {

    private $search_api_url = 'http://itunes.apple.com/lookup?id=%d';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    function login()
    {
        try {
            $email = trim($this->input->post('email', TRUE));
            $password = trim($this->input->post('password', TRUE));
            
            if ( $email == '' ) {
                throw new Exception('Please input email.');
            }
            if ( $password == '' ) {
                throw new Exception('Please input password.');
            }
            
            $q = $this->user_model->get_by( array( 'email' => $email ) );
            if ( $q->num_rows() == 0 ) {
                throw new Exception('There is no such account.');
            }
            
            $user = $q->row();
            if ( $user->password != md5($password) ) {
                throw new Exception('Wrong password');
            }
            
            $data = array();
            if ( isset($_SESSION['login']['from_url']) && trim($_SESSION['login']['from_url']) != '' ) {
                $data['from_url'] = $_SESSION['login']['from_url'];
                unset($_SESSION['login']['from_url']);
            }
            
            session_regenerate_id(TRUE);
            $_SESSION['login']['user_id'] = $user->id;
            $_SESSION['login']['user_name'] = "{$user->first_name} {$user->last_name}";
            $_SESSION['login']['user_picture'] = ( $user->fb_picture != '' ) ? $user->fb_picture : ( $user->twitter_picture != '' ) ? $user->twitter_picture : '';
            $_SESSION['login']['login_time'] = date('Y-m-d H:i:s');

            $data['error'] = 0;
            $data['msg'] = '';
            echo json_encode($data);
                
        } catch (Exception $e) {
            
            $data = array( 'error' => 1,
                           'msg' => $e->getMessage() );
            echo json_encode($data);
        }
    }
    
    function fb_login()
    {
        try {
            $fb_user = array();
            $fb_user['fb_id'] = $this->input->post('fb_id', TRUE);
            $fb_user['fb_name'] = $this->input->post('fb_name', TRUE);
            $fb_user['fb_gender'] = $this->input->post('fb_gender', TRUE);
            $fb_user['fb_birthday'] = $this->input->post('fb_birthday', TRUE);
            $fb_user['fb_email'] = $this->input->post('fb_email', TRUE);
            $fb_user['fb_picture'] = $this->input->post('fb_picture', TRUE);
            
            if ( empty($fb_user) || $fb_user['fb_id'] == '' ) {
                throw new Exception('缺少必要參數: fb_id');
            }
            
            $query = $this->user_model->get_by(array('fb_id' => $fb_user['fb_id']));
            if ( $query->num_rows() > 0 ) {
                $user = $query->row();
                $fb_user['last_login_time'] = date('Y-m-d H:i:s');
                $this->user_model->update_by($fb_user, array('fb_id' => $fb_user['fb_id']));
                $user_id = $user->id;
            } else {
                $fb_user['add_time'] = date('Y-m-d H:i:s');
                $fb_user['last_login_time'] = $fb_user['add_time'];
                $user_id = $this->user_model->add($fb_user);
            }
            
            $data = array();
            if ( isset($_SESSION['login']['from_url']) && trim($_SESSION['login']['from_url']) != '' ) {
                $data['from_url'] = $_SESSION['login']['from_url'];
                unset($_SESSION['login']['from_url']);
            }
            
            session_regenerate_id(TRUE);
            $_SESSION['login']['user_id'] = $user_id;
            $_SESSION['login']['user_name'] = $fb_user['fb_name'];
            $_SESSION['login']['user_picture'] = $fb_user['fb_picture'];
            $_SESSION['login']['login_time'] = date('Y-m-d H:i:s');
            
            $data['error'] = 0;
            $data['msg'] = '';
            echo json_encode($data);
            
        } catch (Exception $e) {
            
            $data = array( 'error' => 1,
                           'msg' => $e->getMessage() );
            echo json_encode($data);
        }
    }
    
    function make_login() 
    {
        $user = $this->input->post('user', TRUE);
        echo json_encode($user);
    }
    
    function signup()
    {
        try {
            $first_name = trim($this->input->post('first_name', TRUE));
            $last_name = trim($this->input->post('last_name', TRUE));
            $email = trim($this->input->post('email', TRUE));
            $password = trim($this->input->post('password', TRUE));
            $confirm_password = trim($this->input->post('confirm_password', TRUE));
            
            if ( $email == '' ) {
                throw new Exception('Please input email.');
            }
            if ( $password == '' ) {
                throw new Exception('Please input password.');
            }
            if ( $confirm_password == '' ) {
                throw new Exception('Please input confirm password.');
            }
            if ( $confirm_password != $confirm_password ) {
                throw new Exception('Confirm password is not same with password.');
            }
            
            $q = $this->user_model->get_by( array( 'email' => $email ) );
            if ( $q->num_rows() > 0 ) {
                throw new Exception('Email is already taken.');
            }
            
            $data = array(  'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'password' => md5($password),
                            'add_time' => date('Y-m-d H:i:s'),
                            'last_login_time' => date('Y-m-d H:i:s'), 
                            );
            $user_id = $this->user_model->add($data);
            if ( ! empty($user_id) ) {
                session_regenerate_id(TRUE);
                $_SESSION['login']['user_id'] = $user_id;
                $_SESSION['login']['user_name'] = "{$first_name} {$last_name}";
                $_SESSION['login']['user_picture'] = '';
                $_SESSION['login']['login_time'] = date('Y-m-d H:i:s');
            } else {
                throw new Exception('Sign-up error');
            }
                
            $data = array( 'error' => 0,
                           'msg' => '',
                           );
            echo json_encode($data);
            
        } catch (Exception $e) {
            
            $data = array( 'error' => 1,
                           'msg' => $e->getMessage(),
                           );
            echo json_encode($data);
        }
    }
    
    function delete_screenshot()
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
            
            $upload_file = $this->config->item('upload_file');
            $q = $this->db->get_where('user_screenshot', array( 'id' => $screenshot_id ));
            if ( $q->num_rows() == 1 ) {
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

            $this->db->delete('user_screenshot', array( 'id' => $screenshot_id, 'user_id' => $user_id ));

            if ( isset($_SESSION['upload']['user_screenshot_id_arr']) ) {
                foreach ( $_SESSION['upload']['user_screenshot_id_arr'] as &$id ) {
                    if ( $screenshot_id == $id ) {
                        unset($id);
                    }
                }
            }

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
    
    function add_screenshot_tag()
    {
        try {
            $screenshot_json = trim($this->input->post('screenshot_json', TRUE));
            //$screenshot_json = trim($this->input->post('screenshot_json', FALSE));
            
            $json = json_decode($screenshot_json);
            
            if ( ! isset($json->id) || ! isset($json->file_name) ) {
                throw new Exception('You must select a screenshot.');
            }
            
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
            
            // 寫入screenshot
            $upload_file = $this->config->item('upload_file');  // 實際應該存放的路徑
            $dir = substr($json->file_name, strrpos($json->file_name,'.')-3,3);
            $url = base_url("{$upload_file}/{$dir}/{$json->file_name}");

            $data = array(  'appId'  => $app_id, 
                            'device' => null, 
                            'url' => $url, 
                            'version' => isset($json->version) ? $json->version : NULL, 
                            );
            $this->db->insert('screenshot', $data);
            $screenshot_id = $this->db->insert_id();

            // 更新user_screenshot
            $this->db->update('user_screenshot', array( 'screenshot_id' => $screenshot_id ), array( 'id' => $json->id ));
            
            // 寫入screenshot_tag和user_screenshot_tag
            if ( ! isset($json->tags) ) {
                throw new Exception('no set tags');
            }
            
            $tags_arr = explode(',', $json->tags);
            foreach ( $tags_arr as $tag ) {
                $tag = strtolower(trim($tag));
                // 寫入tag
                $q = $this->db->get_where('tag', array( 'tag' => $tag ));
                if ( $q->num_rows() > 0 ) {
                    $this->db->set('frequency', 'frequency+1', FALSE);
                    $this->db->where('tag', $tag);
                    $this->db->update('tag');
                } else {
                    $this->db->insert('tag', array( 'tag' => $tag, 'frequency' => '1' ));
                    $this->db->insert_id();
                }
                // 寫入screenshot_tag
                $q = $this->db->get_where('screenshot_tag', array( 'screenshot_id' => $screenshot_id, 'tag' => $tag ));
                if ( $q->num_rows() > 0 ) {
                    $this->db->set('frequency', 'frequency+1', FALSE);
                    $this->db->where(array( 'screenshot_id' => $screenshot_id, 'tag' => $tag ));
                    $this->db->update('screenshot_tag');
                } else {
                    $this->db->insert('screenshot_tag', array( 'screenshot_id' => $screenshot_id, 'tag' => $tag, 'frequency' => '1' ));
                    $this->db->insert_id();
                }
                // 寫入user_tag
                $q = $this->db->get_where('user_tag', array( 'user_id' => $user_id, 'tag' => $tag ));
                if ( $q->num_rows() > 0 ) {
                    $this->db->set('frequency', 'frequency+1', FALSE);
                    $this->db->where(array( 'user_id' => $user_id, 'tag' => $tag ));
                    $this->db->update('user_tag');
                } else {
                    $this->db->insert('user_tag', array( 'user_id' => $user_id, 'tag' => $tag, 'frequency' => '1' ));
                    $this->db->insert_id();
                }
                // 寫入user_screenshot_tag
                $data = array(  'user_id' => $user_id,
                                'screenshot_id' => $screenshot_id,
                                'tag' => $tag,
                                'add_time' => date('Y-m-d H:i:s'), 
                                );
                $this->db->insert('user_screenshot_tag', $data);
            }
            
            // 寫入 comment 
            $comment = isset($json->comment) ? trim($json->comment) : '';
            if ( $comment != '' ) {
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
                $word_arr = $this->word_process_model->make_word_process($comment);
                $this->user_screenshot_comment_model->save_screenshot_comment_word($screenshot_id, $word_arr);
            }
            
            unset($_SESSION['upload']['user_screenshot_id_arr'][$json->id]);

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
    
    function add_pin()
    {
        try {
            $screenshot_id = (int)$this->input->post('ss_id', TRUE);
            
            if ( $screenshot_id == 0 ) {
                throw new Exception('error: invalid parameter: ss_id');
            }
            
            if ( isset($_SESSION['login']['user_id']) ) {
                $user_id = $_SESSION['login']['user_id'];
            } else {
                throw new Exception('error: no session user_id');
            }
            
            $data = array(  'user_id' => $user_id,
                            'screenshot_id' => $screenshot_id,
                            'update_time' => date('Y-m-d H:i:s'), 
                            );
            $this->db->update('user_pin', $data, array('user_id'=>$user_id,'screenshot_id'=>$screenshot_id));
            if ( $this->db->affected_rows() == 0 ) {
                $data['add_time'] = $data['update_time'];
                $this->db->insert('user_pin', $data);
            }
            
            $this->db->set('pinCount', 'pinCount+1', FALSE);
            $this->db->where('id', $screenshot_id);
            $this->db->update('screenshot');
            
            $data = array(  'error' => 0,
                            'msg' => null, 
                            );
            echo json_encode($data);
            
        } catch( Exception $e ) {
            
            $data = array(  'error' => 1,
                            'msg' => $e->getMessage(), 
                            );
            echo json_encode($data);
        }
    }
    
    function remove_pin()
    {
        try {
            $screenshot_id = (int)$this->input->post('ss_id', TRUE);
            
            if ( $screenshot_id == 0 ) {
                throw new Exception('error: invalid parameter: ss_id');
            }
            
            if ( isset($_SESSION['login']['user_id']) ) {
                $user_id = $_SESSION['login']['user_id'];
            } else {
                throw new Exception('error: no session user_id');
            }
            
            $data = array(  'user_id' => $user_id,
                            'screenshot_id' => $screenshot_id,
                            );
            $this->db->delete('user_pin', $data);
            
            $this->db->set('pinCount', 'pinCount-1', FALSE);
            $this->db->where('id', $screenshot_id);
            $this->db->update('screenshot');
            
            $data = array(  'error' => 0,
                            'msg' => null, 
                            );
            echo json_encode($data);
            
        } catch( Exception $e ) {
            
            $data = array(  'error' => 1,
                            'msg' => $e->getMessage(), 
                            );
            echo json_encode($data);
        }
    }

    function add_like() 
    {
        try {
            $screenshot_id = (int)$this->input->post('ss_id', TRUE);
            
            if ( $screenshot_id == 0 ) {
                throw new Exception('error: invalid parameter: ss_id');
            }
            
            if ( isset($_SESSION['login']['user_id']) ) {
                $user_id = $_SESSION['login']['user_id'];
            } else {
                throw new Exception('error: no session user_id');
            }
            
            $data = array(  'user_id' => $user_id,
                            'screenshot_id' => $screenshot_id,
                            'add_time' => date('Y-m-d H:i:s'), 
                            );
            $this->db->update('user_screenshot_like', $data, array('user_id'=>$user_id,'screenshot_id'=>$screenshot_id));
            if ( $this->db->affected_rows() == 0 ) {
                $this->db->insert('user_screenshot_like', $data);
            }
            
            $this->db->set('likeCount', 'likeCount+1', FALSE);
            $this->db->where('id', $screenshot_id);
            $this->db->update('screenshot');
            
            $data = array(  'error' => 0,
                            'msg' => null, 
                            );
            echo json_encode($data);
            
        } catch( Exception $e ) {
            
            $data = array(  'error' => 1,
                            'msg' => $e->getMessage(), 
                            );
            echo json_encode($data);
        }
    }

    function remove_like() 
    {
        try {
            $screenshot_id = (int)$this->input->post('ss_id', TRUE);
            
            if ( $screenshot_id == 0 ) {
                throw new Exception('error: invalid parameter: ss_id');
            }
            
            if ( isset($_SESSION['login']['user_id']) ) {
                $user_id = $_SESSION['login']['user_id'];
            } else {
                throw new Exception('error: no session user_id');
            }
            
            $data = array(  'user_id' => $user_id,
                            'screenshot_id' => $screenshot_id,
                            );
            $this->db->delete('user_screenshot_like', $data);
            
            $this->db->set('likeCount', 'likeCount-1', FALSE);
            $this->db->where('id', $screenshot_id);
            $this->db->update('screenshot');
            
            $data = array(  'error' => 0,
                            'msg' => null, 
                            );
            echo json_encode($data);
            
        } catch( Exception $e ) {
            
            $data = array(  'error' => 1,
                            'msg' => $e->getMessage(), 
                            );
            echo json_encode($data);
        }
    }

    function add_dislike() 
    {
        try {
            $screenshot_id = (int)$this->input->post('ss_id', TRUE);
            
            if ( $screenshot_id == 0 ) {
                throw new Exception('error: invalid parameter: ss_id');
            }
            
            if ( isset($_SESSION['login']['user_id']) ) {
                $user_id = $_SESSION['login']['user_id'];
            } else {
                throw new Exception('error: no session user_id');
            }
            
            $data = array(  'user_id' => $user_id,
                            'screenshot_id' => $screenshot_id,
                            'add_time' => date('Y-m-d H:i:s'), 
                            );
            $this->db->update('user_screenshot_dislike', $data, array('user_id'=>$user_id,'screenshot_id'=>$screenshot_id));
            if ( $this->db->affected_rows() == 0 ) {
                $this->db->insert('user_screenshot_dislike', $data);
            }
            
            $this->db->set('dislikeCount', 'dislikeCount+1', FALSE);
            $this->db->where('id', $screenshot_id);
            $this->db->update('screenshot');
            
            $data = array(  'error' => 0,
                            'msg' => null, 
                            );
            echo json_encode($data);
            
        } catch( Exception $e ) {
            
            $data = array(  'error' => 1,
                            'msg' => $e->getMessage(), 
                            );
            echo json_encode($data);
        }
    }

    function remove_dislike() 
    {
        try {
            $screenshot_id = (int)$this->input->post('ss_id', TRUE);
            
            if ( $screenshot_id == 0 ) {
                throw new Exception('error: invalid parameter: ss_id');
            }
            
            if ( isset($_SESSION['login']['user_id']) ) {
                $user_id = $_SESSION['login']['user_id'];
            } else {
                throw new Exception('error: no session user_id');
            }
            
            $data = array(  'user_id' => $user_id,
                            'screenshot_id' => $screenshot_id,
                            );
            $this->db->delete('user_screenshot_dislike', $data);
            
            $this->db->set('dislikeCount', 'dislikeCount-1', FALSE);
            $this->db->where('id', $screenshot_id);
            $this->db->update('screenshot');
            
            $data = array(  'error' => 0,
                            'msg' => null, 
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

/* End of file user.php */
/* Location: ./application/controllers/api/user.php */