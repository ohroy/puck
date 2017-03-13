# 数据库

本框架的数据库仅支持`mysqli`,由[PHP-MySQLi-Database-Class](https://github.com/joshcam/PHP-MySQLi-Database-Class)提供底层支持.
但框架对其进行了易用性封装,使其语法更容易被接受.  

## 配置
只需要在`config/database.php`中直接返回一个数组即可.  

```php
<?php
return [
    'host' => 'localhost',
    'db' => 'xxx',
    'port' => 3306,
    'username' => 'xxx',
    'password' => 'xxx',
    'charset' => 'utf8',
    'prefix' => 'xx_'
];
```

## 基本使用

### 直接查询
这样简单来说就是直接执行sql语句,这里只是演示,在实际开发过程中,不建议这样做.  

```php
$users = $this->db->rawQuery('SELECT * from users where id >= ?', Array (10));
foreach ($users as $user) {
    print_r ($user);
}
```
同样的,我们可以直接返回查到的第一个结果 

```php
$users = $this->db->rawQueryOne('SELECT * from users where id >= ?', Array (10));
echo $user['login'];
```
甚至,我们可以只要其中一个字段

```php
$password = $this->db->rawQueryValue ('select password from users where id=? limit 1', Array(10));
echo "Password is {$password}";
```
更甚至,返回字段数组

```php
$logins = $this->db->rawQueryValue ('select login from users limit 10');
foreach ($logins as $login)
    echo $login;
```

### 返回对象

```php
//默认情况下,返回一个关联数组
$user = $this->db->rawQueryOne ('select * from users where id=?', Array(10));
echo $user['login'];

//我们可以返回一个对象
$user = $this->db->ObjectBuilder()->rawQueryOne ('select * from users where id=?', Array(10));
echo $user->login;
```

### 返回json
更进一步地,我们可以直接返回一个`json`而不是数组,也不是对象. 

```php
//我们可以返回一个对象
$user = $this->db->JsonBuilder()->rawQueryOne ('select * from users where id=?', Array(10));
echo $user;
```
### 参数绑定

```php
$params = Array(1, 'admin');
$users = $this->db->rawQuery("SELECT id, firstName, lastName FROM users WHERE id = ? AND login = ?", $params);
print_r($users); 

// 关联查询
$params = Array(10, 1, 10, 11, 2, 10);
$q = "(
    SELECT a FROM t1
        WHERE a = ? AND B = ?
        ORDER BY a LIMIT ?
) UNION (
    SELECT a FROM t2 
        WHERE a = ? AND B = ?
        ORDER BY a LIMIT ?
)";
$resutls = $this->db->rawQuery ($q, $params);
print_r ($results);
```

## 异常追踪

```php
$this->db->where('login', 'admin')->update('users', ['firstName' => 'Jack']);

if ($this->db->getLastErrno() === 0)
    echo 'Update succesfull';
else
    echo 'Update failed. Error: '. $this->db->getLastError();
```
## 性能统计
可以用来跟踪查询时间和sql语句等.

```php
$this->db->setTrace (true);
// As a second parameter it is possible to define prefix of the path which should be striped from filename
// $this->db->setTrace (true, $_SERVER['SERVER_ROOT']);
$this->db->get("users");
$this->db->get("test");
print_r ($this->db->trace);
```

则它可能输出以下

```array
    [0] => Array
        (
            [0] => SELECT  * FROM t_users ORDER BY `id` ASC
            [1] => 0.0010669231414795
            [2] => MysqliDb->get() >>  file "/avb/work/PHP-MySQLi-Database-Class/tests.php" line #151
        )

    [1] => Array
        (
            [0] => SELECT  * FROM t_test
            [1] => 0.00069189071655273
            [2] => MysqliDb->get() >>  file "/avb/work/PHP-MySQLi-Database-Class/tests.php" line #152
        )

```

## 辅助函数
### 自动重连
```php
if (!$this->db->ping())
    $this->db->connect()
```
### 输出上条语句
```php
$this->db->get('users');
echo "Last executed query was ". $this->db->getLastQuery();
```
### 检查表存在
```php
    if ($this->db->tableExists ('users'))
        echo "hooray";
```
### 转义字符
实际上它是封装的`mysqli_real_escape_string`.

```php
   $escaped = $this->db->escape ("' and 1=1");
```

## 增

### 普通插入
```php
$data = Array ("login" => "admin",
               "firstName" => "John",
               "lastName" => 'Doe'
);
$id = $this->db->insert ('users', $data);
if($id)
    echo 'user was created. Id=' . $id;
```

### 带函数的插入

```php
$data = Array (
    'login' => 'admin',
    'active' => true,
    'firstName' => 'John',
    'lastName' => 'Doe',
    'password' => $this->db->func('SHA1(?)',Array ("secretpassword+salt")),
    // password = SHA1('secretpassword+salt')
    'createdAt' => $this->db->now(),
    // createdAt = NOW()
    'expires' => $this->db->now('+1Y')
    // expires = NOW() + interval 1 year
    // Supported intervals [s]econd, [m]inute, [h]hour, [d]day, [M]onth, [Y]ear
);

$id = $this->db->insert ('users', $data);
if ($id)
    echo 'user was created. Id=' . $id;
else
    echo 'insert failed: ' . $this->db->getLastError();
```

### 重复键插入
经常遇到这样的情景，向一个表里插入一条数据，如果已经存在就更新一下，用程序实现麻烦而且在并发的时候可能会有问题，这时用mysql的DUPLICATE KEY 很方便.  

比如
```mysql
CREATE TABLE `q_user` (
  `id` int(11) UNSIGNED NOT NULL primary key auto_increment,
  `login` varchar(32) NOT NULL UNIQUE KEY,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `createdAt` int(11) UNSIGNED NOT NULL,
  `updatedAt` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
这个数据表中,`id`为主键自增,`login`为`UNIQUE`唯一. 在此时如果想要实现存在修改,不存在则添加时,常规的方法是用
php判断下是否存在,然后进行自己的一些逻辑.  
这里我们直接使用`mysql`的`Duplicate`机制.  

```php
$data = Array ("login" => "admin",
               "firstName" => "John",
               "lastName" => 'Doe',
               "createdAt" => time(),
               "updatedAt" => time(),
);
$updateColumns = Array ("updatedAt");
$lastInsertId = "id";
$this->$this->db->onDuplicate($updateColumns, $lastInsertId);
$id = $this->db->insert ('user', $data);
```
在上面这段代码中,第一次执行会成功的插入,第二次则是仅仅修改了`updatedAt`的值.  
其生成的sql语句为

```mysql
INSERT INTO q_user (`login`, `firstName`, `lastName`, `createdAt`, `updatedAt`) VALUES ('admin', 'John', 'Doe', '1483171123', '1483171123') ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID (id), `updatedAt` = '1483171123'
```

### 批量插入
批量插入不同的字段.  

```php
$data = Array(
    Array ("login" => "admin",
        "firstName" => "John",
        "lastName" => 'Doe'
    ),
    Array ("login" => "other",
        "firstName" => "Another",
        "lastName" => 'User',
        "password" => "very_cool_hash"
    )
);
$ids = $this->db->insertMulti('users', $data);
if(!$ids) {
    echo 'insert failed: ' . $this->db->getLastError();
} else {
    echo 'new users inserted with following id\'s: ' . implode(', ', $ids);
}
```

批量插入相同字段.  

```php
$data = Array(
    Array ("admin", "John", "Doe"),
    Array ("other", "Another", "User")
);
$keys = Array("login", "firstName", "lastName");

$ids = $this->db->insertMulti('users', $data, $keys);
if(!$ids) {
    echo 'insert failed: ' . $this->db->getLastError();
} else {
    echo 'new users inserted with following id\'s: ' . implode(', ', $ids);
}
```

## 替换
[Replace()](https://dev.mysql.com/doc/refman/5.7/en/replace.html)  
这个函数和`insert`的使用方法完全一致.  
**但是**,这里要对仅仅从里面上意思理解的小伙伴提个醒,`mysql`的所谓替换,可能和你理解的有些不一样.  
`mysql`在遇到数据冲突时,删掉了旧记录，再写入新记录，这是使用 REPLACE INTO 时最大的一个误区.  
问题在此时出现了,写入新记录时不会将你期望的没有修改的写进去,而是不去管他.而这个字段就相当于`丢失`了.  
这可能并非你的逻辑上需要的.更常见的需求是当存在`login`时,则更新某些个字段,而其他的保持不变.  
而满足这以需求的则是上面着重的介绍的`ON DUPLICATE KEY UPDATE`.  
也就是说,**如果是想存在时更新,不存在插入,请使用`onDuplicate`方法!**

## 导入
很多时候我们需要把数据从`csv`甚至`xml`中导入到数据库里.  
针对于此,`puck`也提供了简单的方式

### 导入csv

```php
$path_to_file = "/path/to/file.csv";
$this->db->loadData("users", $path_to_file);
```
当然,这里还有一些选项,如果这个`csv`并不十分规范:

```php
Array(
    "fieldChar" => ';',     // 分割符
    "lineChar" => '\r\n',   // 换行符
    "linesToIgnore" => 1    // 忽略开头的几行
);
```
然后就可以

```php
$options = Array("fieldChar" => ';', "lineChar" => '\r\n', "linesToIgnore" => 1);
$this->db->loadData("users", "/path/to/file.csv", $options);
```

### 导入xml
和上面一样,没有什么大的不同,除了一些细节

```php
$path_to_file = "/path/to/file.xml";
$this->db->loadXML("users", $path_to_file);
```
同样地,这里也有一些选项

```php
Array(
    "linesToIgnore" => 0,    //忽略开头的几行
    "rowTag"    => "<user>" // 入口标记
)
```
然后,不出意外地

```php
$options = Array("linesToIgnore" => 0, "rowTag" => "<user>"):
$path_to_file = "/path/to/file.xml";
$this->db->loadXML("users", $path_to_file, $options);

```

## 更新

```php
$data = Array (
    'firstName' => 'Bobby',
    'lastName' => 'Tables',
    'editCount' => $this->db->inc(2),
    // editCount = editCount + 2;
    'active' => $this->db->not()
    // active = !active;
);
$this->db->where ('id', 1);
if ($this->db->update ('users', $data))
    echo $this->db->count . ' records were updated';
else
    echo 'update failed: ' . $this->db->getLastError();
```
更新用起来非常的方便,也非常的简单.

## 查找

### 普通查找
```php
//查找所有
$users = $this->db->get('users');
//查找10个
$users = $this->get('users', 10);
```
### 查找指定列

```php
$cols = Array ("id", "name", "email");
$users = $this->db->get ("users", null, $cols);
if ($this->db->count > 0)
    foreach ($users as $user) { 
        print_r ($user);
    }
```
### 查找一行

```php
$this->db->where ("id", 1);
$user = $this->db->getOne ("users");
echo $user['id'];

$stats = $this->db->getOne ("users", "sum(id), count(*) as cnt");
echo "total ".$stats['cnt']. "users found";
```

### 直接获取值

```php
$count = $this->db->getValue ("users", "count(*)");
echo "{$count} users found";
```

### 获取多行到数组
```php
$logins = $this->db->getValue ("users", "login", null);
// select login from users
$logins = $this->db->getValue ("users", "login", 5);
// select login from users limit 5
foreach ($logins as $login)
    echo $login;
```

### 设置返回类型
`puck`支持3中返回类型,分别是`数组`,`对象`,`json`,默认情况下,返回的结构是`数组`,但我们也可以返回其他的数据类型.  

```php
// Array return type
$= $this->db->getOne("users");
echo $u['login'];
// Object return type
$u = $this->db->ObjectBuilder()->getOne("users");
echo $u->login;
// Json return type
$json = $this->db->JsonBuilder()->getOne("users");
```

### 分页查询
如果使用`paginate`而不是`get`进行的查询,则会自动进行分页.  

```php
$page = 1;
// set page limit to 2 results per page. 20 by default
$this->db->pageLimit = 2;
$products = $this->db->arraybuilder()->paginate("products", $page);
echo "showing $page out of " . $this->db->totalPages;
```

其实还有另外一种分页的方式,就是利用mysql的`SQL_CALC_FOUND_ROWS`.它可以在limit的同时,顺便算以下不带
limit的条数.这就意味着通常需要两条sql的分页,可以一条来完成.
但是这样做,将不会对查询结果缓存.  
它适用与传统的`select count(*)`比较耗时的时候,另外计算数量肯定不划算.这时就可以用它来实现一次查询,获得想要的结果.  
但也不要滥用它,比如以某主键或索引为条件的时候,这个时候如果直接`count(*)`是不需要扫描全表的,而用`SQL_CALC_FOUND_ROWS`
则会强制扫描全表,反而导致性能的下降.  

```php
$offset = 10;
$count = 15;
$users = $this->db->withTotalCount()->get('users', Array ($offset, $count));
echo "Showing {$count} from {$this->db->totalCount}";
```


### 结果转换
很多时候,返回的关联数组并不是我们想要的.  
此时我们可以通过`map`方法将其转为自定义的`key=>value`格式.

```php
$user = $this->db->map ('login')->ObjectBuilder()->getOne ('users', 'login, id');
Array
(
    [user1] => 1
)

$user = $this->db->map ('login')->ObjectBuilder()->getOne ('users', 'id,login,createdAt');
Array
(
    [user1] => stdClass Object
        (
            [id] => 1
            [login] => user1
            [createdAt] => 2015-10-22 22:27:53
        )

)
```

## 条件

用于条件查询的几个方法有`where()`,`orwhere()`,`having()`,`orHaving()` 分别用于各种场景..  

where的两个参数分别列名和值,多个`where`的关系为`and`.

### 算术运算
```php
$this->db->where ('id', 1);
$this->db->where ('login', 'admin');
$results = $this->db->get ('users');
// 解析为: SELECT * FROM users WHERE id=1 AND login='admin';
```

```php
$this->db->where ('id', 1);
$this->db->having ('login', 'admin');
$results = $this->db->get ('users');
// 解析为:SELECT * FROM users WHERE id=1 HAVING login='admin';
```
如果想要两个列相等,

```php
$this->db->where ('lastLogin', 'createdAt');
```
这个是错误的做法,因为会被解析为

```sql
where (lastLogin='createdAt')
```
这明显不是我们所期望的结果.正确做法应该是

```php
$this->db->where ('lastLogin = createdAt');
$results = $this->db->get ('users');
// 解析为:SELECT * FROM users WHERE lastLogin = createdAt;
```
如果想使用其他的运算符,比如`>`,`<`而非`==`,应该通过第三个参数传入

```php
$this->db->where ('id', 50, ">=");
// or $this->db->where ('id', Array ('>=' => 50));
$results = $this->db->get ('users');
// 解析为: SELECT * FROM users WHERE id >= 50;
```

### BETWEEN / NOT BETWEEN

```php
$this->db->where('id', Array (4, 20), 'BETWEEN');
// or $this->db->where ('id', Array ('BETWEEN' => Array(4, 20)));

$results = $this->db->get('users');
// 解析为: SELECT * FROM users WHERE id BETWEEN 4 AND 20
```

### IN / NOT IN

```php
$this->db->where('id', Array(1, 5, 27, -1, 'd'), 'IN');
// or $this->db->where('id', Array( 'IN' => Array(1, 5, 27, -1, 'd') ) );

$results = $this->db->get('users');
// 解析为: SELECT * FROM users WHERE id IN (1, 5, 27, -1, 'd');
```

### 或

```php
$this->db->where ('firstName', 'John');
$this->db->orWhere ('firstName', 'Peter');
$results = $this->db->get ('users');
// 解析为: SELECT * FROM users WHERE firstName='John' OR firstName='peter'
```

### 是否为空

```php
$this->db->where ("lastName", NULL, 'IS NOT');
$results = $this->db->get("users");
// 解析为: SELECT * FROM users where lastName IS NOT NULL
```

### sql表达式
如果你是个急性子,并且对sql语法足够了解,你可以....

```php
$this->db->where ("id != companyId");
$this->db->where ("DATE(createdAt) = DATE(lastLogin)");
$results = $this->db->get("users");
```

### 变量绑定
如果你感觉上面那种方式太麻烦了,你还可以...   

```php
$this->db->where ("(id = ? or id = ?)", Array(6,2));
$this->db->where ("login","mike")
$res = $this->db->get ("users");
// 解析为: SELECT * FROM users WHERE (id = 6 or id = 2) and login='mike';
```

## 删除
删除的方法非常简单

```php
$this->db->where('id', 1);
if($this->db->delete('users')) 
  echo 'successfully deleted';
```
## 排序
其实就是`sql`标准语句的`order by`,并且支持多个排序条件,以调用先后顺序.

### 常规排序
常规排序就是我们常用的按照大小,asc码等.

```php
$this->db->orderBy("id","asc");
$this->db->orderBy("login","Desc");
$this->db->orderBy("RAND ()");
$results = $this->db->get('users');
// 解析为: SELECT * FROM users ORDER BY id ASC,login DESC, RAND ();
```
### 字段排序
有事我们可能需要按照某个字段的某些值排序.我们可以  

```php
$this->db->orderBy('userGroup', 'ASC', array('superuser', 'admin', 'users'));
$this->db->get('users');
// 解析为: SELECT * FROM users ORDER BY FIELD (userGroup, 'superuser', 'admin', 'users') ASC;
```

## 分组查询
分组就是标准`sql`语法的`group by`语句. 

```php
$this->db->groupBy ("name");
$results = $this->db->get ('users');
// 解析为: SELECT * FROM users GROUP BY name;
```
## 联表查询
即标准`sql`语法中的`join`.
一般的,

```php
$this->db->join("users u", "p.tenantID=u.tenantID", "LEFT");
$this->db->where("u.id", 6);
$products = $this->db->get ("products p", null, "u.login, p.productName");
print_r ($products);
```

多条件的,

```php
$this->db->join("users u", "p.tenantID=u.tenantID", "LEFT");
$this->db->joinWhere("users u", "u.tenantID", 5);
$products = $this->db->get ("products p", null, "u.login, p.productName");
print_r ($products);
// 解析为: SELECT  u.login, p.productName FROM products p LEFT JOIN users u ON (p.tenantID=u.tenantID AND u.tenantID = 5)
```

```php
$this->db->join("users u", "p.tenantID=u.tenantID", "LEFT");
$this->db->joinOrWhere("users u", "u.tenantID", 5);
$products = $this->db->get ("products p", null, "u.name, p.productName");
print_r ($products);
// 解析为: SELECT  u.login, p.productName FROM products p LEFT JOIN users u ON (p.tenantID=u.tenantID OR u.tenantID = 5)
```

## 子查询
子查询也是在开发过程中比较常用的功能. 

### 筛选

```php
$ids = $this->db->subQuery ();
$ids->where ("qty", 2, ">");
$ids->get ("products", null, "userId");

$this->db->where ("id", $ids, 'in');
$res = $this->db->get ("users");
// 解析为: SELECT * FROM users WHERE id IN (SELECT userId FROM products WHERE qty > 2)
```

### 插入

```php
$userIdQ = $this->db->subQuery ();
$userIdQ->where ("id", 6);
$userIdQ->getOne ("users", "name"),

$data = Array (
    "productName" => "test product",
    "userId" => $userIdQ,
    "lastUpdated" => $this->db->now()
);
$id = $this->db->insert ("products", $data);
// 解析为: INSERT INTO PRODUCTS (productName, userId, lastUpdated) values ("test product", (SELECT name FROM users WHERE id = 6), NOW());
```

### 联表

```php
$usersQ = $this->db->subQuery ("u");
$usersQ->where ("active", 1);
$usersQ->get ("users");

$this->db->join($usersQ, "p.userId=u.id", "LEFT");
$products = $this->db->get ("products p", null, "u.login, p.productName");
print_r ($products);
// SELECT u.login, p.productName FROM products p LEFT JOIN (SELECT * FROM t_users WHERE active = 1) u on p.userId=u.id;
```

### 结果判断

```php
$sub = $this->db->subQuery();
    $sub->where("company", 'testCompany');
    $sub->get ("users", null, 'userId');
$this->db->where (null, $sub, 'exists');
$products = $this->db->get ("products");
// SELECT * FROM products WHERE EXISTS (select userId from users where company='testCompany')
```
## 存在检测

很多时候,我需要判断数据库里面符合某(些)个条件的记录是否存在,一般而言可能需要查询后,再自行判断.  
本框架也提供了此类方法.  


```php
$this->db->where("user", $user);
$this->db->where("password", md5($password));
if($this->db->has("users")) {
    return "You are logged";
} else {
    return "Wrong user/password";
}
```

## 事务支持
这个显然是`innodb`所支持的特性了.  
是一个复杂数据的必备了.  

```php
$this->db->startTransaction();
...
if (!$this->db->insert ('myTable', $insertData)) {
    //Error while saving, cancel new record
    $this->db->rollback();
} else {
    //OK
    $this->db->commit();
}
```

## 锁表支持
框架支持`读`|`写`锁,可以自由地,灵活地,对表进行读写锁定和解锁.  
但只有一件事你可能需要注意的是当你锁定了之后,一定要记得解锁....


```php
//锁定users的写入操作
$this->db->setLockMethod("WRITE")->lock("users");
//解锁
$this->db->unlock();
//批量锁定
//同时锁定users log两个表的read操作
$this->db->setLockMethod("READ")->lock(array("users", "log"));
```


## 查询关键字

mysql支持非常多的查询关键字,比如 `LOW PRIORITY` | `DELAYED` | `HIGH PRIORITY` | `IGNORE`

```php
$this->db->setQueryOption ('LOW_PRIORITY')->insert ($table, $param);
//INSERT LOW_PRIORITY INTO table ...
```

```php
$this->db->setQueryOption ('FOR UPDATE')->get ('users');
//SELECT * FROM USERS FOR UPDATE;
```

当然,批量设置也是可以的.  

```php
$this->db->setQueryOption (Array('LOW_PRIORITY', 'IGNORE'))->insert ($table,$param);
// INSERT LOW_PRIORITY IGNORE INTO table ...
```

```php
$this->db->setQueryOption ('SQL_NO_CACHE');
$this->db->get("users");
// SELECT SQL_NO_CACHE * FROM USERS;
```

唯一可能需要注意的是,你可能需要了解一下这些关键字的作用.  


## 连续操作

由于这些方法都返回了`$this`,所以我们可以将上述分布操作了进行连续

```php
$results = $this->db
    ->where('id', 1)
    ->where('login', 'admin')
    ->get('users');

```