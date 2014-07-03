<?php
class Model_News extends Lib_Model{
    protected $_table = 'news';
    
    public $shortText;
    public $fullText;
    
    public function setData(array $data){
        parent::setData($data);
        
        if ($this->text) $this->setText($this->text);
    }
    
    public function setText($text){
       if ($text){
           $pos = strpos($text, '<!--more-->');
           $this->shortText = substr($text, 0, $pos!==false ? $pos : strlen($text));
           $this->fullText = str_replace('<!--more-->', '', $text);
       }
       $this->text = $text;
    }
}

/*CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(200) NOT NULL,
  `text` mediumtext,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subject` (`subject`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 PACK_KEYS=0;*/