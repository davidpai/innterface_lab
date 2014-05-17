<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Weight_model extends CI_Model {

    const TAG_WEIGHT_MULTIPLIER = 0.4;
    const GENRE_WEIGHT_MULTIPLIER = 0.15;
    const APP_NAME_WEIGHT_MULTIPLIER = 0.14;
    const NOTE_WEIGHT_MULTIPLIER = 0.13;
    const KEYWORD_WEIGHT_MULTIPLIER = 0.07;
    //const RATING_WEIGHT_MULTIPLIER = 0.1;
    //const CLICK_WEIGHT_MULTIPLIER = 0.1;
    //const LIKE_WEIGHT_MULTIPLIER = 0.1;
    //const DISLIKE_WEIGHT_MULTIPLIER = -0.2;
    //const TIME_WEIGHT_MULTIPLIER = 0.01;

    // 整個DB的user數
    // COUNT(user.id)
    private $totalCount_user = 0;
    // 整個DB的click數總和
    // SUM(screenshot.clickCount)
    private $totalCount_userClick = 0;
    // 整個DB的like數總和
    // SUM(screenshot.likeCount)
    private $totalCount_like = 0;
    // 整個DB的dislike數總和
    // SUM(screenshot.dislikeCount)
    private $totalCount_dislike = 0;
    // App的averageUserRating總和(TcAR)
    // SUM(app.averageUserRating)
    private $totalSum_appRating = 0;
    // App的userRatingCount總和(TcDR)
    // SUM(app.userRatingCount)
    private $totalCount_appRatingCount = 0;
    // App的Rating平均值
    private $totalCount_ratingAverage = 0;
    // 屬性值: 一個自訂的調整值(TcPV)
    private $propertyValue = 900;
    // 暫定變數: 另一個可自訂的調整值(TcTV)
    private $tempVar = 30;
    
    private $time = null;
    private $start = null;
    
    function __construct() 
    {
        parent::__construct();
        
        // 計算出DB初始值
        $data = $this->db->get('weight_param')->row();
        $this->totalCount_user = (int)$data->totalCount_user;
        $this->totalCount_userClick = (int)$data->totalCount_userClick;
        $this->totalCount_like = (int)$data->totalCount_like;
        $this->totalCount_dislike = (int)$data->totalCount_dislike;
        $this->totalSum_appRating = (float)$data->totalSum_appRating;
        $this->totalCount_appRatingCount = (int)$data->totalCount_appRatingCount;
        $this->totalCount_ratingAverage = (float)$data->totalCount_ratingAverage;
        
        $this->load->model('screenshot_model', 'screenshot');
        $this->load->model('word_process_model');
        
        $this->time = microtime(true);
        $this->start = microtime(true);
    }

    public function set_param() 
    {
        $data = array();
        
        $data['totalCount_user'] = $this->db->count_all('user');
        
        $this->db->select('SUM(clickCount) AS c1, SUM(likeCount) AS c2, SUM(dislikeCount) AS c3', false);
        $r = $this->db->get('screenshot')->row();
        $data['totalCount_userClick'] = $r->c1;
        $data['totalCount_like'] = $r->c2;
        $data['totalCount_dislike'] = $r->c3;
        
        $this->db->select('SUM(averageUserRating) AS c1, SUM(userRatingCount) AS c2, SUM(averageUserRating)/COUNT(id) AS c3', false);
        $r = $this->db->get('app')->row();
        $data['totalSum_appRating'] = $r->c1;
        $data['totalCount_appRatingCount'] = $r->c2;
        $data['totalCount_ratingAverage'] = $r->c3;
        
        $this->db->truncate('weight_param');
        $this->db->insert('weight_param', $data);
    }

    function make_weight($query_term) 
    {
        $this->log_speed('weight_model/make_weight start');
        // 2015-05-02
        // 下列欄位有 keyword 的撈出來
        // 1. screenshot_tag.tag
        // 2. app.category
        // 3. app.appName
        // 4. screenshot_comment_word.word
        // 5. app_keyword.word
        //
        // 1. screenshot_tag.tag 佔權重 40% ，滿分 0.4 ，命名為 tagWeight
        //      計算方式: 0.4*(每中一個keyword)
        // 2. app.category 佔權重 15% ，滿分 0.15 ，命名為 genreWeight
        //      計算方式: 0.15*(每中一個keyword)
        // 3. app.appName 佔權重 14% ，滿分 0.14 ，命名為 appNameWeight
        //      計算方式: 0.14*(每中一個keyword)
        // 4. screenshot_comment_word.word 佔權重 13% ，滿分 0.13 ，命名為 noteWeight
        //      計算方式: 0.13*(每中一個keyword)
        // 5. app_keyword.word 佔權重 7% ，滿分 0.07 ，命名為 keywordWeight
        //      計算方式: 0.07*(每中一個keyword)
        // 6. 撈出的 screenshot 他們的 app.averageUserRating，命名為 ratingWeight
        //      計算方式: 看文件
        // 7. 撈出的 screenshot 的 clickCount，命名為 clickWeight
        //      計算方式: 看文件
        // 8. 撈出的 screenshot 的 likeCount，命名為 likeWeight
        //      計算方式: 看文件
        // 9. 撈出的 screenshot 的 dislikeCount，命名為 dislikeWeight
        //      計算方式: 看文件
        // 總分計算: 1+2+3+4+5+6+7+8+9+10 命名為 weight
        
        // 建立最後所有screenshot的容器
        $screenshot_arr = array();

        $term_arr = $this->query_term_break($query_term);
        //var_dump($term_arr); exit;
        
        // 撈出同義字陣列 $synonym_arr
        $synonym_arr = $this->find_synonym($term_arr);
        $this->log_speed('weight_model/make_weight find_synonym');
        
        // tagWeight 要再找同義字
        // 由於tag部分，整個query_term也要當作一個tag下去查詢，所以這裡多弄一個anonymous function
        // 在裡面處理term_arr和synonym_arr，避免影響外面的term_arr及synonym_arr
        // PHP 5.4以上限定
        /*
        $tagWeight = function($query_term) use (&$screenshot_arr) {
            $term_arr = $this->query_term_break($query_term);
            $term_arr[] = $query_term;
            foreach ( $term_arr as $term ) {
                $this->make_tagWeight($term, $screenshot_arr);
            }
            $synonym_arr = $this->find_synonym($term_arr);
            foreach ( $synonym_arr as $synonym ) {
                $this->make_tagWeight($synonym, $screenshot_arr);
            }
        };
        $tagWeight($query_term);
        */
        // PHP 5.3以下可用
        $term_arr1 = $this->query_term_break($query_term);
        $term_arr1[] = $query_term;
        foreach ( $term_arr1 as $term ) {
            $this->make_tagWeight($term, $screenshot_arr);
        }
        $synonym_arr1 = $this->find_synonym($term_arr1);
        foreach ( $synonym_arr1 as $synonym ) {
            $this->make_tagWeight($synonym, $screenshot_arr);
        }
        unset($term_arr1, $synonym_arr1);
        //var_dump($screenshot_arr); exit;
        $this->log_speed('weight_model/make_weight make_tagWeight');
        
        // genreWeight 不找同義字
        foreach ( $term_arr as $term ) {
            $this->make_genreWeight($term, $screenshot_arr);
        }
        //var_dump($screenshot_arr); exit;
        $this->log_speed('weight_model/make_weight make_genreWeight');
        
        // appNameWeight 不找同義字
        foreach ( $term_arr as $term ) {
            $this->make_appNameWeight($term, $screenshot_arr);
        }
        //var_dump($screenshot_arr); exit;
        $this->log_speed('weight_model/make_weight make_appNameWeight');
        
        // noteWeight 要再找同義字
        foreach ( $term_arr as $term ) {
            $this->make_noteWeight($term, $screenshot_arr);
        }
        foreach ( $synonym_arr as $synonym ) {
            $this->make_noteWeight($synonym, $screenshot_arr);
        }
        //var_dump($screenshot_arr); exit;
        $this->log_speed('weight_model/make_weight make_noteWeight');
        
        // keywordWeight 要再找同義字
        foreach ( $term_arr as $term ) {
            $this->make_keywordWeight($term, $screenshot_arr);
        }
        foreach ( $synonym_arr as $synonym ) {
            $this->make_keywordWeight($synonym, $screenshot_arr);
        }
        //var_dump($screenshot_arr); exit;
        $this->log_speed('weight_model/make_weight make_keywordWeight');
        
        $this->make_likeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;
        $this->log_speed('weight_model/make_weight make_likeWeight');

        $this->make_dislikeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;
        $this->log_speed('weight_model/make_weight make_dislikeWeight');
        
        $this->make_clickWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;
        $this->log_speed('weight_model/make_weight make_clickWeight');

        $this->make_ratingWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;
        $this->log_speed('weight_model/make_weight make_ratingWeight');

        // 2014-01-23 timeWeight暫時先不算
        //$this->make_timeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        // 計算weight總和
        foreach ( $screenshot_arr as $screenshot ) {
            $screenshot->weight = 0;
            // 1. tagWeight
            if ( isset($screenshot->tagWeight) ) {
                $screenshot->weight += $screenshot->tagWeight;
            }
            // 2. genreWeight
            if ( isset($screenshot->genreWeight) ) {
                $screenshot->weight += $screenshot->genreWeight;
            }
            // 3. appNameWeight
            if ( isset($screenshot->appNameWeight) ) {
                $screenshot->weight += $screenshot->appNameWeight;
            }
            // 4. noteWeight
            if ( isset($screenshot->noteWeight) ) {
                $screenshot->weight += $screenshot->noteWeight;
            }
            // 5. keywordWeight
            if ( isset($screenshot->keywordWeight) ) {
                $screenshot->weight += $screenshot->keywordWeight;
            }
            // 6. ratingWeight
            if ( isset($screenshot->ratingWeight) ) {
                $screenshot->weight += $screenshot->ratingWeight;
            }
            // 7. clickWeight
            if ( isset($screenshot->clickWeight) ) {
                $screenshot->weight += $screenshot->clickWeight;
            }
            // 8. likeWeight
            if ( isset($screenshot->likeWeight) ) {
                $screenshot->weight += $screenshot->likeWeight;
            }
            // 9. dislikeWeight
            if ( isset($screenshot->dislikeWeight) ) {
                $screenshot->weight += $screenshot->dislikeWeight;
            }
            // 2014-01-23 timeWeight暫時先不算
            // 10. timeWeight
            //if ( isset($screenshot->timeWeight) ) {
            //    $screenshot->weight += $screenshot->timeWeight;
            //}
        }
        //var_dump($screenshot_arr); exit;
        $this->log_speed('weight_model/make_weight make total weight');

        return $screenshot_arr;
    }
    
    // 10. timeWeight
    /*
    function make_timeWeight(&$screenshot_arr) 
    {
        $two_day = 60*60*24*2;
        $now = time();
        foreach ( $screenshot_arr as $screenshot ) {
            $time = strtotime($screenshot->releaseDate);
            $r = 5-floor(($now-$time)/$two_day);
            if ( $r > 0 ) {
                $screenshot->timeWeight = self::TIME_WEIGHT_MULTIPLIER*$r;
            } else {
                $screenshot->timeWeight = 0;
            }
        }
        unset($now, $time, $two_day, $r, $screenshot);
        //var_dump($screenshot_arr); exit;
    }
    */
    
    // 9. dislikeWeight
    // Dislike Weight (AwDI) = (AvDI*TcUS)/TcDI
    // Dislike Variable (AvDI) = -(TcPV)*(AcDI*TcUS)/TcDI
    function make_dislikeWeight(&$screenshot_arr) 
    {
        foreach ( $screenshot_arr as $screenshot ) {
            if ( $this->totalCount_dislike > 0 ) {
                $dislike_variable = (0 - $this->tempVar) * ( $screenshot->dislikeCount * $this->totalCount_user ) / $this->totalCount_dislike;
                $dislike_weight = ( $dislike_variable * $this->totalCount_user ) / $this->totalCount_dislike;
                $screenshot->dislikeWeight = $dislike_weight * $screenshot->dislikeCount;
            } else {
                $dislike_variable = 0;
                $dislike_weight = 0;
            }
        }
        unset($screenshot);
        //var_dump($screenshot_arr); exit;
    }
    
    // 8. likeWeight
    // Like Weight (AwLI) = (AvLI*TcUS)/TcLI
    // Like Variable (AvLI) = (TcPV*AcLI*TcUS)/TcLI
    // likeWeight = AwLI*AcLI
    function make_likeWeight(&$screenshot_arr) 
    {
        foreach ( $screenshot_arr as $screenshot ) {
            if ( $this->totalCount_like > 0 ) {
                $like_variable = ( $this->propertyValue * $screenshot->likeCount * $this->totalCount_user ) / $this->totalCount_like;
                $like_weight = ( $like_variable * $this->totalCount_user ) / $this->totalCount_like;
                $screenshot->likeWeight = $like_weight * $screenshot->likeCount;
            } else {
                $like_variable = 0;
                $like_weight = 0;
            }
        }
        unset($screenshot);
        //var_dump($screenshot_arr); exit;
    }
    
    // 7. clickWeight
    // Click Weight (AwCL) = (AvCL*TcUS)/TcUC
    // Click Variable (AvCL) = (TcTV*TcUS)/TcUC
    function make_clickWeight(&$screenshot_arr) 
    {
        foreach ( $screenshot_arr as $screenshot ) {
            if ( $this->totalCount_userClick > 0 ) {
                $click_variable = ( $this->tempVar * $this->totalCount_user ) / $this->totalCount_userClick;
                $click_weight = ( $click_variable * $this->totalCount_user ) / $this->totalCount_userClick;
                $screenshot->clickWeight = $click_weight * $screenshot->clickCount;
            } else {
                $click_variable = 0;
                $click_weight = 0;
            }
        }
        unset($screenshot);
        //var_dump($screenshot_arr); exit;
    }
    
    // 6. ratingWeight
    // Rating Weight (AwRA) = (AvRA*TcRA)/TcDR
    // Rating Variable (AvRA) = (TcRA-3)*TcRA*TcUS
    function make_ratingWeight(&$screenshot_arr) 
    {
        foreach ( $screenshot_arr as $screenshot ) {
            if ( isset($screenshot->averageUserRating) ) {
                $screenshot->averageUserRating = (trim($screenshot->averageUserRating)=='') ? 0 : $screenshot->averageUserRating;
            } else {
                $screenshot->averageUserRating = 0;
            }
            if ( $this->totalCount_appRatingCount > 0 ) {
                $rating_variable = ( $screenshot->averageUserRating - 3 ) * $screenshot->averageUserRating * $this->totalCount_user;
                $rating_weight = ( $rating_variable * $this->totalSum_appRating ) / $this->totalCount_appRatingCount;
                $screenshot->ratingWeight = $rating_weight;
            } else {
                $rating_variable = 0;
                $rating_weight = 0;
            }
        }
        unset($screenshot);
        //var_dump($screenshot_arr); exit;
    }
    
    // 5. app_keyword.word 佔權重 7% ，滿分 0.07 ，命名為 keywordWeight
    //      計算方式: 0.07*(每中一個keyword)
    function make_keywordWeight($term, &$screenshot_arr) 
    {
        $app_id_arr = array();

        /* 2014-05-12 David Pai */
        // 改用 MySQL 的 FULLTEXT 全文檢索功能
        // 2014-05-13 FULLTEXT 還是太慢，改回原來方法
        // BEGIN
        $this->db->select('app_id, word, frequency');
        $this->db->where('word', $term);
        $q = $this->db->get('app_keyword');
        
        if ( isset($q) && $q->num_rows() > 0 ) {
            foreach ( $q->result() as $r ) {
                $app_id_arr[$r->app_id] = $r->app_id;
            }
        }
        /*
        $this->db->select('appId');
        $this->db->where("MATCH(description) AGAINST('".$this->db->escape_str($term)."' IN BOOLEAN MODE)", NULL, FALSE);
        $q = $this->db->get('app_desc');
        
        if ( isset($q) && $q->num_rows() > 0 ) {
            foreach ( $q->result() as $r ) {
                $app_id_arr[$r->appId] = $r->appId;
            }
        }
        */
        // END

        if ( !empty($app_id_arr) ) {
            $q = $this->screenshot->get_by_app_list($app_id_arr);
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $r->keywordWeight = self::KEYWORD_WEIGHT_MULTIPLIER;
                    if ( isset($screenshot_arr[$r->id]) ) {
                        if ( isset($screenshot_arr[$r->id]->keywordWeight) ) {
                            $screenshot_arr[$r->id]->keywordWeight += $r->keywordWeight;
                        } else {
                            $screenshot_arr[$r->id]->keywordWeight = $r->keywordWeight;
                        }
                    } else {
                        $screenshot_arr[$r->id] = $r;
                    }
                }
            }
        }
        unset($q, $r, $app_id_arr);
        //var_dump($screenshot_arr); exit;
    }
    
    // 4. screenshot_comment_word.word 佔權重 13% ，滿分 0.13 ，命名為 noteWeight
    //      計算方式: 0.13*(每中一個keyword)
    function make_noteWeight($term, &$screenshot_arr) 
    {
        $screenshot_id_arr = array();

        $term = $this->word_process_model->singularize($term);
        
        $this->db->select('screenshot_id, frequency');
        $this->db->where('word', $term);
        $q = $this->db->get('screenshot_comment_word');
        
        if ( isset($q) && $q->num_rows() > 0 ) {
            foreach ( $q->result() as $r ) {
                $screenshot_id_arr[] = $r->screenshot_id;
            }
        }
        if ( !empty($screenshot_id_arr) ) {
            $q = $this->screenshot->get_by_id_in($screenshot_id_arr);
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $r->noteWeight = self::NOTE_WEIGHT_MULTIPLIER;
                    if ( isset($screenshot_arr[$r->id]) ) {
                        if ( isset($screenshot_arr[$r->id]->noteWeight) ) {
                            $screenshot_arr[$r->id]->noteWeight += $r->noteWeight;
                        } else {
                            $screenshot_arr[$r->id]->noteWeight = $r->noteWeight;
                        }
                    } else {
                        $screenshot_arr[$r->id] = $r;
                    }
                }
            }
        }
        unset($q, $r, $screenshot_id_arr);
        //var_dump($screenshot_arr); exit;
    }
    
    // 3. app.appName 佔權重 14% ，滿分 0.14 ，命名為 appNameWeight
    //      計算方式: 0.14*(每中一個keyword)
    function make_appNameWeight($term, &$screenshot_arr) 
    {
        $app_id_arr = array();

        $this->db->select('id');
        //$this->db->like('appName', $term);
        $this->db->where("MATCH(appName) AGAINST('".$this->db->escape_str($term)."*' IN BOOLEAN MODE)", NULL, FALSE);
        $q = $this->db->get('app');

        if ( isset($q) && $q->num_rows() > 0 ) {
            foreach ( $q->result() as $r ) {
                $app_id_arr[$r->id] = $r->id;
            }
        }
        unset($q, $r);
        if ( !empty($app_id_arr) ) {
            $q = $this->screenshot->get_by_app_list($app_id_arr);
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $r->appNameWeight = self::APP_NAME_WEIGHT_MULTIPLIER;
                    if ( isset($screenshot_arr[$r->id]) ) {
                        if ( isset($screenshot_arr[$r->id]->appNameWeight) ) {
                            $screenshot_arr[$r->id]->appNameWeight += $r->appNameWeight;
                        } else {
                            $screenshot_arr[$r->id]->appNameWeight = $r->appNameWeight;
                        }
                    } else {
                        $screenshot_arr[$r->id] = $r;
                    }
                }
            }
        }
        unset($q, $r, $app_id_arr);
        //var_dump($screenshot_arr); exit;
    }

    // 3. app.appName 佔權重 14% ，滿分 0.14 ，命名為 appNameWeight
    //      計算方式: 0.14*(每中一個keyword)
    function make_appNameWeight_full($term, &$screenshot_arr) 
    {
        $app_id_arr = array();

        $this->db->select('id');
        $this->db->where('appName', $term);
        $q = $this->db->get('app');

        if ( isset($q) && $q->num_rows() > 0 ) {
            foreach ( $q->result() as $r ) {
                $app_id_arr[$r->id] = $r->id;
            }
        }
        unset($q, $r);
        if ( !empty($app_id_arr) ) {
            $q = $this->screenshot->get_by_app_list($app_id_arr);
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $r->appNameWeight = self::APP_NAME_WEIGHT_MULTIPLIER;
                    if ( isset($screenshot_arr[$r->id]) ) {
                        if ( isset($screenshot_arr[$r->id]->appNameWeight) ) {
                            $screenshot_arr[$r->id]->appNameWeight += $r->appNameWeight;
                        } else {
                            $screenshot_arr[$r->id]->appNameWeight = $r->appNameWeight;
                        }
                    } else {
                        $screenshot_arr[$r->id] = $r;
                    }
                }
            }
        }
        unset($q, $r, $app_id_arr);
        //var_dump($screenshot_arr); exit;
    }
    
    
    // 2. genreWeight
    // app.category 佔權重 15% ，滿分 0.15 ，命名為 genreWeight
    //      計算方式: 0.15*(每中一個keyword)
    function make_genreWeight($term, &$screenshot_arr) 
    {
        // 2. genreWeight
        // app.category 佔權重 15% ，滿分 0.15 ，命名為 genreWeight
        //      計算方式: 0.15*(每中一個keyword)
        $app_id_arr = array();
        
        $term = $this->word_process_model->singularize($term);
        
        $this->db->select('app_list');
        $this->db->where('genre', $term);
        $q = $this->db->get('genre_app_index');
        if ( isset($q) && $q->num_rows() > 0 ) {
            foreach ( $q->result() as $r ) {
                $app_list = unserialize($r->app_list);
                if ( is_array($app_list) ) {
                    foreach ( $app_list as $app ) {
                        $app_id_arr[$app['id']] = $app['id'];
                    }
                }
            }
        }
        unset($q, $r, $app_list, $app);
        if ( !empty($app_id_arr) ) {
            $q = $this->screenshot->get_by_app_list($app_id_arr);
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $r->genreWeight = self::GENRE_WEIGHT_MULTIPLIER;
                    if ( isset($screenshot_arr[$r->id]) ) {
                        if ( isset($screenshot_arr[$r->id]->genreWeight) ) {
                            $screenshot_arr[$r->id]->genreWeight += $r->genreWeight;
                        } else {
                            $screenshot_arr[$r->id]->genreWeight = $r->genreWeight;
                        }
                    } else {
                        $screenshot_arr[$r->id] = $r;
                    }
                }
            }
        }
        unset($q, $r, $app_id_arr);
        //var_dump($screenshot_arr);
    }
    
    // 1. tagWeight
    // screenshot_tag.tag 佔權重 40% ，滿分 0.4 ，命名為 tagWeight
    //      計算方式: 0.4*(每中一個keyword)
    function make_tagWeight($term, &$screenshot_arr) 
    {
        $screenshot_id_arr = array();
        
        $this->db->select('screenshot_id, tag, frequency');
        $this->db->where('tag', $term);
        $q = $this->db->get('screenshot_tag');
        if ( isset($q) && $q->num_rows() > 0 ) {
            foreach ( $q->result() as $r ) {
                $screenshot_id_arr[] = $r->screenshot_id;
            }
        }
        unset($q, $r);
        
        if ( !empty($screenshot_id_arr) ) {
            $q = $this->screenshot->get_by_id_in($screenshot_id_arr);
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $r->tagWeight = self::TAG_WEIGHT_MULTIPLIER;
                    if ( isset($screenshot_arr[$r->id]) ) {
                        if ( isset($screenshot_arr[$r->id]->tagWeight) ) {
                            $screenshot_arr[$r->id]->tagWeight += $r->tagWeight;
                        } else {
                            $screenshot_arr[$r->id]->tagWeight = $r->tagWeight;
                        }
                    } else {
                        $screenshot_arr[$r->id] = $r;
                    }
                }
            }
        }
        unset($q, $r, $screenshot_id_arr);
        //var_dump($screenshot_arr);
    }

    // 1. tagWeight
    // screenshot_tag.tag 佔權重 40% ，滿分 0.4 ，命名為 tagWeight
    //      計算方式: 0.4*(每中一個keyword)
    function make_tagWeight_tag_and($term_arr, &$screenshot_arr) 
    {
        $screenshot_id_arr = array();

        $i = 0;
        foreach ( $term_arr as $term ) {
            $tmp_arr = array();
            $this->db->select('screenshot_id, tag, frequency');
            $this->db->where('tag', $term);
            $q = $this->db->get('screenshot_tag');
            if ( isset($q) && $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $tmp_arr[] = $r->screenshot_id;
                }
            }
            if ( $i == 0 ) {
                $screenshot_id_arr = $tmp_arr;
            } else {
                $screenshot_id_arr = array_intersect($screenshot_id_arr, $tmp_arr);
            }
            $i++;
        }
        unset($q, $r, $tmp_arr);
        
        if ( !empty($screenshot_id_arr) ) {
            $q = $this->screenshot->get_by_id_in($screenshot_id_arr);
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $r->tagWeight = self::TAG_WEIGHT_MULTIPLIER*count($term_arr);
                    if ( isset($screenshot_arr[$r->id]) ) {
                        if ( isset($screenshot_arr[$r->id]->tagWeight) ) {
                            $screenshot_arr[$r->id]->tagWeight += $r->tagWeight;
                        } else {
                            $screenshot_arr[$r->id]->tagWeight = $r->tagWeight;
                        }
                    } else {
                        $screenshot_arr[$r->id] = $r;
                    }
                }
            }
        }
        unset($q, $r, $screenshot_id_arr);
        //var_dump($screenshot_arr);
    }

    function make_weight_by_tag_or($query_term) 
    {
        // 2014-01-30
        // 下列欄位有 keyword 的撈出來
        // 1. screenshot_tag.tag 佔權重 40% ，滿分 0.4 ，命名為 tagWeight
        //      計算方式: 0.4*(每中一個keyword)
        
        // 建立最後所有screenshot的容器
        $screenshot_arr = array();

        $term_arr = $this->query_term_break($query_term, ',');
        //var_dump($term_arr); exit;
        
        // 撈出同義字陣列 $synonym_arr
        $synonym_arr = $this->find_synonym($term_arr);
        //var_dump($synonym_arr); exit;
        
        // tag_or 要再找同義字
        foreach ( $term_arr as $term ) {
            $this->make_tagWeight($term, $screenshot_arr);
        }
        foreach ( $synonym_arr as $synonym ) {
            $this->make_tagWeight($synonym, $screenshot_arr);
        }
        //var_dump($screenshot_arr); exit;
        
        $this->make_likeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        $this->make_dislikeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
        $this->make_clickWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        $this->make_ratingWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        // 2014-01-23 timeWeight暫時先不算
        //$this->make_timeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        // 計算weight總和
        foreach ( $screenshot_arr as $screenshot ) {
            $screenshot->weight = 0;
            // 1. tagWeight
            if ( isset($screenshot->tagWeight) ) {
                $screenshot->weight += $screenshot->tagWeight;
            }
            // 6. ratingWeight
            if ( isset($screenshot->ratingWeight) ) {
                $screenshot->weight += $screenshot->ratingWeight;
            }
            // 7. clickWeight
            if ( isset($screenshot->clickWeight) ) {
                $screenshot->weight += $screenshot->clickWeight;
            }
            // 8. likeWeight
            if ( isset($screenshot->likeWeight) ) {
                $screenshot->weight += $screenshot->likeWeight;
            }
            // 9. dislikeWeight
            if ( isset($screenshot->dislikeWeight) ) {
                $screenshot->weight += $screenshot->dislikeWeight;
            }
            // 2014-01-23 timeWeight暫時先不算
            // 10. timeWeight
            //if ( isset($screenshot->timeWeight) ) {
            //    $screenshot->weight += $screenshot->timeWeight;
            //}
        }
        //var_dump($screenshot_arr); exit;

        return $screenshot_arr;
    }
    
    function make_weight_by_tag_and($query_term) 
    {
        // 2014-01-30
        // 下列欄位有 keyword 的撈出來
        // 1. screenshot_tag.tag 佔權重 40% ，滿分 0.4 ，命名為 tagWeight
        //      計算方式: 0.4*(每中一個keyword)
        
        // 建立最後所有screenshot的容器
        $screenshot_arr = array();

        $term_arr = $this->query_term_break($query_term, ',');
        //var_dump($term_arr); exit;

        // tag_and 不找同義字
        $this->make_tagWeight_tag_and($term_arr, $screenshot_arr);
        //var_dump($screenshot_arr); exit;

        $this->make_likeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        $this->make_dislikeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
        $this->make_clickWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        $this->make_ratingWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        // 2014-01-23 timeWeight暫時先不算
        //$this->make_timeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        // 計算weight總和
        foreach ( $screenshot_arr as $screenshot ) {
            $screenshot->weight = 0;
            // 1. tagWeight
            if ( isset($screenshot->tagWeight) ) {
                $screenshot->weight += $screenshot->tagWeight;
            }
            // 6. ratingWeight
            if ( isset($screenshot->ratingWeight) ) {
                $screenshot->weight += $screenshot->ratingWeight;
            }
            // 7. clickWeight
            if ( isset($screenshot->clickWeight) ) {
                $screenshot->weight += $screenshot->clickWeight;
            }
            // 8. likeWeight
            if ( isset($screenshot->likeWeight) ) {
                $screenshot->weight += $screenshot->likeWeight;
            }
            // 9. dislikeWeight
            if ( isset($screenshot->dislikeWeight) ) {
                $screenshot->weight += $screenshot->dislikeWeight;
            }
            // 2014-01-23 timeWeight暫時先不算
            // 10. timeWeight
            //if ( isset($screenshot->timeWeight) ) {
            //    $screenshot->weight += $screenshot->timeWeight;
            //}
        }
        //var_dump($screenshot_arr); exit;

        return $screenshot_arr;
    }
    
    function make_weight_by_appname($query_term) 
    {
        // 2014-01-23
        // 下列欄位有 keyword 的撈出來
        // 3. app.appName 佔權重 14% ，滿分 0.14 ，命名為 appNameWeight
        //      計算方式: 0.14*(每中一個keyword)
        
        // 建立最後所有screenshot的容器
        $screenshot_arr = array();

        $term_arr = $this->query_term_break($query_term);
        //var_dump($term_arr); exit;
        
        // appNameWeight 不找同義字
        foreach ( $term_arr as $term ) {
            $this->make_appNameWeight($term, $screenshot_arr);
        }
        //var_dump($screenshot_arr); exit;

        $this->make_likeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        $this->make_dislikeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
        $this->make_clickWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        $this->make_ratingWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        // 2014-01-23 timeWeight暫時先不算
        //$this->make_timeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        // 計算weight總和
        foreach ( $screenshot_arr as $screenshot ) {
            $screenshot->weight = 0;
            // 3. appNameWeight
            if ( isset($screenshot->appNameWeight) ) {
                $screenshot->weight += $screenshot->appNameWeight;
            }
            // 6. ratingWeight
            if ( isset($screenshot->ratingWeight) ) {
                $screenshot->weight += $screenshot->ratingWeight;
            }
            // 7. clickWeight
            if ( isset($screenshot->clickWeight) ) {
                $screenshot->weight += $screenshot->clickWeight;
            }
            // 8. likeWeight
            if ( isset($screenshot->likeWeight) ) {
                $screenshot->weight += $screenshot->likeWeight;
            }
            // 9. dislikeWeight
            if ( isset($screenshot->dislikeWeight) ) {
                $screenshot->weight += $screenshot->dislikeWeight;
            }
            // 2014-01-23 timeWeight暫時先不算
            // 10. timeWeight
            //if ( isset($screenshot->timeWeight) ) {
            //    $screenshot->weight += $screenshot->timeWeight;
            //}
        }
        //var_dump($screenshot_arr); exit;

        return $screenshot_arr;
    }
    
    function make_weight_by_appname_full($query_term) 
    {
        // 2014-01-23
        // 下列欄位有 keyword 的撈出來
        // 3. app.appName 佔權重 14% ，滿分 0.14 ，命名為 appNameWeight
        //      計算方式: 0.14*(每中一個keyword)
        
        // 建立最後所有screenshot的容器
        $screenshot_arr = array();

        // appname_full 不拆分字詞
        //$term_arr = $this->query_term_break($query_term);
        //var_dump($term_arr); exit;
        
        // appname_full 不找同義字
        $this->make_appNameWeight_full($query_term, $screenshot_arr);
        //var_dump($screenshot_arr); exit;

        $this->make_likeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        $this->make_dislikeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
        $this->make_clickWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        $this->make_ratingWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        // 2014-01-23 timeWeight暫時先不算
        //$this->make_timeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        // 計算weight總和
        foreach ( $screenshot_arr as $screenshot ) {
            $screenshot->weight = 0;
            // 3. appNameWeight
            if ( isset($screenshot->appNameWeight) ) {
                $screenshot->weight += $screenshot->appNameWeight;
            }
            // 6. ratingWeight
            if ( isset($screenshot->ratingWeight) ) {
                $screenshot->weight += $screenshot->ratingWeight;
            }
            // 7. clickWeight
            if ( isset($screenshot->clickWeight) ) {
                $screenshot->weight += $screenshot->clickWeight;
            }
            // 8. likeWeight
            if ( isset($screenshot->likeWeight) ) {
                $screenshot->weight += $screenshot->likeWeight;
            }
            // 9. dislikeWeight
            if ( isset($screenshot->dislikeWeight) ) {
                $screenshot->weight += $screenshot->dislikeWeight;
            }
            // 2014-01-23 timeWeight暫時先不算
            // 10. timeWeight
            //if ( isset($screenshot->timeWeight) ) {
            //    $screenshot->weight += $screenshot->timeWeight;
            //}
        }
        //var_dump($screenshot_arr); exit;

        return $screenshot_arr;
    }
/*
    function make_weight_by_appname($syntax_mode, $query_term) 
    {
        // 建立最後所有screenshot的容器
        $screenshot_arr = array();
        // 只搜尋appname，乘數就直接設為1吧
        $weight_multipliter = 1;
        $app_id_arr = array();

        switch ( $syntax_mode ) {
            case 'appname_full':
                $this->db->select('id');
                $this->db->from('app');
                $this->db->where('appName', $query_term);
                $q = $this->db->get();
                if ( $q->num_rows() > 0 ) {
                    foreach ( $q->result() as $r ) {
                        $app_id_arr[] = $r->id;
                    }
                }
                break;
                
            case 'appname':
            default:
                $term_arr = $this->query_term_break($query_term);
                if ( is_array($term_arr) && !empty($term_arr) ) {
                    $this->db->select('id');
                    $this->db->from('app');
                    foreach ( $term_arr as $term ) {
                        $this->db->like('appName', $term);
                    }
                    $q = $this->db->get();
                }
                if ( isset($q) && $q->num_rows() > 0 ) {
                    foreach ( $q->result() as $r ) {
                        $app_id_arr[] = $r->id;
                    }
                }
                unset($term_arr, $term);
                break;
        }
        unset($q, $r);
        
        if ( !empty($app_id_arr) ) {
            $app_count = count($app_id_arr);
            $query = $this->get_by_app_list($app_id_arr);
            if ( $query->num_rows() > 0 ) {
                foreach ( $query->result() as $row ) {
                    $row->appNameWeight = round($weight_multipliter*(1/$app_count),10);
                    if ( isset($screenshot_arr[$row->id]) ) {
                        $screenshot_arr[$row->id]->appNameWeight = $row->appNameWeight;
                    } else {
                        $screenshot_arr[$row->id] = $row;
                    }
                }
            }
        }
        unset($row);
        unset($app_id_arr);
        unset($query);
        //var_dump($screenshot_arr);

        $this->make_ratingWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
        $this->make_clickWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
        $this->make_likeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
        $this->make_dislikeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
        $this->make_timeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        // 計算weight總和
        reset($screenshot_arr);
        $screenshot_count = count($screenshot_arr);
        $this->load->model('screenshot_tag_model');
        foreach ( $screenshot_arr as $screenshot_id => $screenshot ) {
            $screenshot->weight = 0;
            // 3. appNameWeight
            if ( isset($screenshot->appNameWeight) ) {
                $screenshot->weight += $screenshot->appNameWeight;
            }
            // 6. ratingWeight
            if ( isset($screenshot->ratingWeight) ) {
                $screenshot->weight += $screenshot->ratingWeight;
            }
            // 7. clickWeight
            if ( isset($screenshot->clickWeight) ) {
                $screenshot->weight += $screenshot->clickWeight;
            }
            // 8. likeWeight
            if ( isset($screenshot->likeWeight) ) {
                $screenshot->weight += $screenshot->likeWeight;
            }
            // 9. dislikeWeight
            if ( isset($screenshot->dislikeWeight) ) {
                $screenshot->weight += $screenshot->dislikeWeight;
            }
            // 10. timeWeight
            if ( isset($screenshot->timeWeight) ) {
                $screenshot->weight += $screenshot->timeWeight;
            }
        }
        //var_dump($screenshot_arr); exit;

        return $screenshot_arr;
    }
*/
    function make_weight_by_trackid_list($trackid_list) 
    {
        // 建立最後所有screenshot的容器
        $screenshot_arr = array();
        
        $q = $this->get_by_app_trackid_list($trackid_list);
        if ( $q->num_rows() > 0 ) {
            foreach ( $q->result() as $row ) {
                $screenshot_arr[] = $row;
            }
        }
        unset($q, $row);
        
        $this->make_clickWeight($screenshot_arr);
        $this->make_likeWeight($screenshot_arr);
        $this->make_dislikeWeight($screenshot_arr);
        $this->make_timeWeight($screenshot_arr);
        //var_dump($screenshot_arr); exit;

        // 計算weight總和
        $this->load->model('screenshot_tag_model');
        foreach ( $screenshot_arr as $screenshot ) {
            $screenshot->weight = 0;
            // 7. clickWeight
            if ( isset($screenshot->clickWeight) ) {
                $screenshot->weight += $screenshot->clickWeight;
            }
            // 8. likeWeight
            if ( isset($screenshot->likeWeight) ) {
                $screenshot->weight += $screenshot->likeWeight;
            }
            // 9. dislikeWeight
            if ( isset($screenshot->dislikeWeight) ) {
                $screenshot->weight += $screenshot->dislikeWeight;
            }
            // 10. timeWeight
            if ( isset($screenshot->timeWeight) ) {
                $screenshot->weight += $screenshot->timeWeight;
            }
        }
        //var_dump($screenshot_arr); exit;
        
        return $screenshot_arr;
    }
    
    /**
     * 將傳入的 screenshot array 再去撈出他們的 tag 組成 tag_list 變成每個 screenshot 的 property
     * 
     * @access public
     * @param  array  已計算好權重分數的 screenshot array
     * @return array  (by reference) 已加上 tag_list 的 screenshot array
     */
    function add_tag_list(&$screenshot_arr) 
    {
        if ( is_array($screenshot_arr) && !empty($screenshot_arr) ) {
        } else {
            return;
        }
        
        $this->load->model('screenshot_tag_model');
        foreach ( $screenshot_arr as $ss ) {
            $ss_tag_arr = $this->screenshot_tag_model->get_by_screenshot($ss->id);
            $tag_list = array();
            foreach ( $ss_tag_arr as $ss_tag ) {
                $tag_list[] = $ss_tag->tag;
            }
            $ss->tag_list = implode(',', $tag_list);
        }
    }
    
    /**
     * 將傳入的 screenshot array 再去撈出他們的 pinCount, likeCount, dislikeCount
     * 
     * @access public
     * @param  array  已計算好權重分數的 screenshot array
     * @return array  (by reference) 已加上 pinCount, likeCount, dislikeCount 的 screenshot array
     */
    function add_count_number(&$screenshot_arr) 
    {
        if ( is_array($screenshot_arr) && !empty($screenshot_arr) ) {
        } else {
            return;
        }
        $ss_id_arr = array();
        foreach ( $screenshot_arr as $ss ) {
            $ss_id_arr[] = $ss->id;
        }
        $this->db->select('id, pinCount, likeCount, dislikeCount');
        $this->db->from('screenshot');
        $this->db->where_in('id', $ss_id_arr);
        $q = $this->db->get();
        foreach ( $q->result() as $r ) {
            $screenshot_arr[$r->id]->pinCount = $r->pinCount;
            $screenshot_arr[$r->id]->likeCount = $r->likeCount;
            $screenshot_arr[$r->id]->dislikeCount = $r->dislikeCount;
        }
        //var_dump($screenshot_arr); exit;
    }

    /**
     * query term 的拆分
     * 允許的分隔字元為逗號(,)和空白

     * @access protected 
     * @param  string $query_term
     * @param  string $seperater
     * @return array 
     */
    public function query_term_break($query_term, $seperater=null) 
    {
        $term_arr = array();
        if ( $seperater === null ) {
            if ( strpos($query_term, ',') === false ) {
                $term_arr = explode(' ', $query_term);
            } else {
                $term_arr = explode(',', $query_term);
            }
        } else {
            $term_arr = explode($seperater, $query_term);
        }
        return $term_arr;
    }
    
    public function find_synonym($term_arr) 
    {
        $synonym_arr = array();
        
        if ( is_array($term_arr) && !empty($term_arr) ) {
            $this->load->model('synonym_word_model', 'synonym');
            foreach ( $term_arr as $term ) {
                $synonym_arr = $this->synonym->get_synonym($term, $synonym_arr);
            }
            foreach ( $term_arr as $term ) {
                if ( isset($synonym_arr[$term]) ) {
                    unset($synonym_arr[$term]);
                }
            }
        }
        
        return $synonym_arr;
    }
    
    protected function log_speed($text) {
        $time = microtime(true) - $this->time;
        $this->time = microtime(true);
        log_message('debug', "{$text} in {$time} seconds");
    }
}

/* End of file screenshot_model.php */
/* Location: ./application/models/screenshot_model.php */