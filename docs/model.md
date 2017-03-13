 模型
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