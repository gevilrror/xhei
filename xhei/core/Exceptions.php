<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * Exceptions Class
 *
 * 异常类
 * 使php返回404给客户端
 *
 * @author GevilRror
 */

class HEI_Exceptions {

    private $stati = array(
        200    => 'OK',
        201    => 'Created',
        202    => 'Accepted',
        203    => 'Non-Authoritative Information',
        204    => 'No Content',
        205    => 'Reset Content',
        206    => 'Partial Content',

        300    => 'Multiple Choices',
        301    => 'Moved Permanently',
        302    => 'Found',
        304    => 'Not Modified',
        305    => 'Use Proxy',
        307    => 'Temporary Redirect',

        400    => 'Bad Request',
        401    => 'Unauthorized',
        403    => 'Forbidden',
        404    => 'Not Found',
        405    => 'Method Not Allowed',
        406    => 'Not Acceptable',
        407    => 'Proxy Authentication Required',
        408    => 'Request Timeout',
        409    => 'Conflict',
        410    => 'Gone',
        411    => 'Length Required',
        412    => 'Precondition Failed',
        413    => 'Request Entity Too Large',
        414    => 'Request-URI Too Long',
        415    => 'Unsupported Media Type',
        416    => 'Requested Range Not Satisfiable',
        417    => 'Expectation Failed',

        500    => 'Internal Server Error',
        501    => 'Not Implemented',
        502    => 'Bad Gateway',
        503    => 'Service Unavailable',
        504    => 'Gateway Timeout',
        505    => 'HTTP Version Not Supported'
        );

    /**
     * Nesting level of the output buffering mechanism
     *
     * @var int
     * @access public
     */
    var $ob_level;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ob_level = ob_get_level();
        // Note:  Do not log messages from this constructor.
        hei::trace('Exceptions Class Initialized', 'core.Exceptions', 'debug');
    }

    // --------------------------------------------------------------------

    /**
     * 404 Page Not Found Handler
     *
     * @access    private
     * @param    string    the page
     * @param     bool    log error yes/no
     * @return    string
     */
    function show_404($page = '', $log_error = TRUE)
    {
        $heading = "404 Page Not Found";
        $message = "The page you requested was not found.";

        // By default we log this, but allow a dev to skip it
        if ($log_error)
        {
            hei::trace('404 Page Not Found --> '.$page, 'core.Exceptions', 'error');
        }

        echo $this->show_error($heading, $message, 'error_404', 404);
        exit;
    }

    // --------------------------------------------------------------------

    /**
     * General Error Page
     *
     * This function takes an error message as input
     * (either as a string or an array) and displays
     * it using the specified template.
     *
     * @access    private
     * @param    string    the heading
     * @param    string    the message
     * @param    string    the template name
     * @param     int        the status code
     * @return    string
     */
    function show_error($heading, $message, $template = 'error_general', $status_code = 500)
    {
        $this->set_status_header($status_code);

        if (empty($heading) && empty($message))
        {
            return ;
        }

        $message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';

        if (ob_get_level() > $this->ob_level + 1)
        {
            ob_end_flush();
        }
        ob_start();

        foreach (array(SYS_PATH, HEI_PATH) as $path)
        {
            if (file_exists($path.'/errors/'.$template.'.php'))
            {
                include($path.'/errors/'.$template.'.php');
                break;
            }
        }

        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer == '' ? $message : $buffer;
    }

    // ------------------------------------------------------------------------

    /**
     * Set HTTP Status Header
     *
     * @access    public
     * @param    int        the status code
     * @param    string
     * @return    void
     */
    public function set_status_header($code = 200, $text = '')
    {
        if ($code == '' OR ! is_numeric($code))
        {
            hei::show_error('Status codes must be numeric', 500);
        }

        if (isset($this->stati[$code]) AND $text == '')
        {
            $text = $this->stati[$code];
        }

        if ($text == '')
        {
            hei::show_error('No status text available.  Please check your status code number or supply your own message text.', 500);
        }

        $server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;

        if (substr(php_sapi_name(), 0, 3) == 'cgi')
        {
            header("Status: {$code} {$text}", TRUE);
        }
        else if ($server_protocol == 'HTTP/1.1' OR $server_protocol == 'HTTP/1.0')
        {
            header($server_protocol." {$code} {$text}", TRUE, $code);
        }
        else
        {
            header("HTTP/1.1 {$code} {$text}", TRUE, $code);
        }
    }


}
// END Exceptions Class

/* End of file Exceptions.php */