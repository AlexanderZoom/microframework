<?php
class Lib_Cache_MemCache extends Lib_Cache {
    
    protected $_memcacheObj;
    
    public function __construct($config){
        $this->_memcacheObj = new Memcache();
        
        if (!$this->_memcacheObj->connect ( $config['host'], $config['port'])){
            throw new Lib_Exception_Cache('Can not connect to memcache');
        }
    }
    public function set($key, $value, $expire = 0){
        $value = serialize($value);
                
        $method = 'set';
        if ($this->has($key)) $method = 'replace';
        
        if (!$this->_memcacheObj->$method($key, $value, ($this->_config['compress'] ? MEMCACHE_COMPRESSED : false), $expire)){
            throw new Lib_Exception_Cache("Can not {$method} to memcache");
        }
        
    }
    
    public function get($key){
        return unserialize($this->_memcacheObj->get($key));
    }
    
    public function has($key){
        return ( (bool) $this->_memcacheObj->get($key));
    }
    
    public function delete($key){
        if (!$this->_memcacheObj->delete($key)){
            throw new Lib_Exception_Cache('Can not delete from memcache');
        }
    }
    
    public function stat(){
        return $this->_memcacheObj->getstats();
    }
    
    public function flush(){
        return $this->_memcacheObj->flush();
    }
    
    public function __destruct(){
        $this->_memcacheObj->close();
    }
}