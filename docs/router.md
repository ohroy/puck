# 路由
`puck`的路由是是一个轻量级的逻辑，原理是把把整个网址当成一个字符串，然后用正则匹配，进行相应处理，十分的简单。 

框架在初始化时，已经注册过路由的实例。
我们可以像首页那样，使用
```php
$app->route->get('/',function(){
    echo "balabala";
})
```
也可以通过
```php
app('route')->get('/',function(){
  echo "balabala";
})
```
相信聪明的你，已经发现了
`app('route')`就是用来获取`route`的实例。


## 闭包路由

### 一般使用
我们可以使用闭包的方式定义一些特殊需求的路由，而不需要执行控制器的操作方法了，例如：

```php
app('route')->get('/', function() {
  echo 'Hello world!';
});
```
### 参数传递

闭包定义的时候支持参数传递，例如：

```php
app('route')->get('/(:any)', function($slug) {
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
app('route')->get('/([^/]+)', function($slug) {
  echo 'The slug is: ' . $slug;
});
```

因此我们可以自行以正则定义各种路由

```php
app('route')->get('/^([1][358][0-9]{9})$', function($phoneNum) {
  echo 'yeah,the phone num is: ' . $phoneNum;
});
```
## 控制器支持

```php
app('route')->get('/', 'Controllers\demo@index');
app('route')->get('page', 'Controllers\demo@page');
app('route')->get('view/(:num)', 'Controllers\demo@view');
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
app('route')->error(function() {
  echo '404 :: Not Found';
});
```
或者

```php
app('route')->error('\PublicController@notfound');
```

再或者

```php
app('route')->$error_callback=function(){
  echo '404 :: Not Found';
}
```

## RESTful支持

由于路由内部使用的`__callstatic`方法，可以适配所有的http操作。而不仅仅是`GET`,`POST`。  
甚至于`PUT`,`DELETE`。  
本框架也推荐使用`RESTful`。  

```php
app('route')->delete('posts/(:num)', '\app\controller\PostController@delete');
```

将适配`DELETE`请求，并完成转发。

同样的，使用
```php
app('route')->put('posts/(:num)', '\app\controller\PostController@put');
```
将适配`put`操作.

以一步，如果你想适配任何请求，只需要

```php
app('route')->any('posts/(:num)', '\app\controller\PostController@index');
```


## 控制器路由

看完以上的路由用法，相信你心里可能有个疑惑，我网站那个多的页面，难道要一个一个自己去写路由规则吗？

当然不是，上面的都是自定义路由，而大多数情况下，我们都是使用的控制器路由，即让让路自动派遣到对应的控制器上。

```php
app('route')->any('/([\w\W]*)', function ($slug) {
    \puck\helpers\Dispatch::dispatch($slug, '\\app');
    return;
});
```
第二个参数为项目的命令空间。

此时，如果你访问
```html
xxx.com/a/b
```
则是绑定的
`app\controllers\A`里面的`b`方法。
