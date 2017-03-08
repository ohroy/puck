c<?php
/**
 * Created by IntelliJ IDEA.
 * User: rozbo
 * Date: 2017/2/27
 * Time: 下午2:57
 */

namespace export;
use Noodlehaus\Config;

class conf {
    static $config=false;
    static public function load(){
        if(!self::$config){
            self::$config=Config::load(EXPORT_PATH.'/conf');
        }
        return self::$config;
    }
}