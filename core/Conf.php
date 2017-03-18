<?php
/**
 * Created by IntelliJ IDEA.
 * User: rozbo
 * Date: 2017/2/27
 * Time: 下午2:57
 */

namespace puck;
use Noodlehaus\Config;

class Conf {
    static $config=false;
    static public function load() {
        if (!self::$config) {
            self::$config=Config::load(BASE_PATH.'/app/conf');
        }
        return self::$config;
    }
}