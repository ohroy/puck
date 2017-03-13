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