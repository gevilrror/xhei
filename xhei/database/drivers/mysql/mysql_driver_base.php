<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * MySQL Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @author GevilRror
 */
class HEI_DB_mysql_driver extends HEI_DB {

    var $dbdriver = 'mysql';

	/**
	 * Whether to use the MySQL "delete hack" which allows the number
	 * of affected rows to be shown. Uses a preg_replace when enabled,
	 * adding a bit more processing to all queries.
	 */
	var $delete_hack = TRUE;

    /**
     * Non-persistent database connection
     *
     * @access    private called by the base class
     * @return    resource
     */
    function db_connect()
    {
        if ($this->port != '')
        {
            $this->hostname .= ':'.$this->port;
        }

        return @mysql_connect($this->hostname, $this->username, $this->password, TRUE);
    }

    // --------------------------------------------------------------------

    /**
     * Persistent database connection
     *
     * @access    private called by the base class
     * @return    resource
     */
    function db_pconnect()
    {
        if ($this->port != '')
        {
            $this->hostname .= ':'.$this->port;
        }

        return @mysql_pconnect($this->hostname, $this->username, $this->password);
    }

    // --------------------------------------------------------------------

    /**
     * Reconnect
     *
     * Keep / reestablish the db connection if no queries have been
     * sent for a length of time exceeding the server's idle timeout
     *
     * @access    public
     * @return    void
     */
    function reconnect()
    {
        if (mysql_ping($this->conn_id) === FALSE)
        {
            $this->conn_id = FALSE;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Select the database
     *
     * @access    private called by the base class
     * @return    resource
     */
    function db_select()
    {
        return @mysql_select_db($this->database, $this->conn_id);
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
        return @mysql_set_charset($charset, $this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * @access    private called by the base class
     * @param    string    an SQL query
     * @return    resource
     */
    function _execute($sql)
    {
        $sql = $this->_prep_query($sql);
        return @mysql_query($sql, $this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * Prep the query
     *
     * If needed, each database adapter can prep the query string
     *
     * @access    private called by execute()
     * @param    string    an SQL query
     * @return    string
     */
    function _prep_query($sql)
    {
        // "DELETE FROM TABLE" returns 0 affected rows This hack modifies
        // the query so that it returns the number of affected rows
        if ($this->delete_hack === TRUE)
        {
            if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql))
            {
                $sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
            }
        }

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Affected Rows
     *
     * @access    public
     * @return    integer
     */
    function affected_rows()
    {
        return @mysql_affected_rows($this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * Insert ID
     *
     * @access    public
     * @return    integer
     */
    function insert_id()
    {
        return @mysql_insert_id($this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * The error message string
     *
     * @access    private
     * @return    string
     */
    function _error_message()
    {
        return mysql_error($this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * The error message number
     *
     * @access    private
     * @return    integer
     */
    function _error_number()
    {
        return mysql_errno($this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * The error message number
     *
     * @access    private
     * @return    integer
     */
    function simple_result($result_id)
    {
        return mysql_fetch_assoc($result_id);
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access    public
     * @param    resource
     * @return    void
     */
    function _close($conn_id)
    {
        @mysql_close($conn_id);
    }

}

/* End of file mysql_driver_base.php */