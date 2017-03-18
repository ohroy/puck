<?php
/**
 * Created by rozbo at 2017/3/18 下午2:05
 */
define('PUBLIC_PATH', __DIR__);
define('BASE_PATH', PUBLIC_PATH.'/..');
define('VENDOR_PATH', BASE_PATH.'/vendor');
define('DEBUG',1);
require VENDOR_PATH . '/autoload.php';
require BASE_PATH.'/start.php';
$app = new \puck\App();