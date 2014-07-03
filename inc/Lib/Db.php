<?php
abstract class Lib_Db {
    protected  $_dbh;
    protected  $_config;
    
    public function __construct($config){
        $this->_config = $config;
    }
    
    public function getDbh(){
        if (!$this->_dbh) $this->createConnetction();
    
        return $this->_dbh;
    }
    
    abstract public function createConnetction();
    abstract public function escapeValue($val);
    abstract public function escapeParam($val);
    abstract public function getDbName();
    abstract public function getQuery();
    
}