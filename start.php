<?php
@ini_set('date.timezone', 'Asia/Shanghai');
@header('content-type:text/html;charset=utf-8');
define('EXPORT_PATH',__DIR__);
define('PUCK_VER','1.1.0');
define('IS_CLI',php_sapi_name()=='cli');
if(DEBUG){
    error_reporting(E_ALL);
    @ini_set('display_errors', 'On');
    //@ob_start();
    $whoops = new \Whoops\Run;
    $handle=IS_CLI?"PlainTextHandler":"PrettyPageHandler";
    $handle="\\Whoops\\Handler\\".$handle;
    $whoops->pushHandler(new $handle);
    $whoops->register();
}