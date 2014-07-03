<?php
abstract class Lib_Cache {
    protected static $_instance = array();
    
    protected $_config;
    
    public static function getInstance ($label = 'default'){
    
        if (!empty(self::$_instance[$label])) return self::$_instance[$label];
    
        throw new Lib_Exception_DataMapper("DataMapper with label {$label} not found");
    }
    
    public static function initInstance ($config, $label = 'default'){
    
        if (!empty(self::$_instance[$label])){
            throw new Lib_Exception_Cache("Cache with label {$label} exist");
        }
    
        $type = $config['type'];
    
        $class = 'Lib_Cache_' . ucfirst(strtolower($type));
    
        if (!class_exists($class)){
            throw new Lib_Exception_Cache("Cache with class {$class} not found");
        }
    
        $dm = new $class($config);
    
        self::$_instance[$label] = $dm;
    
        return $dm;
    }
    
    public function __construct($config){
        $this->_config = $config;
    }
    
    abstract public function set($key, $value, $expire = 0);
    abstract public function get($key);
    abstract public function delete($key);
    
}