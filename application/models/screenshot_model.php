<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'basic_model.php';

class Screenshot_model extends Basic_Model {

    const TAG_WEIGHT_MULTIPLIER = 0.25;
    const GENRE_WEIGHT_MULTIPLIER = 0.1;
    const APP_NAME_WEIGHT_MULTIPLIER = 0.1;
    const NOTE_WEIGHT_MULTIPLIER = 0.1;
    const KEYWORD_WEIGHT_MULTIPLIER = 0.1;
    const RATING_WEIGHT_MULTIPLIER = 0.1;
    const CLICK_WEIGHT_MULTIPLIER = 0.1;
    const LIKE_WEIGHT_MULTIPLIER = 0.1;
    const DISLIKE_WEIGHT_MULTIPLIER = -0.2;
    const TIME_WEIGHT_MULTIPLIER = 0.01;

    function __construct() 
    {
        parent::__construct();
        $this->table_name = 'screenshot';
    }
    
    /**
     * 檢查URL所指向的圖擋是否存在
     */
    function is_url_exist($url)
    {
        $upload_file = $this->config->item('upload_file');
        $pos = strpos($url, $upload_file);
        if ( preg_match('#^(http|https)://#i', $url) === 1 ) {
            // 為了節省request
            // 只檢查內部的圖檔是否存在，若是外部的URL，則自動視為存在
            return TRUE;
        } else {
            $url = substr($url, $pos);
            return is_readable($url);
        }
    }

    function make_weight($query_term) 
    {
        // 2014-01-13
        // 下列欄位有 keyword 的撈出來
        // 1. screenshot_tag.tag
        // 2. app.category
        // 3. app.appName
        // 4. screenshot_comment_word.word
        // 5. app_keyword.word
        //
        // 1. screenshot_tag.tag 佔權重 25% ，滿分 0.25 ，命名為 tagWeight
        //      計算方式: 0.25*(該項的frequency/全部撈出的frequency總和)
        // 2. app.category 佔權重 10% ，滿分 0.1 ，命名為 genreWeight
        //      計算方式: 有撈到就給0.1
        // 3. app.appName 佔權重 10% ，滿分 0.1 ，命名為 appNameWeight
        //      計算方式: 有撈到就給0.1
        // 4. screenshot_comment_word.word 佔權重 10% ，滿分 0.1 ，命名為 noteWeight
        //      計算方式: 0.1*(該項的frequency/全部撈出的frequency總和)
        // 5. app_keyword.word 佔權重 10% ，滿分 0.1 ，命名為 keywordWeight
        //      計算方式: 0.1*(該項的frequency/全部撈出的frequency總和)
        // 6. 撈出的 screenshot 他們的 app.averageUserRating 佔權重 10% ，滿分 0.1 ，命名為 ratingWeight
        //      計算方式: 0.1*(該app的rating分/rating滿分5) (該app的rating分大於3的加分，小於3的扣分)
        // 7. 撈出的 screenshot 的 clickCount ，佔權重 10% ，滿分 0.1 ，命名為 clickWeight
        //      計算方式: 0.1*(該screenshot的click數/撈出的click數總和)
        // 8. 撈出的 screenshot 的 likeCount ，佔權重 10% ，滿分 0.1 ，命名為 likeWeight
        //      計算方式: 0.1*(該screenshot的like數/撈出的like數總和)
        // 9. 撈出的 screenshot 的 dislikeCount ，額外扣減 20% ，扣減最高 -0.2 ，命名為 dislikeWeight
        //      計算方式: -0.2*(該screenshot的dislike數/撈出的dislike數總和)
        // 10. 撈出的 screenshot 其 app 上架時間與當下的時間間隔，佔權重 5% ，滿分 0.05 ，命名為 timeWeight
        //      計算方式: 0.01*(5-floor(間隔天數/2))
        // 總分計算: 1+2+3+4+5+6+7+8+9+10 命名為 weight
        
        // 建立最後所有screenshot的容器
        $screenshot_arr = array();

        $term_arr = $this->query_term_break($query_term);
        //var_dump($term_arr); exit;
        
        // 撈出同義字陣列 $synonym_arr
        $this->load->model('synonym_word_model');
        $synonym_arr = array();
        foreach ( $term_arr as $term ) {
            $synonym_arr = $this->synonym_word_model->get_synonym($term, $synonym_arr);
        }
        foreach ( $term_arr as $term ) {
            if ( isset($synonym_arr[$term]) ) {
                unset($synonym_arr[$term]);
            }
        }
        //var_dump($synonym_arr); exit;
        
        // tagWeight 要再找同義字
        $this->make_tagWeight($term_arr, $screenshot_arr);
        $this->make_tagWeight($synonym_arr, $screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
        // genreWeight 不找同義字
        $this->make_genreWeight($term_arr, $screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
        // appNameWeight 不找同義字
        $this->make_appNameWeight($term_arr, $screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
        // noteWeight 要再找同義字
        $this->make_noteWeight($term_arr, $screenshot_arr);
        $this->make_noteWeight($synonym_arr, $screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
        // keywordWeight 要再找同義字
        $this->make_keywordWeight($term_arr, $screenshot_arr);
        $this->make_keywordWeight($synonym_arr, $screenshot_arr);
        //var_dump($screenshot_arr); exit;
        
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
        $this->load->model('screenshot_tag_model');
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
            // 10. timeWeight
            if ( isset($screenshot->timeWeight) ) {
                $screenshot->weight += $screenshot->timeWeight;
            }
        }
        //var_dump($screenshot_arr); exit;

        return $screenshot_arr;
    }
    
    // 10. 撈出的 screenshot 其 app 上架時間與當下的時間間隔，佔權重 5% ，滿分 0.05 ，命名為 timeWeight
    //      計算方式: 0.01*(5-floor(間隔天數/2))
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
    
    // 9. 撈出的 screenshot 的 dislikeCount ，額外扣減 20% ，扣減最高 -0.2 ，命名為 dislikeWeight
    //      計算方式: -0.2*(該screenshot的dislike數/撈出的dislike數總和)
    function make_dislikeWeight(&$screenshot_arr) 
    {
        $dislike_sum = 0;
        foreach ( $screenshot_arr as $screenshot) {
            $dislike_sum += $screenshot->dislikeCount;
        }
        foreach ( $screenshot_arr as $screenshot) {
            // DISLIKE_WEIGHT_MULTIPLIER 已設為 -0.2 這裡不要再用0去減
            if ( $dislike_sum > 0 && $screenshot->dislikeCount > 0 ) {
                $screenshot->dislikeWeight = round(self::DISLIKE_WEIGHT_MULTIPLIER*($screenshot->dislikeCount/$dislike_sum),10);
            } else {
                $screenshot->dislikeWeight = 0;
            }
        }
        unset($dislike_sum, $screenshot);
        //var_dump($screenshot_arr); exit;
    }
    
    // 8. 撈出的 screenshot 的 likeCount ，佔權重 10% ，滿分 0.1 ，命名為 likeWeight
    //      計算方式: 0.1*(該screenshot的like數/撈出的like數總和)
    function make_likeWeight(&$screenshot_arr) 
    {
        $like_sum = 0;
        foreach ( $screenshot_arr as $screenshot) {
            $like_sum += $screenshot->likeCount;
        }
        foreach ( $screenshot_arr as $screenshot) {
            if ( $like_sum > 0 && $screenshot->likeCount > 0 ) {
                $screenshot->likeWeight = round(self::LIKE_WEIGHT_MULTIPLIER*($screenshot->likeCount/$like_sum),10);
            } else {
                $screenshot->likeWeight = 0;
            }
        }
        unset($like_sum, $screenshot);
        //var_dump($screenshot_arr); exit;
    }
    
    // 7. 撈出的 screenshot 的 clickCount ，佔權重 10% ，滿分 0.1 ，命名為 clickWeight
    //      計算方式: 0.1*(該screenshot的click數/撈出的click數總和)
    function make_clickWeight(&$screenshot_arr) 
    {
        $click_sum = 0;
        foreach ( $screenshot_arr as $screenshot) {
            $click_sum += $screenshot->clickCount;
        }
        foreach ( $screenshot_arr as $screenshot) {
            if ( $click_sum > 0 && $screenshot->clickCount > 0 ) {
                $screenshot->clickWeight = round(self::CLICK_WEIGHT_MULTIPLIER*($screenshot->clickCount/$click_sum),10);
            } else {
                $screenshot->clickWeight = 0;
            }
        }
        unset($click_sum, $screenshot);
        //var_dump($screenshot_arr); exit;
    }
    
    // 6. 撈出的 screenshot 他們的 app.averageUserRating 佔權重 10% ，滿分 0.1 ，命名為 ratingWeight
    //      計算方式: 0.1*(該app的rating分/全部app的rating總和) (該app的rating分大於3的加分，小於3的扣分)
    function make_ratingWeight(&$screenshot_arr) 
    {
        $user_rating_sum = 0;
        foreach ( $screenshot_arr as $screenshot ) {
            $user_rating_sum += isset($screenshot->averageUserRating) ? $screenshot->averageUserRating : 0;
        }
        foreach ( $screenshot_arr as $screenshot ) {
            if ( $user_rating_sum > 0 ) {
                if ( isset($screenshot->averageUserRating) ) {
                    if ( $screenshot->averageUserRating >= 3 ) {
                        $screenshot->ratingWeight = round(self::RATING_WEIGHT_MULTIPLIER*($screenshot->averageUserRating/$user_rating_sum),10);
                    } else {
                        $screenshot->ratingWeight = 0-round(self::RATING_WEIGHT_MULTIPLIER*($screenshot->averageUserRating/$user_rating_sum),10);
                    }
                } else {
                    $screenshot->ratingWeight = 0;
                }
            } else {
                $screenshot->ratingWeight = 0;
            }
        }
        unset($screenshot);
        //var_dump($screenshot_arr); exit;
    }
    
    // 5. app_keyword.word
    // 計算方式: 0.1*(該項的frequency/全部撈出的frequency總和)
    // 多 term 是 A & B or 0
    function make_keywordWeight($term_arr, &$screenshot_arr) 
    {
        $frequency_sum = 0;
        $app_frequency = array();
        $app_id_arr = array();
        if ( is_array($term_arr) && !empty($term_arr) ) {
            foreach ( $term_arr as $term ) {
                $id_arr = array();
                $this->db->select('app_id, word, frequency');
                $this->db->from('app_keyword');
                $this->db->where('word', $term);
                $q = $this->db->get();
                if ( $q->num_rows() > 0 ) {
                    foreach ( $q->result() as $r ) {
                        $id_arr[] = $r->app_id;
                        if ( isset($app_frequency[$r->app_id]) ) {
                            $app_frequency[$r->app_id] += (int)$r->frequency;
                        } else {
                            $app_frequency[$r->app_id] = (int)$r->frequency;
                        }
                    }
                }
                if ( empty($app_id_arr) ) {
                    $app_id_arr = $id_arr;
                } else {
                    $app_id_arr = array_intersect($app_id_arr, $id_arr);
                }
            }
        }
        foreach ( $app_id_arr as $app_id ) {
            $frequency_sum += $app_frequency[$app_id];
        }
        
        $app_frequency_rate = array();
        foreach ( $app_id_arr as $app_id ) {
            $app_frequency_rate[$app_id] = $app_frequency[$app_id]/$frequency_sum;
        }
        
        if ( !empty($app_id_arr) ) {
            $q = $this->get_by_app_list($app_id_arr);
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $r->keywordWeight = round(self::KEYWORD_WEIGHT_MULTIPLIER*$app_frequency_rate[$r->app_id],10);
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
        unset($q, $r, $app_id_arr, $frequency_sum, $app_frequency_rate);
        //var_dump($screenshot_arr); exit;
    }
    
    // 4.screenshot_comment_word.word
    // 計算方式: 0.1*(該項的frequency/全部撈出的frequency總和)
    // 多 term 是 A or B
    function make_noteWeight($term_arr, &$screenshot_arr) 
    {
        $screenshot_id_arr = array();
        if ( is_array($term_arr) && !empty($term_arr) ) {
            $this->db->select('screenshot_id, frequency');
            $this->db->from('screenshot_comment_word');
            $i=0;
            foreach ( $term_arr as $term ) {
                // 轉為單數形，因為 table 裡面的 word 已經單數形化了
                $term = $this->word_process_model->singularize($term);
                if ( $i == 0 ) {
                    $this->db->where('word', $term);
                } else {
                    $this->db->or_where('word', $term);
                }
                $i++;
            }
            $q = $this->db->get();
        }
        if ( isset($q) && $q->num_rows() > 0 ) {
            $frequency_sum = 0;
            foreach ( $q->result() as $r ) {
                $frequency_sum += $r->frequency;
            }
            $screenshot_frequency_rate = array();
            foreach ( $q->result() as $r ) {
                $screenshot_id_arr[] = $r->screenshot_id;
                $screenshot_frequency_rate[$r->screenshot_id] = $r->frequency/$frequency_sum;
            }
        }
        if ( !empty($screenshot_id_arr) ) {
            $q = $this->get_by_id_in($screenshot_id_arr);
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $r->noteWeight = round(self::NOTE_WEIGHT_MULTIPLIER*$screenshot_frequency_rate[$r->id],10);
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
        unset($q, $r, $frequency_sum, $screenshot_frequency_rate, $screenshot_id_arr);
        //var_dump($screenshot_arr); exit;
    }
    
    // 3. app.appName 佔權重 10% ，滿分 0.1 ，命名為 appNameWeight
    //      計算方式: 有撈到就給0.1
    // 多 term 是 A & B or 0
    function make_appNameWeight($term_arr, &$screenshot_arr) 
    {
        $app_id_arr = array();
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
        unset($q, $r);
        if ( !empty($app_id_arr) ) {
            $q = $this->get_by_app_list($app_id_arr);
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $r->appNameWeight = self::APP_NAME_WEIGHT_MULTIPLIER;
                    if ( isset($screenshot_arr[$r->id]) ) {
                        $screenshot_arr[$r->id]->appNameWeight = $r->appNameWeight;
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
    // 計算方式: 有撈到就給0.1
    // 多 term 是 A or B
    function make_genreWeight($term_arr, &$screenshot_arr) 
    {
        // 2. genreWeight
        // 計算方式: 有撈到就給0.1
        $app_id_arr = array();
        if ( is_array($term_arr) && !empty($term_arr) ) {
            $this->load->model('word_process_model');
            $this->db->select('app_list');
            $this->db->from('genre_app_index');
            $i=0;
            foreach ( $term_arr as $term ) {
                // 轉為單數形，因為 genre_app_index 裡面已經單數形化了
                $term = $this->word_process_model->singularize($term);
                if ( $i == 0 ) {
                    $this->db->where('genre', $term);
                } else {
                    $this->db->or_where('genre', $term);
                }
                $i++;
            }
            $q = $this->db->get();
        }
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
            $q = $this->get_by_app_list($app_id_arr);
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $r->genreWeight = self::GENRE_WEIGHT_MULTIPLIER;
                    if ( isset($screenshot_arr[$r->id]) ) {
                        $screenshot_arr[$r->id]->genreWeight = $r->genreWeight;
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
    // 計算方式: 0.25*(該項的frequency/全部撈出的frequency總和)
    // 多 term 是 A or B
    function make_tagWeight($term_arr, &$screenshot_arr) 
    {
        // 1. tagWeight
        // 計算方式: 0.25*(該項的frequency/全部撈出的frequency總和)
        $screenshot_id_arr = array();
        if ( is_array($term_arr) && !empty($term_arr) ) {
            $this->db->select('screenshot_id, tag, frequency');
            $this->db->from('screenshot_tag');
            $i=0;
            foreach ( $term_arr as $term ) {
                if ( $i == 0 ) {
                    $this->db->where('tag', $term);
                } else {
                    $this->db->or_where('tag', $term);
                }
                $i++;
            }
            $q = $this->db->get();
        }
        if ( isset($q) && $q->num_rows() > 0 ) {
            $frequency_sum = 0;
            foreach ( $q->result() as $r ) {
                $frequency_sum += $r->frequency;
            }
            $screenshot_frequency_rate = array();
            foreach ( $q->result() as $r ) {
                $screenshot_id_arr[] = $r->screenshot_id;
                $screenshot_frequency_rate[$r->screenshot_id] = ($frequency_sum > 0) ? $r->frequency/$frequency_sum : 0;
            }
        }
        unset($q, $r);
        if ( !empty($screenshot_id_arr) ) {
            $q = $this->get_by_id_in($screenshot_id_arr);
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $r ) {
                    $r->tagWeight = round(self::TAG_WEIGHT_MULTIPLIER*$screenshot_frequency_rate[$r->id],10);
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
    
    function make_weight_by_tag($keyword, $match_mode='partial') 
    {
        // $match_mode 有 'partial' 和 'full' 兩種
        // partial 用 like
        // full 用 =
        
        // 建立最後所有screenshot的容器
        $screenshot_arr = array();
        
        // 只搜尋tag，乘數就直接設為1吧
        $weight_multipliter = 1;
        
        // 以下演算法和make_weight_by_keyword()的不同，請勿搞混
        $screenshot_id_arr = array();
        $this->db->select('screenshot_id, frequency');
        $this->db->from('screenshot_tag');
        switch ( $match_mode ) {
            case 'full':
                $this->db->where('tag', $keyword);
                break;
            default:
            case 'partial':
                $this->db->like('tag', $keyword);
                break;
        }
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            $frequency_sum = 0;
            $screenshot_frequency = array();
            foreach ( $query->result() as $row ) {
                $frequency_sum += $row->frequency;
                if ( isset($screenshot_frequency[$row->screenshot_id]) ) {
                    $screenshot_frequency[$row->screenshot_id] += $row->frequency;
                } else {
                    $screenshot_frequency[$row->screenshot_id] = $row->frequency;
                }
            }
            $screenshot_frequency_rate = array();
            foreach ( $query->result() as $row ) {
                $screenshot_id_arr[$row->screenshot_id] = $row->screenshot_id;
                $screenshot_frequency_rate[$row->screenshot_id] = $screenshot_frequency[$row->screenshot_id]/$frequency_sum;
            }
        }
        if ( !empty($screenshot_id_arr) ) {
            $query = $this->get_by_id_in($screenshot_id_arr);
            if ( $query->num_rows() > 0 ) {
                foreach ( $query->result() as $row ) {
                    $row->tagWeight = round($weight_multipliter*$screenshot_frequency_rate[$row->id],10);
                    if ( isset($screenshot_arr[$row->id]) ) {
                        $screenshot_arr[$row->id]->tagWeight = $row->tagWeight;
                    } else {
                        $screenshot_arr[$row->id] = $row;
                    }
                }
            }
        }
        unset($row);
        unset($screenshot_id_arr);
        unset($query);
        //var_dump($screenshot_arr); exit;

        // 計算weight總和
        reset($screenshot_arr);
        $screenshot_count = count($screenshot_arr);
        $this->load->model('screenshot_tag_model');
        foreach ( $screenshot_arr as $screenshot_id => $screenshot ) {
            // 最後調整
            $screenshot->weight = 0;
            if ( isset($screenshot->tagWeight) ) {
                $screenshot->tagWeight = round($screenshot->tagWeight/$screenshot_count,10);
                $screenshot->weight += $screenshot->tagWeight;
            }
        }

        //var_dump($screenshot_arr); exit;
        return $screenshot_arr;
    }
    
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
    
    /*
    function make_weight_by_appname($keyword, $match_mode='partial') 
    {
        // $match_mode 有 'partial' 和 'full' 兩種
        
        // 建立最後所有screenshot的容器
        $screenshot_arr = array();
        
        // 1.app.appName有keyword的(like)，佔整體權重10%，滿分0.1，命名為appNameWeight
        // 只搜尋appname，乘數就直接設為1吧
        $weight_multipliter = 1;
        
        $app_id_arr = array();
        $this->db->select('id');
        $this->db->from('app');
        switch ( $match_mode ) {
            case 'full':
                $this->db->where('appName', $keyword);
                break;
            default:
            case 'partial':
                $this->db->like('appName', $keyword);
                break;
        }
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            foreach ( $query->result() as $row ) {
                $app_id_arr[] = $row->id;
            }
        }
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

        // 計算weight總和
        reset($screenshot_arr);
        $screenshot_count = count($screenshot_arr);
        $this->load->model('screenshot_tag_model');
        foreach ( $screenshot_arr as $screenshot_id => $screenshot ) {
            // 最後調整
            $screenshot->weight = 0;
            if ( isset($screenshot->appNameWeight) ) {
                $screenshot->appNameWeight = round($screenshot->appNameWeight/$screenshot_count,10);
                $screenshot->weight += $screenshot->appNameWeight;
            }
            // 再抓出該screenshot的tag，做成逗號相連的tag_list
            $screenshot_tag_arr = $this->screenshot_tag_model->get_by_screenshot($screenshot_id);
            $tag_list = array();
            foreach ( $screenshot_tag_arr as $screenshot_tag ) {
                $tag_list[] = $screenshot_tag->tag;
            }
            $screenshot->tag_list = implode(',', $tag_list);
        }
        
        // 排序: 依weight高->低排序
        usort($screenshot_arr, function($a, $b) {
            if ( $a->weight == $b->weight ) {
                return 0;
            }
            return ( $a->weight < $b->weight ) ? 1 : -1;
        });

        //var_dump($screenshot_arr);
        return $screenshot_arr;
    }
    */
    function make_weight_by_keyword($keyword)
    {
        $keyword = (string)$keyword;
        
        // 2013/4/1
        // 搜尋因素, 符合下列條件的撈出來
        // 1.app.appName有keyword的
        // 2.app.category有keyword的
        // 3.app_keyword.word有keyword的
        // 4.screenshot_tag.tag有keyword的
        // 7.screenshot_comment_word.word有keyword的
        //
        // 權重排序因素
        // 假設權重總和為100%，滿分1
        // 1.app.appName有keyword的，佔整體權重12%，滿分0.12，命名為appNameWeight
        //      計算方法: 0.12*(1/撈出的app數)
        // 2.app.category有keyword的，佔整體權重13%，滿分0.13，命名為genreNameWeight
        //      計算方法: 0.13*(1/撈出的app數)
        // 3.app_keyword.word有keyword的，佔整體權重10%，滿分0.1，命名為keywordWeight
        //      計算方法: 0.1*(該項的frequency/撈出的frequency總和)
        // 4.screenshot_tag.tag有keyword的，佔整體權重30%，滿分0.3，命名為tagWeight
        //      計算方法: 0.3*(該項的frequency/撈出的frequency總和)
        // 5.screenshot_comment_word.word有keyword的，占整體權重12%，滿分0.12，命名為noteWeight
        //      計算方法: 0.12*(該項的frequency/撈出的frequency總和)
        // 6.撈出的app.averageUserRating，佔整體權重8%，滿分0.08，命名為ratingWeight
        //      計算方法: 0.08*(該app的rating分/rating滿分5)
        // 7.撈出的screenshot.clickCount，佔整體權重15%，滿分0.15，命名為clickWeight
        //      計算方法: 0.15*(該screenshot的click數/撈出的click數總和)
        // 以上7項總得分加總，命名為weight，依weight高->低排序
        
        // 建立最後所有screenshot的容器
        $screenshot_arr = array();

        // 1.app.appName有keyword的(like)，佔整體權重12%，滿分0.12，命名為appNameWeight
        // 訂好乘數
        $weight_multipliter = 0.12;
        
        $app_id_arr = array();
        $this->db->select('id');
        $this->db->from('app');
        $this->db->like('appName', $keyword);
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            foreach ( $query->result() as $row ) {
                $app_id_arr[] = $row->id;
            }
        }
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
        //var_dump($screenshot_arr); exit;

        // 2.app.category有keyword的(like)，佔整體權重13%，滿分0.13，命名為genreNameWeight
        // 訂好乘數
        $weight_multipliter = 0.13;
        
        $app_id_arr = array();
        $this->db->select();
        $this->db->from('app');
        $this->db->like('category', $keyword);
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            foreach ( $query->result() as $row ) {
                $app_id_arr[] = $row->id;
            }
        }
        if ( !empty($app_id_arr) ) {
            $app_count = count($app_id_arr);
            $query = $this->get_by_app_list($app_id_arr);
            if ( $query->num_rows() > 0 ) {
                foreach ( $query->result() as $row ) {
                    $row->genreNameWeight = round($weight_multipliter*(1/$app_count),10);
                    if ( isset($screenshot_arr[$row->id]) ) {
                        $screenshot_arr[$row->id]->genreNameWeight = $row->genreNameWeight;
                    } else {
                        $screenshot_arr[$row->id] = $row;
                    }
                }
            }
        }
        unset($row);
        unset($app_id_arr);
        unset($query);
        //var_dump($screenshot_arr); exit;

        // 3.app_keyword.word有keyword的(等於)，佔整體權重10%，滿分0.1，命名為keywordWeight
        //      計算方法: 0.1*(該項的frequency/撈出的frequency總和)
        $weight_multipliter = 0.1;
        
        $app_id_arr = array();
        $this->db->select('app_id, frequency');
        $this->db->from('app_keyword');
        $this->db->where('word', $keyword);
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            $frequency_sum = 0;
            foreach ( $query->result() as $row ) {
                $frequency_sum += $row->frequency;
            }
            $app_frequency_rate = array();
            foreach ( $query->result() as $row ) {
                $app_id_arr[] = $row->app_id;
                $app_frequency_rate[$row->app_id] = $row->frequency/$frequency_sum;
            }
        }
        if ( !empty($app_id_arr) ) {
            $query = $this->get_by_app_list($app_id_arr);
            if ( $query->num_rows() > 0 ) {
                foreach ( $query->result() as $row ) {
                    $row->keywordWeight = round($weight_multipliter*$app_frequency_rate[$row->app_id],10);
                    if ( isset($screenshot_arr[$row->id]) ) {
                        $screenshot_arr[$row->id]->keywordWeight = $row->keywordWeight;
                    } else {
                        $screenshot_arr[$row->id] = $row;
                    }
                }
            }
        }
        unset($row);
        unset($app_id_arr);
        unset($query);
        //var_dump($screenshot_arr); exit;

        // 4.screenshot_tag.tag有keyword的(like)，佔整體權重30%，滿分0.3，命名為tagWeight
        //      計算方法: 0.3*(該項的frequency/撈出的frequency總和)
        $weight_multipliter = 0.3;
        
        $screenshot_id_arr = array();
        $this->db->select('screenshot_id, frequency');
        $this->db->from('screenshot_tag');
        $this->db->like('tag', $keyword);
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            $frequency_sum = 0;
            foreach ( $query->result() as $row ) {
                $frequency_sum += $row->frequency;
            }
            $screenshot_frequency_rate = array();
            foreach ( $query->result() as $row ) {
                $screenshot_id_arr[] = $row->screenshot_id;
                $screenshot_frequency_rate[$row->screenshot_id] = $row->frequency/$frequency_sum;
            }
        }
        if ( !empty($screenshot_id_arr) ) {
            $query = $this->get_by_id_in($screenshot_id_arr);
            if ( $query->num_rows() > 0 ) {
                foreach ( $query->result() as $row ) {
                    $row->tagWeight = round($weight_multipliter*$screenshot_frequency_rate[$row->id],10);
                    if ( isset($screenshot_arr[$row->id]) ) {
                        $screenshot_arr[$row->id]->tagWeight = $row->tagWeight;
                    } else {
                        $screenshot_arr[$row->id] = $row;
                    }
                }
            }
        }
        unset($row);
        unset($screenshot_id_arr);
        unset($query);
        //var_dump($screenshot_arr); exit;

        // 5.screenshot_comment_word.word有keyword的，占整體權重12%，滿分0.12，命名為noteWeight
        //      計算方法: 0.12*(該項的frequency/撈出的frequency總和)
        $weight_multipliter = 0.12;
        
        $screenshot_id_arr = array();
        $this->db->select('screenshot_id, frequency');
        $this->db->from('screenshot_comment_word');
        $this->db->where('word', $keyword);
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            $frequency_sum = 0;
            foreach ( $query->result() as $row ) {
                $frequency_sum += $row->frequency;
            }
            $screenshot_frequency_rate = array();
            foreach ( $query->result() as $row ) {
                $screenshot_id_arr[] = $row->screenshot_id;
                $screenshot_frequency_rate[$row->screenshot_id] = $row->frequency/$frequency_sum;
            }
        }
        if ( !empty($screenshot_id_arr) ) {
            $query = $this->get_by_id_in($screenshot_id_arr);
            if ( $query->num_rows() > 0 ) {
                foreach ( $query->result() as $row ) {
                    $row->noteWeight = round($weight_multipliter*$screenshot_frequency_rate[$row->id],10);
                    if ( isset($screenshot_arr[$row->id]) ) {
                        $screenshot_arr[$row->id]->noteWeight = $row->noteWeight;
                    } else {
                        $screenshot_arr[$row->id] = $row;
                    }
                }
            }
        }
        unset($row);
        unset($screenshot_id_arr);
        unset($query);
        //var_dump($screenshot_arr); exit;

        // 6.撈出的app.averageUserRating，佔整體權重8%，滿分0.08，命名為ratingWeight
        $weight_multipliter = 0.08;

        foreach ( $screenshot_arr as $screenshot ) {
            // rating滿分5分
            $screenshot->ratingWeight = round($weight_multipliter*($screenshot->averageUserRating/5),10);
        }

        // 7.撈出的screenshot.clickCount，佔整體權重15%，滿分0.15，命名為clickWeight
        $weight_multipliter = 0.15;

        $click_sum = 0;
        foreach ( $screenshot_arr as $screenshot) {
            $click_sum += $screenshot->clickCount;
        }
        if ( $click_sum > 0 ) {
            foreach ( $screenshot_arr as $screenshot) {
                $screenshot->clickWeight = round($weight_multipliter*($screenshot->clickCount/$click_sum),10);
            }
        }

        // 計算weight總和
        reset($screenshot_arr);
        $screenshot_count = count($screenshot_arr);
        $this->load->model('screenshot_tag_model');
        foreach ( $screenshot_arr as $screenshot_id => $screenshot ) {
            // 最後調整
            $screenshot->weight = 0;
            // 1.
            if ( isset($screenshot->appNameWeight) ) {
                $screenshot->appNameWeight = round($screenshot->appNameWeight/$screenshot_count,10);
                $screenshot->weight += $screenshot->appNameWeight;
            }
            // 2.
            if ( isset($screenshot->genreNameWeight) ) {
                $screenshot->genreNameWeight = round($screenshot->genreNameWeight/$screenshot_count,10);
                $screenshot->weight += $screenshot->genreNameWeight;
            }
            // 3.
            if ( isset($screenshot->keywordWeight) ) {
                $screenshot->keywordWeight = round($screenshot->keywordWeight/$screenshot_count,10);
                $screenshot->weight += $screenshot->keywordWeight;
            }
            // 4.
            if ( isset($screenshot->tagWeight) ) {
                $screenshot->weight += $screenshot->tagWeight;
            }
            // 5.
            if ( isset($screenshot->ratingWeight) ) {
                $screenshot->ratingWeight = round($screenshot->ratingWeight/$screenshot_count,10);
                $screenshot->weight += $screenshot->ratingWeight;
            }
            // 6.
            if ( isset($screenshot->clickWeight) ) {
                $screenshot->weight += $screenshot->clickWeight;
            }
            // 7.
            if ( isset($screenshot->noteWeight) ) {
                $screenshot->weight += $screenshot->noteWeight;
            }
            // 再抓出該screenshot的tag，做成逗號相連的tag_list
            $screenshot_tag_arr = $this->screenshot_tag_model->get_by_screenshot($screenshot_id);
            $tag_list = array();
            foreach ( $screenshot_tag_arr as $screenshot_tag ) {
                $tag_list[] = $screenshot_tag->tag;
            }
            $screenshot->tag_list = implode(',', $tag_list);
        }
        
        // 排序: 依weight高->低排序
        // 現在不需要排，反正結果傳出去之後會再排一次
        /*
        usort($screenshot_arr, function($a, $b) {
            if ( $a->weight == $b->weight ) {
                return 0;
            }
            return ( $a->weight < $b->weight ) ? 1 : -1;
        });
        */

        //var_dump($screenshot_arr); exit;
        return $screenshot_arr;
    }

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
     * @return array 
     */
    public function query_term_break($query_term) 
    {
        $term_arr = array();
        if ( strpos($query_term, ',') === false ) {
            $term_arr = explode(' ', $query_term);
        } else {
            $term_arr = explode(',', $query_term);
        }
        return $term_arr;
    }
    
    function get_by_app_list(array $app_id_arr)
    {
        $this->db->select(' app.appName,
                            app.averageUserRating, 
                            app.releaseDate, 
                            screenshot.id,
                            screenshot.appId,
                            screenshot.url,
                            screenshot.clickCount,
                            screenshot.pinCount, 
                            screenshot.likeCount, 
                            screenshot.dislikeCount
                            ');
        $this->db->from('screenshot');
        $this->db->join('app', 'screenshot.appId = app.id');
        $this->db->where_in('screenshot.appId', $app_id_arr);
        return $query = $this->db->get();
    }
    
    function get_by_id_in(array $screenshot_id_arr)
    {
        $this->db->select(' app.appName,
                            app.averageUserRating, 
                            app.releaseDate, 
                            screenshot.id,
                            screenshot.appId,
                            screenshot.url,
                            screenshot.clickCount,
                            screenshot.pinCount, 
                            screenshot.likeCount, 
                            screenshot.dislikeCount
                            ');
        $this->db->from('screenshot');
        $this->db->join('app', 'screenshot.appId = app.id', 'left outer');
        $this->db->where_in('screenshot.id', $screenshot_id_arr);
        return $query = $this->db->get();
    }

    function get_by_app_trackid_list($app_trackid_list) 
    {
        $this->db->select(' app.appName,
                            app.averageUserRating, 
                            app.releaseDate, 
                            screenshot.id,
                            screenshot.appId,
                            screenshot.url,
                            screenshot.clickCount,
                            screenshot.pinCount, 
                            screenshot.likeCount, 
                            screenshot.dislikeCount
                            ');
        $this->db->from('screenshot');
        $this->db->join('app', 'screenshot.appId = app.id');
        $this->db->where_in('app.appPlatformId', $app_trackid_list);
        $this->db->order_by('screenshot.appId', 'desc');
        return $query = $this->db->get();
    }
}

/* End of file screenshot_model.php */
/* Location: ./application/models/screenshot_model.php */