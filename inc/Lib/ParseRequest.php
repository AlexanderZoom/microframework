<?php
class Lib_ParseRequest
{
    public static function make(Lib_Dispatcher $dispatcher)
    {
        $requestFormat = '';

        $globalVars = $dispatcher->getGlobalVarsObject();



        $requestUri = parse_url($globalVars->server('REQUEST_URI'));
        $requestUri = $requestUri['path'];
        if(preg_match("/\.(\w{2,5})$/", $requestUri, $matches))
        {
            $requestFormat = $matches[1];
            $requestUri = substr($requestUri, 0, strlen($requestUri) - (strlen($requestFormat)+1));
        }

        $scriptName = $globalVars->server('SCRIPT_NAME');



		if ($requestFormat == 'php') $requestFormat = '';
		
        $out = array(
        'request_format' => !$requestFormat ? Lib_Request::getInstance()->getFormat() : $requestFormat,
        'ctrl'     => '',
        'params'     => array(),
        'plain_url'  => '',
        'culture'   => '',
        );



        if (Lib_Config::getVar('app_rewrite_rules'))
        {
            $strPos = strrpos($scriptName, '/');
            $scriptPath = substr($scriptName, 0, $strPos);
            if ($scriptPath != '/')
            {
                $requestUri = preg_replace("|^{$scriptPath}|", '',$requestUri);
            }

            $requestList = explode("/", $requestUri);
            $out['ctrl'] = self::makeActionName($requestList[1]);
            

            if (count($requestList) > 2)
            {
                $out['params'] = array_slice($requestList, 2);
            }
        }
        else
        {
            $out['ctrl'] = self::makeActionName($globalVars->get('ctrl'));
            
            $getParams = $globalVars->getAll('GET');
            if (isset($getParams['ctrl'])) unset($getParams['ctrl']);
            $out['params'] = $getParams;
        }

        $uri = $globalVars->server('REQUEST_URI');

        $out['plain_url'] = $uri;
        return $out;
    }
    
    public static function makeActionName($action){
        if ($action && strpos($action, '_') !== FAlSE){
            $tmp = explode('_', $action);
            $tmp = array_map('ucfirst', $tmp);
            $action = implode('', $tmp);
        }
        
        return $action;
    }
    
    public static function makeCli(Lib_Dispatcher $dispatcher)
    {
        
        $out = array(
        'request_format' => '',
        'ctrl'     => '',
        'params'     => array(),
        'plain_url'  => '',
        'culture'   => '',
        );
    
       
        return $out;
    }
}