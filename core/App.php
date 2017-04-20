<?php
/**
 * Created by rozbo at 2017/3/18 下午8:52
 */

namespace puck;


use Dotenv\Dotenv;
use Whoops\Run;

class App extends Container {
    /**
     * 已经加载的config文件
     *
     * @var array
     */
    protected $loadedConfigurations = [];

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
        $this->initConfig();
    }

    private function initEnv(){
        try{
            $dotEnv = new Dotenv($this->basePath);
            $dotEnv->load();
        }
        catch (\Dotenv\Exception\InvalidPathException $e){

        }
        date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Shanghai'));
        define('IS_CLI', $this->runningInConsole());
        define('IS_DEBUG',env('DEBUG',false));
        if (IS_DEBUG) {
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
     * 判断是否是cli模式
     *
     * @return bool
     */
    public function runningInConsole() {
        return php_sapi_name() == 'cli';
    }

    /**
     * 初始化容器
     */
    private function initContainer() {
        static::setInstance($this);
        $this->instance('app',$this);
        $this->instance('config',new Config());
        $this->instance('request',new Request($this->config));
        $this->instance('route',new Route($this->request));
        $this->regexBind('#^(\w+)_model$#', "\\app\\models\\\\$1");
        $this->bind('pinyin','\puck\helpers\PinYin');
        $this->bind('curl','\puck\helpers\Curl');
        $this->bind('dom', '\puck\helpers\Dom');
        $this->bind('db', '\puck\Db');
    }

    private function initConfig() {
        $this->configure('core');
    }

    /**
     * 加载一个配置文件
     *
     * @param  string  $name
     * @return void
     */
    public function configure($name)
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }
        //标记为已加载
        $this->loadedConfigurations[$name] = true;
        $path = $this->getConfigurationPath($name);
        if ($path) {
            $this->make('config')->set($name, require $path);
        }
    }

    /**
     * 获取配置文件的路径。
     *
     * 如果没有给定配置文件的名字，则返回目录。
     *
     * 如果应用目录下有相应配置文件则优先返回。
     *
     * @param  string|null  $name
     * @return string
     */
    public function getConfigurationPath($name = null)
    {
        if (! $name) {
            $appConfigDir = $this->basePath('configs').'/';

            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__.'/configs/')) {
                return $path;
            }
        } else {
            $appConfigPath = $this->basePath('configs').'/'.$name.'.php';
            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__.'/configs/'.$name.'.php')) {
                return $path;
            }
        }
    }

    /**
     * Get the base path for the application.
     *
     * @param  string|null $path
     * @return string
     */
    public function basePath($path = null) {
        if (isset($this->basePath)) {
            return $this->basePath . ($path ? '/' . $path : $path);
        }

        if ($this->runningInConsole()) {
            $this->basePath = getcwd();
        } else {
            $this->basePath = realpath(getcwd() . '/../');
        }

        return $this->basePath($path);
    }

    public function run() {
        $this->route->dispatch();
    }
}