<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Make_index extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    function make_weight_param() 
    {
        $start = microtime(true);
        
        $this->load->model('weight_model');
        $this->weight_model->set_param();
        
        $time = microtime(true) - $start;
        echo "Done! in {$time} seconds";
    }
    
    function make_screenshot_comment_word() 
    {
        $start = microtime(true);
        $this->load->model('user_screenshot_comment_model');
        $this->user_screenshot_comment_model->make_screenshot_comment_word();
        $time = microtime(true) - $start;
        echo "Done! in {$time} seconds";
    }
    
    /**
     * 從同義字字典中撈出定義好的字詞，做出tag對screenshot的index，存入tag_screenshot_index table
     */
    function make_tag_screenshot_index()
    {
        $start = microtime(true);
        try {
            $table_name = 'tag_screenshot_index';
            
            $this->load->model('tag_model');
            $this->load->model('screenshot_model');
            
            $tag_screenshot_index = $this->tag_model->make_screenshot_index();
            $this->db->truncate($table_name);
            foreach ( $tag_screenshot_index as $row ) {
                if ( !empty($row['screenshot_list']) ) {
                
                    // 排序: 依weight高->低排序
                    usort($row['screenshot_list'], function($a, $b) {
                        if ( $a->weight == $b->weight ) {
                            return 0;
                        }
                        return ( $a->weight < $b->weight ) ? 1 : -1;
                    });
                    
                    // 做出 screenshot_list 第一個 screenshot 的 tag_list，首頁會用到
                    $tmp_arr = array($row['screenshot_list'][0]);
                    $this->screenshot_model->add_tag_list($tmp_arr);
                    $row['screenshot_list'][0] = $tmp_arr[0];
                    //var_dump($row['screenshot_list'][0]);
                    
                    $row['screenshot_list'] = serialize($row['screenshot_list']);
                    $this->db->insert($table_name, $row);
                }
            }
            
            echo 'Done';
            
        } catch(Exception $e) {
            
            echo 'Error';
            //echo $e->getMessage();
        }
        $time = microtime(true) - $start;
        echo "Done! in {$time} seconds";
    }

    function make_genre_app_index() 
    {
        $start = microtime(true);
        $this->load->model('word_process_model');
        $genre_app_id_arr = array();
        
        $this->db->truncate('genre_app_index');
        $this->db->select('id, genres, trackName');
        $q = $this->db->get('app');
        foreach ( $q->result() as $r ) {
            $genres = $this->word_process_model->filter_app_genres($r->genres);
            $genre_arr = explode(' ', $genres);
            foreach ( $genre_arr as $genre_word ) {
                $genre_word = $this->word_process_model->singularize($genre_word);
                $genre_app_id_arr[$genre_word][] = array(   'id' => $r->id, 
                                                            'app_name' => $r->trackName, 
                                                            'genres' => $r->genres, 
                                                            );
            }
        }
        
        foreach ( $genre_app_id_arr as $genre_word => $app_arr ) {
            $data = array(  'genre' => $genre_word, 
                            'app_list' => serialize($app_arr), 
                            );
            $this->db->insert('genre_app_index', $data);
        }
        
        $time = microtime(true) - $start;
        echo "Done! in {$time} seconds";
        echo '<br />'.($time/$q->num_rows()).' seconds per app';
    }
    
    function make_appname_app_index() 
    {
        $start = microtime(true);
        
        $this->db->query('TRUNCATE `app_tmp`');
        $this->db->query('INSERT INTO `app_tmp` SELECT `id`, `trackId`, `trackName` FROM `app`');
        
        $this->load->model('word_process_model');
        $table_name = 'appname_app_index';
        $appname_app_id_arr = array();
        $app_id_arr = array();

        $this->db->truncate($table_name);
        $this->db->select('id, trackName');
        $this->db->limit(1000);
        $q = $this->db->get('app_tmp');
        foreach ( $q->result() as $r ) {
            $trackName = $this->word_process_model->filter_app_name($r->trackName);
            $word_arr = explode(' ', $trackName);
            foreach ( $word_arr as $word ) {
                //$word = $this->word_process_model->singularize($word);
                $appname_app_id_arr[$word][$r->id] = array( 'id' => $r->id, 
                                                            'app_name' => $r->trackName, 
                                                            );
            }
            $app_id_arr[] = $r->id;
        }
        unset($q, $r, $trackName, $word_arr, $word);
        //ksort($appname_app_id_arr);
        //var_dump($appname_app_id_arr); exit;
        foreach ( $appname_app_id_arr as $word => $app_arr ) {
            $data = array(  'appname_word' => $word, 
                            'app_list' => serialize($app_arr), 
                            );
            $this->db->insert('appname_app_index', $data);
        }
        
        $time = microtime(true) - $start;
        echo "Done! in {$time} seconds";
        echo '<br />'.($time/$q->num_rows()).' seconds per app';
    }
}

/* End of file home.php */
/* Location: ./application/controllers/home.php */