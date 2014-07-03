<?php
class Ctrl_Error extends Lib_Ctrl_Web {
    protected $availableFormats = array('html');
    protected $decoratorTemplate = 'error';
    
    public function __construct($params = array(), $dispatcher = null)
    {
        if (!in_array(Lib_Request::getInstance()->getFormat(), $this->availableFormats)){
            Lib_Request::getInstance()->setFormat($this->availableFormats[0]);
        }
        
        parent::__construct(array(), $dispatcher);
                
        
    }
    
    public function init(){
        $params = $this->getParams();
        $this->setAction('index');
    }
    
    protected function _checkParam($params){
        return true;
    }
    
    public function index(){
        
    }
    
}