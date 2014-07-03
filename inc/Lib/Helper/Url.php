<?php
class Lib_Helper_Url {
    
    static public function main($withoutFile = true, $port=80){
        $vars = Lib_Dispatcher::getInstance()->getGlobalVarsObject();
        $documentRoot = $vars->server('DOCUMENT_ROOT');
        $scriptFileName = $vars->server('SCRIPT_FILENAME');
        $path = str_replace($documentRoot, '', $scriptFileName);
        
        $path = explode(DIRECTORY_SEPARATOR, $path);
        if ($withoutFile) array_pop($path);
        $path = '/' . implode('/', $path);
       
        if ($port != 80) $port = ':' . $port;
        else $port = '';
        
        $protocol = self::protocol();
         
        $domain = $vars->server('HTTP_HOST') ? $vars->server('HTTP_HOST') : $vars->server('SERVER_NAME');
        
        return $protocol.$domain.$port.$path;
    }
    
    static public function protocol() {
        $vars = Lib_Dispatcher::getInstance()->getGlobalVarsObject();
        if( ($vars->server('HTTPS') == 'on' || $vars->server('HTTPS') == 1) ||
            $vars->server('HTTP_X_FORWARDED_PROTO') == 'https')  return $protocol = 'https://';
        else return $protocol = 'http://';
    }
}