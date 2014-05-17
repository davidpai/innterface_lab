<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search_api extends CI_Controller {

    private $search_api_url = 'http://itunes.apple.com/lookup?id=%d';

    public function __construct()
    {
        parent::__construct();
		$this->load->library('PHPSession');
    }

    /**
     * 撈出raw_search_api，拿appId去Search API撈回資料存入rawData
     */
    public function fetch() 
    {
        $this->load->model('raw_search_api_model', 'search_api');
        $query = $this->search_api->get_by(array('status'=>'add'));
        if ( $query->num_rows() > 0 ) {
            foreach ( $query->result() as $row ) {
                $search_api_url = sprintf($this->search_api_url, $row->appId);
                //echo $search_api_url.'<br />';
                $data = array( 'status' => 'processing' );
                $this->search_api->update_by_id($data, $row->id);
                
                $raw_data = @file_get_contents($search_api_url);
                if ( FALSE === $raw_data ) {
                    $data = array( 'status' => 'fail' );
                    $this->search_api->update_by_id($data, $row->id);
                } else {
                    $data = array(  'rawData' => utf8_encode($raw_data), 
                                    'status' => 'init', 
                                    'fetchDate' => date('Y-m-d'), 
                                    );
                    $this->search_api->update_by_id($data, $row->id);
                }
            }
        }
    }
    
    /**
     * 撈出raw_search_api，處理rawData，寫入app
     */
    public function process() 
    {
        $this->load->model('raw_search_api_model', 'search_api');
        $this->load->model('app_model');
        $this->load->model('screenshot_model');
        
        $query = $this->search_api->get_by(array('status'=>'init'));
        if ( $query->num_rows() > 0 ) {
            
            foreach ( $query->result() as $row ) {
                
                $json = @json_decode($row->rawData);
                if ( !empty($json) && isset($json->results) && is_array($json->results) && count($json->results) > 0 ) {

                    foreach ( $json->results as $app ) {
                        $data = array();
                        $data['trackId'] = $app->trackId;
                        $data['trackName'] = $app->trackName;
                        $data['trackCensoredName'] = $app->trackCensoredName;
                        $data['trackViewUrl'] = $app->trackViewUrl;
                        $data['primaryGenreName'] = $app->primaryGenreName;
                        $data['primaryGenreId'] = $app->primaryGenreId;
                        $data['genres'] = implode(',', $app->genres);
                        $data['genreIds'] = implode(',', $app->genreIds);
                        $data['iconUrl53'] = $row->iconUrl53;
                        $data['iconUrl75'] = $row->iconUrl75;
                        $data['iconUrl100'] = $row->iconUrl100;
                        $data['artworkUrl60'] = $app->artworkUrl60;
                        $data['artworkUrl100'] = $app->artworkUrl100;
                        $data['artworkUrl512'] = $app->artworkUrl512;
                        $data['description'] = $app->description;
                        $data['price'] = $app->price;
                        $data['currency'] = $app->currency;
                        $data['formattedPrice'] = $app->formattedPrice;
                        $data['version'] = $app->version;
                        $data['releaseDate'] = $app->releaseDate;
                        $data['releaseNotes'] = isset($app->releaseNotes) ? $app->releaseNotes : '';
                        $data['bundleId'] = $app->bundleId;
                        $data['features'] = implode(',', $app->features);
                        $data['supportedDevices'] = implode(',', $app->supportedDevices);
                        $data['languageCodesISO2A'] = implode(',', $app->languageCodesISO2A);
                        $data['fileSizeBytes'] = $app->fileSizeBytes;
                        $data['trackContentRating'] = isset($app->trackContentRating) ? $app->trackContentRating : '';
                        $data['contentAdvisoryRating'] = isset($app->contentAdvisoryRating) ? $app->contentAdvisoryRating : '';
                        $data['averageUserRatingForCurrentVersion'] = isset($app->averageUserRatingForCurrentVersion) ? $app->averageUserRatingForCurrentVersion : '';
                        $data['userRatingCountForCurrentVersion'] = isset($app->userRatingCountForCurrentVersion) ? $app->userRatingCountForCurrentVersion : '';
                        $data['averageUserRating'] = isset($app->averageUserRating) ? $app->averageUserRating : '';
                        $data['userRatingCount'] = isset($app->userRatingCount) ? $app->userRatingCount : '';
                        $data['artistId'] = $app->artistId;
                        $data['artistName'] = $app->artistName;
                        $data['artistViewUrl'] = isset($app->artistViewUrl) ? $app->artistViewUrl : '';
                        $data['sellerName'] = isset($app->sellerName) ? $app->sellerName : '';
                        $data['sellerUrl'] = isset($app->sellerUrl) ? $app->sellerUrl : '';
                        $app_id = $this->app_model->add($data);
                        
                        // iPhone Screenshot
                        if ( isset($app->screenshotUrls) && is_array($app->screenshotUrls) && count($app->screenshotUrls) > 0 ) {
                            foreach ( $app->screenshotUrls as $screenshot_url ) {
                                $data = array(  'app_id' => $app_id, 
                                                'trackId' => $app->trackId, 
                                                'device' => 'iphone', 
                                                'url' => $screenshot_url, 
                                                );
                                $this->screenshot_model->add($data);
                            }
                        }
                        
                        // iPad Screenshot
                        if ( isset($app->ipadScreenshotUrls) && is_array($app->ipadScreenshotUrls) && count($app->ipadScreenshotUrls) > 0 ) {
                            foreach ( $app->ipadScreenshotUrls as $screenshot_url ) {
                                $data = array(  'app_id' => $app_id, 
                                                'trackId' => $app->trackId, 
                                                'device' => 'ipad', 
                                                'url' => $screenshot_url, 
                                                );
                                $this->screenshot_model->add($data);
                            }
                        }
                        
                        $data = array(  'status' => 'done', 
                                        'processTime' => date('Y-m-d H:i:s'), 
                                        );
                        $this->search_api->update_by_id($data, $row->id);

                    }

                } else {
                    $data = array(  'status' => 'fail', 
                                    'processTime' => date('Y-m-d H:i:s'), 
                                    );
                    $this->search_api->update_by_id($data, $row->id);
                }
            }
        }
    }
}

/* End of file search_api.php */
/* Location: ./application/controllers/search_api.php */