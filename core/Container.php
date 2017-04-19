<?php
/**
 * Created by rozbo at 2017/3/17 上午10:37
 * 容器类
 */

namespace puck;


use ArrayAccess;
use puck\tools\Str;

class Container implements ArrayAccess
{
    // 容器对象实例
    protected static $instance;
    // 容器中的对象实例
    protected $instances=[];
    // 容器中绑定的对象标识
    protected $bind=[];
    // 正则绑定 
    protected $regexBind = [];
    /**
     * 获取当前容器的实例（单例）
     * @access public
     * @return \puck\Container
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance=new static;
        }

        return static::$instance;
    }


    /**
     * 设置共享容器实例
     * @param ArrayAccess $container
     * @return static
     */
    public static function setInstance(ArrayAccess $container)
    {
        return static::$instance=$container;
    }


    public function regexBind($regex, $class) {
        $this->regexBind[$regex] = $class;
    }

    /**
     * 绑定一个类实例当容器
     * @access public
     * @param string    $abstract    类名或者标识
     * @param object    $instance    类的实例
     * @return void
     */
    public function instance($abstract, $instance)
    {
        $this->instances[$abstract]=$instance;
    }

    /**
     * 调用反射执行callable 支持参数绑定
     * @access public
     * @param mixed $callable
     * @param array $vars   变量
     * @return mixed
     */
    public function invoke($callable, $vars=[])
    {
        if ($callable instanceof \Closure) {
            $result=$this->invokeFunction($callable, $vars);
        } else {
            $result=$this->invokeMethod($callable, $vars);
        }
        return $result;
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     * @access public
     * @param \Closure $function 函数或者闭包
     * @param array $vars 变量
     * @return mixed
     */
    public function invokeFunction($function, $vars = [])
    {
        $reflect = new \ReflectionFunction($function);
        $args = $this->bindParams($reflect, $vars);
        return $reflect->invokeArgs($args);
    }

    /**
     * 绑定参数
     * @access protected
     * @param \ReflectionMethod|\ReflectionFunction $reflect 反射类
     * @param array                                 $vars    变量
     * @return array
     */
    protected function bindParams($reflect, $vars=[])
    {
        $args=[];
        if ($reflect->getNumberOfParameters() > 0) {
            // 判断数组类型 数字数组时按顺序绑定参数
            reset($vars);
            $type=key($vars) === 0 ? 1 : 0;
            $params=$reflect->getParameters();
            foreach ($params as $param) {
                $name=$param->getName();
                $class=$param->getClass();
                if ($class) {
                    $className=$class->getName();
                    $args[]=$this->make($className);
                } elseif (1 == $type && !empty($vars)) {
                    $args[]=array_shift($vars);
                } elseif (0 == $type && isset($vars[$name])) {
                    $args[]=$vars[$name];
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[]=$param->getDefaultValue();
                } else {
                    throw new \InvalidArgumentException('method param miss:'.$name);
                }
            }
        }
        return $args;
    }

    /**
     * 创建类的实例
     * @access public
     * @param array $vars 变量
     * @return object
     */
    public function make($abstract, $vars = [], $newInstance = false) {
        if (isset($this->instances[$abstract])) {
            $object = $this->instances[$abstract];
        } else {
            if (isset($this->bind[$abstract])) {
                $concrete = $this->bind[$abstract];
                $object = $this->makeObject($concrete, $vars);
            } else {
                //正则绑定
                $result = preg_filter(array_keys($this->regexBind), array_values($this->regexBind), $abstract);
                if ($result) {
                    $concrete = is_array($result) ? end($result) : $result;
                    $object = $this->makeObject($concrete, $vars);
                } else {
                    $object = $this->invokeClass($abstract, $vars);
                }
            }

            if (!$newInstance) {
                $this->instances[$abstract] = $object;
            }
        }
        return $object;
    }

    private function makeObject($concrete, $vars = []) {
        if ($concrete instanceof \Closure) {
            $object = $this->invokeFunction($concrete, $vars);
        } else {
            $object = $this->make($concrete, $vars);
        }
        return $object;
    }

    /**
     * 调用反射执行类的实例化 支持依赖注入
     * @access public
     * @param string $class 类名
     * @param array $vars 变量
     * @return mixed
     */
    public function invokeClass($class, $vars = []) {
        try {
            $reflect = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            $classArray = explode("\\", $class);
            $classSelf = array_pop($classArray);
            $classSelf = Str::studly($classSelf);
            array_push($classArray, $classSelf);
            $class = implode("\\", $classArray);
            $reflect = new \ReflectionClass($class);
        }

        $constructor = $reflect->getConstructor();
        if ($constructor) {
            $args = $this->bindParams($constructor, $vars);
        } else {
            $args = [];
        }
        return $reflect->newInstanceArgs($args);
    }

    /**
     * 调用反射执行类的方法 支持参数绑定
     * @access public
     * @param string|array $method 方法
     * @param array $vars 变量
     * @return mixed
     */
    public function invokeMethod($method, $vars = []) {
        if (is_array($method)) {
            $class = is_object($method[0]) ? $method[0] : $this->invokeClass($method[0]);
            $reflect = new \ReflectionMethod($class, $method[1]);
        } else {
            // 静态方法
            $reflect = new \ReflectionMethod($method);
        }
        $args = $this->bindParams($reflect, $vars);
        return $reflect->invokeArgs(isset($class) ? $class : null, $args);
    }

    public function offsetExists($key)
    {
        return $this->bound($key);
    }

    /**
     * 判断容器中是否存在类及标识
     * @access public
     * @param string $abstract 类名或者标识
     * @return bool
     */
    public function bound($abstract) {
        return isset($this->bind[$abstract]) || isset($this->instances[$abstract]);
    }

    public function offsetGet($key)
    {
        return $this->make($key);
    }

    public function offsetSet($key, $value)
    {
        $this->bind($key, $value);
    }

    /**
     * 绑定一个类到容器
     * @access public
     * @param string $abstract 类标识、接口
     * @param string|\Closure $concrete 要绑定的类或者闭包
     * @return void
     */
    public function bind($abstract, $concrete = null) {
        if (is_array($abstract)) {
            $this->bind = array_merge($this->bind, $abstract);
        } else {
            $this->bind[$abstract] = $concrete;
        }
    }

    public function offsetUnset($key)
    {
        $this->__unset($key);
    }

    public function __unset($name)
    {
        unset($this->bind[$name], $this->instances[$name]);
    }

    public function __get($name)
    {
        return $this->make($name);
    }

    public function __set($name, $value) {
        $this->bind($name, $value);
    }

    public function __isset($name)
    {
        return $this->bound($name);
    }

}