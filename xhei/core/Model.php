<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * Model Class
 *
 * @author GevilRror
 */
class HEI_Model {

    /**
     * Constructor
     *
     * @access public
     */
    function __construct()
    {
        hei::trace('Model '.__CLASS__.' Initialized', array('core.Model', 'debug'));
    }

    public function db($dbname = '', $method = '')
    {
        return hei::db($dbname, $method);
    }
}
// END Model Class

/* End of file Model.php */