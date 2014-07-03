<?php
$_APPLICATION_SIMPLE = true;
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'common.php');
try {
    if (!empty($argv[1]) && empty($_SERVER['HTTP_HOST'])) $dispatcher->cli("Cron" . ucfirst($argv[1]));
} catch (Exception $e){
    Lib_Exception_Global::createFromException($e)->printStackTrace();
}