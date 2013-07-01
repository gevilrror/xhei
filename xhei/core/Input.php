<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * Input Class
 *
 * @author GevilRror
 */
class HEI_Input {

    var $filtering_allow_method = array('htmlspecialchars', 'trim');

    /**
     * Constructor
     */
    function __construct($config)
    {
        if (isset($config['filtering']))
        {
            $this->exec_filtering($config['filtering']);
        }

        hei::trace('InputClassInitialized', 'core.Input', 'debug');
    }

    function exec_filtering(& $filtering)
    {
        if (is_array($filtering['data'])) foreach($filtering['method'] as $method) if (in_array($method, $this->filtering_allow_method))
        {
            foreach($filtering['data'] as &$r)
            {
                $this->filtering($r, $method, false);
            }
        }
    }

    function filtering(& $data, $method, $return = true)
    {
        if (is_array($data))
        {
            foreach($data as &$v)
            {
                $v = $this->filtering($v, $method);
            }
        }
        else
        {
            $data = $method($data);
            if ($return)
            {
                return $data;
            }
        }
    }
}
// END Input Class

/* End of file Input.php */