<?php
class Model_Test extends Lib_Model{
    protected $_table = 'test';
    
    public function getData(){
        $this->set('date', date('Y-m-d H:i:s'));
        
        return parent::getData();
    }
}

/*
 * CREATE TABLE `test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 PACK_KEYS=0;
 */