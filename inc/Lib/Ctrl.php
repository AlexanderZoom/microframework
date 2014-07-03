<?php
abstract class Lib_Ctrl
{
    protected $params = array();
    
    protected $action = '';
    
    protected $dispatcher = null;
    
    protected $varHolder = null;
    
    
    public function __construct($params = array(), $dispatcher = null){
    	
        $this->params = $params;
        
        $this->dispatcher = $dispatcher;
        
        $this->varHolder = new Lib_ParameterHolder();
    }
        
    
    public function setAction($action)
    {
        $this->action = $action;
    }
    
    public function getAction()
    {
    	return $this->action;
    }
    
    public function gotoAction($action){
        $this->setAction($action);
        return call_user_func(array($this, $action));
    }
    
    public function runCurrentAction()
    {
    	
    	$this->beforeRunCurrentAction();
        $result = call_user_func(array($this, $this->getAction()));
        $this->afterRunCurrentAction();

        return $result;
    }
    
    protected function beforeRunCurrentAction(){
        
    }
    
    protected function afterRunCurrentAction(){
        
    }
    
    /**
     *
     * @return Lib_ParameterHolder
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
   * Sets a variable for the template.
   *
   * If you add a safe value, the variable won't be output escaped
   * by symfony, so this is your responsability to ensure that the
   * value is escaped properly.
   *
   * @param string  $name  The variable name
   * @param mixed   $value The variable value
   * @param Boolean $safe  true if the value is safe for output (false by default)
   */
    public function setVar($name, $value, $safe = false)
    {
        $this->varHolder->set($name, $safe ? new Lib_OutputEscaperSafe($value) : $value);
    }

    /**
   * Gets a variable set for the template.
   *
   * @param string $name The variable name
   *
   * @return mixed  The variable value
   */
    public function getVar($name)
    {
        return $this->varHolder->get($name);
    }

    /**
   * Gets the ParameterHolder object that stores the template variables.
   *
   * @return ParameterHolder The variable holder.
   */
    public function getVarHolder()
    {
        return $this->varHolder;
    }
    
    
    /**
   * Sets a variable for the template.
   *
   * This is a shortcut for:
   *
   * <code>$this->setVar('name', 'value')</code>
   *
   * @param string $key   The variable name
   * @param string $value The variable value
   *
   * @return boolean always true
   *
   * @see setVar()
   */
    public function __set($key, $value)
    {
        return $this->varHolder->setByRef($key, $value);
    }

    /**
   * Gets a variable for the template.
   *
   * This is a shortcut for:
   *
   * <code>$this->getVar('name')</code>
   *
   * @param string $key The variable name
   *
   * @return mixed The variable value
   *
   * @see getVar()
   */
    public function & __get($key)
    {
        return $this->varHolder->get($key);
    }

    /**
   * Returns true if a variable for the template is set.
   *
   * This is a shortcut for:
   *
   * <code>$this->getVarHolder()->has('name')</code>
   *
   * @param string $name The variable name
   *
   * @return boolean true if the variable is set
   */
    public function __isset($name)
    {
        return $this->varHolder->has($name);
    }

    /**
   * Removes a variable for the template.
   *
   * This is just really a shortcut for:
   *
   * <code>$this->getVarHolder()->remove('name')</code>
   *
   * @param string $name The variable Name
   */
    public function __unset($name)
    {
        $this->varHolder->remove($name);
    }
    
    
    /**
     * Get dispatcher
     *
     * @return Lib_Dispatcher
     */
    public function getDispatcher()
    {
        if ($this->dispatcher instanceof Lib_Dispatcher) return $this->dispatcher;
        
        return null;
    }
        
    
    public function getCtrlName(){
    	return str_replace('Ctrl_', '', get_class($this));
    }
}
?>