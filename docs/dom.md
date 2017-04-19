# dom解析

嘿嘿嘿。上一节我们说过了网络请求支持，这一节就说dom解析，你们是不是发现了什么？

框架支持类似于`jquery`的语法来对dom进行筛选，甚至修改或者添加。

框架在初始化时，已经注册过网络请求类的实例。我们可以使用
```php
app('dom');
```
来获取它。


## 加载文件

```php
//直接从本地文件加载
app('dom')->loadHtmlFile('xxx.html');
//从url端加载文件
app('dom')->loadHtmlFile('http://www.xxx.com/xxx.html');
//加载一个字符串格式的html
$html = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Document</title>
        </head>
        <body>
        </body>
        </html>';
app('dom')->loadHtml($html);

```

## 筛选

本来这是最复杂的一部分，但是这里不做详细的介绍了，相信大家都对jquery的元素筛选有所了解，当然，不了解的可以查下资料。

```php
//解析给定html的第一个body元素为文本
$test=app('dom')->loadHtml($html)->find('body')[0]->text();
//解析给定html的第一个body元素为元素对象
$body=app('dom')->loadHtml($html)->find('body')[0]->html();
//这个元素能接着筛选,筛选所有类名为`post`的元素并遍历输出；
$posts=$body->find('.post');
foreach($posts as $post) {
    echo $post->text(), "\n";
}
```

## 更多筛选
框架像`jquery`那样支持通过`标签`,`类名`,`id`,`name`或者`属性`进行筛选，同样的还支持伪类中筛选。具体如下

- 标签
- 类名, ID, name,属性的值
- 伪类:
    - first-, last-, nth-child
    - empty and not-empty
    - contains
    - has
    
```php

// 通过元素筛选出所有的a标签
$document->find('a');

// 混合筛选出 id = "foo" 并且 类名为 "bar" 的元素
$document->find('#foo.bar');

// 筛选出属性有name的元素
$document->find('[name]');
// 同样的
$document->find('*[name]');

// 混合筛选，通过元素，及元素属性的指定值
$document->find('input[name=foo]');
$document->find('input[name=\'bar\']');
$document->find('input[name="baz"]');

// 筛选出一个拥有data-开头的属性，并且它的值为foo
$document->find('*[^data-=foo]');

// 筛选出所有和href=https开头的a元素
$document->find('a[href^=https]');

// 所有src值的结尾为png的img元素
$document->find('img[src$=png]');

// 所有href的值包含example.com的a元素
$document->find('a[href*=example.com]');

// 查找class为foo的a元素的text
$document->find('a.foo::text');

// 查找class为bar的a元素的href属性或者title属性的值
$document->find('a.bar::attr(href|title)');
```

## 输出

可以通过
`html`,`text`,`innerHtml`方法来输出对应的对象或者文本。

