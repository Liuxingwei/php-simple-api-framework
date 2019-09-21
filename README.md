
# SAF 框架使用说明

本文档为`Markdown`格式，如果使用`VSCode`，可以安装`Markdown Preview Enhanced`预览。

该插件使用方法见插件的自说明文档。

## 零、概述

`SAF（Simple Api Framework）`是一个极简单的`PHP` `API`开发框架，适用于前后端分离架构的`web`项目，作为后端`PHP` `API`服务的框架。

说其极简，一方面是因为它只有`5`个核心类和几个支持文件，另一方面是因为它只支持有限的场景，当然，也是说它非常易于使用。

譬如，由于`PHP`本身的限制，对于`PUT`、`DELETE`、`PATCH`等`HTTP METHOD`支持不够好，很多框架使用了模拟实现，`SAF`没有这样做，而是只支持`GET`、`POST`。

再比如，对于`RestFul`风格，同一`URI`的不同`HTTP METHOD`代表了不同的操作，`SAF`也不支持这种方式，在`SAF`中一个`URI`仅能支持一种`HTTP METHOD`。

`SAF`也没有`Beautiful URL`路由，`GET`请求的参数是通过形如`name=zhangsan&sex=male`的`QueryString`参数传递的。

数据库方面，`SAF`有一个简单的`DB`类，它是以`PDO`为底层的，理论上它可以支持多种数据库服务，但是目前只在`MySQL`上做过测试。因此最适合的数据库搭配就是`MySQL5.7+`。

对于`PHP`，由于`7.2.*`和其前的版本，在`trait`特性支持上有缺陷（引用继承了同一`trait`的多个`trait`时，会导致重复定义方法的致命错误），因此建议`PHP 7.3+`。

## 一、环境要求

支持`PHP 5.6`，建议`PHP 7.3+`，`MySQL 5.7+`。

## 二、下载

### 1. composer

### 2. github

## 三、环境搭建

有如下几种方式，任选其一（前两种方式仅适用于开发、测试环境）：

### 1、PHP Built-in Server

使用`PHP`内建服务时，无需`nginx`、`apache`，只需`PHP`。

在命令行下，切换至`public`文件夹，执行`php -S localhost:xxxx index.php`即可，其中`xxxx`为端口号。

示例：

```Shell
>cd CollectionCloud\php\public
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

示例（`safpath`即指`SAF`的存放路径）：

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
