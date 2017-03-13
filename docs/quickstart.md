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