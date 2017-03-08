<?php
@ini_set('date.timezone', 'Asia/Shanghai');
@header('content-type:text/html;charset=utf-8');

define('EXPORT_PATH',__DIR__);
define('BASE_PATH', EXPORT_PATH.'/..');
define('DEBUG',1);

require BASE_PATH . '/vendor/autoload.php';
require BASE_PATH . '/app/config/config.php';

if(DEBUG){
    error_reporting(E_ALL);
    @ini_set('display_errors', 'On');
    @ob_start();
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

require BASE_PATH.'/app/config/routes.php';