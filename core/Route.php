<?php
/**
 * Created by rozbo at 2017/3/19 下午5:30
 */

namespace puck;

class Route {
    private $request;

    public function __construct(Request $request) {
        $this->request = $request;
        $this->errorCallback=function (){
            die("404");
        };
    }

    public static $halts = false;
    private $routes = array();
    private $regexRoutes = [];
    public static $methods = array();
    public static $callbacks = array();
    private $patterns = array(
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*'
    );
    public $errorCallback;

    /**
     * Defines a route w/ callback and method
     */
    public function __call($method, $params) {
        $this->addRoute($method, $params[0], $params[1]);
    }

    /**
     * 添加一个路由
     * @param string $method
     * @param string $uri
     * @param mixed $callBack
     */
    public function addRoute($method, $uri, $callBack) {
        $method = strtoupper($method);
        //预定义正则路由
        if (strpos($uri, ':') !== false) {
            $searches = array_keys($this->patterns);
            $replaces = array_values($this->patterns);
            $uri = str_replace($searches, $replaces, $uri);
            $this->regexRoutes[] = [
                'method' => $method,
                'regex' => '#^' . $uri . '$#',
                'callback' => $callBack
            ];
        } //自定义正则路由
        elseif ($uri[0] == '#'
            || (strlen($uri) > 2 && tools\Str::endsWith($uri, '/') && tools\Str::startsWith($uri, '/'))
        ) {
            $this->regexRoutes[] = [
                'method' => $method,
                'regex' => $uri,
                'callback' => $callBack
            ];
        } //直接定义的路由
        else {
            $this->routes[$method . $uri] = [
                'method' => $method,
                'uri' => $uri,
                'callback' => $callBack
            ];
        }
    }

    /**
     * Defines callback if route is not found
     */
    public function error($callback) {
        $this->errorCallback = $callback;
    }

    public static function haltOnMatch($flag = true) {
        self::$halts = $flag;
    }


    private function foundRoute($route, $param = []) {
        try {
            if ($route['callback'] instanceof \Closure) {
                app()->invokeFunction($route['callback'],$param);
            } else {
                // Grab all parts based on a / separator
                $parts = explode('/', $route['callback']);
                // Collect the last index of the array
                $last = end($parts);
                // Grab the controller name and method call
                $segments = explode('@', $last);
                app()->invokeMethod($segments, $param);
            }
        } catch (\ReflectionException $e) {
            return false;
        }
        catch (\InvalidArgumentException $e) {
            return false;
        }
        return true;
    }

    public function foundRegexRoute($current) {
        foreach ($this->regexRoutes as $regexRoute) {
            if (preg_match($regexRoute['regex'], $current['uri'], $matched)) {
                if ($regexRoute['method'] == $current['method']
                    || $regexRoute['method'] == 'ANY'
                ) {
                    //将第一个成员，即是全部字符串剔除
                    array_shift($matched);
                    return $this->foundRoute($regexRoute, $matched);
                }
            }
        }
        return false;
    }

    /**
     * Runs the callback for the given request
     */
    public function dispatch() {
        $uri=$this->request->path();
        $current['uri'] = $uri?$uri:'/';
        $current['method'] = $this->request->method();
        # 第一种情况，直接命中
        if (isset($this->routes[$current['method'] . $current['uri']])) {
            $this->foundRoute($this->routes[$current['method'] . $current['uri']]);
        } # 第二种情况，any命中
        else if (isset($this->routes['ANY' . $current['uri']])) {
            $this->foundRoute($this->routes['ANY' . $current['method']]);
        } # 第三种情况，正则命中
        else {
            if ($this->foundRegexRoute($current)) {

            } else {
                $route['callback'] = $this->errorCallback;
                $this->foundRoute($route);
            }
        }
    }
}