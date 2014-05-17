<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Itunes_rss extends CI_Controller {
    
    private $_feed_type = array('topfreeapplications',
                                'toppaidapplications',
                                'topgrossingapplications',
                                'topfreeipadapplications',
                                'toppaidipadapplications',
                                'topgrossingipadapplications',
                                'newapplications',
                                'newfreeapplications',
                                'newpaidapplications', 
                                );
    private $_genre = array(6000,6001,6002,6003,6004,6005,6006,6007,6008,6009,6010,6011,6012,6013,6014,6015,6016,6017,6018,6020,6021,6022,6023);
    
    
    private $_country_code_pattern = '/[a-z]{2}/i';

    public function __construct()
    {
        parent::__construct();
        $this->load->library('PHPSession');
    }
    
    /**
     * 批次處理，每個分類抓top 10回來
     */
    public function batch_fetch() {
        $country_code = 'us';
        $feed_type = 'topfreeapplications';
        $limit = 10;
        $url = "http://localhost/innterface/public/itunes_rss/fetch";
        if ( isset($this->_genre) && is_array($this->_genre) ) {
            foreach( $this->_genre as $genre_id ) {
                $fetch_url = $url."?c={$country_code}&f={$feed_type}&g={$genre_id}&l={$limit}";
                file_get_contents($fetch_url);
            }
        }
        echo 'Done';
    }
    
    
    /**
     * 到iTunes RSS抓資料回來存入raw data檔案
     */
    public function fetch() 
    {
        $country_code = strtolower($this->input->get('c', TRUE));  // Country Code
        $feed_type = strtolower($this->input->get('f', TRUE));  // Feed Type
        $genre_id = $this->input->get('g', TRUE);  // Genre ID
        $limit = $this->input->get('l', TRUE);  // 資料回傳筆數 Limit 最多300筆
        $data_format = 'json';

        if ( preg_match($this->_country_code_pattern, $country_code) === 0 ) {
            // throw exception
            die('country code error');
        }
        if ( ! in_array($feed_type, $this->_feed_type) ) {
            die('feed type errpr');
        }
        if ( $genre_id != '' && preg_match('/\d+/i', $genre_id) === 0 ) {
            die('genre id error');
        }
        if ( $limit != '' && preg_match('/\d+/i', $limit) === 0 ) {
            die('limit error');
        }
        
        // 抓取RSS
        $rss_url = "http://itunes.apple.com/{$country_code}/rss/{$feed_type}";
        if ( $genre_id != '' ) {
            $rss_url .= "/genre={$genre_id}";
        }
        if ( $limit != '' ) {
            $rss_url .= "/limit={$limit}";
        }
        $rss_url .= "/{$data_format}";

        $rss_data = file_get_contents($rss_url);
        if ( $rss_data === FALSE ) {
            // throw exception
            die('fetch error');
        }
        
        // 將RSS data寫入檔案
        $config['raw_data'] = $this->config->item('raw_data');
        $raw_data_filename = "{$country_code}_{$feed_type}";
        if ( $genre_id != '' ) {
            $raw_data_filename .= "_{$genre_id}";
        }
        $raw_data_filename .= "_".date('Ymd').".json";
        $raw_data_file = $config['raw_data']['itunes_rss']['dir'].'/'.$raw_data_filename;
        if ( FALSE === file_put_contents($raw_data_file, $rss_data) ) {
            // throw exception
            die('write raw data to file error');
        }

        // 寫一筆記錄到DB，狀態為init(初始)
        $this->load->model('raw_itunes_rss_model', 'itunes_rss');
        $data = array(  'countryCode' => strtoupper($country_code), 
                        'feedType' => $feed_type, 
                        'rawDataFilename' => $raw_data_filename, 
                        'status' => 'init', 
                        'fetchDate' => date('Y-m-d'), 
                        'addTime' => date('Y-m-d H:i:s'), 
                        );
        $this->itunes_rss->add($data);
    }
    
    /**
     * 讀出iTunes RSS抓來的raw data檔案，parse資料，寫入raw_search_api table
     */
    public function process() 
    {
        $this->load->model('raw_itunes_rss_model', 'itunes_rss');
        // 從DB撈出所有init狀態的RSS紀錄
        $query = $this->itunes_rss->get_by(array('status'=>'init'));
        if ( $query->num_rows() > 0 ) {
            $config['raw_data'] = $this->config->item('raw_data');
            $this->load->model('raw_search_api_model', 'search_api');
            
            foreach ( $query->result() as $row ) {
                // 讀出RSS data檔案內容
                $raw_data_file = $config['raw_data']['itunes_rss']['dir'].'/'.$row->rawDataFilename;
                $raw_data = @file_get_contents($raw_data_file);
                
                if ( FALSE === $raw_data ) {
                    // 讀檔案失敗，DB紀錄失敗
                    $data = array( 'status' => 'fail' );
                    $this->itunes_rss->update_by_id($data, $row->id);
                    
                } else {
                    // DB狀態改為processing(處理中)
                    $data = array( 'status' => 'processing' );
                    $this->itunes_rss->update_by_id($data, $row->id);

                    //header('Content-Type: text/javascript; charset=UTF-8');
                    //echo $raw_data;

                    // 開始解析RSS data，寫入Search API table
                    $feed = json_decode($raw_data)->feed;
                    if ( !empty($feed) && isset($feed->entry) && is_array($feed->entry) ) {
                        foreach ( $feed->entry as $entry ) {
                            $data = array(  'appId' => $entry->id->attributes->{"im:id"}, 
                                            'appName' => $entry->{"im:name"}->label, 
                                            'iconUrl53' => $entry->{"im:image"}[0]->label, 
                                            'iconUrl75' => $entry->{"im:image"}[1]->label,
                                            'iconUrl100' => $entry->{"im:image"}[2]->label,
                                            'status' => 'add', 
                                            'addTime' => date('Y-m-d H:i:s'), 
                                            );
                            $insert_id = $this->search_api->add($data);
                        }
                        // 處理完成，DB狀態改為done(完成)
                        $data = array(  'status' => 'done', 
                                        'processTime' => date('Y-m-d H:i:s'), 
                                        );
                        $this->itunes_rss->update_by_id($data, $row->id);
                    } else {
                        // 解析失敗，DB紀錄失敗
                        $data = array( 'status' => 'fail' );
                        $this->itunes_rss->update_by_id($data, $row->id);
                    }
                }
            }
        }
    }
}

/* End of file itunes_rss.php */
/* Location: ./application/controllers/itunes_rss.php */