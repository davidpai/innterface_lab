<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/../basic_controller.php';

class App extends Basic_Controller {
    
    private $search_api_url = 'https://itunes.apple.com/search?term=%s&media=software';
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('app_model');
    }

    function search()
    {
        try {
            $term = trim($this->input->get('term', TRUE));
            
            if ( $term == '' ) {
                throw new Exception('you need to input some search term');
            }
            
            $source = array();

            $search_api_url = sprintf($this->search_api_url, urlencode($term));
            $raw_data = @file_get_contents($search_api_url);
            if ( FALSE === $raw_data ) {
            } else {
                $json = @json_decode(trim($raw_data));
            }
            
            if ( isset($json->results) && is_array($json->results) ) {
                foreach ( $json->results as $app ) {
                    $source["{$app->trackId}"] = array( 'label' => $app->trackName,
                                                        'value' => $app->trackName, 
                                                        'trackId' => (string)$app->trackId,
                                                        'trackName' => $app->trackName,
                                                        'primaryGenreName' => $app->primaryGenreName,
                                                        'artistName' => $app->artistName,
                                                        'artworkUrl60' => $app->artworkUrl60,
                                                        'artworkUrl100' => $app->artworkUrl100, 
                                                        );
                }
            }

            $this->db->select(' id,
                                trackId,
                                trackName,
                                primaryGenreName,
                                artistName,
                                iconUrl53,
                                iconUrl75,
                                iconUrl100, 
                                artworkUrl60,
                                artworkUrl100,
                                artworkUrl512,
                                ');
            $this->db->from('app');
            $this->db->like('trackName', $term);
            $q = $this->db->get();
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $row ) {
                    $row->label = $row->trackName;
                    $row->value = $row->trackName;
                    $source["{$row->trackId}"] = $row;
                }
            }

            $data = array( 'error' => 0,
                           'msg' => '',
                           'source' => $source);
            echo json_encode($data);
            
        } catch (Exception $e) {
            
            $data = array( 'error' => 1,
                           'msg' => $e->getMessage() );
            echo json_encode($data);
        }
    }
}

/* End of file app.php */
/* Location: ./application/controllers/api/app.php */