<?php
/**
 * Created by rozbo at 2017/3/18 下午8:52
 */

namespace puck;


use Dotenv\Dotenv;
use Whoops\Run;

class App extends Container {

    /**
     * 应用的根目录.
     *
     * @var string
     */
    protected $basePath;
    public function __construct($basePath) {
        $this->basePath=$basePath;
        $this->initEnv();
        $this->initContainer();
    }

    private function initEnv(){
        $dotEnv = new Dotenv($this->basePath);
        $dotEnv->load();
        date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Shanghai'));
        define('IS_CLI', php_sapi_name() == 'cli');
        if (env('DEBUG',false)) {
            error_reporting(E_ALL);
            @ini_set('display_errors', 'On');
            //@ob_start();
            $whoops=new Run;
            $handle=IS_CLI ? "PlainTextHandler" : "PrettyPageHandler";
            $handle="\\Whoops\\Handler\\".$handle;
            $whoops->pushHandler(new $handle);
            $whoops->register();
        }

    }
    /**
     * 初始化容器
     */
    private function initContainer() {
        static::setInstance($this);
        $this->instance('app',$this);
        $this->bind('pinyin','\puck\helpers\PinYin');
    }

}