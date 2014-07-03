<?php
class Lib_OutputEscaperObject
{
  protected
    $object = null,
    $escaper = null;

  /**
   * Constructor.
   *
   * @param mixed $value  The value to mark as safe
   */
  public function __construct($object, $escaper)
  {
    $this->object = $object;
    $this->escaper = $escaper;
    
    
  }

  public function __call($method, $arguments){

  	$result = call_user_func_array(array($this->object, $method), $arguments);
  	if (is_object($result) && ($result instanceof Lib_GlobalVars)) return $result;
  	
  	if (is_object($result)) return new Lib_OutputEscaperObject($result, $this->escaper);
    
  	return  $this->escaper->htmlEscapeExt($result);
  }

  public function __set($name, $value){
      $this->object->$name = $value;
  }
  public function __get($name){
      return  $this->escaper->htmlEscapeExt($this->object->$name);
  }
  
  public function __isset($name){
      return isset($this->object->$name);
  }
  
}
?>