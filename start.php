<?php
@ini_set('date.timezone', 'Asia/Shanghai');
@header('content-type:text/html;charset=utf-8');

define('EXPORT_PATH',__DIR__);

if(DEBUG){
    error_reporting(E_ALL);
    @ini_set('display_errors', 'On');
    @ob_start();
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}