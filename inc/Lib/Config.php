<?php
class Lib_Config
{
	
    private static $instance;

    private $data = array();
    
    

    function __construct($global, $local = null){
        $this->setData($global);
        if($local) $this->addData($local);
        self::$instance = $this;
    }
    
    


    /**
     
     * @return Config
     */
    public static function getInstance()
    {
        if (self::$instance === null)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
	
    
    public static function getVar($name){
    	return self::getInstance()->get($name);
    }
    
    public static function hasVar($name){
        return self::getInstance()->has($name);
    }

    
       
    public static function  specWalkArray(&$vars, &$entireArr)
    {
        foreach ($vars as $key => $value)
        {
            if (isset($entireArr[$key]))
            {
                if (is_array($value) && is_array($entireArr[$key]))
                {
                    self::specWalkArray($value, $entireArr[$key]);
                }
                else
                {
                    $entireArr[$key] = $value;
                }
            }
            else
            {
                $entireArr[$key] = $value;
            }
        }
    }

    public static function getAll(){
    	return self::getInstance()->getAllInfo();
    }

    public function set($key, $value){
    	$this->data[$key] = $value;
    }
    
    public function has($key){
    	return isset($this->data[$key]);
    }
    
    public function get($key, $default = null){
    	if($this->has($key)) return $this->data[$key];
    	
    	return $default;
    }
        
    public function getAllInfo(){
    	return $this->data;
    }

    protected function setData($data){
        $this->data = $data;
    }
    
    public function addData($data){
        self::specWalkArray($data, $this->data);
    }
}
?>