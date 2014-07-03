<?php
class Lib_Db_Mysql_Query
{
    /**
	 * Text of the query
	 *
	 * @var string
	 * @access private
	 */
    protected $_query;

    /**
	 * Result of execured query
	 *
	 * @var resource
	 * @access private
	 */
    protected $_res;

    /**
	 * Shows if the query was executed or was not
	 *
	 * @var bool
	 * @access private
	 */
    protected $_executed;

    /**
	 * Rows containing data fetched by the query
	 *
	 * @var array
	 * @access private
	 */
    protected $_rows;
    
    /**
     *
     * @var Lib_Db_Mysql
     */
    protected $_db;

    /**
	 * Class contructor
	 *
	 * @param string $query
	 * @param mixed additional params which will sprintf'ed in $query
	 *
	 * @return Query
	 */
    function __construct(Lib_Db_Mysql $db){
        $this->_db = $db;
    }


    public function getDbh(){
        return $this->_db->getDbh();
    }
    
    public function query($query)
    {
        if (func_num_args() > 1)
        {
            $query = call_user_func_array("sprintf", $this->escapingArgs(func_get_args()));
        }

        $this->_query = $query;
        $this->_executed = false;
        $this->_current = 0;
        $this->_rows = null;
        
        return $this;
    }
    
    protected function escapingArgs($vals){
        foreach ($vals as $idx => $val){
            if ($idx == 0) continue;
            
            $vals[$idx] = $this->_db->escapeValue($val);
        }
        
        return $vals;
    }

    /**
	 * Determines if query was executed
	 *
	 * @return bool, true = executed, false = was not executed
	 */
    function isExecuted()
    {
        return $this->_executed;
    }


    /**
	 * Executes query to db
	 *
	 * @return bool, true = success execution, false = execution failed
	 */
    function execute()
    {
        $this->_executed = false;
        $this->_res = null;
        
        $this->_res = mysql_query($this->_query, $this->getDbh());
        
        if ($mysqlerr = mysql_error($this->getDbh()))
        {
            throw new Lib_Exception_Db_Mysql_Query("Query was not executed err:{$mysqlerr} query: {$this->_query}");
            return false;
        }

        $this->_executed = true;
        return true;
    }

    /**
	 * Gets all data from the database (actually retreives it), fetched by a query
	 *
	 * @return array
	 */
    function fetchRows()
    {
        if (!$this->isExecuted())
        {
            if (!$this->execute())
            {
                return null;
            }
        }

        $r = array();
        while ($row = mysql_fetch_assoc($this->_res))
        {
            $r[] = $row;
        }

        $this->_rows = $r;
        return $r;
    }


    function fetchRow()
    {
        if (!$this->isExecuted())
        {
            if (!$this->execute())
            {
                return null;
            }
        }

        $row = mysql_fetch_assoc($this->_res);

        return $row;

    }

    /**
	 * Returns data fetched from the database (if it was not retreived, uses $this->fetchRows() method)
	 *
	 * @return array
	 */
    function getRows()
    {
        if ($this->_rows == null)
        {
            return $this->fetchRows();
        }

        return $this->_rows;
    }

    function getRow($current_row = 0)
    {
        if ($current_row == 0 && !isset($this->_rows[0])) return $this->fetchRow();
        elseif (isset($this->_rows[0]) ) return $this->_rows[0];
        else
        {
            $rows = $this->getRows();
            if (isset($rows[0])) return $rows[0];
            else return array();
        }


    }

    function affectedRows()
    {
        return mysql_affected_rows($this->getDbh());
    }

    function lastError()
    {
        return mysql_error($this->getDbh());
    }
    
    function lastErrno()
    {
        return mysql_errno($this->getDbh());
    }

    function insertId()
    {
        return mysql_insert_id($this->getDbh());
    }
    
    function numRows($res = null){
    	if (!$res) $res = $this->_res;
    	return mysql_num_rows($res);
    }
}

?>