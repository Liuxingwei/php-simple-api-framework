
# SAF 框架使用说明

本文档为`Markdown`格式，如果使用`VSCode`，可以安装`Markdown Preview Enhanced`预览。

该插件使用方法见插件的自说明文档。

## 零、概述

`SAF（Simple Api Framework）`是一个极简单的`PHP` `API`开发框架，适用于前后端分离架构的`web`项目，作为后端`PHP` `API`服务的框架。

说其极简，一方面是因为它只有`5`个核心类和几个支持文件，另一方面是因为它只支持有限的场景，当然，也是说它非常易于使用。

`SAF`没有`Beautiful URL`路由，`GET`请求的参数是通过形如`name=zhangsan&sex=male`的`QueryString`参数传递的。

`SAF`遵循了惯例优于配置的理念，`API`必须放在指定的目录（项目目录的`Application\Api`）下，且`GET`请求对应的`API`类要放在`Get`子目录，而`POST`请求对应的`API`类要放在`Post`子目录，其他类型的`HTTP`请求对应的`API`，也放在与其`HTTP METHOD`相对应的子目录。

数据库方面，`SAF`有一个简单的`DB`类，它是以`PDO`为底层的，理论上它可以支持多种数据库服务，但是目前只在`MySQL`上做过测试。因此最适合的数据库搭配就是`MySQL5.7+`。

对于`PHP`，由于`7.2.*`和其前的版本，在`trait`特性支持上有缺陷（引用继承了同一`trait`的多个`trait`时，会导致重复定义方法的致命错误），因此建议`PHP 7.3+`。

## 一、环境要求

支持`PHP 5.6`，建议`PHP 7.3+`，`MySQL 5.7+`。

## 二、下载

### 1. composer

```Shell
composer create-project liuxingwei/simple-api-framework
```

### 2. github

```Shell
git clone https://github.com/Liuxingwei/php-simple-api-framework.git
```

## 三、环境搭建

有如下几种方式，任选其一（前两种方式仅适用于开发、测试环境）：

### 1、PHP Built-in Server

使用`PHP`内建服务时，无需`nginx`、`apache`，只需`PHP`。

在命令行下，切换至`public`文件夹（`safpath`指`SAF`的路径），执行`php -S localhost:xxxx index.php`即可，其中`xxxx`为端口号。

示例：

```Shell
>cd safpath/public
>php -S localhost:xxxx index.php
```

浏览器打开`localhost:xxxx`，看到如下内容，服务启动成功：

```Shell
Please access detail API.
```

### 2、借助 VSCode 中 PHP Server 插件

安装`PHP Server`插件，打开`File > Preferences > Settings`，找到`Extensions > PHP Server Configuration`，将`Relative Path`改为`./public`。

如果`PHP`可执行文件没加到系统路径中，可以将其填写在`PHP Server`插件的`PHP Path`配置项中。

打开`public/index.php`文件，在文件窗口右键，选`PHP Server： Server Project`。

浏览器打开`localhost:xxxx`，看到如下内容，服务启动成功：

```Shell
Please access detail API.
```

`PHP Server: Stop`可以停止服务。

`PHP Server: Reload Server`可以重启服务。

### 3、使用 WAMP

启动`WAMP`，点进托盘区`WAMP`图标，选择`Apache > httpd-vhosts.conf`文件。

在打开的文件中，复制`VirtualHost`段，修改端口，并将路径修改为项目目录的`public`文件夹。

示例（`safpath`即指`SAF`的路径）：

要复制的段：

```Shell
<VirtualHost *:80>
  ServerName localhost
  ServerAlias localhost
  DocumentRoot "${INSTALL_DIR}/www"
  <Directory "${INSTALL_DIR}/www/">
    Options +Indexes +Includes +FollowSymLinks +MultiViews
    AllowOverride All
    Require local
  </Directory>
</VirtualHost>
```

复制后修改`IP`和`DocumentRoot`、`Directory`路径：

```Shell
<VirtualHost *:xxxx>
  ServerName localhost
  ServerAlias localhost
  DocumentRoot "safpath/public"
  <Directory "safpath/public">
    Options +Indexes +Includes +FollowSymLinks +MultiViews
    AllowOverride All
    Require local
  </Directory>
</VirtualHost>
```

修改后的完整文件：

```Shell
<VirtualHost *:80>
  ServerName localhost
  ServerAlias localhost
  DocumentRoot "${INSTALL_DIR}/www"
  <Directory "${INSTALL_DIR}/www/">
    Options +Indexes +Includes +FollowSymLinks +MultiViews
    AllowOverride All
    Require local
  </Directory>
</VirtualHost>


<VirtualHost *:xxxx>
  ServerName localhost
  ServerAlias localhost
  DocumentRoot "safpath/public"
  <Directory "safpath/public">
    Options +Indexes +Includes +FollowSymLinks +MultiViews
    AllowOverride All
    Require local
  </Directory>
</VirtualHost>
```

重启`Apache`。

浏览器打开`localhost:xxxx`，看到如下内容，服务启动成功：

```Shell
Please access detail API.
```

### 4. nginx

添加一个`server`配置：

```SHELL
server {
        listen xxxx default_server;
        listen [::]:xxxx default_server;

        server_name _;

        root safpath/public;

        location / {
                try_files $uri $uri/ =404;
                if (!-e $request_filename) {
                    rewrite  ^(.*)$  /index.php$1  last;
                }
        }

        location ~ \.php(.*)$ {
                include snippets/fastcgi-php.conf;
                fastcgi_split_path_info ^(.+\.php)(/.*)$;
                # With php-fpm (or other unix sockets):
                fastcgi_pass unix:/var/run/php/php7.3-fpm.sock;
                # With php-cgi (or other tcp sockets):
                # fastcgi_pass 127.0.0.1:9000;
        }

        location ~ /\.ht {
                deny all;
        }
}
```

重启`nginx`。

## 四、目录结构

初始的项目目录结构如下：

```Shell
+ application
  + Api
    + Delete
      + Example
        - Index.php
    + Get
      + Example
        - Index.php
    + Patch
      + Example
        - Index.php
    + Post
      + Example
        - Index.php
    + Put
      + Example
        - Index.php
+ conf
  - config.php.sample
+ lib
  + Core
    - BaseApiInterface.php
    - bootstrap.php
    - DB.php
    - ErrorCode.php
    - Request.php
    - Response.php
    - SafException.php
+ doc
  - DB-Class-Usage.md
+ public
  - .htaccess
  - index.html
  - index.php
+ vendor
  + composer
    - autoload_classmap.php
    - autoload_namespaces.php
    - autoload_psr4.php
    - autoload_real.php
    - autoload_static.php
    - ClassLoader.php
    - installed.json
    - LICENSE
  - autoload.php
- .gitignore
- composer.json
- composer.lock
- README.md
```

## 五、创建`API`

在`application/Api/Get`或`application/Api/Post`中根据业务需要创建一个子文件夹（也可以是多级文件夹），在其中创建一个`API`类。

该类实现`Lib\Core\BaseApiInterface`接口，并实现`run()`方法，该方法签名为：`run(array $param):mixed`。

例如，在`Get`文件夹创建`Example`文件夹，并在其中创建`Index.php`，文件内容如下：

```PHP
<?php
namespace Application\Api\Get\Example;

use Lib\Core\BaseApiInterface;

class Index implements BaseApiInterface
{
  public function run(array $params)
  {
    $result = [
      'code' => 200,
      'message' => 'OK',
      'description' => "I'm a GET request.",
    ];
    return $resut;
  }
}
```

`run()`方法的参数即为`HTTP`请求的参数集合。

此时，向服务器的`/example/index`发出`GET`请求，即可收到值为

```Javascript
{
  "code": 200,
  "message": "OK",
  "description": "I'm a GET request."
}
```

的`json`返回。

此时，向`/example/index`发出`POST`请求，收到的则是

```JavaScript
{
  "code": 404,
  "message": "API /example/index 不存在"
}
```

要创建一个接受`/example/index`的`POST`请求的`API`，需要在`application/Api/Post`中创建`Example`文件夹，并在`Example`中创建`Index.php`文件：

```PHP
<?php
namespace Application\Api\Post\Example;

use Lib\Core\BaseApiInterface;

class Index implements BaseApiInterface
{
  public function run(array $params)
  {
    $result = [
      'code' => 200,
      'message' => 'OK',
      'description' => "I'm a POST request.",
    ];
    return $result;
  }
}
```

### 命名空间与`uri`的关系

`API`类遵循`psr4`标准，其命名空间`Application\Api`映射于项目根目录中的`application/Api`目录。

`API`类名与`API`的`uri`之间的关系是，将类的命名空间中的`Application\Api\xxx`部分去除，并将`\`转换为`/`，就是该`API`的`uri`，而其中的`xxx`即对应了`HTTP METHOD`。

例如类`Application\Api\Get\Example\Index`对应的`API`的`uri`即为`/example/index`，相应的`HTTP METHOD`为`GET`。

而类`Application\Api\Post\Example\Index`对应的`API`的`uri`也为`/example/index`，但相应的`HTTP METHOD`为`POST`。

`PUT`、`PATCH`、`DELETE`等`HTTP METHOD`类推。

由于`HTTP`定义的`url`对于大小写不敏感，在转换为类名时，会自动将每部分的首字母转换为大写，并将`短横线`及其后的一个字母转换为大写字母，以对应命名空间中的大写字母。

```text
GET /example/index          =>    Application\Api\Get\Example\Index
POST /user-info/create-user    =>    Application\Api\Post\UserInfo\CreateUser
PUT /user-info/modify-user   =>    Application\Api\Put\UserInfo\ModifyUser
```

### `run()`方法的参数

`run()`方法的`params`参数接收了与`HTTP METHOD`相对应的`request`数据。

对于`POST`、`PUT`、`PATCH`，如果提交的`HEADER`中`Content-Type`为`application/json`的情况，接收了`json`解码后的`Request Payload`数据。

对于`PUT`、`PATCH`的`application/x-www-form-urlencoded`，接收了（解析后的）`Form Data`数据，类似于`$_POST`的值。

对于`POST`的`form-data`、`multipart/form-data`、`application/x-www-form-urlencoded`，则接收了（解析后的）`Form Data`数据。相当于`$_POST`的值。

对于`GET`和`DELETE`，则接收了解析后的`Query String`键值对。相当于`$_GET`的值。

其它情况，则直接在`params`的`BODY`元素中存储了提交的`Request Payload`的原始值。

### `ErrorCode`类

可以将返回的基本结构以类常量的形式定义在`ErrorCode`类中。

`ErrorCode`类里已有了几个预定义的类常量，比如：

```PHP
const OK = ['code' => 200, 'message' => 'OK'];
const API_NOT_EXISTS = ['code' => 404, 'message' => 'API {{:api}} 不存在'];
const HTTP_METHOD_ERROR = ['code' => 500, 'message' => '仅支持 POST 和 GET 提交'];
```

可以这样改写`Example\Index`：

```PHP
<?php
namespace Application\Api\Get\Example;

use Lib\Core\BaseApiInterface;
use Lib\Core\ErrorCode;

class Index implements BaseApiInterface
{
  public function run(array $params)
  {
    return ErrorCode::OK;
  }
}
```

`API_NOT_EXISTS`常量使用了占位符，占位符被包含在`{{:`和`}}`之间。可以使用数组指定要替换的与数组键匹配的值。

例如，定义如下常量：

```PHP
const PARAM_NOT_EXISTS = ['code' => 403, 'message' => '参数 {{:param}} 的长度必须在 {{:min}} 到 {{:max}} 之间'];
```

然后在`Api`的`run()`方法中这样使用：

```PHP
$err = ErrorCode::mapError(ErrorCode::PARAM_NOT_EXISTS, ['param' => 'username', 'min' => 3, 'max' => 16]);
// 最终的输出结果为：
// {
//   "code": 403,
//   "message": "参数 username 的长度必须在 3 到 16 之间"
// }
```

### 结束执行

框架最后调用`API`代码就是`run()`方法，因此该方法执行的最后一行代码就标志了`API`的执行结束。

下面的代码示例了在不同条件下的不同输出并结束`API`的执行：

```PHP
public function run(array $params)
{
  if ($signinSuccess) {
    return [
      'code' => 200,
      'message' => '登录成功',
    ];
  }
  return [
    'code' => 200,
    'message' => '登录失败',
  ];
}
```

默认的输出中，`HTTP CODE`均为`200`。

如果认为登录失败是一种错误，可以定义`errorCode`的属性：

```PHP
public $errorCode;
public function run(array $params)
{
  if ($signinSuccess) {
    return [
      'code' => 200,
      'message' => '登录成功',
    ];
  }
  $this->errorCode = '403';
  return [
    'code' => 403,
    'message' => '登录失败',
  ];
}
```

如果定义的`code`和`HTTP`错误码相同，还可以使用`SafException`类的`throw`静态方法抛异常：

```PHP
public function run(array $params)
{
  if (!$signinSuccess) {
    SafException::throw([
      'code' => 403,
      'message' => '登录失败',
    ])
  }
  $this->responseJson([
    'code' => 200,
    'message' => '登录成功',
  ]);
}
```

这两段代码的输出是一样的。

## 配置文件

通用的配置文件放置在`conf`目录中，文件名为`config.php`，框架提供了一个示例文件`config.php.sample`。

该文件内容示例如下：

```PHP
<?php
return [
    'runtime' => 'development', // 运行环境，development 为开发环境，test 为测试环境，product 为生产环境
    'db' => [ // 数据库配置
        'dbms' => 'mysql',
        'host' => 'localhost',
        'port' => '3306',
        'user' => 'root',
        'password' => '123456',
        'dbname' => 'web2data',
    ],
    'debug' => true, // 是否开启 debug，开启 debug 后，可以在提交中带有 debug 参数，返回的数据中将有 debug 项
];
```

其中的配置数组将被读入超全局变量`$_ENV`的`config`元素中。

上面示例中的变量可以这样读取：

```PHP
$_ENV['config']['runtime']; // 'development'
$_ENV['config']['db']['host']; // 'localhost'
```

为方便使用，该配置也被定义在`CONFIG`常量中，相同的配置还可以这样读取：

```PHP
CONFIG['runtime'];
CONFIG['db']['host'];
```

## `DB`和`Model`

框架实现了一个基于`PDO`的`DB`类，具体使用请参考`doc`目录的`DB-Class-Usage.md`文件。

也可以自定义`Model`类，直接继承`DB`类，建议将`Model`类定义在`application\Model`命名空间中，可以不用修改`composer.json`的自动加载定义。

```PHP
// application/Model/User.php
namespace Application\Model;

use Lib\Core\DB;

class User
{
  public function checkUser($userName, $password)
  {
    return $this->where('user_name = :user_name AND password = :password', [':user_name' => $userName, ':password' => $password])->selectOne();
  }
}
```

上例中，是假定表名与类名相同，都是`user`（在MySQL中不区分大小写）。

如果实际的表名与类名不同，则需要使用`$table`属性自定义表名：

```PHP
// application/Model/UserInfo.php
namespace Application\Model;

use Lib\Core\DB;

class UserInfo
{
  protected $table = 'user_info';
  public function checkUser($userName, $password)
  {
    return $this->where('user_name = :user_name AND password = :password', [':user_name' => $userName, ':password' => $password])->selectOne();
  }
}
```

框架会将类名定义中的大写字母转换「下划线加小写」字母的形式。

注意不要在`MySQL`中用驼峰法命名表名，而要用下划线命名法。

上例也可以省略表名的显式定义：

```PHP
// application/Model/UserInfo.php
namespace Application\Model;

use Lib\Core\DB;

class UserInfo
{
  public function checkUser($userName, $password)
  {
    return $this->where('user_name = :user_name AND password = :password', [':user_name' => $userName, ':password' => $password])->selectOne();
  }
}
```

在创建`DB`类实例或继承了`DB`类实例的`Model`类实例时，可以省略构造函数需要的数据库配置参数，这时`DB`类的构造函数将尝试获取`DB_CONFIG`常量作为其默认数据库配置。

框架已经将配置文件中的`db`元素定义在了`DB_CONFIG`常量中，只需要将`config.php.sample`复制为`config.php`，并按照用户自己的实际服务器情况进行配置即可。
