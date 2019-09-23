
# SAF 框架使用说明

本文档为`Markdown`格式，如果使用`VSCode`，可以安装`Markdown Preview Enhanced`预览。

该插件使用方法见插件的自说明文档。

## 零、概述

`SAF（Simple Api Framework）`是一个极简单的`PHP` `API`开发框架，适用于前后端分离架构的`web`项目，作为后端`PHP` `API`服务的框架。

说其极简，一方面是因为它只有`5`个核心类和几个支持文件，另一方面是因为它只支持有限的场景，当然，也是说它非常易于使用。

譬如，由于`PHP`本身的限制，对于`PUT`、`DELETE`、`PATCH`等`HTTP METHOD`支持不够好，很多框架使用了模拟实现，`SAF`没有这样做，而是只支持`GET`、`POST`。

再比如，对于`RestFul`风格，同一`URI`的不同`HTTP METHOD`代表了不同的操作，`SAF`也不支持这种方式，在`SAF`中一个`URI`仅能支持一种`HTTP METHOD`。

`SAF`也没有`Beautiful URL`路由，`GET`请求的参数是通过形如`name=zhangsan&sex=male`的`QueryString`参数传递的。

`SAF`遵循了惯例优于配置的理念，`API`必须放在指定的目录（项目目录的`Application\Api`）下。

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

## 四、目录结构

初始的项目目录结构如下：

```Shell
+ application
  + Api
    + Example
      - Index.php
+ conf
  - config.php.sample
+ lib
  + Core
    - AbstractBaseApi.php
    - bootstrap.php
    - DB.php
    - ErrorCode.php
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

在`Application\Api`中根据业务需要创建一个子文件夹（也可以是多级文件夹），在其中创建一个`API`类。

该类继承`Lib\Core\AbstractBaseApi`类，并实现`run()`方法。

还要为该指定`HTTP METHOD`，方法是为其定义一个名为`$httpMethod`的变量，并将其赋值为`GET`或`POST`字符串。

例如，创建`Example`文件夹，并在其中创建`Index.php`，文件内容如下：

```PHP
<?php
namespace Application\Api\Example;

use Lib\Core\AbstractBaseApi;

class Index extends AbstractBaeApi
{
  protected $httpMethod = 'GET';

  public function run()
  {
    $result = [
      'code' => 200,
      'message' => 'OK',
    ];
    $this->responseJson($result);
  }
}
```

此时，向服务器的`/example/index`发出`GET`请求，即可收到值为

```Javascript
{
  "code": 200,
  "message": "OK"
}
```

的`json`返回。

### 命名空间与`uri`的关系

`API`类遵循`psr4`标准，其命名空间`Application\Api`映射于项目根目录中的`application/Api`目录。

`API`类名与`API`的`uri`之间的关系是，将类的命名空间中的`Application\Api`部分去除，并将`\`转换为`/`，就是该`API`的`uri`。

例如类`Application\Api\Example\Index`对应的`API`的`uri`即为`/example/index`。

由于`HTTP`定义的`url`对于大小写不敏感，在转换为类名时，会自动将每部分的首字母转换为大写，并将下划线及其后的一个字母转换为大写字母，以对应命名空间和中的大写字母。

```text
/example/index        =>    Application\Api\Example\Index
/user_info/get_list   =>    Application\Api\UserInfo\GetList
```

### `responseJson()`方法

`AbstractBaseApi`类的`responseJson()`方法用于以`json`格式输出数据。

它接受两个参数，第一个参数是要输出的数据，建议以

```PHP
[
  'code' => xxxx,
  'message' => 'xxxxxxxxxxx'
  'data' => [
    ...
  ]
]
```

的格式定义输出数据。

第二个参数是`HTTP`状态码，可以是`404`、`403`、`500`、`200`等值。此参数可以省略，默认值为`200`。

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
namespace Application\Api\Example;

use Lib\Core\AbstractBaseApi;
use Lib\Core\ErrorCode;

class Index extends AbstractBaeApi
{
  protected $httpMethod = 'GET';

  public function run()
  {
    $this->responseJson(ErrorCode::OK);
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
public function run()
{
  if ($signinSuccess) {
    $this->responseJson([
      'code' => 200,
      'message' => '登录成功',
    ]);
    return true;
  }
  $this->responseJson([
    'code' => 200,
    'message' => '登录失败',
  ]);
}
```

如果认为登录失败是一种错误，也可以在输出时指定`HTTP`错误码：

```PHP
public function run()
{
  if ($signinSuccess) {
    $this->responseJson([
      'code' => 200,
      'message' => '登录成功',
    ]);
    return true;
  }
  $this->responseJson([
    'code' => 403,
    'message' => '登录失败',
  ], 403);
}
```

如果定义的`code`和`HTTP`错误码相同，还可以使用`SafException`类的`throw`静态方法抛异常：

```PHP
public function run()
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

### `AbstractBaseApi`类的继承和初始化

有时候，可能某些模块的`API`会有共同的特性，这时，可以定义一个继承`AbstractBaseApi`的类，在其中定义共有特性，供这些`API`类继承。

这些父类可以定义在`application`的其他目录，也可以放在`application\Api`目录的子目录中。不过放在`Api`目录（或其子目录）时，最好将其定义为`抽象类`，以免被当做普通`API`调用。

有些模块会有共通的初始化行为，可以将其定义在`init()`方法中，这个方法是定义在`AbstractBaseApi`类中的，它会在`API`类实例化时被自动调用。在定义`init()`方法时，应该在其第一行调用父类的`init()`，以实现父类中定义的初始化行为（除非你有意要跳过父类的初始化）。

```PHP
namespace Application\Api\Example;
abstract public class AbstractExampleBaseApi
{
  protected function init()
  {
    parent::init();
    ......
  }
}
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

框架也会用类似于`uri`转换`API`类名的方式，将类名定义中的大写字母转换「下划线加小写」字母的形式。

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