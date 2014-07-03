<?php
/**
 * Get global variables with html or sql escaped
 *
 * example:
 * $gb = new GlobalVars();
 * $serv = &$gb->getAll('SERVER'); //geter
 * $serv['first'] = '123456<scr\'ipt>';
 * $gb->enableHtmlEscapeStrategy(true);
 * print_r($gb->getAll('SERVER'));
 * //$gb->enableHtmlEscapeStrategy(false);
 * print_r($gb->getAll('SERVER'));
 * $per = &$gb->server('first');
 * //$gb->enableSqlEscapeStartegy(true);
 * print_r($gb->getAll('SERVER'));
 */
class Lib_GlobalVars
{
    
    protected $htmlEscapeStrategyQuoteStyle = ENT_QUOTES;
    protected $htmlEscapeStrategyCharset    = 'UTF-8';
    protected $htmlEscapeStrategyIsOn       = false;
    
    protected $sqlEscapeStrategyCaller      = array('object' => null, 'method'=>'mysql_escape_string');
    protected $sqlEscapeStrategyIsOn        = false;
    
    
    protected $escapedContainerVars = array();
    
    public function __construct($options = array())
    {
        if (is_array($options))
        {
            ;    
        }
        
    }
    
    public function setHtmlEscapeStrategy($quoteStyle = ENT_QUOTES, $charset = 'UTF-8')
    {
        $this->removeEscapedContainerVars();
        $this->htmlEscapeStrategyQuoteStyle = $quoteStyle;
        $this->htmlEscapeStrategyCharset    = $charset;
    }
    
    public function setSqlEscapeStrategy($method, $object = null)
    {
        $this->removeEscapedContainerVars();
        $this->sqlEscapeStrategyCaller      = array('object' => $object, 'method' => $method);
        
    }
    
    public function enableHtmlEscapeStrategy($flag)
    {
        $this->removeEscapedContainerVars();
        $this->htmlEscapeStrategyIsOn   = (bool) $flag;
    }
    
    public function enableSqlEscapeStartegy($flag)
    {
        $this->removeEscapedContainerVars();
        $this->sqlEscapeStrategyIsOn    = (bool) $flag;
    }
    
    public function htmlEscape($value, $quoteStyle = ENT_QUOTES, $charset = 'UTF-8')
    {
        if (is_array($value)) 
        {
            foreach ($value as $key => $val)
            {
                if (is_array($val)) $value[$key] = $this->htmlEscape($value[$key], $quoteStyle, $charset);
                elseif (!is_object($val)) $value[$key] = htmlspecialchars(($val), $quoteStyle, $charset);
                else $value[$key] = new Lib_OutputEscaperObject($value[$key] , $this);
            }
                        
        }
        elseif (!is_object($value)) $value = htmlspecialchars(($value), $quoteStyle, $charset);
        else $value = new Lib_OutputEscaperObject($value , $this); 
        
        return $value;
    }
    
    public function sqlEscape($value, $method = 'mysql_escape_string', $object = 'null')
    {
        if (is_array($value))
        {
            foreach ($value as $key => $val)
            {
                $value[$key] = $this->_sqlEscape($value, $method, $object);
            }
        }
        else $value = $this->_sqlEscape($value, $method, $object);
        
        return $value;
    }
    
    private function _sqlEscape($value, $method, $object)
    {
        if (is_object($object))
        {
            return call_user_func(array($object, $method), $value);
            
        }
        
        return call_user_func($method, $value);
    }
    
    public function removeEscapedContainerVars($globalVar = '')
    {
        if ($globalVar && isset($this->escapedContainerVars[$globalVar]))
        {
            unset($this->escapedContainerVars[$globalVar]);
        }
        elseif (!$globalVar)
        {
            $this->escapedContainerVars = null;    
        }
        else ;
    }
    
    public function &getAll($globalVar = 'GET', $link = true)
    {
        if (($this->htmlEscapeStrategyIsOn || $this->sqlEscapeStrategyIsOn) && isset($this->escapedContainerVars[$globalVar]))
        {
            return $this->escapedContainerVars[$globalVar];
        }
        
        $out = null;
        switch ($globalVar)
        {
            case 'GET':
                if ($link) $out = & $_GET;
                else $out = $_GET;
                break;
                
            case 'POST':                
                if ($link) $out = & $_POST;
                else $out = $_POST;
                break;
                
            case 'REQUEST':
                if ($link) $out = & $_REQUEST;
                else $out = $_REQUEST;
                break;   

            case 'COOKIE':
                if ($link) $out = & $_COOKIE;
                else $out = $_COOKIE;
                break;  

            case 'ENV':
                if ($link) $out = & $_ENV;
                else $out = $_ENV;
                break;    
                
            case 'FILES':
                if ($link) $out = & $_FILES;
                else $out = $_FILES;
                break;  

            case 'SERVER':
                if ($link) $out = & $_SERVER;
                else $out = $_SERVER;
                break;  

            case 'SESSION':
                if ($link) $out = & $_SESSION;
                else $out = $_SESSION;
                break;  
                        
        }
        
        if ( ($this->htmlEscapeStrategyIsOn || $this->sqlEscapeStrategyIsOn) && count($out))
        {
            $outEscaped = array();
            foreach ($out as $key => $value)
            {
                if ($this->sqlEscapeStrategyIsOn)
                {
                    $value = $this->sqlEscape($value, $this->sqlEscapeStrategyCaller['method'], $this->sqlEscapeStrategyCaller['object']);
                    
                }                                
                
                if ($this->htmlEscapeStrategyIsOn)
                {
                    $value = $this->htmlEscape($value, $this->htmlEscapeStrategyQuoteStyle, $this->htmlEscapeStrategyCharset);
                }
                
                $outEscaped[$key] = $value;
            }
            
            $this->escapedContainerVars[$globalVar] = $outEscaped;
            
            return $this->escapedContainerVars[$globalVar];
        }
        
        return $out;
    }
    
    public function &getOne($name, $globalVar = 'GET')
    {
        $vars = &$this->getAll($globalVar, true);
        
        if (!$this->has($name, $globalVar)) return null;
        
         return $vars[$name];   
    }
    
    
    public function has($name, $globalVar = 'GET'){
        $vars = &$this->getAll($globalVar, true);        
        return isset($vars[$name]) ? true : false ;
    }
    
    public function hasGet($name){
    	$globalVar = 'GET';
        $vars = &$this->getAll($globalVar, true);        
        return isset($vars[$name]) ? true : false ;
    }
    
    public function hasPost($name){
        $globalVar = 'POST';
        $vars = &$this->getAll($globalVar, true);        
        return isset($vars[$name]) ? true : false ;
    }
    
    public function hasRequest($name){
        $globalVar = 'REQUEST';
        $vars = &$this->getAll($globalVar, true);        
        return isset($vars[$name]) ? true : false ;
    }
    
    public function &get($name, $default = null)
    {
        $globalVar = 'GET';
        
        if (!$this->has($name, $globalVar)) return $default;
        
        return $this->getOne($name, $globalVar);
    }
    
    public function &post($name, $default = null)
    {
        $globalVar = 'POST';
        
        if (!$this->has($name, $globalVar)) return $default;
        
        return $this->getOne($name, $globalVar);
    }
    
    public function &request($name, $default = null)
    {
        $globalVar = 'REQUEST';
        
        if (!$this->has($name, $globalVar)) return $default;
        
        return $this->getOne($name, $globalVar);
    }
    
    public function &cookie($name, $default = null)
    {
        $globalVar = 'COOKIE';
        
        if (!$this->has($name, $globalVar)) return $default;
        
        return $this->getOne($name, $globalVar);
    }
    
    public function &env($name, $default = null)
    {
        $globalVar = 'ENV';
        
        if (!$this->has($name, $globalVar)) return $default;
        
        return $this->getOne($name, $globalVar);
    }
    
    public function &files($name, $default = null)
    {
        $globalVar = 'FILES';
        
        if (!$this->has($name, $globalVar)) return $default;
        
        return $this->getOne($name, $globalVar);
    }
    
    public function &server($name, $default = null)
    {
        $globalVar = 'SERVER';
        
        if (!$this->has($name, $globalVar)) return $default;
        
        return $out =& $this->getOne($name, $globalVar);
    }
    
    public function &session($name, $default = null)
    {
        $globalVar = 'SESSION';
        
        if (!$this->has($name, $globalVar)) return $default;
        
        return $this->getOne($name, $globalVar);
    }     

    public function htmlEscapeExt($value)
    {
    	
        if ($this->htmlEscapeStrategyIsOn) return $this->htmlEscape($value, $this->htmlEscapeStrategyQuoteStyle, $this->htmlEscapeStrategyCharset);
        else return $value;
    }
}
/*
$gb = new GlobalVars();
$serv = &$gb->getAll('SERVER'); //geter
$serv['first'] = '123456<scr\'ipt>';
$gb->enableHtmlEscapeStrategy(true);
print_r($serv);
$serv['second'] = '<br>';
print_r($gb->getAll('SERVER'));
//$gb->enableHtmlEscapeStrategy(false);
print_r($gb->getAll('SERVER'));
echo $per = &$gb->server('first');
$per = 'dfuiwhui';
//$gb->enableSqlEscapeStartegy(true);
print_r($gb->getAll('SERVER'));*/
?>