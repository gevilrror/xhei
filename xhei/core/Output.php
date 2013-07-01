<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * Output Class
 *
 * @author GevilRror
 */
class HEI_Output {

    /**
     * Constructor
     *
     */
    function __construct($config = '')
    {
        if (isset($config['charset']))
        {
            $this->set_charset($config['charset']);
        }

        hei::trace('Output Class Initialized', 'core.Output', 'debug');
    }

    function set_charset($charset)
    {
        header('Content-type: text/html; charset='.$charset);
    }

    function jsonp_return($result)
    {

        if (isset($_GET['jsoncallback']))
        {
            echo $_GET['jsoncallback'].'('.json_encode($result).')';
        }
        else if (isset($_GET['return_format']) && $_GET['return_format'] === 'JSON')
        {
            echo json_encode($result);
        }
        else
        {
            echo $result;
        }

        exit();
    }

    public function last_modified($timestamp, $set_cache_control = true)
    {
        $timestamp = is_int($timestamp) ? $timestamp : strtotime($timestamp);

        // If-Modified-Since ?
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $timestamp)
        {
            hei::trace('Last-Modified not timeout', 'core.Output', 'debug');

            hei::load('Exceptions')->set_status_header(304);
            exit();
        }

        if ($set_cache_control)
        {
            header('Cache-Control: max-age=0');
        }

        header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', $timestamp));

        hei::trace('Last-Modified have set', 'core.Output', 'debug');
    }
}
// END Output Class

/* End of file Output.php */