<?php
class Lib_Model extends Lib_ParameterHolder {
    
    protected $_pk = 'id';
    protected $_table = '';
    
    public function __construct($data = array()){
        if (is_array($data) && count($data)) $this->setData($data);
    }
    
    public function setData(array $data){
        $this->setAll($data);
    }
    
    public function getData(){
        return $this->getAll();
    }
    
    
    public function __set($key, $value){
        $this->set($key, $value);
    }
    
    public function __get($key){
        return $this->get($key);
    }
    
    public function __isset($key){
        return $this->has($key);
    }
    
    public function __unset($key){
        throw new Lib_Exception_Model('Operation not permited');
    }
    
    public function getTable(){
        return $this->_table;
    }
    
    public function getPk(){
        return $this->_pk;
    }
    
    public function loaded(){
        return ($this->get($this->getPk()) ? true : false);
    }
}