<?php
class Lib_Db_Mysql extends Lib_Db {
   
    public function __construct($config){
        $defConfig = array(
            'host' => '',
            'dbname' => '',
            'user' => '',
            'pass' => '',
            'charset' => '',
        );
        
        $config = array_merge($defConfig, $config);

        $host       = $config['host'];
        $dbname     = $config['dbname'];
        $dbuser     = $config['user'];

        if (!$host)     throw new Lib_Exception_Db_Mysql('Host to db is empty');
        if (!$dbname)   throw new Lib_Exception_Db_Mysql('DB name is empty');
        if (!$dbuser)   throw new Lib_Exception_Db_Mysql('User name for db is empty');
        
        parent::__construct($config);
                
    }
    
    /**
     *
     * @return Lib_Db_Mysql_Query
     */
    public function getQuery(){
        return new Lib_Db_Mysql_Query($this);;
    }
    
    

    public function createConnetction(){
        $host       = $this->_config['host'];
        $dbname     = $this->_config['dbname'];
        $dbuser     = $this->_config['user'];
        $dbpasswd   = $this->_config['pass'];

                
        
        $dbh = @mysql_connect($host, $dbuser, $dbpasswd, true);
        if (!$dbh) throw new Lib_Exception_Db_Mysql(sprintf('Could not connect: %s', @mysql_error($dbh)));
        
        if (!@mysql_select_db($dbname, $dbh)) throw new Lib_Exception_Db_Mysql("Can't use {$dbname} : " . @mysql_error($dbh));
        
        $this->_dbh = $dbh;
        
        if (isset($this->_config['charset']) && $this->_config['charset'])
        {
            $this->setCharset($dbh, $this->_config['charset']);
        }
        
        return $dbh;
        
        
    }
    
    public function setCharset($dbh, $charset){
        $charset = $this->escapeValue($charset);
        $q = $this->getQuery()->query("SET NAMES '{$charset}'");
        return $q->execute();
    }
    
    public function __destruct(){
        if ($this->_dbh) mysql_close($this->_dbh);
    }
    
    public function escapeValue($val){
        return mysql_escape_string($val);
    }
    
    public function escapeParam($val){
        if (preg_match('/^`.*?`$/', $val)) return $val;
        
        return '`' . $val . '`';
    }
    
    public function getDbName(){
        return $this->_config['dbname'];
    }
}
?>