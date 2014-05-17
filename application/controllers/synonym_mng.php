<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/basic_controller.php';

class Synonym_mng extends Basic_Controller {

    private $sort_name_arr = array('id','word','language','profession','display_name','display_status','modify_time','synonym_count');
    private $sort_order_arr = array('asc','desc');

    public function __construct()
    {
        parent::__construct();
    }

    function index() 
    {
        if ( isset($_SESSION['synonym_mng']) ) {
            $this->word_list();
        } else {
            $pass = trim($this->input->post('pass', TRUE));
            
            if ( $pass != '' && $pass === $this->config->item('synonym_mng_pass') ) {
                session_regenerate_id(true);
                $_SESSION['synonym_mng'] = true;
                redirect('synonym_mng/word_list');
                exit;
            }
            $this->load->view('synonym_mng/login_tpl');
        }
    }
  
    function ajaxDeleteSynonym() 
    {
        if ( !isset($_SESSION['synonym_mng']) ) {
            unset($_SESSION['synonym_mng']);
            redirect('synonym_mng');
            exit;
        }
        
        try {
            $word_id = (int)$this->input->post('word_id', true);
            $synonym_word_id = (int)$this->input->post('synonym_word_id', true);
            
            $this->db->delete('synonym_relation', array('word_id'=>$word_id, 'synonym_word_id'=>$synonym_word_id));
            
            $result = array('error' => 0, 
                            'msg' => '', 
                            );
            echo json_encode($result);
            
        } catch (Exception $e) {
            $result = array('error' => 1, 
                            'msg' => $e->getMessage(), 
                            );
            echo json_encode($result);
        }
    }
    
    function add_synonym() 
    {
        if ( !isset($_SESSION['synonym_mng']) ) {
            unset($_SESSION['synonym_mng']);
            redirect('synonym_mng');
            exit;
        }
        
        try {
            $word_id = (int)$this->input->post('word_id', true); // 字的id
            $synonym_word = strtolower(trim($this->input->post('synonym_word', true))); // 欲加入的同義字
            $language = trim($this->input->post('language', true));
            $profession = trim($this->input->post('profession', true));
            $display_name = trim($this->input->post('display_name', true));
            $display_status = trim($this->input->post('display_status', true));
            
            //  檢查該字是否不存在
            $q = $this->db->get_where('synonym_word', array('id'=>$word_id));
            if ( $q->num_rows() == 0 ) {
                throw new Exception('This word does not exist.');
            }

            // 同義字也加入word table，但如果已經有了，就不加入
            // 先檢查是否已有了，若已有就直接取id，若沒有則先insert再取id
            $where = array( 'word' => $synonym_word, 
                            'language' => $language, 
                            'profession' => $profession, 
                            );
            $synonym = $this->db->get_where('synonym_word', $where)->row();
            if ( empty($synonym) ) {
                $data = array(  'word' => $synonym_word, 
                                'language' => $language, 
                                'profession' => $profession, 
                                'display_name' => $display_name, 
                                'display_status' => $display_status, 
                                'add_time' => date('Y-m-d H:i:s'), 
                                'modify_time' => date('Y-m-d H:i:s'), 
                                );
                $this->db->insert('synonym_word', $data);
                $synonym_word_id = $this->db->insert_id();
            } else {
                $synonym_word_id = $synonym->id;
            }
            
            $where = $data = array( 'word_id' => $word_id, 
                                    'synonym_word_id' => $synonym_word_id, 
                                    );
            $q = $this->db->get_where('synonym_relation', $where);
            if ( $q->num_rows() > 0 ) {
                throw new Exception('This synonym is already exit.');
            }
            if ( $synonym_word_id != $word_id ) {
                $this->db->insert('synonym_relation', $data);
            } else {
                throw new Exception('This word is already exit.');
            }
            
            $result = array( 'error' => 0, 
                             'msg' => '', 
                             );
            echo json_encode($result);
            
        } catch(Exception $e) {
        
            $result = array( 'error' => 1, 
                             'msg' => $e->getMessage(), 
                             );
            echo json_encode($result);
        }
    }
    
    function synonym_list_split() 
    {
        if ( !isset($_SESSION['synonym_mng']) ) {
            unset($_SESSION['synonym_mng']);
            redirect('synonym_mng');
            exit;
        }
        
        try {
            $word_id = (int)$this->input->get('word_id', true);
            
            $tpl = array();
            
            $word = $this->db->get_where('synonym_word', array('id'=>$word_id))->row();
            if ( empty($word) ) {
                throw new Exception('This word is not exists');
            }
            $tpl['word'] = $word;

            $this->load->view('synonym_mng/synonym_list_split_tpl.php', $tpl);
            
        } catch (Exception $e) {
            $tpl = array( 'message' => $e->getMessage() );
            $this->load->view('error_tpl.php', $tpl);
        }
    }
    
    function synonym_list() 
    {
        if ( !isset($_SESSION['synonym_mng']) ) {
            unset($_SESSION['synonym_mng']);
            redirect('synonym_mng');
            exit;
        }
        
        try {
            $word_id = (int)$this->input->get('word_id', true);
            
            $tpl = array();
            
            $word = $this->db->get_where('synonym_word', array('id'=>$word_id))->row();
            if ( empty($word) ) {
                throw new Exception('This word is not exists');
            }
            $tpl['word'] = $word;
            
            //$tpl['q'] = $this->db->get_where('synonym_synonym', array( 'word_id' => $word_id ));
            $this->db->select('sw.*');
            $this->db->from('synonym_relation AS sr');
            $this->db->join('synonym_word AS sw', 'sr.synonym_word_id = sw.id');
            $this->db->where('sr.word_id', $word_id);
            $tpl['q'] = $this->db->get();
            
            $this->load->model('synonym_word_model');
            $language_list = $this->synonym_word_model->getLanguageList();
            $profession_list = $this->synonym_word_model->getProfessionList();
            
            $tpl['language_list'] = $language_list;
            $tpl['profession_list'] = $profession_list;
            
            $this->load->view('synonym_mng/synonym_list_tpl.php', $tpl);
            
        } catch(Exception $e) {
            $tpl = array( 'message' => $e->getMessage() );
            $this->load->view('error_tpl.php', $tpl);
        }
    }
    
    function add_word() 
    {
        if ( !isset($_SESSION['synonym_mng']) ) {
            unset($_SESSION['synonym_mng']);
            redirect('synonym_mng');
            exit;
        }
        
        if ( isset($_POST) && count($_POST) > 0 ) {
            try {
                $word = trim($this->input->post('word', true));
                $language = trim($this->input->post('language', true));
                $profession = trim($this->input->post('profession', true));
                $display_name = trim($this->input->post('display_name', true));
                $display_status = trim($this->input->post('display_status', true));
                
                $where = array( 'word' => $word, 
                                'language' => $language, 
                                'profession' => $profession, 
                                );
                $q = $this->db->get_where('synonym_word', $where);
                if ( $q->num_rows() > 0 ) {
                    throw new Exception('This word already exists');
                }
                
                // modify language string to "zh-TW" format
                if ( preg_match('/^[a-zA-Z]{2}-[a-zA-Z]{2}$/i', $language) === 1 ) {
                    $prefix = substr($language, 0, 2);
                    $suffix = substr($language, -2);
                    $language = strtolower($prefix).'-'.strtoupper($suffix);
                }
                
                $data = array(  'word' => $word, 
                                'language' => $language, 
                                'profession' => $profession,
                                'display_name' => $display_name, 
                                'display_status' => $display_status, 
                                'add_time' => date('Y-m-d H:i:s'), 
                                'modify_time' => date('Y-m-d H:i:s'), 
                                );
                $this->db->insert('synonym_word', $data);
                
                $result = array( 'error' => 0, 
                                 'msg' => '', 
                                 );
                echo json_encode($result);
                
            } catch (Exception $e) {
                
                $result = array( 'error' => 1, 
                                 'msg' => $e->getMessage(), 
                                 );
                echo json_encode($result);
            }
        } else {
        
            $this->load->model('synonym_word_model');
            $language_list = $this->synonym_word_model->getLanguageList();
            $profession_list = $this->synonym_word_model->getProfessionList();
            
            $tpl = array();
            $tpl['language_list'] = $language_list;
            $tpl['profession_list'] = $profession_list;
            $this->load->view('synonym_mng/add_word_tpl.php', $tpl);
        }
    }

    function word_list() 
    {
        if ( !isset($_SESSION['synonym_mng']) ) {
            unset($_SESSION['synonym_mng']);
            redirect('synonym_mng');
            exit;
        }
        /*
        if ( isset($_POST) && count($_POST) > 0 ) {
            $word = $this->input->post('word', TRUE);
            $language = $this->input->post('language', TRUE);
            $profession = $this->input->post('profession', TRUE);
            $display_name = $this->input->post('display_name', TRUE);
            $display_status = $this->input->post('display_status', TRUE);
            $submit = $this->input->post('submit', TRUE);
            if ( $submit === 'Add' ) {
                $data = array(  'word' => $word, 
                                'language' => $language, 
                                'profession' => $profession, 
                                'display_name' => $display_name, 
                                'display_status' => $display_status, 
                                );
                $this->db->insert('synonym_word', $data);
            }
            redirect('synonym_mng/word_list');
        }
        */
        
        $tpl = array();
        
        $id = trim($this->input->get('id', TRUE));
        $word = trim($this->input->get('word', TRUE));
        $language = trim($this->input->get('language', TRUE));
        $profession = trim($this->input->get('profession', TRUE));
        $display_name = trim($this->input->get('display_name', TRUE));
        $display_status = trim($this->input->get('display_status', TRUE));
        $sort_name = trim($this->input->get('sort_name', TRUE));
        $sort_order = trim($this->input->get('sort_order', TRUE));
        
        $default_sort_name = 'word';
        $default_sort_order = 'asc';
        $sort_name = (in_array($sort_name, $this->sort_name_arr)) ? $sort_name : $default_sort_name;
        $sort_order = (in_array($sort_order, $this->sort_order_arr)) ? $sort_order : $default_sort_order;
        
        $tpl['id'] = $id;
        $tpl['word'] = $word;
        $tpl['language'] = $language;
        $tpl['profession'] = $profession;
        $tpl['display_name'] = $display_name;
        $tpl['display_status'] = $display_status;
        $tpl['sort_name'] = $sort_name;
        $tpl['sort_order'] = $sort_order;
        $tpl['default_sort_order'] = $default_sort_order;
        $tpl['active_sort_order'] = ($sort_order=='asc') ? 'desc' : 'asc';
        
        $this->db->select(' sw.id, 
                            sw.word, 
                            sw.language, 
                            sw.display_name, 
                            sw.display_status, 
                            sw.profession, 
                            sw.modify_time, 
                            count(sr.word_id) AS synonym_count
                            ');
        $this->db->from('synonym_word AS sw');
        $this->db->join('synonym_relation AS sr', 'sw.id = sr.word_id', 'left outer');
        if ( $id != '' ) {
            $this->db->where('sw.id', $id);
        }
        if ( $word != '' ) {
            $this->db->like('sw.word', $word);
        }
        if ( $language != '' ) {
            $this->db->where('sw.language', $language);
        }
        if ( $profession != '' ) {
            $this->db->where('sw.profession', $profession);
        }
        if ( $display_name != '' ) {
            $this->db->like('sw.display_name', $display_name);
        }
        if ( $display_status != '' ) {
            $this->db->where('sw.display_status', $display_status);
        }

        $this->db->group_by('sw.id');
        switch ( $sort_name ) {
            case 'synonym_count':
                $this->db->order_by($sort_name, $sort_order);
            break;
            
            default:
                $this->db->order_by("sw.{$sort_name}", $sort_order);
            break;
        }
        $tpl['q'] = $this->db->get();

        $this->load->model('synonym_word_model');
        $language_list = $this->synonym_word_model->getLanguageList();
        $profession_list = $this->synonym_word_model->getProfessionList();
        
        $tpl['language_list'] = $language_list;
        $tpl['profession_list'] = $profession_list;
        
        $this->load->view('synonym_mng/word_list_tpl.php', $tpl);
    }
    
    function ajaxEditWord() 
    {
        if ( !isset($_SESSION['synonym_mng']) ) {
            unset($_SESSION['synonym_mng']);
            redirect('synonym_mng');
            exit;
        }
        
        try {
            if ( isset($_POST) && count($_POST) > 0 ) {
                $id = (int)$this->input->post('id', true);
                $word = trim($this->input->post('word', true));
                $language = trim($this->input->post('language', true));
                $profession = trim($this->input->post('profession', true));
                $display_name = trim($this->input->post('display_name', true));
                $display_status = trim($this->input->post('display_status', true));
                
                $where = array( 'id !=' => $id, 
                                'word' => $word, 
                                'language' => $language, 
                                'profession' => $profession
                                );
                $q = $this->db->get_where('synonym_word', $where);
                if ( $q->num_rows() > 0 ) {
                    throw new Exception('This word already exists');
                }
                
                $data = array(  'word' => $word, 
                                'language' => $language, 
                                'profession' => $profession,
                                'display_name' => $display_name, 
                                'display_status' => $display_status, 
                                );
                $this->db->update('synonym_word', $data, array('id'=>$id));
            }
            
            $result = array( 'error' => 0, 
                             'msg' => '', 
                             );
            echo json_encode($result);
            
        } catch (Exception $e) {
            
            $result = array( 'error' => 1, 
                             'msg' => $e->getMessage(), 
                             );
            echo json_encode($result);
        }
    }
    
    function ajaxCheckWordExist() 
    {
        if ( !isset($_SESSION['synonym_mng']) ) {
            unset($_SESSION['synonym_mng']);
            redirect('synonym_mng');
            exit;
        }
        
        try {
            if ( isset($_POST) && count($_POST) > 0 ) {
                $word = trim($this->input->post('word', true));
                $language = trim($this->input->post('language', true));
                $profession = trim($this->input->post('profession', true));
                
                $where = array( 'word' => $word,
                                'language' => $language, 
                                'profession' => $profession, 
                                );
                $q = $this->db->get_where('synonym_word', $where);
                if ( $q->num_rows() > 0 ) {
                    throw new Exception('This word already exists');
                }
            }
            
            $result = array( 'error' => 0, 
                             'msg' => '', 
                             );
            echo json_encode($result);
            
        } catch (Exception $e) {
            
            $result = array( 'error' => 1, 
                             'msg' => $e->getMessage(), 
                             );
            echo json_encode($result);
        }
    }
    
    function ajaxDeleteWord() 
    {
        if ( !isset($_SESSION['synonym_mng']) ) {
            unset($_SESSION['synonym_mng']);
            redirect('synonym_mng');
            exit;
        }
        
        try {
            $word_id = $this->input->post('word_id', true);
            
            $this->db->delete('synonym_word', array('id'=>$word_id));
            $this->db->delete('synonym_synonym', array('word_id'=>$word_id));
            
            $result = array('error' => 0, 
                            'msg' => '', 
                            );
            echo json_encode($result);
            
        } catch (Exception $e) {
            $result = array('error' => 1, 
                            'msg' => $e->getMessage(), 
                            );
            echo json_encode($result);
        }
    }
    
    function logout() 
    {
        unset($_SESSION['synonym_mng']);
        session_regenerate_id(true);
        redirect('synonym_mng');
    }
}

/* End of file synonym_mng.php */
/* Location: ./application/controllers/synonym_mng.php */