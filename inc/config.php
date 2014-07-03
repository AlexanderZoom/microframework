<?php
    $_GLOBAL_CONFIG = array(
            'app_time_zone'    => 'Europe/Moscow',
            'app_charset'         => 'utf-8',
            'app_path_inc'	   => dirname(__FILE__),
            'app_path_lib'	   => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Lib',
            'app_path_ctrl'	   => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ctrl',
            'app_path_model'	   => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Model',
            'app_path_view'	   => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'View',
            'app_disable_page' => '',
            'app_exception_display' => '1',
            'app_rewrite_rules' => 1,
            'app_ctrl_error' => 'error',  //page for 404 error
            'app_main_template' => 'main',
    
            'php_log'                => '',
            'php_log_errors'      => '1',
            'php_error_reporting'   => E_ALL,
            'php_display_errors'    => 1,
    
    
            'site_enable'          => 1,
    
            'db' => array(
                'server_type' => 'mysql',
                'host' => 'localhost',
                'user' => '',
                'pass' => '',
                'dbname' => '',
                'charset' => 'utf8',
            ),
    
            'cache' => array(
                'type' => 'memcache',
                'host' => '127.0.0.1',
                'port' => 11211,
                'timeout' => 1,
                'compress' => 0
            )
                
    );
?>