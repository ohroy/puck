# 从部署到运行

## 新建项目

框架推荐的项目目录格式如下
```tree
├── admin  --后台项目
│   ├── controllers  --控制器
│   ├── models       --模型
│   └── views       --模版
├── api    --api项目
│   └── controllers
├── app    --主项目
│   ├── conf
│   ├── controllers
│   ├── models
│   └── views
├── cache  ---缓存目录
│   └── xxxx....
├── cli    ---命令行目录
│   ├── controllers
│   ├── functions
│   ├── helper
│   └── logic
├── composer.json   --composer配置文件
├── function.php   -- 助手函数
├── .env      -- 环境配置文件
├── public
│   ├── assets   --资源文件夹
│   └── index.php  --入口文件
├── config          --配置文件目录
│   ├── app.php     --各种配置文件
│   ├── some.php
│   ├── other.php
│   └── etc.php
└── vendor
```

一般而言，我们推荐使用composer来初始化您的项目。

```bash
cd /path/to
mkdir balabala
cd balabala
composer init
```
依次来填写各种配置信息即可。  
如果你之前有使用过composer 或者 npm之类的，你可能对这步并不奇怪。

## 安装框架

正如前文所言，我们使用composer来安装框架。 一般地，我们推荐安装最新release的版本。这能保证体验到越来越好的框架，和越来越少的bug。


```shell
composer require rozbo/puck
```

稍等即可安装成功。

## 然后配置项目

我们开始在项目目录里写代码了. 
如上文所言，我们建议在public里建立index.php文件，作为我们项目的入口。

```shell
mkdir public
```

里面的内容大概为
```php
<?php
$path=__DIR__."/..";
require "$path/vendor/autoload.php";
$app = new \puck\App($path);
```

当然，我们现在这样只是初始化了框架，并没有干什么事，接下来我们开始映射一些路由。

```php
$app->route->get("/",function(){
    echo "balabala";
});
```

最后，我们让他动起来！

```php
$app->run();
```

此时，我们访问首页即可看到我们期望输出的
```html
balabala
```

 

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


