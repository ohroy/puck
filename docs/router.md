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