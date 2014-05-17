<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/basic_controller.php';

class Transfer extends Basic_Controller {

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
    
    public $process_num = 500;

    // transfer dev DB to lab DB : App
    // run per 1 minute
    // transfer status: init => waiting_screenshot => done
	public function dev2lab_app()
	{
        $start = microtime(true);
        //log_message('error', 'transfer/dev2lab_app: Start');
        
        $process_num = $this->process_num;
        
        $this->DB_dev->select('id, trackName, iconUrl100, artworkUrl512, trackViewUrl, trackId, primaryGenreName, description, formattedPrice, version, releaseDate, releaseNotes, fileSizeBytes, supportedDevices, averageUserRating, userRatingCount, artistName');
        $this->DB_dev->where('transfer_status', 'init');
        $q = $this->DB_dev->get('app', $process_num);
        // insert into app
        foreach ($q->result() as $app) {
            $data = array(
                'id'            => $app->id, 
                'appName'       => $app->trackName, 
                'appIconUrl'    => empty($app->artworkUrl512) ? $app->iconUrl100 : $app->artworkUrl512, 
                'appViewUrl'    => $app->trackViewUrl, 
                'appPlatform'   => self::APPLE, 
                'appPlatformId' => $app->trackId, 
                'category'      => $app->primaryGenreName, 
                'price'         => $app->formattedPrice, 
                'version'       => $app->version, 
                'releaseDate'   => $app->releaseDate, 
                'fileSize'      => $app->fileSizeBytes, 
                'supportedDevice'   => $app->supportedDevices, 
                'averageUserRating' => $app->averageUserRating, 
                'userRatingCount'   => $app->userRatingCount, 
                'developer'    => $app->artistName, 
            );
            if ( $r = $this->DB_lab->insert('app', $data) ) {
                // insert into app_desc
                $data = array(
                    'appId' => $app->id, 
                    'version' => $app->version, 
                    'language' => self::ENGLISH, 
                    'description' => $app->description, 
                    'releaseNote' => $app->releaseNotes, 
                );
                if ( $r = $this->DB_lab->insert('app_desc', $data) ) {
                    $this->DB_dev->update('app', array('transfer_status'=>'waiting_screenshot'), array('id'=>$app->id));
                } else {
                    $this->DB_dev->update('app', array('transfer_status'=>'desc_fail'), array('id'=>$app->id));
                }
            } else {
                $this->DB_dev->update('app', array('transfer_status'=>'app_fail'), array('id'=>$app->id));
            }
        }
        
        $time = microtime(true) - $start;
        //log_message('error', "transfer/dev2lab_app: Done in {$time} seconds");
        echo "Done! in {$time} seconds";
	}
    
    // transfer dev DB to lab DB : screenshot
    // run per 3 minutes
    // transfer status: init => done
    public function dev2lab_screenshot() 
    {
        $start = microtime(true);
        //log_message('error', 'transfer/dev2lab_screenshot: Start');
        
        $process_num = (int)($this->process_num/2);
        
        $this->DB_dev->select('id');
        $this->DB_dev->where('transfer_status', 'waiting_screenshot');
        $q = $this->DB_dev->get('app', $process_num);

        foreach ($q->result() as $app) {
            // insert into screenshot
            $q1 = $this->DB_dev->get_where('screenshot', array('app_id'=>$app->id, 'transfer_status'=>'init'));
            foreach ( $q1->result() as $ss ) {
                $data = array(
                    'id' => $ss->id, 
                    'appId' => $app->id, 
                    'device' => empty($ss->device) ? null : $ss->device, 
                    'url' => empty($ss->url) ? null : $ss->url, 
                    's3Url' => empty($ss->s3_url) ? null : $ss->s3_url, 
                    'version' => empty($ss->version) ? null : $ss->version, 
                    'clickCount' => $ss->clickCount, 
                    'pinCount' => $ss->pinCount, 
                    'likeCount' => $ss->likeCount, 
                    'dislikeCount' => $ss->dislikeCount, 
                );
                if ( $r1 = $this->DB_lab->insert('screenshot', $data) ) {
                    $this->DB_dev->update('screenshot', array('transfer_status'=>'done'), array('id'=>$ss->id));
                } else {
                    $this->DB_dev->update('screenshot', array('transfer_status'=>'fail'), array('id'=>$ss->id));
                }
            }
            $this->DB_dev->update('app', array('transfer_status'=>'done'), array('id'=>$app->id));
        }
        
        $time = microtime(true) - $start;
        //log_message('error', "transfer/dev2lab_screenshot: Done in {$time} seconds");
        echo "Done! in {$time} seconds";
    }

    // transfer dev DB to lab DB : rest screenshot with no app
    // run per 3 minutes
    // transfer status: init => done
    public function dev2lab_rest_screenshot() 
    {
        $start = microtime(true);
        //log_message('error', 'transfer/dev2lab: Start');
    
        // insert into screenshot
        $q1 = $this->DB_dev->get_where('screenshot', array('transfer_status'=>'init'));
        foreach ( $q1->result() as $ss ) {
            $appId = null;
            if ( $ss->trackId != '' ) {
                $app = $this->DB_dev->get_where('app', array('trackId'=>$ss->trackId))->row();
                if ( is_object($app) ) {
                    $appId = $app->id;
                }
            }
            $data = array(
                'id' => $ss->id, 
                'appId' => $appId, 
                'device' => empty($ss->device) ? null : $ss->device, 
                'url' => empty($ss->url) ? null : $ss->url, 
                's3Url' => empty($ss->s3_url) ? null : $ss->s3_url, 
                'version' => empty($ss->version) ? null : $ss->version, 
                'clickCount' => $ss->clickCount, 
                'pinCount' => $ss->pinCount, 
                'likeCount' => $ss->likeCount, 
                'dislikeCount' => $ss->dislikeCount, 
            );
            if ( $r1 = $this->DB_lab->insert('screenshot', $data) ) {
                $this->DB_dev->update('screenshot', array('transfer_status'=>'done'), array('id'=>$ss->id));
            } else {
                $this->DB_dev->update('screenshot', array('transfer_status'=>'fail'), array('id'=>$ss->id));
            }
        }
    
        $time = microtime(true) - $start;
        //log_message('error', "transfer/dev2lab: Done in {$time} seconds");
        echo "Done! in {$time} seconds";
    }

    // transfer crawler android DB to lab DB_android
    // run per 3 minutes
    // transfer status: init => processing => done
    public function android2lab() 
    {
        $start = microtime(true);
        //log_message('error', 'transfer/android2lab: Start');
        
        $process_num = $this->process_num;
        //$process_num = 10;
        
        $q = $this->DB_android->get_where('apps_app', array('transfer_status'=>'init'), $process_num);
        foreach ( $q->result() as $app ) {
            $this->DB_android->update('apps_app', array('transfer_status'=>'processing'), array('id'=>$app->id));
            // 1. 寫入 labe DB 的 app
            // 檢查 lab DB 的 app 是否已有相同版本，有同版本的就不再新增
            $this->DB_lab->select('id');
            $this->DB_lab->where(array(
                'appPlatform' => self::GOOGLE, 
                'appPlatformId' => trim($app->play_id), 
                'version' => trim($app->current_version), 
            ));
            $q1 = $this->DB_lab->get('app');
            if ( $q1->num_rows() > 0 ) {
                // 已有，不需要再新增，直接把insert result設成true，撈出appId
                $r = true;
                $row = $q1->row();
                $appId = $row->id;
            } else {
                // 沒有，新增一筆
                $data = array(
                    'appName'       => trim($app->name) == '' ? null : trim($app->name), 
                    'appIconUrl'    => trim($app->app_icon_url) == '' ? null : trim($app->app_icon_url), 
                    'appViewUrl'    => trim($app->app_url) == '' ? null : trim($app->app_url), 
                    'appPlatform'   => self::GOOGLE, 
                    'appPlatformId' => trim($app->play_id) == '' ? null : trim($app->play_id), 
                    'category'      => trim($app->category) == '' ? null : trim($app->category), 
                    'price'         => trim($app->price) == '' ? null : trim($app->price), 
                    'version'       => trim($app->current_version) == '' ? null : trim($app->current_version), 
                    'releaseDate'   => trim($app->release_date) == '' ? null : date('Y-m-d', strtotime(trim($app->release_date))), 
                    'fileSize'      => trim($app->size) == '' ? null : trim($app->size), 
                    'supportedDevice' => null, 
                    'supportedOSVersion' => trim($app->required_android) == '' ? null : trim($app->required_android), 
                    'averageUserRating' => trim($app->rating) == '' ? null : trim($app->rating), 
                    'userRatingCount' => trim($app->rating_count) == '' ? null : trim($app->rating_count), 
                    'developer' => trim($app->developer) == '' ? null : trim($app->developer), 
                );
                
                $r = $this->DB_lab->insert('app', $data);
                $appId = $this->DB_lab->insert_id();
            }
            // 2. 寫入 lab DB 的 app_desc
            if ( $r ) {
                $this->DB_android->update('apps_app', array('transfer_status'=>'app_done'), array('id'=>$app->id));
                // 檢查是否已有相同版本相同語言，若有就不再新增
                $this->DB_lab->select('id');
                $this->DB_lab->where(array(
                    'appId' => $appId, 
                    'version' => trim($app->current_version), 
                    'language' => self::ENGLISH, 
                ));
                $q2 = $this->DB_lab->get('app_desc');
                if ( $q2->num_rows() > 0 ) {
                    // 已有，不需要再新增，直接把insert result設成true
                    $r1 = true;
                } else {
                    // 沒有，新增一筆
                    $data = array(
                        'appId' => $appId, 
                        'version' => trim($app->current_version) == '' ? null : trim($app->current_version), 
                        'language' => self::ENGLISH, 
                        'description' => trim($app->description) == '' ? null : trim($app->description), 
                        'releaseNote' => trim($app->whatsnews) == '' ? null : trim($app->whatsnews), 
                    );
                    $r1 = $this->DB_lab->insert('app_desc', $data);
                }
                if ( $r1 ) {
                    $this->DB_android->update('apps_app', array('transfer_status'=>'app_desc_done'), array('id'=>$app->id));
                    //$ss_url_arr = explode("\r\n", trim($app->screenshots_url));
                    $ss_url_arr = preg_split("/\s+/", trim($app->screenshots_url));
                    foreach ( $ss_url_arr as $ss_url ) {
                        // 檢查是否已有重複(url相同)，有重複的就不再新增
                        $this->DB_lab->select('id');
                        $this->DB_lab->where(array(
                            'url' => trim($ss_url), 
                        ));
                        $q2 = $this->DB_lab->get('screenshot');
                        if ( $q2->num_rows() > 0 ) {
                            // 已有，不需要再新增
                        } else {
                            // 沒有，新增一筆
                            $data = array(
                                'appId' => $appId, 
                                'device' => self::ANDROID, 
                                'url' => trim($ss_url) == '' ? null : trim($ss_url), 
                                's3Url' => null, 
                                'version' => trim($app->current_version) == '' ? null : trim($app->current_version), 
                            );
                            $this->DB_lab->insert('screenshot', $data);
                        }
                    }
                    $this->DB_android->update('apps_app', array('transfer_status'=>'done'), array('id'=>$app->id));
                } else {
                    $this->DB_android->update('apps_app', array('transfer_status'=>'fail'), array('id'=>$app->id));
                }
            } else {
                $this->DB_android->update('apps_app', array('transfer_status'=>'fail'), array('id'=>$app->id));
            }
        }
        
        $time = microtime(true) - $start;
        //log_message('error', "transfer/android2lab: Done in {$time} seconds");
        echo "Done! in {$time} seconds";
    }

    // transfer crawler iOS DB to lab DB : App
    // run per 1 minute
    // transfer status: init => waiting_screenshot => done
    public function ios2lab_app() 
    {
        $start = microtime(true);
        //log_message('error', 'transfer/ios2lab_app: Start');
        
        $process_num = $this->process_num;
        //$process_num = 10;
        
        $this->DB_ios->select('id, trackName, iconUrl100, artworkUrl512, trackViewUrl, trackId, primaryGenreName, description, formattedPrice, version, releaseDate, releaseNotes, fileSizeBytes, supportedDevices, averageUserRating, userRatingCount, artistName');
        $this->DB_ios->where('transfer_status', 'init');
        $q = $this->DB_ios->get('app', $process_num);
        
        foreach ($q->result() as $app) {
        
            $this->DB_ios->update('app', array('transfer_status'=>'processing'), array('id'=>$app->id));
            
            // 1. 寫入 lab DB 的 app
            // 檢查 lab DB 的 app 是否已有相同版本，有同版本的就不再新增
            $this->DB_lab->select('id');
            $this->DB_lab->where(array(
                'appPlatform' => self::APPLE, 
                'appPlatformId' => trim($app->trackId), 
                'version' => trim($app->version), 
            ));
            $q1 = $this->DB_lab->get('app');
            if ( $q1->num_rows() > 0 ) {
                // 已有，不需要再新增，直接把insert result設成true，撈出appId
                $r = true;
                $row = $q1->row();
                $appId = $row->id;
            } else {
                // 沒有，新增一筆
                $data = array(
                    //'id'            => $app->id, // 這裡是從iOS crawler移轉進來的，id讓他auto increment
                    'appName'       => $app->trackName, 
                    'appIconUrl'    => empty($app->artworkUrl512) ? $app->iconUrl100 : $app->artworkUrl512, 
                    'appViewUrl'    => $app->trackViewUrl, 
                    'appPlatform'   => self::APPLE, 
                    'appPlatformId' => $app->trackId, 
                    'category'      => $app->primaryGenreName, 
                    'price'         => $app->formattedPrice, 
                    'version'       => $app->version, 
                    'releaseDate'   => $app->releaseDate, 
                    'fileSize'      => $app->fileSizeBytes, 
                    'supportedDevice'   => $app->supportedDevices, 
                    'averageUserRating' => $app->averageUserRating, 
                    'userRatingCount'   => $app->userRatingCount, 
                    'developer'    => $app->artistName, 
                );

                $r = $this->DB_lab->insert('app', $data);
                $appId = $this->DB_lab->insert_id();
            }
        
            // 寫入 app_desc
            if ( $r ) {
                // 檢查是否已有相同版本相同語言，若有就不再新增
                $this->DB_lab->select('id');
                $this->DB_lab->where(array(
                    'appId' => $appId, 
                    'version' => trim($app->version), 
                    'language' => self::ENGLISH, 
                ));
                $q2 = $this->DB_lab->get('app_desc');
                if ( $q2->num_rows() > 0 ) {
                    // 已有，不需要再新增，直接把insert result設成true
                    $r1 = true;
                    $row = $q2->row();
                } else {
                    // 沒有，新增一筆
                    // insert into app_desc
                    $data = array(
                        'appId' => $appId, 
                        'version' => $app->version, 
                        'language' => self::ENGLISH, 
                        'description' => $app->description, 
                        'releaseNote' => $app->releaseNotes, 
                    );
                    $r1 = $this->DB_lab->insert('app_desc', $data);
                }

                if ( $r1 ) {
                    $this->DB_ios->update('app', array('transfer_status'=>'waiting_screenshot'), array('id'=>$app->id));
                } else {
                    $this->DB_ios->update('app', array('transfer_status'=>'desc_fail'), array('id'=>$app->id));
                }
                
            } else {
                $this->DB_ios->update('app', array('transfer_status'=>'app_fail'), array('id'=>$app->id));
            }
        }
        
        $time = microtime(true) - $start;
        //log_message('error', "transfer/ios2lab_app: Done in {$time} seconds");
        echo "Done! in {$time} seconds";
    }
    
    // transfer crawler iOS DB to lab DB : screenshot
    // run per 3 minutes
    // transfer status: init => done
    public function ios2lab_screenshot() 
    {
        $start = microtime(true);
        //log_message('error', 'transfer/ios2lab_screenshot: Start');
        
        $process_num = (int)($this->process_num/2);
        //$process_num = 5;
        
        $this->DB_ios->select('id, version, trackId');
        $this->DB_ios->where('transfer_status', 'waiting_screenshot');
        $q = $this->DB_ios->get('app', $process_num);

        foreach ($q->result() as $app) {
        
            // 撈出 lab DB 的 appId
            $appId = 0;
            $this->DB_lab->select('id');
            $this->DB_lab->where(array(
                'appPlatform' => self::APPLE, 
                'appPlatformId' => trim($app->trackId), 
                'version' => trim($app->version), 
            ));
            $lab_app = $this->DB_lab->get('app')->row();
            if ( is_object($lab_app) ) {
                $appId = $lab_app->id;
            }
            
            if ( $appId > 0 ) {
                // 寫入 screenshot
                $q1 = $this->DB_ios->get_where('screenshot', array('app_id'=>$app->id, 'transfer_status'=>'init'));
                
                foreach ( $q1->result() as $ss ) {
                    // 檢查是否已有重複(url相同)，有重複的就不再新增
                    $this->DB_lab->select('id');
                    $this->DB_lab->where(array(
                        'url' => trim($ss->url), 
                    ));
                    $q2 = $this->DB_lab->get('screenshot');
                    if ( $q2->num_rows() > 0 ) {
                        $r1 = true;
                    } else {
                        $data = array(
                            //'id' => $ss->id, // 這裡是從iOS crawler移轉進來的，id讓他auto increment
                            'appId' => $appId, 
                            'device' => empty($ss->device) ? null : $ss->device, 
                            'url' => empty($ss->url) ? null : $ss->url, 
                            's3Url' => empty($ss->s3_url) ? null : $ss->s3_url, 
                            'version' => empty($ss->version) ? $app->version : $ss->version, 
                            'clickCount' => isset($ss->clickCount) ? $ss->clickCount : 0, 
                            'pinCount' => isset($ss->pinCount) ? $ss->pinCount : 0, 
                            'likeCount' => isset($ss->likeCount) ? $ss->likeCount : 0, 
                            'dislikeCount' => isset($ss->dislikeCount) ? $ss->dislikeCount : 0, 
                        );
                        $r1 = $this->DB_lab->insert('screenshot', $data);
                    }

                    if ( $r1 ) {
                        $this->DB_ios->update('screenshot', array('transfer_status'=>'done'), array('id'=>$ss->id));
                    } else {
                        $this->DB_ios->update('screenshot', array('transfer_status'=>'fail'), array('id'=>$ss->id));
                    }
                }
            }
            $this->DB_ios->update('app', array('transfer_status'=>'done'), array('id'=>$app->id));
        }
        
        $time = microtime(true) - $start;
        //log_message('error', "transfer/ios2lab_screenshot: Done in {$time} seconds");
        echo "Done! in {$time} seconds";
    }
}

/* End of file transfer.php */
/* Location: ./application/controllers/transfer.php */