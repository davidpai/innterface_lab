<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('PHPSession');
    }

    /**
     * 將keyword_app_index的內容輸出成CSV檔，放在tmp下
     */
    function keyword_app_file() 
    {
        $query = $this->db->get('keyword_app_index');
        if ( $query->num_rows() > 0 ) {
            
            $fp = fopen('../tmp/keyword_app_list.csv', 'w');
            
            $data = array( 'word', 'frequency', 'genre_count', 'genre_list', 'app count', 'app list' );
            fputcsv($fp, $data, ',', '"');

            foreach ( $query->result() as $row ) {
                $app_list_arr = unserialize($row->app_list);
                
                $app_arr = array();
                $genre_arr = array();
                foreach ( $app_list_arr as $app ) {
                    $app_arr[] = $app['app_name'].'('.$app['frequency'].')';
                    $genre_arr[$app['primary_genre_id']] = $app['primary_genre_name'];
                }
                ksort($genre_arr);
                $data = array(  $row->word, 
                                $row->frequency, 
                                count($genre_arr), 
                                implode(', ', $genre_arr), 
                                count($app_list_arr), 
                                implode(', ', $app_arr), 
                                 );
                fputcsv($fp, $data, ',', '"');
                //var_dump($data);
                
            }
            
            fclose($fp);
            
        }
    }
    
    /**
     * 做出keyword_app的反向index存入keyword_app_index
     */
    function make_keyword_app_index() 
    {
        $this->db->truncate('keyword_app_index');
        // 不區分類別，全部的keyword
        $query = $this->db->select('word')->distinct()->get('app_keyword');
        // 各類別App的keyword
        /*
        $this->db->select('word');
        $this->db->distinct();
        $this->db->from('app_keyword AS k');
        $this->db->join('app AS a', 'k.app_id = a.id', 'left outer');
        $this->db->where('a.primaryGenreName', 'Social Networking');
        $query = $this->db->get();
        */
        if ( $query->num_rows() > 0 ) {
            foreach ( $query->result() as $row ) {
                $this->db->select('
                    a.id, 
                    a.trackId, 
                    a.trackName, 
                    a.primaryGenreId, 
                    a.primaryGenreName, 
                    k.frequency
                    ');
                $this->db->from('app_keyword AS k');
                $this->db->join('app AS a', 'k.app_id = a.id', 'left outer');
                $this->db->where('k.word', $row->word);
                $this->db->order_by('k.frequency', 'desc');
                $query1 = $this->db->get();
                $arr = array();
                $frequency = 0;
                foreach ( $query1->result() as $row1 ) {
                    $arr[] = array( 'app_id' => $row1->id, 
                                    'apple_app_id' => $row1->trackId, 
                                    'app_name' => $row1->trackName, 
                                    'primary_genre_id' => $row1->primaryGenreId, 
                                    'primary_genre_name' => $row1->primaryGenreName, 
                                    'frequency' => $row1->frequency, 
                                    );
                    $frequency += $row1->frequency;
                }
                $data = array(  'word' => $row->word, 
                                'frequency' => $frequency, 
                                'app_list' => serialize($arr), 
                                );
                $this->db->insert('keyword_app_index', $data);
            }
        }
        echo 'Done';
    }
    
    /*
     * 讀入一個app_id的列表純文字檔案，依上面的app_id(trackId)去Search API一個一個把App資料抓下存入app
     */
    function fetch_from_file() 
    {
        try {
            //$this->db->truncate('app');
            $this->load->helper('file');
            
            $app_id_file = FCPATH.'../raw_data/app_id_v.txt';
            $app_id_arr = file_to_array($app_id_file);
            
            if ( empty($app_id_arr) ) {
                throw new Exception('Read File Error');
            }
            
            foreach ( $app_id_arr as $app_id ) {
                $this->fetch_single_app($app_id);
            }
            
            echo "Done";

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /*
     * 給定app_id(trackId)去Search API一個一個把App資料抓下存入app
     */
    function fetch_single_app($app_id=null) 
    {
        try {
            if ( !empty($app_id) ) {
            } else {
                $app_id = (int)$this->input->get('app_id', TRUE);
            }
            $this->load->model('raw_search_api_model', 'search_api');
            $result = $this->search_api->fetch_single_app($app_id);
            
            echo "Done: {$app_id}\r\n";
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /**
     * 抓出DB中現有的App資料，從name和description解析出keyword，並算出其出現次數，寫入app_keyword
     */
    function make_index()
    {
        $min_index_len = 2;  // 字元長度超過這個數字的詞才收入index
        $app_id = (int)$this->input->get('app_id', TRUE);
        
        // 載入無用字詞列表 stopwords.dic (放在application/libraries/)
        $stopwords_file = APPPATH.'libraries/stopwords.dic';
        $stopwords_arr = array();
        if (($handle = fopen($stopwords_file, "r")) !== FALSE) {
            while (($csv_arr = fgetcsv($handle, 0, ",")) !== FALSE) {
                $stopwords_arr = array_merge($stopwords_arr, $csv_arr);
            }
            fclose($handle);
        }
        $stopwords_arr = array_unique($stopwords_arr);
        
        // 載入標點符號列表 punctuation.dic (放在application/libraries/)
        $punctuation_file = APPPATH.'libraries/punctuation.dic';
        $punctuation_arr = array();
        if (($handle = fopen($punctuation_file, "r")) !== FALSE) {
            while (($buffer = fgets($handle)) !== FALSE) {
                $punctuation_arr[] = rtrim($buffer);
            }
            fclose($handle);
        }
        
        // 載入符號列表 symbol.dic (放在application/libraries/)
        $symbol_file = APPPATH.'libraries/symbol.dic';
        $symbol_arr = array();
        if (($handle = fopen($symbol_file, "r")) !== FALSE) {
            while (($buffer = fgets($handle)) !== FALSE) {
                $symbol_arr[] = rtrim($buffer);
            }
            fclose($handle);
        }
        
        // 載入特殊過濾pattern列表 preg_filter.dic (放在application/libraries/)
        $preg_filter_file = APPPATH.'libraries/preg_filter.dic';
        $preg_filter_arr = array();
        if (($handle = fopen($preg_filter_file, "r")) !== FALSE) {
            while (($buffer = fgets($handle)) !== FALSE) {
                $preg_filter_arr[] = rtrim($buffer);
            }
            fclose($handle);
        }

        $this->db->truncate('app_keyword');
        $this->db->select('id, trackName, description');
        $this->db->from('app');
        if ( $app_id > 0 ) {
            $this->db->where('id',$app_id);
        }
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            $this->load->model('app_keyword_model');
            $text = '';
            foreach ( $query->result() as $row ) {
                $text = $row->trackName.' '.utf8_decode($row->description);

                //var_dump($text);
                
                // 轉小寫
                $text = strtolower($text);

                // 拿掉符號
                $text = str_replace($symbol_arr, '', $text);
                
                // 拿掉標點符號
                $text = str_replace($punctuation_arr, '', $text);

                // 去除前後空白
                //$text = trim($text);

                // 去除字和字之間的空白
                $text = preg_replace('/\s(?=\s)/', '', $text);

                // 換行符號換成空白
                $text = preg_replace('/[\n\r\t]/', ' ', $text);

                // 去除前後空白
                $text = trim($text);
//var_dump($text);
                // 用RegEx過濾某些特殊情況，換為空白
                $text = preg_replace($preg_filter_arr, ' ', $text);
//var_dump($text);
                // 去除字和字之間的空白
                $text = preg_replace('/\s(?=\s)/', '', $text);
//var_dump($text);
                // 轉成陣列
                $arr = explode(' ', $text);

                // 去掉無用的字詞和標點符號
                $arr = array_diff($arr, $stopwords_arr);
                $arr = array_diff($arr, $punctuation_arr);
                
                $this->load->library('Inflector');
                foreach ( $arr as $k => &$word ) {
                    // 濾掉任何非字詞字元
                    $word = preg_replace('/[^a-zA-Z0-9\-_\.@]/i','',$word);
                    // 複數詞轉成單數詞
                    // 不需要將複數詞轉為單數詞 => 不轉換單複數的理由?
                    $word = $this->inflector->singularize($word);
                    // 濾掉字元數太少的字
                    if ( strlen($word) <= $min_index_len ) {
                        unset($arr[$k]);
                    }
                }
                unset($word);

                //var_dump($arr);

                // 計算關鍵字出現的次數
                $arr = array_count_values($arr);
                
                //var_dump($arr);

                foreach ( $arr as $word => $frequency ) {
                    if ( strlen(trim($word)) > $min_index_len ) {
                        $data = array(  'app_id' => $row->id, 
                                        'word' => $word, 
                                        'frequency' => $frequency, 
                                        );
                        $this->app_keyword_model->add($data);
                    }
                }
            }
        }
    }
    
    function index() 
    {
        $this->load->view('home_tpl');
    }
}

/* End of file home.php */
/* Location: ./application/controllers/home.php */