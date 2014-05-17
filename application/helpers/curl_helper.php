<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * cURL Helpers for CodeIgniter
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		David Pai
 */

// ------------------------------------------------------------------------

if ( ! function_exists('curl_post') ) 
{
    /**
     * Send a POST requst using cURL
     * @param string $url to request
     * @param array $post values to send
     * @param array $options for cURL
     * @return string
     */
    function curl_post($url, array $post = NULL, array $options = array())
    {
        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_POSTFIELDS => http_build_query($post)
        );

        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        if( ! $result = curl_exec($ch))
        {
            log_message('error', 'cURL error: '.curl_error($ch));
            //trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
}

if ( ! function_exists('curl_get') ) 
{
    /**
     * Send a GET requst using cURL
     * @param string $url to request
     * @param array $get values to send
     * @param array $options for cURL
     * @return string
     */
    function curl_get($url, array $get = NULL, array $options = array())
    {   
        $defaults = array(
            CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get),
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 8
        );
       
        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        if( ! $result = curl_exec($ch))
        {
            log_message('error', 'cURL error: '.curl_error($ch));
            //trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
}

/* End of file curl_helper.php */
/* Location: ./application/helpers/curl_helper.php */