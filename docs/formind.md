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
│  ├─controllers            
│  │  ├─Xxx.php
│  ├─helpers            
│  │  ├─Xxx.php
│  ├─models            
│  │  ├─Xxx.php
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
          "app\\":"app",
          "api\\":"api"
        },
    "files": [
      "function.php"
    ]
  },
```

其中`files`为自动载入的文件。为了使函数高可用，我们需要提前加载进去。