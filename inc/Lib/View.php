<?php
class Lib_View
{
    private $vars = array();
    
    /**
     *
     * @var Lib_Ctrl_Web
     */
    private $currentCtrl = null;
    
    public function __construct(Lib_Ctrl $ctrl)
    {
        $dispatcher = Lib_Dispatcher::getInstance();
        $global_vars = $dispatcher->getGlobalVarsObject();
        
        $dispatcher = new Lib_OutputEscaperObject($dispatcher, $global_vars);
        
        $this->vars = array(
        'dispatcher' => $dispatcher,
        'global_vars'=> $global_vars,
        'request'    => new Lib_OutputEscaperObject(Lib_Request::getInstance(), $global_vars),
        'response'   => new Lib_OutputEscaperObject(Lib_Request::getInstance(), $global_vars),
        'ctrl'       => new Lib_OutputEscaperObject($ctrl, $global_vars),
        'params'     => new Lib_OutputEscaperObject($ctrl->getVarHolder(), $global_vars),
        );
        
        $this->currentCtrl = $ctrl;
    }
    
    public function AddToVars($key, $value)
    {
        $this->vars[$key] = $value;
    }
    
    public function DelFromVars($key)
    {
        if (isset($this->vars[$key])) unset($this->vars[$key]);
    }
    
        
    protected function renderFile($_sfFile)
    {
        
        
        extract($this->vars);
        
        $global_vars->enableHtmlEscapeStrategy(true);
                
        // render
        ob_start();
        ob_implicit_flush(0);

        try
        {
            require($_sfFile);
        }
        catch (Exception $e)
        {
            // need to end output buffering before throwing the exception #7596
            ob_end_clean();
            throw $e;
        }
        
        $global_vars->enableHtmlEscapeStrategy(false);
        return ob_get_clean();
    }

    /**
   * Renders the presentation.
   *
   * @return string A string representing the rendered presentation
   */
    public function render()
    {
        $content = null;


        // render template if no cache
        if (is_null($content))
        {
            
            // render template file
            $content = $this->renderFile($this->getTemplate());

        }

        // now render decorator template, if one exists
        if ($this->isDecorator())
        {
            $content = $this->decorate($content);
        }

        return $content;
    }

    /**
   * Loop through all template slots and fill them in with the results of presentation data.
   *
   * @param  string $content  A chunk of decorator content
   *
   * @return string A decorated template
   */
    protected function decorate($content)
    {
        // render the decorator template and return the result
        $this->AddToVars('content', $content);
        $ret = $this->renderFile($this->getDecoratorTemplateFile());
        return $ret;
    }



    /**
   * Retrieves this views decorator template.
   *
   * @return string A template filename, if a template has been set, otherwise null
   */
    public function getDecoratorTemplateFile()
    {
        return $this->currentCtrl->getDecoratorTemplateFile();
    }

    public function isDecorator()
    {
        //echo $this->getDecoratorTemplateFile();
        return (bool) $this->currentCtrl->getDecoratorTemplateFile();
    }


    /**
   * Retrieves this views template.
   *
   * @return string A template filename, if a template has been set, otherwise null
   */
    public function getTemplate()
    {
        return $this->currentCtrl->getTemplateFile();
    }
    
    

}

?>