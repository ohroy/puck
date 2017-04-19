# curl类

这个功能主要是对php的curl请求的封装，主要用于做`api`的请求，或者...爬虫。  
框架在初始化时，已经注册过网络请求类的实例。我们可以使用
```php
app('curl');
```
来获取它。

## 基本使用

```php
$curl = app('curl');
$curl->get('https://www.baidu.com/');
if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
    echo 'Response:' . "\n";
    var_dump($curl->response);
}
```
如果请求出错(包括因为网络原因的，或者网站本身原因的，例如404，或者500等)。`error`成员会被置为`true`，同时`errorCode`则为错误代码，而`errorMessage`成员则是错误的详情。

如果一切正常，请求的返回结果则存放在`response`上。 

值得一提的是，如果返回的`response`头部用`content-type`指明了页面内容类型，框架会自动的进行转化，例如返回头设置为类型为`application/json`,框架则自动会将内容转化为`对象`。同理，如果是`application/xml`,框架则自动转化`xml`对象。
而不是其本身。

## 参数提交

当`get`请求的网址有复杂的参数时，虽然仍然可以拼接一个很长的字符串到`url`上，但是总归不够优雅。框架支持传入一个数组类型的参数。

```php
$curl = app('curl');
$curl->get('https://www.baidu.com/search',[
    'q' => 'keyword',
]);
```

## restfull支持

如你所想，它当然不止支持`get`请求，`post`请求甚至`put`请求或者`delete`等都不在话下。

```php
$curl = app('curl');
$curl->put('https://api.example.com/user/', array(
    'first_name' => 'Zach',
    'last_name' => 'Borboa',
));

$curl->patch('https://api.example.com/profile/', array(
    'image' => '@path/to/file.jpg',
));

$curl->patch('https://api.example.com/profile/', array(
    'image' => new CURLFile('path/to/file.jpg'),
));


$curl->delete('https://api.example.com/user/', array(
    'id' => '1234',
));

//下载文件
$curl->download(https://down.example.com/xxx.zip, '/path/to/xxx.zip');
```

## 请求头设置

不同到网站，可能要求的请求头不一样。框架封装了这些方法。

```php
$curl=app('curl');
//设置授权
$curl->setBasicAuthentication('username', 'password');
$curl->setDigestAuthentication('username', 'password');
//设置user agent
$curl->setUserAgent('MyUserAgent/0.0.1 (+https://www.example.com/bot.html)');
//设置来源页面
$curl->setReferrer('https://www.example.com/url?url=https%3A%2F%2Fwww.example.com%2F');
//设置cookie
$curl->setCookie('key', 'value');
```
发现了问题？`cookie`设置太麻烦？没有关系，框架支持多种设置cookie的方法

```php
$curl=app('curl');
Curl::setCookie($key, $value)
Curl::setCookieFile($cookie_file)
Curl::setCookieJar($cookie_jar)
Curl::setCookieString($string)
Curl::setCookies($cookies)
```

还不够？没有关系。框架支持通过`setHeader`来自定以设置http头。
```php
$curl=app('curl');
$curl->setHeader('X-Requested-With', 'XMLHttpRequest');
//批量设置
$headers=[
    'X-Requested-With'=>'XMLHttpRequest',
    'xxx'=>'yyy'
];
$curl->setHeaders($headers)
```

## 返回头获取

that is easy!
```php
$curl=app('curl');
$curl->get('https://www.example.com/');

if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
    echo 'Response:' . "\n";
    var_dump($curl->response);
}
//请求头
var_dump($curl->requestHeaders);
//响应头
var_dump($curl->responseHeaders);
```

## 自定义配置

除此之外，框架还支持更多的配置

```php
$curl=app('curl');
//设置超时时间为5秒
$curl->setConnectTimeout(5);
//设置重试次数为5次
$curl->retryCount=5;
//设置json解码函数
$curl->setJsonDecoder(myfunction)
//设置xml解码函数
$curl->setXmlDecoder(myfunction)
```

如果这都满足不了你，好吧，还有更直接的！
不过，这可能要求你去查阅curl文档来进行设置了。


```php
$curl=app('curl');
//直接设置curl选项
$curl->setOpt($option, $value);
//批量设置curl选项
$curl->setOpts($options);
```

如我们可以设置
```php
$curl=app('curl');
$curl->setOpt(CURLOPT_ENCODING , 'gzip');
```
来解码服务端`gzip`压缩的网页。

可以设置
```php
$curl=app('curl');
$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
$curl->get('https://url.cn/xxxxx');
```
来跟随url跳转。

## 回调函数
```php
$curl=app('curl');
//发送请求之前
$curl->beforeSend('myfunction');
//进度回调
$curl->progress('myfunction')；
//完成回调
$curl->complete('myfunction')；
//成功回调
$curl->success('myfunction')；
//失败回调
$curl->error('myfunction')；
```