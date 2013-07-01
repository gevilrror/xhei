<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * Database Class
 *
 * 注意：第一次初始化将决定本次db使用什么方法
 *
 * @author GevilRror
 */
class HEI_Database {

    //DB配置
    protected $_dbconfig = NULL;
    //DB配置
    protected $_dbmethod = NULL;

    protected $_dbdriver_loaded = array();

    /**
     * Constructor
     *
     * Sets the path to the view files and gets the initial output buffering level
     */
    public function __construct()
    {
        Hei::$_DB = array();

        Hei::trace('Database Class Initialized', 'core.Database', 'debug');
    }

    // --------------------------------------------------------------------

    /**
     * Database Loader
     *
     * @param    string    the DB credentials
     * @param    bool    whether to return the DB object
     * @param    bool    whether to enable active record (this allows us to override the config setting)
     * @return    object
     */
    public function init($params = '', $method = '')
    {
        // Load the DB config file if a DSN string wasn't passed
        if ($params == '')
        {
            //加载配置
            $this->set_dbconfig();

            $params = reset($this->_dbconfig);

        }
        else if (is_string($params) AND strpos($params, '://') === FALSE)
        {
            //加载配置
            $this->set_dbconfig();

            if ( ! is_array($this->_dbconfig[$params]))
            {
                Hei::show_error('You have specified an invalid database connection group.');
            }

            $params = $this->_dbconfig[$params];
        }

        /**
         *  parse the URL from the DSN string
         *  Database settings can be passed as discreet
         *  parameters or as a data source name in the first
         *  parameter. DSNs must have this prototype:
         *  $dsn = 'driver://username:password@hostname/database';
         */
        else if (is_string($params))
        {
            if (($dns = @parse_url($params)) === FALSE)
            {
                Hei::show_error('Invalid DB Connection String');
            }

            $params = array(
                                'dbdriver'    => $dns['scheme'],
                                'hostname'    => (isset($dns['host'])) ? rawurldecode($dns['host']) : '',
                                'username'    => (isset($dns['user'])) ? rawurldecode($dns['user']) : '',
                                'password'    => (isset($dns['pass'])) ? rawurldecode($dns['pass']) : '',
                                'database'    => (isset($dns['path'])) ? rawurldecode(substr($dns['path'], 1)) : ''
                            );

            // were additional config items set?
            if (isset($dns['query']))
            {
                parse_str($dns['query'], $extra);

                foreach ($extra as $key => $val)
                {
                    // booleans please
                    if (strtoupper($val) == "TRUE")
                    {
                        $val = TRUE;
                    }
                    else if (strtoupper($val) == "FALSE")
                    {
                        $val = FALSE;
                    }

                    $params[$key] = $val;
                }
            }
        }

        if ($method == '' || $method == 'default')
        {
            $method = $params['dbmethod'];
        }

        // No DB specified yet?  Beat them senseless...
        if ( ! isset($params['dbdriver']) OR $params['dbdriver'] == '' OR $params['dbdriver'] != basename($params['dbdriver']))
        {
            Hei::show_error('You have not selected a database driver to connect to.');
        }

        // 加载 database 操作方式
        $this->set_dbmethod($method);

        if ( ! isset($_dbdriver_loaded[$params['dbdriver']]))
        {
            $dbdriver_path = HEI_PATH.'/database/drivers/'.$params['dbdriver'].'/'.$params['dbdriver'].'_driver_'.$method.'.php';

            if ( ! is_file($dbdriver_path))
            {
                Hei::show_error('You have not selected a database driver to connect to.');
            }

            require_once($dbdriver_path);

            $_dbdriver_loaded[$params['dbdriver']] = TRUE;
        }

        // Instantiate the DB adapter
        $driver = 'HEI_DB_'.$params['dbdriver'].'_driver';
        $dbname = $params['dbname'];

        Hei::$_DB[$dbname] = NULL;

        Hei::$_DB[$dbname] = new $driver($params);

        $DB = & Hei::$_DB[$dbname] ;

        if ($DB->autoinit == TRUE)
        {
            $DB->initialize();
        }

        if (isset($params['stricton']) && $params['stricton'] == TRUE)
        {
            $DB->query('SET SESSION sql_mode="STRICT_ALL_TABLES"');
        }

        unset($DB);

        Hei::trace('Initialize '.$dbname.' Database', 'core.Database', 'debug');

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Database Loader
     *
     * @param    string    the DB credentials
     * @param    bool    whether to return the DB object
     * @param    bool    whether to enable active record (this allows us to override the config setting)
     * @return    object
     */
    protected function set_dbconfig()
    {

        //配置已经加载，直接返回
        if (isset($this->_dbconfig))
        {
            return TRUE;
        }

        // Is the config file in the environment folder?
        if (! is_file($file_path = SYS_PATH.'/config/database.php'))
        {
            Hei::show_error('The configuration file database.php does not exist.');
            return FALSE;
        }

        include($file_path);

        if ( ! isset($db) OR count($db) == 0)
        {
            Hei::show_error('No database connection settings were found in the database config file.');
            return FALSE;
        }

        $this->_dbconfig = $db;

        unset($db);

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Database Loader
     *
     * @param    string    the DB credentials
     * @param    bool    whether to return the DB object
     * @param    bool    whether to enable active record (this allows us to override the config setting)
     * @return    object
     */
    protected function set_dbmethod($method)
    {

        //配置已经加载，直接返回
        if (isset($this->_dbmethod))
        {
            return TRUE;
        }

        if (class_exists('HEI_DB'))
        {
            Hei::show_error('HEI_DB was already exist.');
            return FALSE;
        }


        // base
        // extend
        // active_rec : Active Record
        if ( ! in_array($method, array('base', 'extend', 'active_rec')))
        {
            Hei::show_error('Invalid DB Handle Type.');
            return FALSE;
        }

        // load database method class
        foreach(array('base', 'extend', 'active_rec') as $m)
        {
            require_once(HEI_PATH.'/database/DB_driver_'.$m.'.php');

            if ( $method == $m)
            {
                eval('class HEI_DB extends HEI_DB_driver_'.$m.' { }');

                $this->_dbmethod = $method;

                break;
            }
        }

        return TRUE;

    }
}

/* End of file Database.php */