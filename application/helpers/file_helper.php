<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * File Helpers for CodeIgniter
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	File handle
 * @author		David Pai
 */

// ------------------------------------------------------------------------

if ( ! function_exists('file_to_array') ) 
{
    /**
     * Parse a file into array
     * one line to one array element
     * @param string $file_path Absolute file path
     * @return array return array if no error, return false if any error occur
     */
    function file_to_array($file_path)
    {
        $handle = @fopen($file_path, 'r');
        if ( FALSE === $handle ) {
            return false;
        }
        
        $return_arr = array();
        while ( ($buffer = fgets($handle)) !== FALSE ) {
            $return_arr[] = trim($buffer);
        }
        fclose($handle);
        
        return $return_arr;
    }
}

/* End of file file_helper.php */
/* Location: ./application/helpers/file_helper.php */