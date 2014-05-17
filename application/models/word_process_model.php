<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// 這不是DB Table的Model，這個Model只是用來做文字解析，所以不extends Basic_Model
class Word_process_model extends CI_Model {

    // 字元長度超過這個數字的詞才抓出來
    private $min_index_len = 2;
    // 無用字詞列表
    public $stopwords_arr = array();
    // 標點符號列表
    public $punctuation_arr = array();
    // 特殊符號列表
    public $symbol_arr = array();
    // 特殊過濾pattern列表
    public $preg_filter_arr = null;
    
    function __construct() 
    {
        parent::__construct();
        // 載入無用字詞列表 stopwords.dic (放在application/libraries/)
        $stopwords_file = APPPATH.'libraries/stopwords.dic';
        $stopwords_arr = array();
        if (($handle = fopen($stopwords_file, "r")) !== FALSE) {
            while (($csv_arr = fgetcsv($handle, 0, ",")) !== FALSE) {
                $stopwords_arr = array_merge($stopwords_arr, $csv_arr);
            }
            fclose($handle);
        }
        $this->stopwords_arr = array_unique($stopwords_arr);
        unset($stopwords_arr);
        
        // 載入標點符號列表 punctuation.dic (放在application/libraries/)
        $punctuation_file = APPPATH.'libraries/punctuation.dic';
        $punctuation_arr = array();
        if (($handle = fopen($punctuation_file, "r")) !== FALSE) {
            while (($buffer = fgets($handle)) !== FALSE) {
                $punctuation_arr[] = rtrim($buffer);
            }
            fclose($handle);
        }
        $this->punctuation_arr = $punctuation_arr;
        unset($punctuation_arr);
        
        // 載入特殊符號列表 symbol.dic (放在application/libraries/)
        $symbol_file = APPPATH.'libraries/symbol.dic';
        $symbol_arr = array();
        if (($handle = fopen($symbol_file, "r")) !== FALSE) {
            while (($buffer = fgets($handle)) !== FALSE) {
                $symbol_arr[] = rtrim($buffer);
            }
            fclose($handle);
        }
        $this->symbol_arr = $symbol_arr;
        unset($symbol_arr);
        
        // 載入特殊過濾pattern列表 preg_filter.dic (放在application/libraries/)
        $preg_filter_file = APPPATH.'libraries/preg_filter.dic';
        $preg_filter_arr = array();
        if (($handle = fopen($preg_filter_file, "r")) !== FALSE) {
            while (($buffer = fgets($handle)) !== FALSE) {
                $preg_filter_arr[] = rtrim($buffer);
            }
            fclose($handle);
        }
        $this->preg_filter_arr = $preg_filter_arr;
        unset($preg_filter_arr);
        
        // 載入單複數轉換程式庫
        $this->load->library('Inflector');
    }

    /**
     * 輸入一串字串，把字串中的英文單字拆解出來，單字全部轉為小寫單數型
     * 傳回陣列，key為拆解出來的單字，value為該單字出現頻率
     *
     * @param string $text
     * @return array 陣列，key 為拆解出來的單字，value 為出現頻率
     *               ex: array('internal' => 1, 'app' => 1, 'company' => 1)
     */
    function make_word_process($text) 
    {
        if ( !is_string($text) ) { return false; }

        // 轉小寫
        $text = strtolower($text);

        // 拿掉特殊符號
        $text = $this->filter_symbol($text);
        
        // 拿掉標點符號
        $text = $this->filter_punctuation($text);

        // 去除字和字之間的空白
        $text = $this->filter_space_between_word($text);

        // 換行符號換成空白
        $text = $this->filter_null($text);

        // 去除前後空白
        $text = trim($text);
//var_dump($text); exit;
        // 用RegEx過濾某些特殊情況，換為空白
        $text = $this->filter_preg_filter($text);
//var_dump($text);  exit;
        // 去除字和字之間的空白
        $text = $this->filter_space_between_word($text);
//var_dump($text); exit;
        // 轉成陣列
        $arr = explode(' ', $text);

        // 去掉無用的字詞和標點符號
        $arr = $this->filter_arr_stopword($arr);
        $arr = $this->filter_arr_punctuation($arr);
//var_dump($arr); exit;
        $this->load->library('Inflector');
        foreach ( $arr as $k => &$word ) {
            // 濾掉任何非字詞字元
            $word = $this->filter_non_word($word);
            // 複數詞轉成單數詞
            $word = $this->inflector->singularize($word);
            // 濾掉字元數太少的字
            if ( strlen($word) <= $this->min_index_len ) {
                unset($arr[$k]);
            }
        }
        unset($word);
//var_dump($arr); exit;
        // 計算關鍵字出現的次數
        $arr = array_count_values($arr);
//var_dump($arr); exit;
        return $arr;
    }
    
    function filter_query_word($text) 
    {
        if ( !is_string($text) ) { return $text; }

        // 轉小寫
        $text = strtolower($text);

        // 拿掉特殊符號
        $text = $this->filter_symbol($text);
        
        // 拿掉標點符號
        $text = $this->filter_punctuation($text);

        // 去除字和字之間的空白
        $text = $this->filter_space_between_word($text);

        // 換行符號換成空白
        $text = $this->filter_null($text);

        // 去除前後空白
        $text = trim($text);
//var_dump($text); exit;
        // 用RegEx過濾某些特殊情況，換為空白
        $text = $this->filter_preg_filter($text);
//var_dump($text);  exit;
        // 去除字和字之間的空白
        $text = $this->filter_space_between_word($text);
//var_dump($text); exit;
        // 複數詞轉成單數詞
        //$text = $this->inflector->singularize($text);
        
        return $text;
    }
    
    function filter_app_genres($text) 
    {
        if ( !is_string($text) ) { return $text; }

        // 轉小寫
        $text = strtolower($text);

        // 逗號(,)轉成一個空白，好方便後面的處理
        $text = str_replace(',', ' ', $text);
        
        // 拿掉特殊符號
        $text = $this->filter_symbol($text);
        
        // 拿掉標點符號
        $text = $this->filter_punctuation($text);

        // 去除字和字之間的空白
        $text = $this->filter_space_between_word($text);

        // 換行符號換成空白
        $text = $this->filter_null($text);

        // 去除前後空白
        $text = trim($text);

        return $text;
    }
    
    function filter_app_name($text) 
    {
        if ( !is_string($text) ) { return $text; }
        $t = $text;
        // 轉小寫
        $text = strtolower($text);

        // 換行符號換成一個空白
        $text = $this->filter_null($text, ' ');

        // 特殊符號換成一個空白
        $text = $this->filter_symbol($text, ' ');

        // 標點符號換成一個空白
        $text = $this->filter_punctuation($text, ' ');

        // 兩個以上空白變成一個空白
        $text = preg_replace('/\s+/i', ' ', $text);

        // 用RegEx過濾某些特殊情況，換為一個空白
        $text = $this->filter_preg_filter($text, ' ');

        // 兩個以上空白變成一個空白
        $text = preg_replace('/\s+/i', ' ', $text);

        // 去除前後空白
        $text = trim($text);

        return $text;
    }
    
    function singularize($text) 
    {
        return $this->inflector->singularize($text);
    }
    
    // 過濾掉特殊符號
    function filter_symbol($text, $replace='') 
    {
        return str_replace($this->symbol_arr, $replace, $text);
    }
    
    // 過濾掉標點符號
    function filter_punctuation($text, $replace='') 
    {
        return str_replace($this->punctuation_arr, $replace, $text);
    }
    
    // 去除字和字之間多餘的空白，兩個以上空白變成一個空白
    function filter_space_between_word($text) 
    {
        return preg_replace('/\s(?=\s)/', '', $text);
    }
    
    // 去除逗號之間多餘的空白
    function filter_space_between_comma($text) 
    {
        return preg_replace('/\s*,\s*/', ',', $text);
    }
    
    // Tab, New line, carriage return 字元換成空白
    function filter_null($text, $replace=' ') 
    {
        return preg_replace('/[\n\r\t\0\x0B]/', $replace, $text);
    }
    
    // 用RegEx過濾某些特殊情況，換為空白
    function filter_preg_filter($text, $replace=' ') 
    {
        return preg_replace($this->preg_filter_arr, $replace, $text);
    }
    
    // 去掉無用的字詞(陣列)
    function filter_arr_stopword($arr) 
    {
        return array_diff($arr, $this->stopwords_arr);
    }
    
    // 去掉標點符號(陣列)
    function filter_arr_punctuation($arr) 
    {
        return array_diff($arr, $this->punctuation_arr);
    }
    
    // 濾掉任何非字詞字元
    function filter_non_word($text) 
    {
        return preg_replace('/[^a-zA-Z0-9\-_\.@]/i','',$text);
    }
}