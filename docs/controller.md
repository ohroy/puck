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