<?php
class Lib_AutoLoad
{
    public static function run($name)
    {
        $libsPath[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . "..";
        
        
        $fileName = str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
        
        foreach ($libsPath as $path)
        {
            $reqFileName = "{$path}/$fileName";
            if (file_exists($reqFileName) && is_readable($reqFileName))
            {
                require_once($reqFileName);
                return true;
            }
        }
        return false;
    }
    
}