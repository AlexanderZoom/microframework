<?php
class Lib_Dispatcher
{
	
	
    protected static $instance;

    protected function __clone() {}
    
    protected $requestParams = array();
    
    protected $factory = array();
    protected $storage = null;

    protected $ctrl = null;

    protected $nestingForward = 5;
    protected $nestingForwardCount = 0;

    
    public static function hasInstance(){
    	return self::$instance ? true : false;
    }
    
   
    /**
     * get Request instance
     *
     * @return Lib_Dispatcher
     */
    public static function getInstance()
    {
		
        
        if (self::$instance) return self::$instance;
        
        self::$instance = new Lib_Dispatcher();
        
        return self::$instance;
    }


    protected function __construct()
    {

        $this->factory['global_vars'] = new Lib_GlobalVars();
    }


    public function getRequestParams()
    {
        return $this->requestParams;
    }


    /**
     * Get global Vars object
     *
     * @return Lib_GlobalVars
     */
    public function getGlobalVarsObject()
    {
        return $this->factory['global_vars'];
    }

    

    /**
     * set current control
     *
     * @param Lib_Ctrl $ctrl
     */
    public function setCtrl(Lib_Ctrl $ctrl)
    {
        $this->ctrl = $ctrl;
    }

    /**
     * get control class
     *
     * @return Lib_Ctrl
     */
    public function getCtrl()
    {
        return $this->ctrl;
    }

   
    public function getCtrlClassName($ctrl)
    {
        $action = ucfirst($ctrl);
        return "Ctrl_{$ctrl}";
    }

    /**
     *
     * Get action name for current process
     *
     * @return string
     */
    public function getCurrentCtrlName()
    {
    	$this->requestParams['ctrl'] = ucfirst($this->requestParams['ctrl']);
        if (!$this->requestParams['ctrl'])
        {
            $this->requestParams['ctrl'] = 'main';
        }

        $actionName = $this->requestParams['ctrl'];
        return $actionName;
    }

    public function getCtrlParams()
    {
        return $this->requestParams['params'];
    }

    public function initialize()
    {
        
              //disable or enable site.
            
        	$response = Lib_Response::getInstance();
        	$response->init();
        
            $appIsEnable = Lib_Config::getVar('site_enable');
            
            if (!$appIsEnable)
            {
                $response->setStatusCode(501);
                
                $disablePage = Lib_Config::getVar('app_disable_page');
                
                ob_start();
        		ob_implicit_flush(0);

                if ($disablePage && php_sapi_name() != 'cli') require_once($disablePage);
                else echo "site off";
                $out =  ob_get_clean();
                
                $response->setContent($out);
                $response->send();
                exit;
            }
            
            //init
                        
            if (php_sapi_name() =='cli') $this->requestParams = Lib_ParseRequest::makeCli($this);
            else $this->requestParams = Lib_ParseRequest::make($this);
                     
            if (php_sapi_name() !='cli' && $this->requestParams['request_format']){
                Lib_Request::getInstance()->setFormat($this->requestParams['request_format']);
            }
                
        	$statusCode = 200;
            $response->setStatusCode($statusCode);

    }
    
    
    
    public function forward($ctrlName='', $actionName = '', $statusCode = 200)
    {
    	$this->nestingForwardCount++;
    	if ($this->nestingForwardCount >= $this->nestingForward){
    		throw new Lib_Exception_Dispatcher("Max level nesting forward level {$this->nestingForwardCount} reached");
    	}
    	
    	
        if (!$ctrlName)
        {
            $ctrlName = $this->getCurrentCtrlName();
        }
        else
        {
            $this->requestParams['ctrl'] = $ctrlName;
        }
       

        $ctrlName = $this->getCurrentCtrlName();
        $ctrlClass = $this->getCtrlClassName($ctrlName);
        
        if (!class_exists($ctrlClass)) $this->forward(Lib_Config::getVar('app_ctrl_error'), null, 404);
        
        $this->setCtrl(new $ctrlClass($this->getCtrlParams(), $this));

        if (!($this->getCtrl() instanceof Lib_Ctrl_Web)) $this->forward(Lib_Config::getVar('app_ctrl_error'), null, 404);
        
        $view = new Lib_View($this->getCtrl());

        if ($actionName){
            $this->getCtrl()->setAction($actionName);
        }

        
        
        $this->getCtrl()->runCurrentAction();
       
        $out = $view->render();

        $response = Lib_Response::getInstance();
        
        
        if ($statusCode == 404)
        {
            $response->clearHttpHeaders();
        }
        
        $response->setStatusCode($statusCode);

        $response->setContent($out);
        $response->send();
                
        exit;
    }



    /**
   * Redirects the request to another URL.
   *
   * @param string $url        An existing URL
   * @param int    $delay      A delay in seconds before redirecting. This is only needed on
   *                           browsers that do not support HTTP headers
   * @param int    $statusCode The status code
   */
    public function redirect($url, $delay = 0, $statusCode = 302)
    {

        // redirect
        $response = Lib_Response::getInstance();
        $response->clearHttpHeaders();
        $response->setStatusCode($statusCode);
        $response->setHttpHeader('Location', $url);
        $response->setContent(sprintf('<html><head><meta http-equiv="refresh" content="%d;url=%s"/></head></html>', $delay, htmlspecialchars($url, ENT_QUOTES, Lib_Config::getVar('app_charset'))));
        $response->send();
        exit;
    }

    public function dispatch()
    {
        $this->initialize();
        $this->forward();

    }
    
    public function cli($ctrlName)
    {
        $this->nestingForwardCount++;
        if ($this->nestingForwardCount >= $this->nestingForward){
            throw new Lib_Exception_Dispatcher("Max level nesting forward level {$this->nestingForwardCount} reached");
        }
         
         
        if (!$ctrlName){
            throw new Lib_Exception_Dispatcher("ctrl is empty");
        }
         
    
        
        $ctrlClass = $this->getCtrlClassName($ctrlName);
    
        if (!class_exists($ctrlClass)) throw new Lib_Exception_Dispatcher("ctrl class {$ctrlClass} notfound");
    
        $this->setCtrl(new $ctrlClass($this->getCtrlParams(), $this));
    
        if (!($this->getCtrl() instanceof Lib_Ctrl_Cli)) throw new Lib_Exception_Dispatcher("ctrl class {$ctrlClass} not cli");
    
        $this->getCtrl()->runCurrentAction();
        
        exit;
    }
    

}
?>