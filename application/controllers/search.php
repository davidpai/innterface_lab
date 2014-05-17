<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/basic_controller.php';

class Search extends Basic_Controller {

    // Progressive Load 分頁，第一頁的顯示數量
    public $min_num = 100;
    // 第二頁之後，每一頁的顯示數量
    public $per_page = 90;
    
    private $is_appname_and_tag_arr = array( 'appname', 'appname_full', 'tag_and', 'tag_or', 'normal_search' );

    private $time = null;
    private $start = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->model('screenshot_model');
        $this->load->model('word_process_model');
        $this->load->model('weight_model', 'weight');
        $this->time = microtime(true);
        $this->start = microtime(true);
    }

    function index() 
    {
        $this->log_speed('search/index START');
        
        // 術語定義
        // query_term 整個搜尋框裡面全部的文字
        // term       query_term 扣掉 Syntax 語法之後的文字 ex: query_term 是 tag=camera controller 那麼 term 就是 camera controller
        // syntax     特殊的搜尋語法 ex: appname=facebook manager

        $query_term = trim($this->input->get('q', true));
        $term_unsanitized = trim($this->input->get('q'));
        $mode = trim($this->input->get('m', true));
        $page = (int)trim($this->input->get('p', true));

        if ( $query_term == '' ) {
            redirect('home');
            exit;
        }

        $tpl = array();
        $tpl['is_login'] = $this->_is_login();
        $tpl['keyword'] = $term_unsanitized;

        // 在upload網頁時，若按下cancel時應返回的URL
        $_SESSION['upload']['cancel_url'] = site_url('search').'?'.$_SERVER['QUERY_STRING'];
        // 記錄原始的搜尋字詞，後面 記錄 query_term 會用到
        $_SESSION['user_session_click']['query_term'] = $query_term;

        if ( $this->_is_login() ) {
            $user = new stdClass();
            $user->id = $_SESSION['login']['user_id'];
            $user->name = isset($_SESSION['login']['user_name']) ? $_SESSION['login']['user_name'] : '';
            $user->picture = isset($_SESSION['login']['user_picture']) ? $_SESSION['login']['user_picture'] : '';
            $tpl['user'] = $user;
        } else {
            // Login後應轉回的URL
            $tpl['return_url'] = site_url('search').'?'.$_SERVER['QUERY_STRING'];
            //$_SESSION['login']['from_url'] = site_url('search').'?'.$_SERVER['QUERY_STRING'];
            // 其他各項cancel時應返回的URL
            //$_SESSION['cancel_url'] = site_url('search').'?'.$_SERVER['QUERY_STRING'];
            // 紀錄傳進來的參數，如果layer3需要跳去登入頁，這些參數可以讓layer3頁面去組出login的from_url
            // 參看screenshot.php
            //$_SESSION['layer3']['q'] = $query_term;
            //$_SESSION['layer3']['m'] = isset($_SESSION['layer3']['m']) ? $_SESSION['layer3']['m'] : $mode;
            //$_SESSION['layer3']['p'] = $page;
        }
        // 丟給頁面的JavaScript去觸發layer3 modal
        //if ( isset($_SESSION['layer3']['trigger']) && $_SESSION['layer3']['trigger'] === true ) {
        //    $tpl['trigger_layer3'] = true;
        //    $tpl['layer3_url'] = $_SESSION['layer3']['url'];
        //    unset($_SESSION['layer3']);
        //}
        
        //** 多個syntax組合 **//
        // 1. appname_and_tag: 判斷是否為 appname=xxx::tag=xxx 的這種 syntax
        $is_appname_and_tag = false;
        $syntax_arr = array();
        if ( $this->is_multi_syntax($query_term) ) {
            $is_appname_and_tag = true;
            $syntax_arr = $this->break_multi_syntax($query_term);
            foreach ( $syntax_arr as $r ) {
                if ( in_array($r['syntax'], $this->is_appname_and_tag_arr) ) {
                } else {
                    $is_appname_and_tag = false;
                }
            }
        }
        //var_dump($syntax_arr); var_dump($is_appname_and_tag); exit;

        //** 分析 query term **//
        // $result['syntax'] 哪一種syntax，目前有: 
        //      appid
        //      appkw
        //      appname
        //      appname_full
        //      tag_and
        //      tag_or
        //      normal_search
        // $result['match'] syntax後面的字詞
        $result = $this->syntax_match($query_term);
        $syntax_mode = $result['syntax'];
        $keyword = $result['match'];
        //var_dump($result); exit;

        //** 在 normal_search 的情況下，再將 keyword 拿去給 appkw=xxx 抓資料 **//
        // 組出 appkw 的 url 再丟給 view 頁面，用 javascript 建立 iframe 去抓
        // #to-do#
        // 這個影響效能太大，要改用 cron 定期處理
        //if ( $syntax_mode == 'normal_search' ) {
        //    $tpl['appkw_url_arr'] = $this->make_appkw_url($syntax_mode, $keyword);
        //}
        
        //** 處理抓資料的 syntax **//
        // 1. appid 
        //      當搜尋框打入 appid = 123456789 , 987654321 這樣的格式時，執行秘技
        //      拿這些 appid 去 itunes search api 搜尋，搜到的 app 寫入 DB
        // 2. appkw 
        //      當搜尋框打入 appkw= bank , transport , aaa , bbb 這樣的格式時，執行秘技
        //      拿這些 keyword list 直接去 itunes search api 搜尋，搜到的 app 寫入 DB
        $trick = false;  // 是否執行秘技?
        switch ( $syntax_mode ) {
            // appid 除了抓資料之外，也要把該 appid 的 screenshot 秀出來，不做排序不做cache
            case 'appid':
                $trick = false; // 後續要把 screenshot 搜尋出來，所以這裡 flag 把它設成 false
                $return_msg = array();
                $succ_appid_list = array();
                
                $appid_arr = explode(',', $result['match']);
                $this->load->model('raw_search_api_model');
                foreach ( $appid_arr as $appid ) {
                    $result = $this->raw_search_api_model->fetch_single_app($appid);
                    if ( $result['error'] ) {
                        $return_msg[] = $result['msg'];
                    } elseif ( isset($result['appid']) ) {
                        $succ_appid_list[$result['appid']] = $result['appid'];
                    }
                }
                $tpl['exception_msg'] = implode("\n", $return_msg);
                break;
                
            case 'appkw':
                $trick = true;
                $return_msg = array();

                $appkw_arr = explode(',', $result['match']);
                $this->load->model('raw_search_api_model');
                foreach ( $appkw_arr as $appkw ) {
                    $msg = $this->raw_search_api_model->keyword_search_app($appkw);
                    if ( is_array($msg) ) {
                        $return_msg = $return_msg + $msg;
                    } else {
                        $return_msg[] = $msg;
                    }
                }
                $tpl['exception_msg'] = sprintf("Fetch %d apps\n", count($return_msg)).implode("\n", $return_msg);
                //log_message('debug', sprintf("Fetch %d apps\n", count($return_msg)).implode("\n", $return_msg));
                break;
        }
        //var_dump($succ_appid_list); var_dump($tpl['exception_msg']); exit;
        //** end: 處理抓資料的 syntax **//

        //** 決定那些搜尋模式需要做cache，哪些需要排序 **//
        $do_cache = true;
        $do_sort = true;
        switch ( $syntax_mode ) {
            // appid不要做cache
            case 'appid':
                $do_memcache = false;
                $do_session_cache = false;
                $do_sort = false;
                break;
            // 其他都要做
            default:
                $do_memcache = false;
                $do_session_cache = false;
                if ( $mode == 'tag' ) {
                    // 從首頁點pattern進來的就不再排序了，因為已經做好index並排序過才存入DB的
                    // 若是又再排序一次有可能順序會亂掉 ex: weight同分的會跳來跳去
                    $do_sort = false;
                } else {
                    $do_sort = true;
                }
                break;
        }
        
        //** 搜尋結果 **//

        // Progressive Load: 有 memcache 先從 memcache 拿
        $screenshot_arr = false;
        $query_term_hashed = md5($query_term); // 將search term用md5 hash過，當成memcache的key
        if ( $do_memcache ) {
            $memcache_conn_result = false;

            if ( class_exists('Memcache') ) {
                $memcache = new Memcache;
                $memcache_conn_result = @$memcache->connect("localhost",11211);
            }
            if ( $memcache_conn_result ) {
                $screenshot_arr = $memcache->get($query_term_hashed);
            }
        }

        // 沒有 memcache 的情況
        if ( $screenshot_arr === false ) {

            // 再檢查有無 session cache
            if ( $do_session_cache && isset($_SESSION['search_result'][$query_term_hashed]) ) {
            
                // 有 session cache 就從 session cache 拿出來
                $screenshot_arr = $_SESSION['search_result'][$query_term_hashed];

            } else {
            
                //** 搜尋流程 **//
                $screenshot_arr = array(); // 搜尋結果的容器
                
                // 非抓資料的syntax
                if ( $trick == false ) {
                
                    // 多 syntax : appname=xxx::tag=xxx
                    if ( count($syntax_arr) > 0 && $is_appname_and_tag ) {
                        $screenshot_arr = array();
                        $final_arr = array();
                        foreach ( $syntax_arr as $r ) {
                            $ss_arr = $this->do_search($mode, $r['syntax'], $r['match']);
                            // 只找出重複的screenshot，其他捨棄，重複的screenshot分數要相加
                            if ( empty($screenshot_arr) ) {
                                $screenshot_arr = $ss_arr;
                            } else {
                                foreach ( $screenshot_arr as $ss1 ) {
                                    foreach ( $ss_arr as $ss2 ) {
                                        if ( $ss1->id == $ss2->id ) {
                                            $ss1->weight += $ss2->weight;
                                            $final_arr[$ss1->id] = $ss1;
                                        }
                                    }
                                }
                            }
                        }
                        $screenshot_arr = $final_arr;
                        unset($ss1, $ss2, $final_arr);
                        //var_dump($screenshot_arr); exit;
                        
                    // 單 syntax
                    } else {
                        $this->log_speed('search/index before do_search()');
                        
                        $screenshot_arr = $this->do_search($mode, $syntax_mode, $keyword);
                        //var_dump($screenshot_arr); exit;
                        
                        $this->log_speed('search/index end do_search()');
                    }
                    //var_dump($screenshot_arr); exit;

                    // 排序: 依 weight 高 -> 低排序
                    if ( $do_sort ) {
                        usort($screenshot_arr, function($a, $b) {
                            if ( $a->weight == $b->weight ) {
                                return 0;
                            }
                            return ( $a->weight < $b->weight ) ? 1 : -1;
                        });
                    }
                    //var_dump($screenshot_arr); exit;
                    $this->log_speed('search/index do_sort');
                    
                    // 排序後，key又會跑掉，再插一次screenshot_id到$screenshot_arr的key
                    $arr = $screenshot_arr;
                    $screenshot_arr = array();
                    foreach ( $arr as $ss ) {
                        $screenshot_arr[$ss->id] = $ss;
                    }
                    unset($arr, $ss);
                    //var_dump($screenshot_arr); exit;
                    $this->log_speed('search/index arrange key');

                    // 檢查圖片URL，若圖片已不存在，則從陣列裡移除
                    foreach ( $screenshot_arr as $k => $screenshot ) {
                        $read = $this->screenshot_model->is_url_exist($screenshot->url);
                        if ( $read === false ) {
                            unset($screenshot_arr[$k]);
                        }
                    }
                    //var_dump($screenshot_arr); exit;
                    $this->log_speed('search/index check screenshot url');
                    
                    //** 記錄 search term **//
                    // user 若有登入 => 記錄到 user_query table
                    if ( $this->_is_login() ) {
                        $data = array( 'user_id' => $user->id,
                                       'query' => $query_term,
                                       'add_time' => date('Y-m-d H:i:s'), 
                                       );
                        $this->db->insert('user_query', $data);
                    }
                    // 紀錄 search term 被搜尋的次數 & 搜出來的 screenshot 數量
                    $q = $this->db->get_where('query', array('term'=>$query_term));
                    if ( $q->num_rows() > 0 ) {
                        $this->db->set('count', 'count+1', false);
                        $this->db->set('result_count', count($screenshot_arr));
                        $this->db->where(array('term'=>$query_term));
                        $this->db->update('query');
                    } else {
                        $data = array( 'term' => $query_term,
                                       'count' => 1,
                                       'result_count' => count($screenshot_arr), 
                                       );
                        $this->db->insert('query', $data);
                    }
                    $this->log_speed('search/index log search term');
                }
                //var_dump($screenshot_arr); exit;
                
                // 搜尋結果放入 cache
                if ( $do_memcache && $memcache_conn_result ) {
                    // 資料超過1MB會跳警告訊息，這裡用@把訊息抑制掉
                    $memcache_set_result = @$memcache->set($query_term_hashed, $screenshot_arr, MEMCACHE_COMPRESSED, 1800);
                }
                if ( $do_session_cache ) {
                    $_SESSION['search_result'][$query_term_hashed] = $screenshot_arr;
                }
                //** end: 搜尋流程 **//
                
            }
        }
        //** end: 搜尋結果 **//

        // 撈出 user 有 like 的 screenshot，當然必須是在有 user login 的情況下
        if ( $this->_is_login() ) {
            $this->db->select('screenshot_id');
            $this->db->from('user_screenshot_like');
            $this->db->where('user_id', $user->id);
            $q = $this->db->get();
            
            $user_like = array(); // user 有 like 的 screenshot 容器
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $row ) {
                    $user_like[$row->screenshot_id] = $row->screenshot_id;
                }
            }
        }
        //var_dump($user_like); exit;
        $this->log_speed('search/index user like');

        // 撈出 user 有 dislike 的 screenshot，當然必須是在有 user login 的情況下
        if ( $this->_is_login() ) {
            $this->db->select('screenshot_id');
            $this->db->from('user_screenshot_dislike');
            $this->db->where('user_id', $user->id);
            $q = $this->db->get();
            
            $user_dislike = array(); // user 有 dislike 的 screenshot 容器
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $row ) {
                    $user_dislike[$row->screenshot_id] = $row->screenshot_id;
                }
            }
        }
        //var_dump($user_dislike); exit;
        $this->log_speed('search/index user dislike');

        // 撈出 user 有 pin 的 screenshot，當然必須是在有 user login 的情況下
        if ( $this->_is_login() ) {
            $this->db->select('screenshot_id');
            $this->db->from('user_pin');
            $this->db->where('user_id', $user->id);
            $q = $this->db->get();
            
            $user_pin = array(); // user 有 pin 的 screenshot 容器
            if ( $q->num_rows() > 0 ) {
                foreach ( $q->result() as $row ) {
                    $user_pin[$row->screenshot_id] = $row->screenshot_id;
                }
            }
        }
        //var_dump($user_pin); exit;
        $this->log_speed('search/index user pin');

        // Progressive Load 分頁
        $result_num = count($screenshot_arr);
        $page = ($page <= 0) ? 1 : $page;
        
        if ( $result_num > $this->min_num ) {
            $per_page = ( $page == 1 ) ? $this->min_num : $this->per_page;
            $offset = ( $page == 1 ) ? 0 : $this->min_num+(($page-2)*$this->per_page);
            if ( $mode == 'ajax' ) {
                $screenshot_arr = array_slice($screenshot_arr, $offset, $per_page, true);
            } else {
                $offset = $this->min_num+(($page-1)*$this->per_page);
                $screenshot_arr = array_slice($screenshot_arr, 0, $offset, true);
            }
            if ( $this->min_num+(($page-1)*$this->per_page) < $result_num ) {
                $tpl['have_more'] = true;
            }
        }
        $this->log_speed('search/index pagination');

        // 標記出 $screenshot_arr 中 user 有 like, dislike, pin 的 screenshot
        foreach ( $screenshot_arr as $screenshot ) {
            $screenshot->like = isset($user_like[$screenshot->id]) ? true : false;
            $screenshot->dislike = isset($user_dislike[$screenshot->id]) ? true : false;
            $screenshot->pin = isset($user_pin[$screenshot->id]) ? true : false;
        }
        //var_dump($screenshot_arr); exit;
        $this->log_speed('search/index screenshot like,dislike,pin');

        // 因為 pinCount, likeCount, dislikeCount 要即時顯示，不放入 cache 裡
        // 所以切出分頁後的結果之後，再撈一次 pinCount, likeCount, dislikeCount 
        // 加入到已分好頁的結果裡
        $this->screenshot_model->add_count_number($screenshot_arr);
        $this->log_speed('search/index add_count_number');

        // 每個 screenshot 加上 tag_list
        $this->screenshot_model->add_tag_list($screenshot_arr);
        $this->log_speed('search/index add_tag_list');

        if ( 'debug' == trim($this->input->get('mode', true)) ) {
            var_dump($screenshot_arr); exit;
        }
        
        // user 行為紀錄
        $this->_do_user_session_log();
        $this->_do_user_session_click_log();
        $this->log_speed('search/index log user session');
        
        $time = microtime(true) - $this->start;
        
        $tpl['time'] = $time;
        $tpl['q'] = $query_term;
        $tpl['m'] = $mode;
        $tpl['p'] = $page;
        $tpl['result_num'] = $result_num;
        $tpl['screenshot_arr'] = $screenshot_arr;
        $this->load->view('search_new_tpl', $tpl);
    }
    
    protected function do_search($mode, $syntax_mode, $term)  
    {
        $this->log_speed('search/do_search start');
        //** 多字詞 term 拆分 **//
        $r = $this->break_term($syntax_mode, $term);
        $keyword_arr = $r['keyword_arr'];  // 拆分之後 keyword 的容器
        $keyword_count = $r['keyword_count'];  // keyword 個數
        $include_synonym = $r['include_synonym'];  // 是否要處理同義字?
        //var_dump($r); exit;
        //var_dump($keyword_arr); exit;

        $this->log_speed('search/do_search before get_synonym');
        //** keyword 同義字處理 **//
        $synonym_arr = array(); // 同義字的容器
        if ( $include_synonym === true ) {
            $this->load->model('synonym_word_model');
            // 再去同義字字典撈出每個字詞的同義字，也納入搜尋的字詞裡
            foreach ( $keyword_arr as $k ) {
                $synonym_arr = $this->synonym_word_model->get_synonym($k, $synonym_arr);
            }
        }
        //var_dump($synonym_arr); exit;
        $this->log_speed('search/do_search end get_synonym');

        //** 開始搜尋　**//
        $screenshot_list_arr = array(); // screenshot列表的容器
        
        // 點首頁 pattern 範例圖進來搜尋結果頁的
        if ( $mode == 'tag' ) {

            // 直接從做好index的DB撈出來
            $term = strtolower($term);
            $r = $this->db->get_where('tag_screenshot_index', array('tag'=>$term))->row();
            if ( isset($r->screenshot_list) ) {
                $screenshot_list_arr[] = unserialize($r->screenshot_list);
            }

        // 在搜尋框打 query term 進來的
        } else {

            switch ( $syntax_mode ) {
            
                case 'appid':
                    $screenshot_list_arr[] = $this->screenshot_model->make_weight_by_trackid_list($keyword_arr);
                    $this->log_speed('search/do_search appid');
                    break;
                    
                case 'appname_full': 
                    $screenshot_list_arr[] = $this->weight->make_weight_by_appname_full($term);
                    $this->log_speed('search/do_search appname_full');
                    break;
                    
                case 'appname':
                    $screenshot_list_arr[] = $this->weight->make_weight_by_appname($term);
                    $this->log_speed('search/do_search appname');
                    break;

                case 'tag_or':
                    $screenshot_list_arr[] = $this->weight->make_weight_by_tag_or($term);
                    $this->log_speed('search/do_search tag_or');
                    break;
                    
                case 'tag_and':
                    $screenshot_list_arr[] = $this->weight->make_weight_by_tag_and($term);
                    $this->log_speed('search/do_search tag_and');
                    break;
                
                case 'normal_search':
                default: 
                    $screenshot_list_arr[] = $this->weight->make_weight($term);
                    //var_dump($screenshot_list_arr); exit;
                    $this->log_speed('search/do_search normal_search');
                    break;
            }
        }
        
        // 重建 $screenshot_arr
        // 由於分數排序了之後，以 screenshot_id 當成的 key 就不見了，所以要重建一次
        // 處理多字詞 keyword 有可能撈到重複的 screenshot，重複的 screenshot 合併，分數加總
        $screenshot_arr = array(); // 最後的 screenshot 容器
        foreach ( $screenshot_list_arr as $screenshot_list ) {
            foreach ( $screenshot_list as $ss ) {
                if ( isset($screenshot_arr[$ss->id]) ) {
                    $screenshot_arr[$ss->id]->weight += $ss->weight;
                } else {
                    $screenshot_arr[$ss->id] = $ss;
                }
            }
        }
        unset($ss, $screenshot_list, $screenshot_list_arr);
        //var_dump($screenshot_arr); exit;
        $this->log_speed('search/do_search arrange key');
        
        // 2014-01-17
        // 這段有邏輯問題，先註解掉
        //** 處理 appname=AAA BBB CCC 這種 appname 的 syntax 語法 **//
        // 由於是用LIKE語法去撈，有可能會撈到 appname 是 AAABBBCCC 的情況，這不是我們要的，要把他剔除
        /*
        if ( $mode == 'appname' ) {
            $ss_arr = $screenshot_arr;
            $screenshot_arr = array();
            foreach ( $ss_arr as $ss ) {
                $trackName = preg_replace('/\W/i', ' ', $ss->trackName);  // 把字元、數字、底線以外的換成空白
                $trackName = strtolower(preg_replace('/\s+/i', ' ', $trackName)); // 把多個空白換成一個空白
                $trackName_arr = explode(' ', $trackName);
                $have_all_keyword = true;
                foreach ( $keyword_arr as $keyword ) {
                    if ( in_array(strtolower($keyword), $trackName_arr) === false ) {
                        $have_all_keyword = false;
                    }
                }
                if ( $have_all_keyword ) {
                    $screenshot_arr[$ss->id] = $ss;
                }
            }
        }
        unset($have_all_keyword, $trackName, $trackName_arr, $ss_arr, $ss);
        */
        
        //var_dump($screenshot_arr); exit;
        return $screenshot_arr;
    }
    
    /**
     * 處理多字詞 term 的拆分
     * 1. 拆分多字詞: 允許的分隔字元為逗號(,)和空白
     * 2. 字詞個數: 如果單個字詞，字詞個數是1，如果是多個字詞，要搜尋的字詞個數應該是n+1
     * ex: keyword是empty data set
     * 要搜尋的字詞應該包含: "empty data set" + empty + data + set這樣4個字
     * 
     * @access protected 
     * @param  string  語法模式，包含: appid, appkw, appname, appname_full, tag_and, tag_or, normal_search 
     * @param  string  關鍵字詞
     * @return array   array( 'keyword_arr' => array 拆出來的字詞陣列, 'keyword_count' => integer 拆出來的字詞個數, 'include_synonym' => boolean 搜尋是否要包含同義字 )
     */
    protected function break_term($syntax_mode, $term) 
    {
        $include_synonym = true; // 搜尋是否包含同義字?
        $term_arr = array(); // 拆分字詞的容器
        
        switch ( $syntax_mode ) {
        
            // appid 拆分字詞，不找同義字
            case 'appid':
                $term_arr = $this->weight->query_term_break($term, ',');
                $term_count = count($term_arr);
                $include_synonym = false;
                break;
                
            // appname_full 不拆分字詞，不找同義字
            case 'appname_full':
                $term_arr = array($term);
                $term_count = count($term_arr);
                $include_synonym = false;
                break;
                
            // appname 拆分字詞，不找同義字
            case 'appname':
                $term_count = 1; // keyword字詞個數預設1
                $term_arr = array();
                // 自動判斷分隔字元，若有,則以,分隔，否則以一個空白分隔
                $arr = $this->weight->query_term_break($term);
                foreach ($arr as $v) {
                    $v = $this->filter_query_word($v);
                    if ( strlen($v) > 2 ) {  // 若有字元數太少的字，多半是a, at, of等介係詞，略過不計
                        $term_arr[$v] = $v;
                    }
                }
                unset($arr);
                $term_count = count($term_arr);
                //var_dump($term_arr); var_dump($term_count); exit;
                $include_synonym = false;
                break;
                
            // 如果是 tag_and 或 tag_or 拆分字詞，找同義字
            case 'tag_or':
            case 'tag_and':
                $term_count = 1; // keyword字詞個數預設1
                $term_arr = $this->weight->query_term_break($term, ',');
                $term_count = count($term_arr);
                //var_dump($term_arr); var_dump($term_count); exit;
                $include_synonym = true;
                break;
                
            // 一般搜尋
            case 'normal_search':
            default: 
                $term_count = 1; // keyword字詞個數預設1
                $term_arr = array();
                $term = str_replace(',', ' ', $term); // 把逗號換成空白
                $term = preg_replace('/\s+/i', ' ', $term); // 把多個空白換成一個空白
                $arr = $this->weight->query_term_break($term, ' '); // 分隔字元為一個空白
                // 如果拆分過後，有一個以上，那麼原多字詞keyword本身也要算一個
                if ( count($arr) > 1 ) { $term_arr[$term] = $term; } 
                foreach ($arr as $v) {
                    $v = $this->filter_query_word($v);
                    if ( strlen($v) > 2 ) {  // 若有字元數太少的字，多半是a, at, of等介係詞，略過不計
                        $term_arr[$v] = $v;
                    }
                }
                unset($arr);
                $term_count = count($term_arr);
                //var_dump($term_arr); var_dump($term_count); exit;
                $include_synonym = true;
                break;
        }
        return array( 'keyword_arr' => $term_arr, 'keyword_count' => $term_count, 'include_synonym' => $include_synonym );
    }
    
    /**
     * 輸入 語法類型syntax mode 和 搜尋詞term ， 組合出一個 appkw=xxx 的搜尋 URL 陣列
     * 
     * @param string $syntax_mode 語法類型
     * @param string $term 搜尋詞
     * @return array 
     */
    function make_appkw_url($syntax_mode, $term) 
    {
        $r = $this->break_term($syntax_mode, $term);
        // 多字詞的狀況下，第一個陣列元素是整個 term，這個不是這裡要的，所以把第一個元素拿掉
        if ( count($r['keyword_arr']) > 1 ) {
            array_shift($r['keyword_arr']);
        }
        $appkw_url_arr = array();
        foreach ( $r['keyword_arr'] as $k ) {
            $q = "appkw={$k}";
            $appkw_url_arr[] = site_url('search').'?q='.urlencode($q);
        }
        return $appkw_url_arr;
    }

    /**
     * 判斷 query term 屬於哪一種 syntax語法
     * @param string $query_term user輸入的搜尋字串
     * @return array array('syntax', 'match') syntax: 語法類型, match: 語法=後真正要搜尋的字詞
     */
    protected function syntax_match($query_term) 
    {
        if ( !is_string($query_term) ) { return false; }

        if ( preg_match('/^appid\s*=\s*(\d{1,}(\s*,\s*\d{1,})*)/iu', $query_term, $matches) === 1 ) {
            return array( 'syntax' => 'appid', 'match' => $this->normalize_term($matches[1]) );
        }
        if ( preg_match('/^appkw\s*=\s*(.*)/iu', $query_term, $matches) === 1 ) {
            return array( 'syntax' => 'appkw', 'match' => $this->normalize_term($matches[1]) );
        }
        if ( preg_match('/^appname\s*=\s*"(.+)"$/i', $query_term, $matches) === 1 ) {
            return array( 'syntax' => 'appname_full', 'match' => $this->normalize_term($matches[1]) );
        }
        if ( preg_match('/^appname\s*=\s*(.+)$/i', $query_term, $matches) === 1 ) {
            return array( 'syntax' => 'appname', 'match' => $this->normalize_term($matches[1]) );
        }
        if ( preg_match('/^tag\s*=\s*"((.+)(,.+)*)"$/i', $query_term, $matches) === 1 ) {
            return array( 'syntax' => 'tag_and', 'match' => $this->normalize_term($matches[1]) );
        }
        if ( preg_match('/^tag\s*=\s*((.+)(,.+)*)$/i', $query_term, $matches) === 1 ) {
            return array( 'syntax' => 'tag_or', 'match' => $this->normalize_term($matches[1]) );
        }
        return array( 'syntax' => 'normal_search', 'match' => $this->normalize_term($query_term) );
    }

    /**
     * 判斷 query term 是否由多個 syntax 組成
     * 
     * @param string $query_term
     * @return boolean
     */
    protected function is_multi_syntax($query_term) 
    {
        if ( strpos($query_term, '::') !== false ) {
            return true;
        }
        return false;
    }
    
    /**
     * 將 query term 拆解成多個 syntax
     *
     * @param string $query_term user 輸入的搜尋字串
     * @return array 多個 syntax 的陣列
     */
    protected function break_multi_syntax($query_term) 
    {
        $syntax_arr = array();
        
        // 把::前後多個空白拿掉
        $query_term = preg_replace('/\s*::\s*/i', '::', trim($query_term));
        $r = explode('::', $query_term);
        foreach ( $r as $q ) {
            $syntax_arr[] = $this->syntax_match($q);
        }
        return $syntax_arr;
    }
    
    /**
     * 把搜尋字詞做統一化的處理
     *
     * @param string $term 搜尋字詞
     * @return string 處理好的
     */
    protected function normalize_term($term) 
    {
        $term = trim($term);
        $term = $this->word_process_model->filter_space_between_comma($term);
        $term = $this->word_process_model->filter_space_between_word($term);
        return $term;
    }
    
    protected function filter_query_word($text) 
    {
        return $this->word_process_model->filter_query_word($text);
    }
    
    protected function log_speed($text) {
        $time = microtime(true) - $this->time;
        $this->time = microtime(true);
        log_message('debug', "{$text} in {$time} seconds");
    }
}

/* End of file search.php */
/* Location: ./application/controllers/search.php */