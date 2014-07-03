<?php
class Lib_DataMapper_Default extends Lib_DataMapper {
    
    public function getNews($id = 0, $limit = 100, $offset = 0){
        $model = new Model_News();
        $query = "SELECT * FROM " . $this->_db->escapeParam($this->_db->getDbName()) .
                 "." . $this->_db->escapeParam($model->getTable()) ;
        
        if ($id){
            $key = $this->genCacheKey(array('data', $model, $model->get($model->getPk())));
                        
            $query .= " WHERE `id` = " . $this->_db->escapeValue($id) .
                      " LIMIT 1";
            
            $row = $this->_db->getQuery()->query($query)->fetchRow();
            $model->setData($row ? $row : array());
            return $model;
        }
        else {
            $query .= " ORDER BY `datetime` DESC";
            
            if ($limit){
                $query .= " LIMIT {$limit}";
                if ($offset) $query .= " OFFSET {$offset}";
            }
            
            $models = array();
            $qObj = $this->_db->getQuery()->query($query);
            while($row = $qObj->fetchRow()){
                $model = new Model_News($row ? $row : array());
                $models[] = $model;
            }
            
            return $models;
        }

    }
    
    public function getNewsCount(){
        $model = new Model_News();
        $key = $this->genCacheKey(array('cnt', $model, 'total_records'));
        
        if (($cnt = $this->getCacheValue($key)) !== false && $cnt) return $cnt;
            
        
        $query = "SELECT count(`id`) as cnt FROM " . $this->_db->escapeParam($this->_db->getDbName()) .
        "." . $this->_db->escapeParam($model->getTable()) ;
        
        $row = $this->_db->getQuery()->query($query)->fetchRow();
        
        if ($this->hasCache()){
            $this->setCacheValue($key, $row['cnt'], $this->_cacheExpire);
        }
        return $row['cnt'];
    }
}
