<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * Database Driver Class
 *
 * This is the platform-independent base DB implementation class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * @author GevilRror
 */
class HEI_DB_driver_base {

    var $username;
    var $password;
    var $hostname;
    var $database;
    var $port           = '';

    var $server         = array();

    var $dbdriver       = 'mysql';
    var $dbprefix       = '';
    var $char_set       = 'utf8';
    var $dbcollat       = 'utf8_general_ci';
    var $autoinit       = TRUE; // Whether to automatically initialize the DB
    var $swap_pre       = '';
    var $pconnect       = FALSE;

    var $server_id      = NULL;
    var $conn_id        = FALSE;
    var $result_id      = FALSE;
    var $is_write_type  = FALSE;
    var $benchmark      = 0;
    var $query_count    = 0;

    var $save_queries   = FALSE;
    var $queries        = array();
    var $query_times    = array();

    // These are use with Oracle
    var $stmt_id;
    var $curs_id;
    var $limit_used;



    /**
     * Constructor.  Accepts one parameter containing the database
     * connection settings.
     *
     * @param array
     */
    function __construct($params)
    {
        if (is_array($params))
        {
            foreach ($params as $key => $val)
            {
                $this->$key = $val;
            }
        }

        //读写分离
        if(empty($hostname))
        {
            $this->set_server();
        }

        Hei::trace('Database Driver '.$this->dbdriver.' Class Initialized', 'database.DB_driver', 'debug');
    }

    // --------------------------------------------------------------------

    /**
     * 销毁的时候执行
     */
    function __destruct(){
        $this->close();
    }

    // --------------------------------------------------------------------

    /**
     * Initialize Database Settings
     *
     * @access    private Called by the constructor
     * @param    mixed
     * @return    void
     */
    function initialize()
    {
        // If an existing connection resource is available
        // there is no need to connect and select the database
        if (is_resource($this->conn_id) OR is_object($this->conn_id))
        {
            return TRUE;
        }

        // ----------------------------------------------------------------

        // Connect to the database and set the connection ID
        $this->conn_id = ($this->pconnect == FALSE) ? $this->db_connect() : $this->db_pconnect();

        // No connection resource?  Throw an error
        if ( ! $this->conn_id)
        {
            Hei::trace('Unable to connect to the database server "'.$this->hostname.'"', 'database.DB_driver', 'error');

            return FALSE;
        }

        // ----------------------------------------------------------------

        // Select the DB... assuming a database name is specified in the config file
        if ($this->database != '')
        {
            if ( ! $this->db_select())
            {
                Hei::trace('Unable to select database: '.$this->database, 'database.DB_driver', 'error');

                return FALSE;
            }
            else
            {
                // We've selected the DB. Now we set the character set
                if ( ! $this->db_set_charset($this->char_set, $this->dbcollat))
                {
                    return FALSE;
                }

                return TRUE;
            }
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Set client character set
     *
     * @access    public
     * @param    string
     * @param    string
     * @return    resource
     */
    function db_set_charset($charset, $collation)
    {
        if ( ! $this->_db_set_charset($this->char_set, $this->dbcollat))
        {
            Hei::trace('Unable to set database connection charset: '.$this->char_set, 'database.DB_driver', 'error');

            return FALSE;
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Set client character set
     *
     * @access    public
     * @param    string
     * @param    string
     * @return    resource
     */
    function set_server($type = 'read')
    {
        if(isset($this->server_id))
        {
            $this->server[$this->server_id]['conn_id'] = $this->conn_id;
        }
        //检验当前
        else if($this->server_id == $type)
        {
            return ;
        }
        else if( ! isset($this->server[$type]))
        {
            reset($this->server);
            $type = key($this->server);

             if($this->server_id == $type)
             {
                return ;
             }
        }

        $server = $this->server[$type];

        $this->server_id = $type;
        $this->username = $server['username'];
        $this->password = $server['password'];
        $this->hostname = $server['hostname'];
        $this->database = $server['database'];
        $this->port     = isset($server['port']) ? $server['port'] : '';
        $this->conn_id  = isset($server['conn_id']) ? $server['conn_id'] : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * Accepts an SQL string as input and returns a result object upon
     * successful execution of a "read" type query.  Returns boolean TRUE
     * upon successful execution of a "write" type query. Returns boolean
     * FALSE upon failure, and if the $db_debug variable is set to TRUE
     * will raise an error.
     *
     * @access    public
     * @param    string    An SQL query string
     * @param    array    An array of binding data
     * @return    mixed
     */
    function query($sql, $binds = FALSE, $return_type = 'array')
    {
        if ($sql == '')
        {
            return FALSE;
        }

        // Verify table prefix and replace if necessary
        if ( ($this->dbprefix != '' AND $this->swap_pre != '') AND ($this->dbprefix != $this->swap_pre) )
        {
            $sql = preg_replace("/(\W)".$this->swap_pre."(\S+?)/", "\\1".$this->dbprefix."\\2", $sql);
        }

        // Save the query for debugging
        if ($this->save_queries == TRUE)
        {
            $this->queries[] = $sql;

            // Start the Query Timer
            $time_start = list($sm, $ss) = explode(' ', microtime());
        }

        //读写分离处理

        // Was the query a "write" type?
        // If so we'll simply return true
        $this->set_server(($this->is_write_type = $this->is_write_type($sql)) === TRUE ? 'write' : 'read');

        // Run the Query
        if (FALSE === ($this->result_id = $this->simple_query($sql)))
        {
            if ($this->save_queries == TRUE)
            {
                $this->query_times[] = 0;
            }

            return FALSE;
        }

        if ($this->save_queries == TRUE)
        {
            // Stop and aggregate the query time results
            $time_end = list($em, $es) = explode(' ', microtime());
            $this->benchmark += ($em + $es) - ($sm + $ss);

            $this->query_times[] = ($em + $es) - ($sm + $ss);

            // Increment the query counter
            $this->query_count++;
        }

        if ($this->is_write_type)
        {
            return TRUE;
        }

        // Return TRUE if we don't need to create a result object
        // Currently only the Oracle driver uses this when stored
        // procedures are used
        // $return_type
        // object/array/bool
        if ($return_type == 'array')
        {

            $result = array();

            while ($row = $this->simple_result($this->result_id))
            {
                $result[] = $row;
            }

            return $result;
        }

        else if ($return_type == 'object')
        {

            // Load and instantiate the result driver

            $driver         = $this->load_rdriver();
            $RES            = new $driver();
            $RES->conn_id   = $this->conn_id;
            $RES->result_id = $this->result_id;

            if ($this->dbdriver == 'oci8')
            {
                $RES->stmt_id       = $this->stmt_id;
                $RES->curs_id       = NULL;
                $RES->limit_used    = $this->limit_used;
                $this->stmt_id      = FALSE;
            }

            // oci8 vars must be set before calling this
            $RES->num_rows    = $RES->num_rows();

            return $RES;

        }

        else
        {
            return TRUE;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Load the result drivers
     *
     * @access    public
     * @return    string    the name of the result class
     */
    function load_rdriver()
    {
        $driver = 'HEI_DB_'.$this->dbdriver.'_result';

        if ( ! class_exists($driver))
        {
            include_once(HEI_PATH.'/database/DB_result.php');
            include_once(HEI_PATH.'/database/drivers/'.$this->dbdriver.'/'.$this->dbdriver.'_result.php');
        }

        return $driver;
    }

    // --------------------------------------------------------------------

    /**
     * Simple Query
     * This is a simplified version of the query() function.  Internally
     * we only use it when running transaction commands since they do
     * not require all the features of the main query() function.
     *
     * @access    public
     * @param    string    the sql query
     * @return    mixed
     */
    function simple_query($sql)
    {
        if ( ! $this->conn_id)
        {
            $this->initialize();
        }

        return $this->conn_id ? $this->_execute($sql) : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Determines if a query is a "write" type.
     *
     * @access    public
     * @param    string    An SQL query string
     * @return    boolean
     */
    function is_write_type($sql)
    {
        if ( ! preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK)\s+/i', $sql))
        {
            return FALSE;
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Calculate the aggregate query elapsed time
     *
     * @access    public
     * @param    integer    The number of decimal places
     * @return    integer
     */
    function elapsed_time($decimals = 6)
    {
        return number_format($this->benchmark, $decimals);
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access    public
     * @return    void
     */
    function close()
    {
        if (is_resource($this->conn_id) OR is_object($this->conn_id))
        {
            $this->_close($this->conn_id);
        }
        $this->conn_id = FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Dummy method that allows Active Record class to be disabled
     *
     * This function is used extensively by every db driver.
     *
     * @return    void
     */
    protected function _reset_select()
    {
    }

}

/* End of file DB_driver_base.php */