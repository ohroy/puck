# 依赖注入&容器


## 容器支持

与`laravel`等代化的框架一样，`puck`也使用容器来管理依赖解决耦合，在这种前提下，理解`依赖注入`,`容器`等概念就显得尤为重要。
至少对于一个有追求的程序员来说。  

从1.0版本起，框架从底层引入了`容器`，并且框架本身的组件实例都由其来管理。  

如果不了解依赖注入或者容器的概念的，强烈推荐查找资料，或阅读本框架源码来理解。  

## 简单使用

框架在初始化时绑定了必要的核心类到容器中，但也仅仅是绑定，并没有初始化类本身。  
你也可以自行绑定自己的类到容器中去。  

```php
app()->bind('myxxxz','\app\helpers\xxxz');
```
此后，当你要使用此类的时候，就可以通过

```php
app('myxxxz')
```
来获得这个类的实例，容器会自动初始化这个类。

## 依赖注入

上面所说的只是一个简单的用法，还远远体现不了依赖注入的优势--因为他没有依赖。  
此处我们用另外一个例子来说明。假设我们现在的动作是`旅游`。而完成这个动作需要一个交通工具，即`旅游`依赖一个`车`，车又分为很多种，而每个车又依赖一个司机。  
按照传统方式，我们想都不用想
```php
$driver= new \app\drivers\Driver();
$car =new \app\cars\Car();
$action= new \app\actions\Travel($car,$driver);
```
事实上，这种方式没有什么不好的，至少目前来说是这样的。好吧，我们只是想把他变的简单一些————依赖自动注入。我们想`Travel`这个类的时候，自动new它的依赖的类，然后传参数进去————就像`apt-get/yum/npm/pip/composer`等依赖管理工具一样，自动分析依赖并加载。我们唯一需要做的就是只关心动作，而忽略过程。  
怎么办呢？  
唯一一件需要做的事情就是需要更改Travel的构造函数，指明依赖参数的类型。

```php
<?php
namespace \app\actions;
use \app\drivers\Driver;
use \app\cars\Car;
class Travel{
    function __construct(Driver $driver,Car $car) {
        //....
    }
    public function go($target){
        //...
    }
}

```
在这里，我们指明了这个类的构造函数的参数类型。此时
```php
app()->bind('travel','\app\actions\Travel');
app('travel')->go('dali');
```
这时，就能简单方便的去！大！理！了！

## 高级依赖注入

在上面的例子中，由于逻辑不够复杂，还不能体现依赖注入的方便之处，假使逻辑更加复杂，比如我们可以更换不同的交通工具，更换不同的司机，执行不同的动作等。  
此时我们就要用php自身的接口`interface`来实现复杂的依赖注入。也可以通过后续的提供更换对象方法来实现，也可以通过闭包绑定来实现。这里以较为自由的闭包绑定为例。


```php
app()->bind('travel',function(){
    $driver=app('config')->get('driver','default_driver');
    $car=app('config')->get('car','default_car');
    return new \app\actions\Travel($driver,$car);
})
```

同样地，这样实现了从配置文件里获取当前的司机和汽车，以后如果想更换，只需要更改配置文件即可。  
进一步的，你当然也可以通过数据库进行控制。

## 批量绑定
上面的绑定都针对的一个类，但对于很多同一种类，如`model`。还要依次绑定，岂不是很累，很麻烦？ 
本框架原创的一种解决方案是和路由功能相似的`正则绑定`。

```php
app()->regexBind('/^(\w+)_model$/',"\\app\\models\\\\$1");
```
这样即可实现
```php
app('test_model');// \app\models\Test;
app('user_model');// \app\models\User;
app('detail_model');// \app\models\Detail;
app('user_info_model');// \app\models\UserInfo;
```

