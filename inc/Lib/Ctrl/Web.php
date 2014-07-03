<?php
abstract class Lib_Ctrl_Web extends Lib_Ctrl
{
    protected $availableFormats = array('html');
    
    
    protected $decoratorTemplate = null;
    protected $currentTemplate = null;
    
    protected $actionTitle = '';
    
    protected $makeInit = true;
       
    public function __construct($params = array(), $dispatcher = null)
    {
    
        parent::__construct($params, $dispatcher);
        
        if (!$this->_checkParam($params)) $dispatcher->forward(Lib_Config::getVar('app_ctrl_error'), null, 404);
                
        if (!in_array(Lib_Request::getInstance()->getFormat(), $this->availableFormats))
        {
        	 $dispatcher->forward(Lib_Config::getVar('app_ctrl_error'), null, 404);
        }
        
        if (is_null($this->decoratorTemplate)){
        	$this->setDecoratorTemplate(Lib_Config::getVar('app_main_template'));
        }
        
        
        
        if ($this->makeInit) $this->init();
                
    }
    
    abstract public function init();
    
    protected function _checkParam($params){
        return false;
    }
    
    public function getCurrentTemplate()
    {
        return $this->currentTemplate;
    }
    
    public function getTemplateFileName()
    {
        $formatOut = Lib_Request::getInstance()->getFormat();
        if ($formatOut == 'html') $formatOut = '';
        else $formatOut = '.' . $formatOut;
        
        $ctrl = $this->getCtrlName();
        $action = ucfirst($this->getAction());
        return "{$ctrl}{$action}{$formatOut}.php";
        
    }
    
    public function getTemplateFile()
    {
        $templateFile = $this->getTemplateFileName();

        $templatePath = '';

        $ctrl = $this->getCtrlName();
        $templatePath = Lib_Config::getVar('app_path_view') . DIRECTORY_SEPARATOR . 'Ctrl' . DIRECTORY_SEPARATOR . $templateFile;
        if (!is_readable($templatePath)) throw new Lib_Exception_Global("Template file {$templateFile} for ctrl {$ctrl} not readdable {$templatePath}");

        return $templatePath;
    }
    
    public function getDecoratorTemplateFile(){
    	$templateFile = $this->getDecoratorTemplateFileName();
        

        if (!$templateFile) return '';
        
    	$ctrl = $this->getCtrlName();
        $templatePath = Lib_Config::getVar('app_path_view') . DIRECTORY_SEPARATOR . $templateFile;
        if (!is_readable($templatePath)) throw new Lib_Exception_Global("Template decorator file {$templateFile} for ctrl {$ctrl} not readdable {$templatePath}");
        
        return $templatePath;
    }
    
    public function setDecoratorTemplate($name)
    {
        $this->decoratorTemplate = $name;
    }
    
    public function getDecoratorTemplate()
    {
        return $this->decoratorTemplate;
    }
    
    public function getDecoratorTemplateFileName()
    {
        $formatOut = Lib_Request::getInstance()->getFormat();
        if ($formatOut == 'html') $formatOut = '';
        else $formatOut = '.' . $formatOut;
        
        
        if ($this->decoratorTemplate) return "{$this->decoratorTemplate}{$formatOut}.php";
        
        return '';
    }
    
    /**
     * @return Lib_GlobalVars
     */
    public function getGlobalVar(){
        return $this->getDispatcher()->getGlobalVarsObject();
    }
    
}