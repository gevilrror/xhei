<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * URI Class
 *
 * @author GevilRror
 */
class HEI_URI {

    const FGET_FORMAT   = 'fget'; // first get variable name
    const GET_FORMAT    = 'get';
    const POST_FORMAT   = 'post';
    const PATH_FORMAT   = 'path';

    /**
     * @var array the URI rules (pattern=>route).
     */
    public $rules       = array();

    /**
     * @var string the GET variable name for route. Defaults to 'r'.
     */
    public $route_var   = 'r';

    public $controller_var   = 'c';

    public $method_var   = 'm';

    /**
     * @var 参数
     */
    public $params      = array();

    /**
     * @var 引用
     */
    public $references  = array();

    /**
     *
     */
    public $path_info   = '';


    private $_url_format = self::GET_FORMAT;

    private $_request_URI = null;

    private $_rules     = array();

    private $_route     = null;

    function __construct($params = array())
    {
        // url_format
        if (isset($params['url_format']))
        {
            $this->_url_format = $params['url_format'];
            unset($params['url_format']);
        }

        foreach($params as $k => $p)
        {
            $this->$k = $p;
        }

        // rules 初始化
        foreach($this->rules as $p => $r)
        {
            $_rule = array(
                'pattern'       => $p,
                'route'         => $r,
                'references'    => array());

            // references
            if (strpos($r, '<') !== false && preg_match_all('/<(\w+)>/', $r, $m))
            {
                foreach($m[1] as $name)
                {
                    $_rule['references'][$name] = "<$name>";
                }
            }

            $this->_rules[$p] = $_rule;
        }

        //初始化即进行url转化
        //保持输入(get,post,request)一致
        $this->parse_url();

        hei::trace('URI Class Initialized', 'core.URI', 'debug');
    }

    public function parse_url()
    {
        if ($this->_route === null)
        {
            //url格式
            if ($this->_url_format == self::PATH_FORMAT)
            {
                //获得path内容
                $this->path_info = $this->get_request_uri();

                $r = $this->match_rule();

                $this->_route = isset($_GET[$this->route_var]) ? $_GET[$this->route_var] : $r;
            }
            else if ($this->_url_format == self::FGET_FORMAT)
            {
                //获得path内容
                $this->path_info = (reset($_GET) !== false) ? urldecode(key($_GET)) : '';

                $r = $this->match_rule();

                $this->_route = isset($_GET[$this->route_var]) ? $_GET[$this->route_var] : $r;
            }
            else if (isset($_GET[$this->route_var]))
            {
                $this->_route = $_GET[$this->route_var];
            }
            else if (isset($_POST[$this->route_var]))
            {
                $this->_route = $_POST[$this->route_var];
            }
            else if (isset($_GET[$this->controller_var]))
            {
                $this->_route = $_GET[$this->controller_var].'/'.$_GET[$this->method_var];
            }
            else
            {
                $this->_route = '';
            }
        }

        return $this->_route;
    }

    public function parse_url_run()
    {

    }

    public function get_request_uri()
    {
        if ($this->_request_URI === null)
        {
            if ( ! isset($_SERVER['REQUEST_URI']) OR ! isset($_SERVER['SCRIPT_NAME']))
            {
                hei::trace('Can not get the request_uri or script_name.', 'core.URI', 'warning');
                return '';
            }

            $uri = $_SERVER['REQUEST_URI'];
            if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
            {
                $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
            }
            else if (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
            {
                $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
            }

            $this->_request_URI = urldecode(ltrim(parse_url($uri, PHP_URL_PATH), '/'));

        }

        // Do some final cleaning of the URI and return it
        return $this->_request_URI;
    }

    public function match_rule()
    {
        if ($this->path_info == '')
        {
            return '';
        }

        $route = '';

        foreach($this->_rules as $p => $r)
        {
            if (preg_match($p, $this->path_info, $matches))
            {
                $tr = array();
                foreach($matches as $key=>$value)
                {
                    //因为考虑url中参数通常不会太大, 这里将参数备份在自身变量里
                    if (isset($r['references'][$key]))
                    {
                        $tr[$r['references'][$key]] = $this->references[$key] = $value;
                    }
                    //非整数，即非正则产生的值
                    else if ( ! is_int($key))
                    {
                        $_REQUEST[$key] = $_GET[$key] = $this->params[$key] = $value;
                    }
                }

                if ( ! empty($r['references']))
                {
                    $route = strtr($r['route'],$tr);
                }
                else
                {
                    $route = $r['route'];
                }

                break;
            }
        }

        //未获取到
        if ($route === '')
        {
            hei::trace('URI was not matche', 'core.URI', 'warning');
            return '';
        }

        return $route;

    }


}
// END URI Class

/* End of file URI.php */