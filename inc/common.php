<?php
$path = dirname(__FILE__);

require_once ($path . "/config.php");
require_once ($path . "/Lib/Config.php");

new Lib_Config($_GLOBAL_CONFIG);

if (Lib_Config::getVar('php_log')) {
    ini_set('error_log', Lib_Config::getVar('php_log'));
    ini_set('log_errors', 1);
}

error_reporting(Lib_Config::getVar('php_error_reporting'));
ini_set("display_errors", Lib_Config::getVar('php_display_errors'));
////////////////////////////////////////////////////////////////////////////////

require_once ($path . "/Lib/AutoLoad.php");
require_once ($path . "/Lib/Dispatcher.php");

spl_autoload_register(array('Lib_AutoLoad', 'run'));

try {
    ////////////////////////DB ////////////////////
    // if (Lib_Config::getVar('site_enable') && Lib_Config::hasVar('db')){
    //     $db = Lib_DbConnectionManager::initDb(Lib_Config::getVar('db'));
    //     $dm = Lib_DataMapper::initInstance($db);
    //     $dm->setCache(Lib_Cache::initInstance(Lib_Config::getVar('cache')));
    // }
    ///////////////////////////////////////////////
    
    $dispatcher = Lib_Dispatcher::getInstance();
    if (!isset($_APPLICATION_SIMPLE) || !$_APPLICATION_SIMPLE){
        $dispatcher->dispatch();
    }
    else {
        $dispatcher->initialize();
    }
     
} catch (Exception $e){
    Lib_Exception_Global::createFromException($e)->printStackTrace();
}