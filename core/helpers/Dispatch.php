<?php


namespace puck\helpers;


class Dispatch
{
    static $path;
    static $ext;
    static public function init() {
        if (!IS_CLI) {
            define('NOW_TIME', $_SERVER['REQUEST_TIME']);
            define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
            define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
            define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
            define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
            define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);
            define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) ? true : false);
            define('__SELF__', strip_tags($_SERVER['REQUEST_URI']));
        }
    }
    static public function dispatch($path='', $app='\\admin') {
        self::init();
        self::$path=$path;
        if ($path == '') {
            $path=array();
        } else {
            $path=str_replace('-', '_', $path);
            $path=explode('/', $path);
        }

        if (count($path) == 0) {
            array_push($path, 'home');
            array_push($path, 'index');
        } elseif (count($path) == 1) {
            array_push($path, 'index');
        }
        if (!empty($path)) {
            $tmpAction=array_pop($path);
            $tmpArray=explode(".",$tmpAction);
            self::$ext=$tmpArray[1]??"";
            $tmpAction=preg_replace('/\.(html|aspx|do|php|htm|h5|api|json|xml)$/i', '', $tmpAction);
            $tmpAction=parse_name($tmpAction, 1);
            $var['a']=$tmpAction;
        }
        define('ACTION_NAME', $var['a']);
        if (!preg_match('/^[A-Za-z](\w)*$/', ACTION_NAME)) {
            die("error action");
        }
        if (!empty($path)) {
            $tmpController=array_pop($path);
            $tmpController=parse_name($tmpController, 1);
            $var['c']=$tmpController;
        }
        define('CONTROLLER_NAME', $var['c']);
        if (!preg_match('/^[A-Za-z](\/|\w)*$/', CONTROLLER_NAME)) {
            die("error controller");
        }
        $class=$app.'\\controllers\\'.ucfirst(CONTROLLER_NAME);
        if (!class_exists($class)) {
            not_found('this controller is can not work now!');
        }
        $class=new $class();
        if (!method_exists($class, ACTION_NAME)) {
            not_found();
        }
        self::param();
        self::exec($class, ACTION_NAME);
    }

    /**
     * @param string $function
     */
    static public function exec($class, $function) {
        $method=new \ReflectionMethod($class, $function);
        if ($method->isPublic() && !$method->isStatic()) {
            $refClass=new \ReflectionClass($class);
            //前置方法
            if ($refClass->hasMethod('_before_'.$function)) {
                $before=$refClass->getMethod('_before_'.$function);
                if ($before->isPublic()) {
                    $before->invoke($class);
                }
            }
            //方法本身
            $response=$method->invoke($class);
            //后置方法
            if ($refClass->hasMethod('_after_'.$function)) {
                $after=$refClass->getMethod('_after_'.$function);
                if ($after->isPublic()) {
                    $after->invoke($class);
                }
            }
            self::render($response);
        }
    }
    static public function param() {
        if (!IS_CLI) {
            $vars=array();
            parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $vars);
            $_GET=$vars;
        }

    }
    static public function render($res) {
        $response=$res;
        $renderType=self::$ext;

        if($renderType=='json'){
            $response=json($res);
        }elseif ($renderType=='xml'){
            //todo:: 支持xml格式化输出
            $response='don\'t support xml now!';
        }elseif ($renderType==""){
            if(is_array($res)||is_object($res)){
                $response=json($res);
            }
        }

        echo $response;
    }
}