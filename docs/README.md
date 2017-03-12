


# 过期声明

!> 此文档针对`puck 0.*`，目前现行版本已经升级，故此文档仅供参考。

# 啰嗦一番
## 为什么
我为什么要做个框架？这是一开始最先考虑的问题。在开发`php`程序的过程中，我们习惯性的总是在使用各种
框架，在自己的代码中添加大小和重量是自己代码好几倍的框架----然而，你确信这些框架是必须的吗？  
至少我不确定，在反复的测试之后，我终于放弃了一切现有的框架，因为他是为大家，为大众使用的，注定里面
有各种的添加，而无论我是否需要。  
其实我始终觉得**适合的才是最好的** ，可是究竟什么才是适合？我想每个项目都是不同的，那么有没有一种两全其美
的办法，既能敏捷开发，又能性能至上呢？  
受`npm`的启发，我想`php`也可以像积木一样，用不同的模块拼接成不同的框架，应用成不同的逻辑。  
幸运的是，`php`也早就有了包管理系统，`composer`。  
没错，本框架就是基于此进行扩展和灌装的。  
借用`laruence`的一句话，

> 剑的三层境界：一是手中有剑，心中亦有剑；二是手中无剑，心中有剑；三是手中无剑，心中亦无剑。

那么本框架也同样是在第二层，手中无剑，心中有剑。  
这也就是本框架与其它框架的最大的不同----我们允许甚至鼓励修改框架本身！  
希望在使用本框架的过程中，能够真正的做到**心中也无剑**。  
本框架更多的意义在于约定和规范，甚至代码片段的作用。

## 用于谁？
本框架只适用于，追求极致的性能，处女座，有一定动手能力的，强迫症，折腾狂。  
对*得过且过，能用就行，简单就好* 的开发者不够友好。

## 约定

* 各个脚手架都必须适用当下性能最好的部件，无论其是否麻烦。
* 代码风格遵循`psr-4`。
* 零冗余
* 高迭代
* 大巧不工，使用最简单的代码，最巧妙的逻辑
* 尽可能的使用`强类型`

## 特性

* 轻量级，非重型框架。框架逻辑简单明了。
* logic,util分离。
* 执行栈浅，性能优先。
* 高度可定制。
* 拟`thinkphp`语法，对国内用户友好。

# 从部署到运行
## 要求
虽然本框架并没有什么特别的，理论上可运行与5.3+的`php`之上，但是为了性能及减少故障的发生，我们建议
使用以下环境。

- php7 + 
- hhvm [可选]
- mariadb[推荐]/mysql
- nginx[推荐]/tengine/openresty/apache[不推荐]
- composer 
- centos[推荐]/或者其他的linux发行版/windows[不推荐]
- jemalloc
- redis

由于php的`zval`改进，产生了巨大的性能提升，而且本框架的理念就是**要用就用最好的**，所以这里强烈推荐使用。    
如果没有使用`php7`而使用的低版本`php`则强烈推荐`facebook`的`hhvm`来对`php`进行优化。  
另外，在使用本框架之前，你可能得花一定的时间去学习[phpcomposer](http://docs.phpcomposer.com/).  
如果你确定满足了以上条件，请继续，否则请先配置以上。不然接下来可能会导致一系列诡异的问题。  


## 安装

```shell
git clone https://github.com/rozbo/puck.
cd puck
composer install
```

## 配置网站
**以下的说明非常重要**  
特别需要说明的是，为了最大化安全考虑，我们要把网站的根目录设置为`puck/public`.以避免框架本身访问到。  

## 配置url rewrite
鉴于我们是一个`mvc框架`，在网址的美化上是第一步我们要做的，为了支持`url rewrite`
我们得在`web server`进行设置。  
为了缩短更新文件的时间，此处我们使用`nginx`为例，如果有童鞋使用了其他的服务器，欢迎补充。

```nginx
autoindex off;

location / {
	try_files $uri $uri/ /index.php?/$uri;
}
```
原理是将  `something.html`映射为`/index.php?/something.html` 
这样便于我们的路由进行后续的逻辑处理。

## 初窥
这个时候，正常情况下，你可以打开网站，并得到如下显示：

> Hello, welcome to use puck....

如果访问 `domain/admin/`将会得到如下显示：

> hello ,welcome to admin !

而实质上，这是完全不同的两种渲染方式，前者通过路由主动调用类，并通过`twig`渲染，
后者则是通过路由解析，然后反射被类主动调用的。  
没错，在本框架中，不会使用`smarty`等模板引擎，而是会使用`twig`。

# 大体了解
## 架构
和传统的mvc框架一样,`puck`同样基于`（模型-视图-控制器）`的方式来组织逻辑。

> MVC是一个设计模式，它强制性的使应用程序的输入、处理和输出分开。使用MVC应用程序被分成三个核心部件：模型（M）、视图（V）、控制器（C），它们各自处理自己的任务。

不同的是，`puck`是一个真正的去重设计，以`路由`为中心，由其来完成对整个系统的派遣和调用。  
大体是：

* 路由->控制器<->模型
* 路由->控制器->视图
* 路由->控制器[<->模型]->视图
* 路由->控制器[<->模型<->逻辑<->行为<->助手]->视图

总之，它们都是受路由驱动，然后由控制器来进行对下层的调用。

## 周期

`index`->`bootstrap`->`composer`->`error handler`->`init`->`router`->[`dispather`->]`controller`->`view`

其中，`[]`内代表可选的。
这是因为，如果没有使用复杂路由，而是直接使用`类映射`,`重定向`,`跳转`,`闭包`等是不需要进行路由分发的。

一般情况下，我们推荐后台使用`dispather`,而前台则直接使用自定义的路由规则。  
这是因为后台一般情况下都有`权限控制`，而`路由分发`可以方便的结合它。

## url

和其他大而全的框架不同的是，`puck`只允许一种访问方式。同时对url进行了必要的规范和约束。  
使用本框架的前提就是必须设置`url rewrite`,参见[这里](#配置url rewrite)  。
`puck`允许`url`和逻辑分离，除非yidabo你使用复杂的`dispather`模式，否则可以自由的定义映射规则。

## 目录结构
```tree
├─app          
│  ├─config         	
│  │  ├─config.php      
│  ├─controllers            
│  │  ├─XxxController.php
│  ├─helpers            
│  │  ├─XxxHelper.php
│  ├─models            
│  │  ├─XxxModel.php
│  ├─views            
│  │  ├─Xxx
│  │  │  ├─yyy.twig
```

## 命名空间
`puck`的架构均建立在遵循`psr-4`的命名空间之上。  
因为，控制器的命名空间必须为
```php
namespace app\controllers;
```
同理，模型则为
```php
namespace app\models;
```

则使用时为
```php
use \app\controllers\HomeController  as HomeController;
```

## 自动加载
`puck`建立在`compsoer`之上，使用其自动加载逻辑。  
因此，当你的代码需要被自动加载的时候，需要配置`composer.json`.

```json
  "autoload": {
        "psr-4": {
          "export\\":"export",
          "app\\":"app",
          "admin\\":"admin"
        },
    "files": [
      "export/functions/function.php"
    ]
  },
```

其中`files`为自动载入的文件。为了使函数高可用，我们需要提前加载进去。

# 路由
`puck`的路由是由[Macaw](https://github.com/noahbuscher/Macaw)提供支持的。  
这个路由只有一个文件，大小仅仅不超过5kb（带注释），然而逻辑却非常巧妙，完全符合我们的原则。  
把整个网址当成一个字符串，然后用正则匹配，进行相应处理，十分的简单。  

## 路由模式

我们将路由分为两种模式，一种是`简单模式`，另外一种则是`派遣模式`。  
两者本质的区别在于调用方。  
`简单模式`的路由逻辑处理由路由本身调用，即路由自己`new`对应的类，执行对象的方法，甚至自行执行闭包。
即：

```php
$parts = explode('/',self::$callbacks[$route]);
// Collect the last index of the array
$last = end($parts);
// Grab the controller name and method call
$segments = explode('@',$last);
// Instanitate controller
$controller = new $segments[0]();
// Call method
$controller->{$segments[1]}();
```
`派遣模式`则是基于此扩展而来，我们在此基础之上，将所有的路由转发给一个固定的分发中心，然后再用反射的方式进行逻辑处理,这也是类似
`thinkphp`的路由方式：

```php
$method = new \ReflectionMethod($class, $function);
if ($method->isPublic() && !$method->isStatic()) {
      $refClass = new \ReflectionClass($class);
    //前置方法
    if ($refClass->hasMethod('_before_' . $function)) {
          $before = $refClass->getMethod('_before_' . $function);
        if ($before->isPublic()) {
              $before->invoke($class);
        }

    }
        //方法本身
        $method->invoke($class);
        //后置方法
        if ($refClass->hasMethod('_after_' . $function)) {
              $after = $refClass->getMethod('_after_' . $function);
            if ($after->isPublic()) {
                  $after->invoke($class);
            }
        }
    }
```
这样做的好处是可以方便后台的权限控制，以及前置后置方法等。弊端则是更为复杂的逻辑，url不够自由等。  
因此，对于非必要的情况下，强烈建议使用一般路由。 

## 闭包路由
### 一般使用
我们可以使用闭包的方式定义一些特殊需求的路由，而不需要执行控制器的操作方法了，例如：

```php
Macaw::get('/', function() {
  echo 'Hello world!';
});
```
### 参数传递

闭包定义的时候支持参数传递，例如：

```php
Macaw::get('/(:any)', function($slug) {
  echo 'The slug is: ' . $slug;
});
```
例如，我们访问的URL地址是：

```url
http://xxx.com/hello
```
则浏览器输出的结果是：

```text
The slug is: hello
```

## 正则支持

在上面的案例中，你也许会问`(:any)`是什么鬼？实质上`(:any)`本身即为一个正则表达式。  

```php
 public static $patterns = array(
      ':any' => '[^/]+',
      ':num' => '[0-9]+',
      ':all' => '.*'
  );
```
路由默认提供以上三个常用的正则，而其他的怎么办呢，手写即可。  
因此，上面的例子即等同于

```php
Macaw::get('/([^/]+)', function($slug) {
  echo 'The slug is: ' . $slug;
});
```

因此我们可以自行以正则定义各种路由

```php
Macaw::get('/^([1][358][0-9]{9})$', function($phoneNum) {
  echo 'yeah,the phone num is: ' . $phoneNum;
});
```
## 控制器支持

```php
Macaw::get('/', 'Controllers\demo@index');
Macaw::get('page', 'Controllers\demo@page');
Macaw::get('view/(:num)', 'Controllers\demo@view');
```
则分别对应以下类的各个方法：

```php
<?php
namespace controllers;

class Demo {

    public function index()
    {
        echo 'home';
    }

    public function page()
    {
        echo 'page';
    }

    public function view($id)
    {
        echo $id;
    }

}
```

## 错误处理

当没有路由可以匹配时，可以返回预先定义的默认路由。

```php
Macaw::error(function() {
  echo '404 :: Not Found';
});
```
或者

```php
Macaw::error('\PublicController@notfound');
```
fu
再或者

```php
Macaw::$error_callback=function(){
  echo '404 :: Not Found';
}
```

## RESTful支持

由于路由内部使用的`__callstatic`方法，可以适配所有的http操作。而不仅仅是`GET`,`POST`。  
甚至于`PUT`,`DELETE`。  
本框架也推荐使用`RESTful`。  

```php
Macaw::delete('posts/(:num)', '\app\controller\PostController@delete');
```

将适配`DELETE`请求，并完成转发。

# 控制器
控制器作为代码和逻辑主要承载，框架本身对其约束很少。本质上，它就是一个用于被路由调用的类。  
同时，它担负着与模型和视图交互的重要责任。  

## 定义

```php
<?php
namespace app\controllers;
/**
 * \HomeController
 */
class HomeController extends BaseController
{

    public function home()
    {
       echo "hello !";
    }
}
```
这个文件按照`psr-4`规范，实际上它确实位于`\app\Controllers\HomeCOntroller.php`.  

## 输出json

很多时候，后台需要返回json给前端使用(ajax,app等)，框架内部有便捷的方法:

```php
<?php
namespace app\controllers;
/**
 * \HomeController
 */
class HomeController extends BaseController
{

    public function home()
    {
       $ret['name']='张三';
       $ret['age']=26;
       success($ret);
    }
    public function school()
    {
       $ret['time']=time();
       $ret['remark']='链接缓存服务器失败！';
       error($ret);
    }
}
```

## 初始化操作
[仅对派遣模式有效]  
默认地，类控制器中提供了`init`方法，用来自动初始化类中的数值。  
它总是在类被初始化时被调用，类似`构造函数`。  
可以在这里对当前控制器进行相应的操作.  

```php
<?php


namespace admin\controllers;


class HomeController extends BaseController
{
    protected function init(){
        echo "hello ,";
    }
}
```



## 前后置操作
[仅对派遣模式有效]  
同样地,对于逻辑关联性较强的控制器,单纯一个初始化是远远不够的,框架提供了前后置操作来加强这方便.  

```php
<?php

namespace admin\controllers;

class HomeController extends BaseController
{
    protected function init(){
        echo "hello ,";
    }
    public function _before_index(){
        echo "welcome ";
    }
    public function index(){
        echo " to ";
    }
    public function _after_index(){
        echo "admin !";
    }
}
```

此时如果访问此页面,

```shell
curl http://xxx.com/admin/home/index
```
将会输出

```html
hello ,welcome  to admin !
```


# 配置读写

?> 待维护。。。





# 数据库

本框架的数据库仅支持`mysqli`,由[PHP-MySQLi-Database-Class](https://github.com/joshcam/PHP-MySQLi-Database-Class)提供底层支持.
但框架对其进行了易用性封装,使其语法更容易被接受.  

## 配置
只需要在`config/database.php`中直接返回一个数组即可.  

```php
<?php
return [
    'host' => 'localhost',
    'db' => 'xxx',
    'port' => 3306,
    'username' => 'xxx',
    'password' => 'xxx',
    'charset' => 'utf8',
    'prefix' => 'xx_'
];
```

## 基本使用

### 直接查询
这样简单来说就是直接执行sql语句,这里只是演示,在实际开发过程中,不建议这样做.  

```php
$users = $this->db->rawQuery('SELECT * from users where id >= ?', Array (10));
foreach ($users as $user) {
    print_r ($user);
}
```
同样的,我们可以直接返回查到的第一个结果 

```php
$users = $this->db->rawQueryOne('SELECT * from users where id >= ?', Array (10));
echo $user['login'];
```
甚至,我们可以只要其中一个字段

```php
$password = $this->db->rawQueryValue ('select password from users where id=? limit 1', Array(10));
echo "Password is {$password}";
```
更甚至,返回字段数组

```php
$logins = $this->db->rawQueryValue ('select login from users limit 10');
foreach ($logins as $login)
    echo $login;
```

### 返回对象

```php
//默认情况下,返回一个关联数组
$user = $this->db->rawQueryOne ('select * from users where id=?', Array(10));
echo $user['login'];

//我们可以返回一个对象
$user = $this->db->ObjectBuilder()->rawQueryOne ('select * from users where id=?', Array(10));
echo $user->login;
```

### 返回json
更进一步地,我们可以直接返回一个`json`而不是数组,也不是对象. 

```php
//我们可以返回一个对象
$user = $this->db->JsonBuilder()->rawQueryOne ('select * from users where id=?', Array(10));
echo $user;
```
### 参数绑定

```php
$params = Array(1, 'admin');
$users = $this->db->rawQuery("SELECT id, firstName, lastName FROM users WHERE id = ? AND login = ?", $params);
print_r($users); 

// 关联查询
$params = Array(10, 1, 10, 11, 2, 10);
$q = "(
    SELECT a FROM t1
        WHERE a = ? AND B = ?
        ORDER BY a LIMIT ?
) UNION (
    SELECT a FROM t2 
        WHERE a = ? AND B = ?
        ORDER BY a LIMIT ?
)";
$resutls = $this->db->rawQuery ($q, $params);
print_r ($results);
```

## 异常追踪

```php
$this->db->where('login', 'admin')->update('users', ['firstName' => 'Jack']);

if ($this->db->getLastErrno() === 0)
    echo 'Update succesfull';
else
    echo 'Update failed. Error: '. $this->db->getLastError();
```
## 性能统计
可以用来跟踪查询时间和sql语句等.

```php
$this->db->setTrace (true);
// As a second parameter it is possible to define prefix of the path which should be striped from filename
// $this->db->setTrace (true, $_SERVER['SERVER_ROOT']);
$this->db->get("users");
$this->db->get("test");
print_r ($this->db->trace);
```

则它可能输出以下

```array
    [0] => Array
        (
            [0] => SELECT  * FROM t_users ORDER BY `id` ASC
            [1] => 0.0010669231414795
            [2] => MysqliDb->get() >>  file "/avb/work/PHP-MySQLi-Database-Class/tests.php" line #151
        )

    [1] => Array
        (
            [0] => SELECT  * FROM t_test
            [1] => 0.00069189071655273
            [2] => MysqliDb->get() >>  file "/avb/work/PHP-MySQLi-Database-Class/tests.php" line #152
        )

```

## 辅助函数
### 自动重连
```php
if (!$this->db->ping())
    $this->db->connect()
```
### 输出上条语句
```php
$this->db->get('users');
echo "Last executed query was ". $this->db->getLastQuery();
```
### 检查表存在
```php
    if ($this->db->tableExists ('users'))
        echo "hooray";
```
### 转义字符
实际上它是封装的`mysqli_real_escape_string`.

```php
   $escaped = $this->db->escape ("' and 1=1");
```

## 增

### 普通插入
```php
$data = Array ("login" => "admin",
               "firstName" => "John",
               "lastName" => 'Doe'
);
$id = $this->db->insert ('users', $data);
if($id)
    echo 'user was created. Id=' . $id;
```

### 带函数的插入

```php
$data = Array (
    'login' => 'admin',
    'active' => true,
    'firstName' => 'John',
    'lastName' => 'Doe',
    'password' => $this->db->func('SHA1(?)',Array ("secretpassword+salt")),
    // password = SHA1('secretpassword+salt')
    'createdAt' => $this->db->now(),
    // createdAt = NOW()
    'expires' => $this->db->now('+1Y')
    // expires = NOW() + interval 1 year
    // Supported intervals [s]econd, [m]inute, [h]hour, [d]day, [M]onth, [Y]ear
);

$id = $this->db->insert ('users', $data);
if ($id)
    echo 'user was created. Id=' . $id;
else
    echo 'insert failed: ' . $this->db->getLastError();
```

### 重复键插入
经常遇到这样的情景，向一个表里插入一条数据，如果已经存在就更新一下，用程序实现麻烦而且在并发的时候可能会有问题，这时用mysql的DUPLICATE KEY 很方便.  

比如
```mysql
CREATE TABLE `q_user` (
  `id` int(11) UNSIGNED NOT NULL primary key auto_increment,
  `login` varchar(32) NOT NULL UNIQUE KEY,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `createdAt` int(11) UNSIGNED NOT NULL,
  `updatedAt` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
这个数据表中,`id`为主键自增,`login`为`UNIQUE`唯一. 在此时如果想要实现存在修改,不存在则添加时,常规的方法是用
php判断下是否存在,然后进行自己的一些逻辑.  
这里我们直接使用`mysql`的`Duplicate`机制.  

```php
$data = Array ("login" => "admin",
               "firstName" => "John",
               "lastName" => 'Doe',
               "createdAt" => time(),
               "updatedAt" => time(),
);
$updateColumns = Array ("updatedAt");
$lastInsertId = "id";
$this->$this->db->onDuplicate($updateColumns, $lastInsertId);
$id = $this->db->insert ('user', $data);
```
在上面这段代码中,第一次执行会成功的插入,第二次则是仅仅修改了`updatedAt`的值.  
其生成的sql语句为

```mysql
INSERT INTO q_user (`login`, `firstName`, `lastName`, `createdAt`, `updatedAt`) VALUES ('admin', 'John', 'Doe', '1483171123', '1483171123') ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID (id), `updatedAt` = '1483171123'
```

### 批量插入
批量插入不同的字段.  

```php
$data = Array(
    Array ("login" => "admin",
        "firstName" => "John",
        "lastName" => 'Doe'
    ),
    Array ("login" => "other",
        "firstName" => "Another",
        "lastName" => 'User',
        "password" => "very_cool_hash"
    )
);
$ids = $this->db->insertMulti('users', $data);
if(!$ids) {
    echo 'insert failed: ' . $this->db->getLastError();
} else {
    echo 'new users inserted with following id\'s: ' . implode(', ', $ids);
}
```

批量插入相同字段.  

```php
$data = Array(
    Array ("admin", "John", "Doe"),
    Array ("other", "Another", "User")
);
$keys = Array("login", "firstName", "lastName");

$ids = $this->db->insertMulti('users', $data, $keys);
if(!$ids) {
    echo 'insert failed: ' . $this->db->getLastError();
} else {
    echo 'new users inserted with following id\'s: ' . implode(', ', $ids);
}
```

## 替换
[Replace()](https://dev.mysql.com/doc/refman/5.7/en/replace.html)  
这个函数和`insert`的使用方法完全一致.  
**但是**,这里要对仅仅从里面上意思理解的小伙伴提个醒,`mysql`的所谓替换,可能和你理解的有些不一样.  
`mysql`在遇到数据冲突时,删掉了旧记录，再写入新记录，这是使用 REPLACE INTO 时最大的一个误区.  
问题在此时出现了,写入新记录时不会将你期望的没有修改的写进去,而是不去管他.而这个字段就相当于`丢失`了.  
这可能并非你的逻辑上需要的.更常见的需求是当存在`login`时,则更新某些个字段,而其他的保持不变.  
而满足这以需求的则是上面着重的介绍的`ON DUPLICATE KEY UPDATE`.  
也就是说,**如果是想存在时更新,不存在插入,请使用`onDuplicate`方法!**

## 导入
很多时候我们需要把数据从`csv`甚至`xml`中导入到数据库里.  
针对于此,`puck`也提供了简单的方式

### 导入csv

```php
$path_to_file = "/path/to/file.csv";
$this->db->loadData("users", $path_to_file);
```
当然,这里还有一些选项,如果这个`csv`并不十分规范:

```php
Array(
    "fieldChar" => ';',     // 分割符
    "lineChar" => '\r\n',   // 换行符
    "linesToIgnore" => 1    // 忽略开头的几行
);
```
然后就可以

```php
$options = Array("fieldChar" => ';', "lineChar" => '\r\n', "linesToIgnore" => 1);
$this->db->loadData("users", "/path/to/file.csv", $options);
```

### 导入xml
和上面一样,没有什么大的不同,除了一些细节

```php
$path_to_file = "/path/to/file.xml";
$this->db->loadXML("users", $path_to_file);
```
同样地,这里也有一些选项

```php
Array(
    "linesToIgnore" => 0,    //忽略开头的几行
    "rowTag"    => "<user>" // 入口标记
)
```
然后,不出意外地

```php
$options = Array("linesToIgnore" => 0, "rowTag" => "<user>"):
$path_to_file = "/path/to/file.xml";
$this->db->loadXML("users", $path_to_file, $options);

```

## 更新

```php
$data = Array (
    'firstName' => 'Bobby',
    'lastName' => 'Tables',
    'editCount' => $this->db->inc(2),
    // editCount = editCount + 2;
    'active' => $this->db->not()
    // active = !active;
);
$this->db->where ('id', 1);
if ($this->db->update ('users', $data))
    echo $this->db->count . ' records were updated';
else
    echo 'update failed: ' . $this->db->getLastError();
```
更新用起来非常的方便,也非常的简单.

## 查找

### 普通查找
```php
//查找所有
$users = $this->db->get('users');
//查找10个
$users = $this->get('users', 10);
```
### 查找指定列

```php
$cols = Array ("id", "name", "email");
$users = $this->db->get ("users", null, $cols);
if ($this->db->count > 0)
    foreach ($users as $user) { 
        print_r ($user);
    }
```
### 查找一行

```php
$this->db->where ("id", 1);
$user = $this->db->getOne ("users");
echo $user['id'];

$stats = $this->db->getOne ("users", "sum(id), count(*) as cnt");
echo "total ".$stats['cnt']. "users found";
```

### 直接获取值

```php
$count = $this->db->getValue ("users", "count(*)");
echo "{$count} users found";
```

### 获取多行到数组
```php
$logins = $this->db->getValue ("users", "login", null);
// select login from users
$logins = $this->db->getValue ("users", "login", 5);
// select login from users limit 5
foreach ($logins as $login)
    echo $login;
```

### 设置返回类型
`puck`支持3中返回类型,分别是`数组`,`对象`,`json`,默认情况下,返回的结构是`数组`,但我们也可以返回其他的数据类型.  

```php
// Array return type
$= $this->db->getOne("users");
echo $u['login'];
// Object return type
$u = $this->db->ObjectBuilder()->getOne("users");
echo $u->login;
// Json return type
$json = $this->db->JsonBuilder()->getOne("users");
```

### 分页查询
如果使用`paginate`而不是`get`进行的查询,则会自动进行分页.  

```php
$page = 1;
// set page limit to 2 results per page. 20 by default
$this->db->pageLimit = 2;
$products = $this->db->arraybuilder()->paginate("products", $page);
echo "showing $page out of " . $this->db->totalPages;
```

其实还有另外一种分页的方式,就是利用mysql的`SQL_CALC_FOUND_ROWS`.它可以在limit的同时,顺便算以下不带
limit的条数.这就意味着通常需要两条sql的分页,可以一条来完成.
但是这样做,将不会对查询结果缓存.  
它适用与传统的`select count(*)`比较耗时的时候,另外计算数量肯定不划算.这时就可以用它来实现一次查询,获得想要的结果.  
但也不要滥用它,比如以某主键或索引为条件的时候,这个时候如果直接`count(*)`是不需要扫描全表的,而用`SQL_CALC_FOUND_ROWS`
则会强制扫描全表,反而导致性能的下降.  

```php
$offset = 10;
$count = 15;
$users = $this->db->withTotalCount()->get('users', Array ($offset, $count));
echo "Showing {$count} from {$this->db->totalCount}";
```


### 结果转换
很多时候,返回的关联数组并不是我们想要的.  
此时我们可以通过`map`方法将其转为自定义的`key=>value`格式.

```php
$user = $this->db->map ('login')->ObjectBuilder()->getOne ('users', 'login, id');
Array
(
    [user1] => 1
)

$user = $this->db->map ('login')->ObjectBuilder()->getOne ('users', 'id,login,createdAt');
Array
(
    [user1] => stdClass Object
        (
            [id] => 1
            [login] => user1
            [createdAt] => 2015-10-22 22:27:53
        )

)
```

## 条件

用于条件查询的几个方法有`where()`,`orwhere()`,`having()`,`orHaving()` 分别用于各种场景..  

where的两个参数分别列名和值,多个`where`的关系为`and`.

### 算术运算
```php
$this->db->where ('id', 1);
$this->db->where ('login', 'admin');
$results = $this->db->get ('users');
// 解析为: SELECT * FROM users WHERE id=1 AND login='admin';
```

```php
$this->db->where ('id', 1);
$this->db->having ('login', 'admin');
$results = $this->db->get ('users');
// 解析为:SELECT * FROM users WHERE id=1 HAVING login='admin';
```
如果想要两个列相等,

```php
$this->db->where ('lastLogin', 'createdAt');
```
这个是错误的做法,因为会被解析为

```sql
where (lastLogin='createdAt')
```
这明显不是我们所期望的结果.正确做法应该是

```php
$this->db->where ('lastLogin = createdAt');
$results = $this->db->get ('users');
// 解析为:SELECT * FROM users WHERE lastLogin = createdAt;
```
如果想使用其他的运算符,比如`>`,`<`而非`==`,应该通过第三个参数传入

```php
$this->db->where ('id', 50, ">=");
// or $this->db->where ('id', Array ('>=' => 50));
$results = $this->db->get ('users');
// 解析为: SELECT * FROM users WHERE id >= 50;
```

### BETWEEN / NOT BETWEEN

```php
$this->db->where('id', Array (4, 20), 'BETWEEN');
// or $this->db->where ('id', Array ('BETWEEN' => Array(4, 20)));

$results = $this->db->get('users');
// 解析为: SELECT * FROM users WHERE id BETWEEN 4 AND 20
```

### IN / NOT IN

```php
$this->db->where('id', Array(1, 5, 27, -1, 'd'), 'IN');
// or $this->db->where('id', Array( 'IN' => Array(1, 5, 27, -1, 'd') ) );

$results = $this->db->get('users');
// 解析为: SELECT * FROM users WHERE id IN (1, 5, 27, -1, 'd');
```

### 或

```php
$this->db->where ('firstName', 'John');
$this->db->orWhere ('firstName', 'Peter');
$results = $this->db->get ('users');
// 解析为: SELECT * FROM users WHERE firstName='John' OR firstName='peter'
```

### 是否为空

```php
$this->db->where ("lastName", NULL, 'IS NOT');
$results = $this->db->get("users");
// 解析为: SELECT * FROM users where lastName IS NOT NULL
```

### sql表达式
如果你是个急性子,并且对sql语法足够了解,你可以....

```php
$this->db->where ("id != companyId");
$this->db->where ("DATE(createdAt) = DATE(lastLogin)");
$results = $this->db->get("users");
```

### 变量绑定
如果你感觉上面那种方式太麻烦了,你还可以...   

```php
$this->db->where ("(id = ? or id = ?)", Array(6,2));
$this->db->where ("login","mike")
$res = $this->db->get ("users");
// 解析为: SELECT * FROM users WHERE (id = 6 or id = 2) and login='mike';
```

## 删除
删除的方法非常简单

```php
$this->db->where('id', 1);
if($this->db->delete('users')) 
  echo 'successfully deleted';
```
## 排序
其实就是`sql`标准语句的`order by`,并且支持多个排序条件,以调用先后顺序.

### 常规排序
常规排序就是我们常用的按照大小,asc码等.

```php
$this->db->orderBy("id","asc");
$this->db->orderBy("login","Desc");
$this->db->orderBy("RAND ()");
$results = $this->db->get('users');
// 解析为: SELECT * FROM users ORDER BY id ASC,login DESC, RAND ();
```
### 字段排序
有事我们可能需要按照某个字段的某些值排序.我们可以  

```php
$this->db->orderBy('userGroup', 'ASC', array('superuser', 'admin', 'users'));
$this->db->get('users');
// 解析为: SELECT * FROM users ORDER BY FIELD (userGroup, 'superuser', 'admin', 'users') ASC;
```

## 分组查询
分组就是标准`sql`语法的`group by`语句. 

```php
$this->db->groupBy ("name");
$results = $this->db->get ('users');
// 解析为: SELECT * FROM users GROUP BY name;
```
## 联表查询
即标准`sql`语法中的`join`.
一般的,

```php
$this->db->join("users u", "p.tenantID=u.tenantID", "LEFT");
$this->db->where("u.id", 6);
$products = $this->db->get ("products p", null, "u.login, p.productName");
print_r ($products);
```

多条件的,

```php
$this->db->join("users u", "p.tenantID=u.tenantID", "LEFT");
$this->db->joinWhere("users u", "u.tenantID", 5);
$products = $this->db->get ("products p", null, "u.login, p.productName");
print_r ($products);
// 解析为: SELECT  u.login, p.productName FROM products p LEFT JOIN users u ON (p.tenantID=u.tenantID AND u.tenantID = 5)
```

```php
$this->db->join("users u", "p.tenantID=u.tenantID", "LEFT");
$this->db->joinOrWhere("users u", "u.tenantID", 5);
$products = $this->db->get ("products p", null, "u.name, p.productName");
print_r ($products);
// 解析为: SELECT  u.login, p.productName FROM products p LEFT JOIN users u ON (p.tenantID=u.tenantID OR u.tenantID = 5)
```

## 子查询
子查询也是在开发过程中比较常用的功能. 

### 筛选

```php
$ids = $this->db->subQuery ();
$ids->where ("qty", 2, ">");
$ids->get ("products", null, "userId");

$this->db->where ("id", $ids, 'in');
$res = $this->db->get ("users");
// 解析为: SELECT * FROM users WHERE id IN (SELECT userId FROM products WHERE qty > 2)
```

### 插入

```php
$userIdQ = $this->db->subQuery ();
$userIdQ->where ("id", 6);
$userIdQ->getOne ("users", "name"),

$data = Array (
    "productName" => "test product",
    "userId" => $userIdQ,
    "lastUpdated" => $this->db->now()
);
$id = $this->db->insert ("products", $data);
// 解析为: INSERT INTO PRODUCTS (productName, userId, lastUpdated) values ("test product", (SELECT name FROM users WHERE id = 6), NOW());
```

### 联表

```php
$usersQ = $this->db->subQuery ("u");
$usersQ->where ("active", 1);
$usersQ->get ("users");

$this->db->join($usersQ, "p.userId=u.id", "LEFT");
$products = $this->db->get ("products p", null, "u.login, p.productName");
print_r ($products);
// SELECT u.login, p.productName FROM products p LEFT JOIN (SELECT * FROM t_users WHERE active = 1) u on p.userId=u.id;
```

### 结果判断

```php
$sub = $this->db->subQuery();
    $sub->where("company", 'testCompany');
    $sub->get ("users", null, 'userId');
$this->db->where (null, $sub, 'exists');
$products = $this->db->get ("products");
// SELECT * FROM products WHERE EXISTS (select userId from users where company='testCompany')
```
## 存在检测

很多时候,我需要判断数据库里面符合某(些)个条件的记录是否存在,一般而言可能需要查询后,再自行判断.  
本框架也提供了此类方法.  


```php
$this->db->where("user", $user);
$this->db->where("password", md5($password));
if($this->db->has("users")) {
    return "You are logged";
} else {
    return "Wrong user/password";
}
```

## 事务支持
这个显然是`innodb`所支持的特性了.  
是一个复杂数据的必备了.  

```php
$this->db->startTransaction();
...
if (!$this->db->insert ('myTable', $insertData)) {
    //Error while saving, cancel new record
    $this->db->rollback();
} else {
    //OK
    $this->db->commit();
}
```

## 锁表支持
框架支持`读`|`写`锁,可以自由地,灵活地,对表进行读写锁定和解锁.  
但只有一件事你可能需要注意的是当你锁定了之后,一定要记得解锁....


```php
//锁定users的写入操作
$this->db->setLockMethod("WRITE")->lock("users");
//解锁
$this->db->unlock();
//批量锁定
//同时锁定users log两个表的read操作
$this->db->setLockMethod("READ")->lock(array("users", "log"));
```


## 查询关键字

mysql支持非常多的查询关键字,比如 `LOW PRIORITY` | `DELAYED` | `HIGH PRIORITY` | `IGNORE`

```php
$this->db->setQueryOption ('LOW_PRIORITY')->insert ($table, $param);
//INSERT LOW_PRIORITY INTO table ...
```

```php
$this->db->setQueryOption ('FOR UPDATE')->get ('users');
//SELECT * FROM USERS FOR UPDATE;
```

当然,批量设置也是可以的.  

```php
$this->db->setQueryOption (Array('LOW_PRIORITY', 'IGNORE'))->insert ($table,$param);
// INSERT LOW_PRIORITY IGNORE INTO table ...
```

```php
$this->db->setQueryOption ('SQL_NO_CACHE');
$this->db->get("users");
// SELECT SQL_NO_CACHE * FROM USERS;
```

唯一可能需要注意的是,你可能需要了解一下这些关键字的作用.  


## 连续操作

由于这些方法都返回了`$this`,所以我们可以将上述分布操作了进行连续

```php
$results = $this->db
    ->where('id', 1)
    ->where('login', 'admin')
    ->get('users');

```

# 模型
虽然上面的篇幅里,用了非常多的口水去讲解数据库的操作,但是我们并不建议在`controller`里直接操作数据库.  
因为那样会带来非常多的维护上的问题.  
我们建议把数据库操作放到`model`层里,严格按照`mvc`的策略,来进行数据库的操作.  
并且,在模型中,框架的基类封装了常规数据的操作,使之可能性更高.  

## 定义

定义个user模型

```php
namespace app\models;
use \export\model as model;

class UserModel extends model
{
   protected $table='user';
}
```

虽然可以从类名转化为数据表名,但我们不赞成这么做,我们建议在每个模型中都**显式**的声明表名,以便于效率.  
但是这里的表明是不带后缀的,即在数据库中,这个表的真正名字为`puck_user`,如果在配置中配置的表前缀为`puck`的话.  

## 初始化

为了贴近底层,初始化还是使用构造函数.  
例如

```php
namespace app\models;
use \export\model as model;

class UserModel extends model
{
   function __construct()
    {
        parent::__construct();
        $this->table='user';
    }
}
```
本质上,它就是一个类.

## 使用

使用的时候,同样是当做一个类.  

```php
<?php
namespace app\controllers;

use app\models\UserModel as User;

class TestController extends BaseController
{
    public function test()
    {
      //实例化模型
       $user = new User;
    }
}

```
虽然这边可以同`thinkphp`一样,提供助手函数`M`,`Loader`等,但我们没有这么做,原因是选择越多,苦恼越多.  
而且,这些语法糖真的能够起到正面作用吗?

## 新增

新增一个数据非常的简单.  

```php
<?php
namespace app\controllers;

use app\models\UserModel as User;

class TestController extends BaseController
{
    public function test()
    {
       $data= array(
          'login'='heiheihei',
          'firstName'='hei',
          'lastName'='heihei',
          'createdAt'=time()
       );
       //实例化模型
       $user = new User;
       $isOk=$user->add($user);
    }
}

```
## 查询条件

在模型里,同样支持查询条件的连续直接调用.  
更多条件语句,请参考上面数据库节点下的->[条件](#条件)


## 更新

```php
<?php
namespace app\controllers;

use app\models\UserModel as User;

class TestController extends BaseController
{
    public function test()
    {
       $data= array(
          'login'='heiheihei',
          'firstName'='hei',
          'lastName'='heihei',
          'createdAt'=time()
       );
       //实例化模型
       $user = new User;
       $isOk=$user->where('id',1)->update($user);
    }
}

```

## 删除

```php
<?php
namespace app\controllers;

use app\models\UserModel as User;

class TestController extends BaseController
{
    public function test()
    {
       //实例化模型
       $user = new User;
       $isOk=$user->where('id',1)->delete();
    }
}

```

## 字段筛选
很多时候,比如查找的时候,我们可能不太想查找所有的数据,而是仅仅查找部分字段.  
框架内部提供了`field()`方法来实现这个功能.  
参考下面的例子

## 查询

### 查询所有

```php
<?php
namespace app\controllers;

use app\models\UserModel as User;

class TestController extends BaseController
{
    public function test()
    {
       //实例化模型
       $user = new User;
       $isOk=$user->field('login,firstName,lastName,id')->where('id',1,'>')->select();
    }
}

```

### 查询一条
当只需要一条的时候,可以使用`find()`方法来快速返回.  

```php
<?php
namespace app\controllers;

use app\models\UserModel as User;

class TestController extends BaseController
{
    public function test()
    {
       //实例化模型
       $user = new User;
       $isOk=$user->field('login,firstName,lastName,id')->where('id',1,'>')->find();
    }
}

```

## 计数

同样地,框架内部提供了`count()`方法来实现统计功能.  
但它并非是mysql内部的`count(*)`,而是查询后的缓存`count`


```php
<?php
namespace app\controllers;

use app\models\UserModel as User;

class TestController extends BaseController
{
    public function test()
    {
       //实例化模型
       $user = new User;
       $info=$user->field('id')->where('id',1,'>')->select();
       //这样获取上次结果的记录数
       $count=$user->count();
    }
}

```


如果想要根据条件来获取数据库内部符合条件的记录数,应该使用

```php
<?php
namespace app\controllers;

use app\models\UserModel as User;

class TestController extends BaseController
{
    public function test()
    {
       //实例化模型
       $user = new User;
       $info=$user->field('count(*) as count')->where('id',1,'>')->find();
       $count=$info['count'];
       //这样获取符合条件的记录数
    }
}

```

## 分页

框架内部对分页做过完善的,甚至带了分页助手函数.  

```php
<?php
namespace app\controllers;

use \app\models\UserModel as User;
use \export\helpers\PageHelper
class TestController extends BaseController
{
    public function test($page)
    {
       //实例化模型
       $user = new User;
       $info=$user->field('login,firstName,lastName,id')
                  ->where('id',1,'>')
                  ->orderBy("createdAt","Desc")
                  ->orderBy("id","Desc")
                  ->page($page,20);
       if($user->totalCount>1){
            $pageClass= new PageHelper(20,'page');
            $pageClass->set_total($model->totalCount);
            //打印页面的导航,输出到前台即可
            $pageLinks=$pageClass->page_links();
        }
    }
}

```

## 其他

除此之外,模型还支持 [数据库](#数据库) 里面的各种的复杂的实现. 

# 视图

本框架使用[twig](http://twig.sensiolabs.org/documentation)作为模板引擎.  
可以具体的使用方法,看看去其官网文档了解.  
## 模板位置
除非特别制定,框架会在`/app/views/CONTROLLER_NAME/ACTION_NAME.twig`读取模板.  


## 变量传递
在`控制器`中,将后台的数据传递到前台,使用`assgin()`方法.
例如


```php
<?php
namespace app\controllers;

use \app\models\UserModel as User;
use \export\helpers\PageHelper
class TestController extends BaseController
{
    public function test($page)
    {
       //实例化模型
       $user = new User;
       $pageLinks='仅一页.';
       $list=$user->field('login,firstName,lastName,id')
                  ->where('id',1,'>')
                  ->orderBy("createdAt","Desc")
                  ->orderBy("id","Desc")
                  ->page($page,20);
       if($user->totalCount>1){
            $pageClass= new PageHelper(20,'page');
            $pageClass->set_total($model->totalCount);
            //打印页面的导航,输出到前台即可
            $pageLinks=$pageClass->page_links();
        }
        //传递变量到前台,用于输出
        $this->assign('userList',$list);
        $this->assign('pageLinks',$pageLinks);
        $this->title='使用日志';
    }
}

```
特别地,`puck`会使用控制器的`title`属性作为页面的`title`.  



## 模板渲染

在非`开发者模式`下,第一次渲染的结果被会缓存在`/cache`下.  
在`控制器`中,当变量注册完毕,需要通过`show`方法来渲染模板.  

```php
<?php
namespace app\controllers;

use \app\models\UserModel as User;
use \export\helpers\PageHelper
class TestController extends BaseController
{
    public function test($page)
    {
       //实例化模型
       $user = new User;
       $pageLinks='仅一页.';
       $list=$user->field('login,firstName,lastName,id')
                  ->where('id',1,'>')
                  ->orderBy("createdAt","Desc")
                  ->orderBy("id","Desc")
                  ->page($page,20);
       if($user->totalCount>1){
            $pageClass= new PageHelper(20,'page');
            $pageClass->set_total($model->totalCount);
            //打印页面的导航,输出到前台即可
            $pageLinks=$pageClass->page_links();
        }
        //传递变量到前台,用于输出
        $this->assign('userList',$list);
        $this->assign('pageLinks',$pageLinks);
        $this->title='使用日志';
        //渲染模板
        $this->show();
    }
}

```

# 错误和调试

`puck`使用`whoops`作为错误处理,这也是`Laravel4`默认集成的错误处理.  
它拥有漂亮的界面,来作为一个有趣的提示页.  

默认地,如果你使用`调试模式`,框架会自动启用`whoops`.  
当程序发生异常时,便可以更改方便的调试.

![img](https://camo.githubusercontent.com/31a4e1410e740fd0ccda128cbcab8723f45e7e73/687474703a2f2f692e696d6775722e636f6d2f305651706539362e706e67)
