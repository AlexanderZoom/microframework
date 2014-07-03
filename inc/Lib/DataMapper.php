<?php
class Lib_DataMapper {
    protected static $_instance = array();
    
    /**
     *
     * @var Lib_Db
     */
    protected $_db;
    
    /**
     *
     * @var Lib_Cache
     */
    protected $_cache;
    
    protected $_cacheEnable = true;
    
    protected $_cacheExpire = 5;
    
    public static function getInstance ($label = 'default'){
        
        if (!empty(self::$_instance[$label])) return self::$_instance[$label];
    
        throw new Lib_Exception_DataMapper("DataMapper with label {$label} not found");
    }
    
    public static function initInstance (Lib_Db $db, $label = 'default'){
    
        if (!empty(self::$_instance[$label])){
            throw new Lib_Exception_DataMapper("DataMapper with label {$label} exist");
        }
    
            
        $class = 'Lib_DataMapper_' . ucfirst(strtolower($label));
    
        if (!class_exists($class)){
            throw new Lib_Exception_DataMapper("DataMapper with class {$class} not found");
        }
    
        $dm = new $class($db);
    
        self::$_instance[$label] = $dm;
    
        return $dm;
    }
    
    public function __construct(Lib_Db $db){
        $this->_db = $db;
    }
    
    public function add(Lib_Model $model){
        $table = $model->getTable();
        if (!$table) throw new Lib_Exception_DataMapper('add record error table for ' . get_class($model) . ' is empty');
        
        if ($model->loaded()) throw new Lib_Exception_DataMapper('add record error model ' . get_class($model) . ' loaded');
        
        $data = $model->getData();
        $keys = array();
        $values = array();
        $iter = 0;
        foreach ($data as $key => $value){
            $keys[$iter] = $this->_db->escapeParam($key);
            $values[$iter] = "'" . $this->_db->escapeValue($value) . "'";

            $iter++;
        }
        
        $query = "INSERT INTO " . $this->_db->escapeParam($this->_db->getDbName()) .
                 "." . $this->_db->escapeParam($table) .
                 " (" . implode(', ', $keys) . ")".
                 " VALUES (" . implode(', ', $values) . ")";
        
        $this->_db->getQuery()->query($query)->execute();
        $model->set($model->getPk(), $this->_db->getQuery()->insertId());
        
        
        return true;
    }
    
    public function update(Lib_Model $model){
        $table = $model->getTable();
        if (!$table) throw new Lib_Exception_DataMapper('update record error table for ' . get_class($model) . ' is empty');
        
        if (!$model->loaded()) throw new Lib_Exception_DataMapper('update record error model ' . get_class($model) . ' was not loaded');
        
        $data = $model->getData();
        $pk = '';
        $values = array();
        
        foreach ($data as $key => $value){
            if ($model->getPk() == $key) $pk = $this->_db->escapeParam($key) . " = '" . $this->_db->escapeValue($value) . "'";
            else $values[] = $this->_db->escapeParam($key) . " = '" . $this->_db->escapeValue($value) . "'";
                    
        }
        
        $query = "UPDATE " . $this->_db->escapeParam($this->_db->getDbName()) .
        "." . $this->_db->escapeParam($table) .
        "SET " . implode(', ', $values) . " ".
        " WHERE " . $pk;
        
        $this->_db->getQuery()->query($query)->execute();
        
        return true;
    }
    
    public function save(Lib_Model $model){
        return $model->loaded() ? $this->update($model) : $this->add($model);
    }
    
    public function delete(Lib_Model $model){
        $table = $model->getTable();
        if (!$table) throw new Lib_Exception_DataMapper('delete record error table for ' . get_class($model) . ' is empty');
        
        if (!$model->loaded()) throw new Lib_Exception_DataMapper('delete record error model ' . get_class($model) . ' was not loaded');
        
        $query = "DELETE FROM " . $this->_db->escapeParam($this->_db->getDbName()) .
                 "." . $this->_db->escapeParam($table) .
                 " WHERE " . $this->_db->escapeParam($model->getPk()) . " = '" . $model->get($model->getPk()) . "'";
        
        $this->_db->getQuery()->query($query)->execute();
         
        return true;
    }
    
    public function getLastErrno(){
        return $this->_db->getQuery()->LastErrno();
    }
    
    public function getLastError(){
        return $this->_db->getQuery()->LastError();
    }
    
    public function setCache(Lib_Cache $cache){
        $this->_cache = $cache;
    }
    
    public function getCache(){
        return ($this->hasCache() ? $this->_cache : null);
    }
    
    public function genCacheKey($options){
        $lst = array();

        foreach ($options as $value){
            if (is_object($value)) $lst[] = strtolower(get_class($value));
            elseif(is_string($value)) $lst[] = $value;
            elseif(is_numeric($value)) $lst[] = $value;
            else ;
        }
        
        return strtolower(implode('_', $lst));
    }
    
    public function hasCache(){
        return ((bool) $this->_cache);
    }
    
    public function getCacheValue($key){
        if ($this->hasCache()) return $this->getCache()->get($key);

        return false;
    }
    
    public function setCacheValue($key, $value, $expire = 0){
        if ($this->hasCache()) $this->getCache()->set($key, $value, $expire);
    }
       
}