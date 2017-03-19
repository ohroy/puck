<?php
use puck\Container;
use puck\tools\Str;

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type=0, $adv=true)
{
    $type=$type ? 1 : 0;
    static $ip=null;
    if (null !== $ip) {
        return $ip[$type];
    }
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr=explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos=array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip=trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip=$_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long=sprintf("%u", ip2long($ip));
    $ip=$long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}
//2为直接输出数组
function show_json(array $arr, $type=2)
{

    if (isset($arr['status']) && $type == 2) {
        $ret=$arr;
    }
    else {
        $ret['status']=$type;
        $ret['data']=$arr;
    }

    $obj=json_encode($ret);
    header('Content-Type: application/json');
    echo $obj;
    exit();
}

function success($arr)
{
    show_json($arr, 1);
}

function error($arr)
{
    show_json($arr, 0);
}
function not_found($str='page not found,that is all we know!') {
    header('HTTP/1.1 404 Not Found');
    header("status: 404 Not Found");
    exit($str);
}


/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data) {
    //数据类型检测

    if (!is_array($data)) {

        $data=(array) $data;
    }
    ksort($data); //排序
    $code=http_build_query($data); //url编码并生成query字符串
    $sign=sha1($code); //生成签名
    return $sign;
}
/**
 * session管理函数
 * @param string $name session名称 如果为数组则表示进行session设置
 * @param mixed $value session值
 * @return mixed
 */
function session($name='', $value='') {
    if (is_array($name)) {

        if (isset($name['id'])) {
            session_id($name['id']);
        }

        if (isset($name['name'])) {
            session_name($name['name']);
        }

        if (isset($name['path'])) {
            session_save_path($name['path']);
        }

        if (isset($name['domain'])) {
            ini_set('session.cookie_domain', $name['domain']);
        }

        if (isset($name['expire'])) {
            ini_set('session.gc_maxlifetime', $name['expire']);
            ini_set('session.cookie_lifetime', $name['expire']);
        }
        if (isset($name['use_trans_sid'])) {
            ini_set('session.use_trans_sid', $name['use_trans_sid'] ? 1 : 0);
        }

        if (isset($name['use_cookies'])) {
            ini_set('session.use_cookies', $name['use_cookies'] ? 1 : 0);
        }

        if (isset($name['cache_limiter'])) {
            session_cache_limiter($name['cache_limiter']);
        }

        if (isset($name['cache_expire'])) {
            session_cache_expire($name['cache_expire']);
        }
        session_start();
    } elseif ('' === $value) {
        if ('' === $name) {
            // 获取全部的session
            return $_SESSION;
        } elseif (0 === strpos($name, '[')) {
            // session 操作
            if ('[pause]' == $name) {// 暂停session
                session_write_close();
            } elseif ('[start]' == $name) {
                // 启动session
                session_start();
            } elseif ('[destroy]' == $name) {
                // 销毁session
                $_SESSION=array();
                session_unset();
                session_destroy();
            } elseif ('[regenerate]' == $name) {
                // 重新生成id
                session_regenerate_id();
            }
        } else {
            if (strpos($name, '.')) {
                list($name1, $name2)=explode('.', $name);
                return isset($_SESSION[$name1][$name2]) ? $_SESSION[$name1][$name2] : null;
            } else {
                return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
            }
        }
    } elseif (is_null($value)) {
        // 删除session
        if (strpos($name, '.')) {
            list($name1, $name2)=explode('.', $name);
            unset($_SESSION[$name1][$name2]);
        } else {
            unset($_SESSION[$name]);
        }
    } else {
        // 设置session
        if (strpos($name, '.')) {
            list($name1, $name2)=explode('.', $name);
            $_SESSION[$name1][$name2]=$value;
        } else {
            $_SESSION[$name]=$value;
        }
    }
    return null;
}


function admin_is_login() {
    $user=session('admin_user_auth');
    if (empty($user)) {
        return 0;
    } else {
        $auth_sign=session('admin_user_auth_sign');
        if (data_auth_sign($user) != $auth_sign) {
            return 0;
        }
        return $user['uid'];
    }
}
function json($str) {
    $obj=json_encode($str, JSON_UNESCAPED_UNICODE);
    header('Content-Type: application/json');
    echo $obj;
}
/**
 * 浏览器友好的变量输出
 * @param mixed         $var 变量
 * @param boolean       $echo 是否输出 默认为true 如果为false 则返回输出字符串
 * @param string        $label 标签 默认为空
 * @param integer       $flags htmlspecialchars flags
 * @return void|string
 */
function dump($var, $echo=true, $label=null, $flags=ENT_SUBSTITUTE)
{
    $label=(null === $label) ? '' : rtrim($label).':';
    ob_start();
    var_dump($var);
    $output=ob_get_clean();
    $output=preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
    if (IS_CLI) {
        $output=PHP_EOL.$label.$output.PHP_EOL;
    } else {
        if (!extension_loaded('xdebug')) {
            $output=htmlspecialchars($output, $flags);
        }
        $output='<pre>'.$label.$output.'</pre>';
    }
    if ($echo) {
        echo($output);
        return;
    } else {
        return $output;
    }
}


/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
 * @param mixed $datas 要获取的额外数据源
 * @return mixed
 */
function I($name, $default='', $filter=null, $datas=null)
{
    static $_PUT=null;
    if (strpos($name, '/')) {
        // 指定修饰符
        list($name, $type)=explode('/', $name, 2);
    } else {
        // 默认强制转换为字符串
        $type='s';
    }
    if (strpos($name, '.')) {
        // 指定参数来源
        list($method, $name)=explode('.', $name, 2);
    } else {
        // 默认为自动判断
        $method='param';
    }
    switch (strtolower($method)) {
        case 'get':
            $input=&$_GET;
            break;
        case 'post':
            $input=&$_POST;
            break;
        case 'put':
            if (is_null($_PUT)) {
                parse_str(file_get_contents('php://input'), $_PUT);
            }
            $input=$_PUT;
            break;
        case 'param':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input=$_POST;
                    break;
                case 'PUT':
                    if (is_null($_PUT)) {
                        parse_str(file_get_contents('php://input'), $_PUT);
                    }
                    $input=$_PUT;
                    break;
                default:
                    $input=$_GET;
            }
            break;
        case 'path':
            $input=array();
            if (!empty($_SERVER['PATH_INFO'])) {
                $depr=C('URL_PATHINFO_DEPR');
                $input=explode($depr, trim($_SERVER['PATH_INFO'], $depr));
            }
            break;
        case 'request':
            $input=&$_REQUEST;
            break;
        case 'session':
            $input=&$_SESSION;
            break;
        case 'cookie':
            $input=&$_COOKIE;
            break;
        case 'server':
            $input=&$_SERVER;
            break;
        case 'globals':
            $input=&$GLOBALS;
            break;
        case 'data':
            $input=&$datas;
            break;
        default:
            return null;
    }
    if ('' == $name) {
        // 获取全部变量
        $data=$input;
        $filters=isset($filter) ? $filter : 'htmlspecialchars';
        if ($filters) {
            if (is_string($filters)) {
                $filters=explode(',', $filters);
            }
            else if (is_array($filters)) {
                foreach ($filters as $filter) {
                    $data=array_map_recursive($filter, $data); // 参数过滤
                }
            } else {
                throw new \RuntimeException('$filters must be an array or string.');
            }
        }
    } elseif (isset($input[$name])) {
        // 取值操作
        $data=$input[$name];
        $filters=isset($filter) ? $filter : 'htmlspecialchars';
        if ($filters) {
            if (is_string($filters)) {
                if (0 === strpos($filters, '/')) {
                    if (1 !== preg_match($filters, (string) $data)) {
                        // 支持正则验证
                        return isset($default) ? $default : null;
                    }
                } else {
                    $filters=explode(',', $filters);
                }
            } elseif (is_int($filters)) {
                $filters=array($filters);
            }

            if (is_array($filters)) {
                foreach ($filters as $filter) {
                    $filter=trim($filter);
                    if (function_exists($filter)) {
                        $data=is_array($data) ? array_map_recursive($filter, $data) : $filter($data); // 参数过滤
                    } else {
                        $data=filter_var($data, is_int($filter) ? $filter : filter_id($filter));
                        if (false === $data) {
                            return isset($default) ? $default : null;
                        }
                    }
                }
            }
        }
        if (!empty($type)) {
            switch (strtolower($type)) {
                case 'a':    // 数组
                    $data=(array) $data;
                    break;
                case 'd':    // 数字
                    $data=(int) $data;
                    break;
                case 'f':    // 浮点
                    $data=(float) $data;
                    break;
                case 'b':    // 布尔
                    $data=(boolean) $data;
                    break;
                case 's':// 字符串
                default:
                    $data=(string) $data;
            }
        }
    } else {
        // 变量默认值
        $data=isset($default) ? $default : null;
    }
    is_array($data) && array_walk_recursive($data, 'think_filter');
    return $data;
}
function array_map_recursive($filter, $data)
{
    $result=array();
    foreach ($data as $key => $val) {
        $result[$key]=is_array($val)
            ? array_map_recursive($filter, $val)
            : call_user_func($filter, $val);
    }
    return $result;
}
function think_filter(&$value)
{
    // TODO 其他安全过滤

    // 过滤查询特殊字符
    if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
        $value .= ' ';
    }
}
/**
 * @param string $name
 *
 * @return string|null
 */
function config($name=null,$value=null,$default=null){
    $config=\puck\Conf::load();
    if ($name===null){
        return $config->all();
    }
    if ($value===null){
        return $config->get($name,$default);
    }
    $config->set($name,$value);
}
/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parse_name($name, $type=0) {
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match) {return strtoupper($match[1]); }, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

if (! function_exists('app')) {
    /**
     * 获取容器实例
     *
     * @param  string  $make
     * @return mixed|\puck\App
     */
    function app($make = null)
    {
        if (is_null($make)) {
            return Container::getInstance();
        }
        return Container::getInstance()->make($make);
    }
}

if (! function_exists('env')) {
    /**
     * 获取环境变量
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return value($default);
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }
        if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }
        return $value;
    }
}
if (! function_exists('value')) {
    /**
     * 返回给定的表达式的结果
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}