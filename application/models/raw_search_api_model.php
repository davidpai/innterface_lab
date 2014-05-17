<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'basic_model.php';

class Raw_search_api_model extends Basic_Model {

    private $fetch_single_api_url = 'http://itunes.apple.com/lookup?id=%d';
    private $keyword_search_api_url = 'https://itunes.apple.com/search?entity=software&limit=300&term=%s';
    
    const APPLE = 'Apple';
    const GOOGLE = 'Google';
    const MICROSOFT = 'Microsoft';
    
    const ENGLISH = 'en_us';
    const JAPANESS = 'ja_jp';
    const TRAD_CHINESE = 'zh_tw';
    const SIMP_CHINESE = 'zh_cn';
    
    const IPHONE = 'iPhone';
    const IPAD = 'iPad';
    const ANDROID = 'Android';
    const WP = 'Windows Phone';
    
    function __construct() 
    {
        parent::__construct();
        $this->table_name = 'raw_search_api';
    }
    
    /**
     * 向Search API丟一個搜尋字，將app資料寫入App table，screenshot資料寫入screenshot table
     * app資料若已存在DB，就覆蓋掉，screenshot若有新的，才新增
     * 
     * @param string $keyword 要搜尋的字詞
     */
    function keyword_search_app($keyword)
    {
        try {
            $keyword = trim($keyword);
            $search_api_url = sprintf($this->keyword_search_api_url, urlencode($keyword));

            $raw_data = @file_get_contents($search_api_url);
            if ( FALSE === $raw_data ) {
                throw new Exception("Fail: appkw={$keyword} (Fetch Error)".print_r($raw_data, TRUE));
            }
            //echo $raw_data; exit;
            
            $json = @json_decode($raw_data);
            if ( !empty($json) && isset($json->results) && is_array($json->results) ) {
            } else {
                throw new Exception("Fail: appkw={$keyword} (Fetch Error)".print_r($raw_data, TRUE));
            }
            //var_dump($json->results); exit;
            
            $return_msg = array();
            if ( count($json->results) > 0 ) {
                foreach ( $json->results as $app ) {
                    //$return_msg[] = $this->json_result_app_to_db($app);
                    if ( false === $this->json_result_app_to_db($app) ) {
                        $return_msg[] = "Fail: Parse no such App";
                    } else {
                        $return_msg[] = "Success: appId={$app->trackId} appName={$app->trackName}";
                    }
                }
            } else {
                throw new Exception("Fail: appkw={$keyword} (Parse no such App)");
            }
            
            return $return_msg;
            
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
    /**
     * 向Search API擷取單一個App資料，將app資料寫入App table，screenshot資料寫入screenshot table
     * app資料若已存在DB，就覆蓋掉，screenshot若有新的，才新增
     * 
     * @param integer $app_id Apple的App ID
     */
    function fetch_single_app($trackId) 
    {
        try {
            $trackId = (int)$trackId;
            $search_api_url = sprintf($this->fetch_single_api_url, $trackId);
            
            $raw_data = @file_get_contents($search_api_url);
            if ( FALSE === $raw_data ) {
                throw new Exception("Fail: appId={$trackId} (Fetch Error)".print_r($raw_data, TRUE));
            }
            //echo $raw_data; exit;
            
            $json = @json_decode($raw_data);
            if ( !empty($json) && isset($json->results) && is_array($json->results) ) {
            } else {
                throw new Exception("Fail: appId={$trackId} (Fetch Error)".print_r($raw_data, TRUE));
            }
            //var_dump($json->results); exit;
            
            if ( count($json->results) > 0 ) {
                foreach ( $json->results as $app ) {
                    $app_id = $this->json_result_app_to_db($app);
                }
            } else {
                throw new Exception("Fail: appId={$trackId} (Parse no such App)");
            }
            
            $return = array( 'error' => 0, 
                             'appid' => $app->trackId, 
                             'app_id' => $app_id, 
                             'msg' => "Success: appId={$app->trackId} appName={$app->trackName}", 
                             );
            return $return;
            
        } catch (Exception $e) {
            $return = array( 'error' => 1, 
                             'msg' => $e->getMessage(), 
                             );
            return $return;
        }
    }

    function json_result_app_to_db($app) 
    {
        $this->load->model('screenshot_model');
        
        if ( isset($app->trackId) && isset($app->trackName) ) {
            // Save app & app_desc
            $appId = $this->save_app_data($app);
            
            // iPhone Screenshot
            if ( isset($app->screenshotUrls) && is_array($app->screenshotUrls) && count($app->screenshotUrls) > 0 ) {
                foreach ( $app->screenshotUrls as $screenshot_url ) {
                    $q = $this->screenshot_model->get_by(array('url'=>$screenshot_url));
                    // 若有URL相同(重複)的就不新增
                    if ( $q->num_rows() > 0 ) {
                    } else {
                        $data = array(  'appId' => $appId, 
                                        'device' => 'iphone', 
                                        'url' => $screenshot_url, 
                                        'version' => isset($app->version) ? $app->version : null,
                                        );
                        $this->screenshot_model->add($data);
                    }
                }
            }
            
            // iPad Screenshot
            if ( isset($app->ipadScreenshotUrls) && is_array($app->ipadScreenshotUrls) && count($app->ipadScreenshotUrls) > 0 ) {
                foreach ( $app->ipadScreenshotUrls as $screenshot_url ) {
                    $q = $this->screenshot_model->get_by(array('url'=>$screenshot_url));
                    // 若有URL相同(重複)的就不新增
                    if ( $q->num_rows() > 0 ) {
                    } else {
                        $data = array(  'appId' => $appId, 
                                        'device' => 'ipad', 
                                        'url' => $screenshot_url, 
                                        'version' => isset($app->version) ? $app->version : null,
                                        );
                        $this->screenshot_model->add($data);
                    }
                }
            }
            
            //return "Success: appId={$app->trackId} appName={$app->trackName}";
            return $appId;
            
        } else {
        
            //return "Fail: Parse no such App";
            return false;
        }
    }
    
    /* 2014-05-22 DB結構已更改，改為整合後的DB，寫入欄位已不同
    function json_result_app_to_db($app)
    {
        $this->load->model('screenshot_model');
        
        if ( isset($app->trackId) && isset($app->trackName) ) {
            // Save app
            $app_id = $this->save_app_data($app);
            
            // iPhone Screenshot
            if ( isset($app->screenshotUrls) && is_array($app->screenshotUrls) && count($app->screenshotUrls) > 0 ) {
                foreach ( $app->screenshotUrls as $screenshot_url ) {
                    $q = $this->screenshot_model->get_by(array('url'=>$screenshot_url));
                    // 若有URL相同(重複)的就不新增
                    if ( $q->num_rows() > 0 ) {
                    } else {
                        $data = array(  'app_id' => $app_id, 
                                        'trackId' => $app->trackId, 
                                        'device' => 'iphone', 
                                        'version' => $app->version, 
                                        'url' => $screenshot_url, 
                                        );
                        $this->screenshot_model->add($data);
                    }
                }
            }
            
            // iPad Screenshot
            if ( isset($app->ipadScreenshotUrls) && is_array($app->ipadScreenshotUrls) && count($app->ipadScreenshotUrls) > 0 ) {
                foreach ( $app->ipadScreenshotUrls as $screenshot_url ) {
                    $q = $this->screenshot_model->get_by(array('url'=>$screenshot_url));
                    // 若有URL相同(重複)的就不新增
                    if ( $q->num_rows() > 0 ) {
                    } else {
                        $data = array(  'app_id' => $app_id, 
                                        'trackId' => $app->trackId, 
                                        'device' => 'ipad', 
                                        'version' => $app->version, 
                                        'url' => $screenshot_url, 
                                        );
                        $this->screenshot_model->add($data);
                    }
                }
            }
            
            //return "Success: appId={$app->trackId} appName={$app->trackName}";
            return $app_id;
            
        } else {
        
            //return "Fail: Parse no such App";
            return false;
        }
    }
    */
    
    function save_app_data($app) 
    {
        $this->load->model('app_model');
        
        $data = array();
        $data['appName'] = isset($app->trackName) ? $app->trackName : null;
        $data['appIconUrl'] = empty($app->artworkUrl512) ? $app->iconUrl100 : $app->artworkUrl512;
        $data['appViewUrl'] = isset($app->trackViewUrl) ? $app->trackViewUrl : null;
        $data['appPlatform'] = self::APPLE;
        $data['appPlatformId'] = isset($app->trackId) ? (int)$app->trackId : 0;
        $data['category'] = isset($app->primaryGenreName) ? $app->primaryGenreName : null;
        $data['price'] = isset($app->formattedPrice) ? $app->formattedPrice : null;
        $data['version'] = isset($app->version) ? $app->version : null;
        $data['releaseDate'] = isset($app->releaseDate) ? $app->releaseDate : null;
        $data['fileSize'] = isset($app->fileSizeBytes) ? $app->fileSizeBytes : null;
        $data['supportedDevice'] = isset($app->supportedDevices) ? implode(',', $app->supportedDevices) : null;
        $data['averageUserRating'] = isset($app->averageUserRating) ? $app->averageUserRating : null;
        $data['userRatingCount'] = isset($app->userRatingCount) ? $app->userRatingCount : null;
        $data['developer'] = isset($app->artistName) ? $app->artistName : null;

        $q = $this->app_model->get_by(array(
                'appPlatform' => $data['appPlatform'], 
                'appPlatformId' => $data['appPlatformId'], 
                'version' => $data['version'], 
        ));
        if ( $q->num_rows() > 0 ) {
            $appId = $q->row()->id;
            $this->app_model->update_by_id($data, $appId);
        } else {
            $appId = $this->app_model->add($data);
        }
        
        if ( $appId ) {
            $this->load->model('app_desc_model');
            $data = array(
                'appId' => $appId, 
                'version' => isset($app->version) ? $app->version : null, 
                'language' => self::ENGLISH, 
                'description' => isset($app->description) ? $app->description : null, 
                'releaseNote' => isset($app->releaseNotes) ? $app->releaseNotes : null, 
            );
            $q = $this->app_desc_model->get_by(array(
                'appId' => $data['appId'], 
                'version' => $data['version'], 
                'language' => $data['language'], 
            ));
            if ( $q->num_rows() > 0 ) {
                $app_desc_id = $q->row()->id;
                $this->app_desc_model->update_by_id($data, $app_desc_id);
            } else {
                $app_desc_id = $this->app_desc_model->add($data);
            }
        }
        
        return $appId;
    }

    /* 2014-05-22 DB結構已更改，改為整合後的DB，寫入欄位已不同
    function save_app_data($app) 
    {
        $this->load->model('app_model');
        
        $data = array();
        $data['trackId'] = isset($app->trackId) ? $app->trackId : 0;
        $data['trackName'] = isset($app->trackName) ? $app->trackName : '';
        $data['trackCensoredName'] = isset($app->trackCensoredName) ? $app->trackCensoredName : '';
        $data['trackViewUrl'] = isset($app->trackViewUrl) ? $app->trackViewUrl : '';
        $data['primaryGenreName'] = isset($app->primaryGenreName) ? $app->primaryGenreName : '';
        $data['primaryGenreId'] = isset($app->primaryGenreId) ? $app->primaryGenreId : 0;
        $data['genres'] = isset($app->genres) ? implode(',', $app->genres) : '';
        $data['genreIds'] = isset($app->genreIds) ? implode(',', $app->genreIds) : '';
        $data['artworkUrl60'] = isset($app->artworkUrl60) ? $app->artworkUrl60 : '';
        $data['artworkUrl100'] = isset($app->artworkUrl100) ? $app->artworkUrl100 : '';
        $data['artworkUrl512'] = isset($app->artworkUrl512) ? $app->artworkUrl512 : '';
        $data['description'] = isset($app->description) ? $app->description : '';
        $data['price'] = isset($app->price) ? $app->price : '0.00';
        $data['currency'] = isset($app->currency) ? $app->currency : '';
        $data['formattedPrice'] = isset($app->formattedPrice) ? $app->formattedPrice : '';
        $data['version'] = isset($app->version) ? $app->version : '';
        $data['releaseDate'] = isset($app->releaseDate) ? $app->releaseDate : '';
        $data['releaseNotes'] = isset($app->releaseNotes) ? $app->releaseNotes : '';
        $data['bundleId'] = isset($app->bundleId) ? $app->bundleId : '';
        $data['features'] = isset($app->features) ? implode(',', $app->features) : '';
        $data['supportedDevices'] = isset($app->supportedDevices) ? implode(',', $app->supportedDevices) : '';
        $data['languageCodesISO2A'] = isset($app->languageCodesISO2A) ? implode(',', $app->languageCodesISO2A) : '';
        $data['fileSizeBytes'] = isset($app->fileSizeBytes) ? $app->fileSizeBytes : '';
        $data['trackContentRating'] = isset($app->trackContentRating) ? $app->trackContentRating : '';
        $data['contentAdvisoryRating'] = isset($app->contentAdvisoryRating) ? $app->contentAdvisoryRating : '';
        $data['averageUserRatingForCurrentVersion'] = isset($app->averageUserRatingForCurrentVersion) ? $app->averageUserRatingForCurrentVersion : '';
        $data['userRatingCountForCurrentVersion'] = isset($app->userRatingCountForCurrentVersion) ? $app->userRatingCountForCurrentVersion : '';
        $data['averageUserRating'] = isset($app->averageUserRating) ? $app->averageUserRating : '';
        $data['userRatingCount'] = isset($app->userRatingCount) ? $app->userRatingCount : '';
        $data['artistId'] = isset($app->artistId) ? $app->artistId : 0;
        $data['artistName'] = isset($app->artistName) ? $app->artistName : '';
        $data['artistViewUrl'] = isset($app->artistViewUrl) ? $app->artistViewUrl : '';
        $data['sellerName'] = isset($app->sellerName) ? $app->sellerName : '';
        $data['sellerUrl'] = isset($app->sellerUrl) ? $app->sellerUrl : '';
        
        $where = array( 'trackId' => $app->trackId, 'version' => $app->version );
        $q = $this->app_model->get_by($where);
        if ( $q->num_rows() > 0 ) {
            $app_id = $q->row()->id;
            $this->app_model->update_by_id($data, $app_id);
        } else {
            $app_id = $this->app_model->add($data);
        }
        
        return $app_id;
    }
    */
}

/* End of file raw_itunes_rss_model.php */
/* Location: ./application/models/raw_itunes_rss_model.php */