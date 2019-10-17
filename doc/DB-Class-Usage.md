# DB 类使用说明

## 一、`DB`类实例的命名空间

`DB`类实例的命名空间为`\Lib\Core\DB`，在使用`DB`类之前，要在`PHP`文件的开头用`use`指令声明，格式如下：

```PHP
use Lib\Core\DB;
$db = new DB($dbConfig);
```

```PHP
use Lib\Core\DB;
$db = DB::getInstance($dbConfig);
```

或者用类的全名引用`DB`类：

```PHP
$db = new \Lib\Core\DB($dbConfig);
```

```PHP
$db = \Lib\Core\DB::getInstance($dbConfig);
```

## 二、创建`DB`类实例

创建`DB`类实例有两种方式： `new DB(\$dbConfig)` 和 `DB::getInstance(\$dbConfig)`。

两种方法的签名是一样的：需要一个数据库配置数组作参数，返回一个`DB`类实例。

参数是可选的，不提供参数，则依据配置文件中的数据库配置作为默认参数值实例化。

```PHP
$db = new DB();
```

```PHP
$db = DB::getInstance();
```

两种方法的差别在于：

`new DB()`仅能创建`DB`类的实例。

`DB::getInstance()`在指定表名的情况下，会有两种可能的返回值。如果表名对应的 Model 类存在，则会初始化该类，并返回其实例；如果表名对应的 Model 类不存在，则会初始化`DB`类，并调用其`table()`方法初始化表名，并返回此实例。

```PHP
$db = DB::getInstance('user');
// 如果 Model\User 类存在，则返回其实例。
// 如果 Model\User 类不存在，则返回已经调用过 table('user') 的 DB 类实例。
```

### 1. 构造方法

方法签名：`__construct([array $dbConfig]):DB`。

`$dbConfig`参数是数据库连接配置数组，示例如下：

```PHP
$config = [
    'dbms' => 'mysql', // 数据库类型，必填
    'host' => '192.168.1.30', // 数据库服务器ip，必填
    'port' => '3306', // 服务器数据库服务端口，可选，默认为 3306
    'user' => 'root', // 连接用户名，必填
    'password' => '123456', // 连接密码，必填
    'dbname' => 'mydb', // 默认数据库，可选
    'encoding' => 'gbk', // 数据库字符编码，可选，默认为 UTF8MB4
    'standAlone' => true, // 【可选】标识该实例是否使用独立的数据库连接，不与其他同配置的 DB 类共享
   ];
```

此参数可选，如果省略，则使用系统默认配置。

为节省创建数据库连接的开销，默认情况下，数据库配置相同的实例会共享同一数据库连接，如果想要独享，只需将`standAlone`设置为`true`。

### 2. DB::getInstance()

方法签名：

* `DB::getInstance([string $table,][array $dbConfig]):DB`
* `DB::getInstance([array $dbConfig,][string $table]):DB`

简单说，其实`DB`类的构造方法有两个参数，一个是表名（`$table`），另一个是数据库连接配置（`$dbConfig`）,顺序无关，且均可选。

`$dbConfig`参数示例：

```PHP
$config = [
    'dbms' => 'mysql', // 数据库类型，必填
    'host' => '192.168.1.30', // 数据库服务器ip，必填
    'port' => '3306', // 服务器数据库服务端口，可选，默认为 3306
    'user' => 'root', // 连接用户名，必填
    'password' => '123456', // 连接密码，必填
    'dbname' => 'mydb', // 默认数据库，可选
    'encoding' => 'gbk', // 数据库字符编码，可选，默认为 UTF8MB4
    ];
```

如果省略`$dbConfig`参数，则使用系统默认的配置。

如果有表名参数，则会尝试实例化`\Model\TableName`实例，否则初始化`DB`类，并调用其`table()`方法初始化表名，并返回此实例。

```PHP
$db = DB::getInstance('user');
// 如果 Model\User 类存在，则返回其实例。
// 如果 Model\User 类不存在，则返回已经调用过 table('user') 的 DB 类实例。
```

### 3. clear()

`DB`类的实例，持有`fields`、`where`、`join`、`order`、`group`、`having`、`error`等多个属性，这些属性将用于拼装实际的`SQL`串，这些属性在创建实例时均为`null`，此时的实例即为清洁实例。

如果要对同一张表进行多个`DB`操作，或者使用一个`DB`实例，对多张表进行连续操作，可以使用一个`DB`类实例（包括其 Model 子类实例）。

为免前一操作的属性影响到后续操作，在进行一下操作前，需要主动调用`clear()`方法进行清洁工作：

```PHP
$db = DB::getInstance();
$userId = $db->table('user')->insert(['username' => 'tony', 'password' => '123456']);
if (false === $userId) {
    $db->rollback();
}
$db->clear();
$userInfo = [
    'user_id' => $userId,
    'telephone' => '18888888888',
    'email' => '888@163.com'
];
$insertUserInfoResult = $db->table('user_info')->insert($userInfo);
if (false === $insertUserInfoResult) {
    $db->rollback();
}
```

相对于每次都实例化一个新类，`clear()`方法很好地体现了实例的延续性，清楚显示出了实例的清洁过程。当在一个方法内实现全部数据库操作时，更清晰易懂。但当跨多个方法时，就需要传递`DB`实例或者利用类变量；如果是牵涉到多个类联合，就只能在类间传递`DB`实例。

多实例可以在任意情况下使用，不拘于同方法、同类，更具通用性。

## 四、构建方法和执行方法

`DB`类中的`table`、`fields`、`where`、`order`、`join`、`group`、`having`、`setPageSize`方法用于为组装`SQL`作准备，称为构建方法。

`insert`、`update`、`delete`、`select`、`selectOne`、`selectColumn`、`selectPage`方法完成`SQL`组装并执行，称为执行方法。

在调用执行方法时，会组装`SQL`并执行，因此必须在调用执行方法前，对构建方法进行调用；各构建方法的调用顺序则无关紧要。

以下语句的执行结果相同：

```PHP
$db = DB::getInstance();
$db->table('user');
$db->where('status = :status', [':status' => $_GET['status']]);
$db->order('id DESC');
$db->fields('id, name, status');
$res = $db->select();
```

```PHP
$db = DB::getInstance();
$db->order('id DESC');
$db->where('status = :status', [':status' => $_GET['status']]);
$db->table('user');
$db->fields('id, name, status');
$res = $db->select();
```

以下语句则是错误的：

```PHP
$db = DB::getInstance();
$db->order('id DESC');
$db->where('status = :status', [':status' => $_GET['status']]);
$db->table('user');
$res = $db->select();
$db->order('id DESC');
$db->fields('id, name, status');
```

在执行完`select()`方法之后，再指定要筛选的字段和排序规则，已经没有意义了。

## 五、构建方法与链式调用

所有的构建方法均返回`DB`类实例，可用于继续调用，这种调用方式称为链式调用。

上例可以写成：

```PHP
$db = DB::getInstance();
$res = $db->table('user')
  ->where('status = :status', [':status' => $_GET['status']])
  ->order('id DESC')
  ->fields('id, name, status')
  ->select();
```

执行方法返回的是`SQL`执行的结果，不是`DB`类实例，不能在其后继续链式调用，即执行方法仅可以作为链接调用的最后一环。

将一次`SQL`调用写成链式调用，可以使代码的层次更清晰，更容易辨别区分多个`SQL`。

## 六、构建方法的用法

### 1. table()

方法签名：`table(string $tableName):DB`。

`table()`方法用于指定要操作的数据库表，其参数语法与`SQL`的表名部分相同。

如果是多张表，用逗号间隔。可以起别名，可以带有数据库前缀。可以用“\`”将可能与保留字冲突的表名、数据库名括住。

```PHP
$db->table('application_system_configure AS `asc`, `order`.`delete` od');
```

上例是故意设计的，只是为了展示多表、前缀、别名、保留字冲突的处理。

### 2. fields()

方法签名：`fields([string $fields]):DB`。

`fields()`方法用于指定`SELECT`语句要返回的列（字段），其参数语法与`SQL`的列（字段）定义部分相同。

参数可以省略，省略参数相当于`*`。

多列用逗号间隔，可以起别名，可以带有数据库、表前缀。可以用“\`”将可能与保留字冲突的数据库名、表名、列名括住。

```PHP
$db->fields('`order`.id oid, `order`.`sum` as os');
```

### 3. where()

方法签名：`where(string $condition, [array $params]):DB`。

`where()`方法用于指定`SQL`条件。

`$where`参数为条件字符串，语法与`SQL`的`WHERE`子句基本相同。

```PHP
$status = 'ENABLED';
$db->where("creator_id = 3 and status = '$status'");
```

`in`条件示例：

```PHP
$ids = [1, 3, 100];
$db->where('id in (' . implode(',', $ids) . ')');
```

#### 与 WHERE 子名不同的是，$where 可以使用占位符

占位符分为两种： ? 点位符和由 : 开头的具名占位符。

当使用占位符时，须使用 $params 参数为占位符提供具体的值。

对于 ? 占位符，$params 为数值索引数组，按索引次序依次替换占位符。

```PHP
$status = 'ENABLED';
$db->where('creator_id = ? and status = ?', [6, $status]);
```

对应的实际查询条件为 creator_id = 3 and status = 'ENABLED'。

对于具名占位符，$params 为关联数组，按数组元素的键替换相应占位符。(注意，具名占位符必须以 ： 开头)

```PHP
$status = 'ENABLED';
$db->where('creator_id = :userid and status = :status', [':status' => $status, ':userid' => 3]);
```

对应的实际查询条件为 creator_id = 3 and status = 'ENABLED'。

#### 关于占位符

位符形式会传递给`PHP`的底层数据库函数，`PHP`官方文档是这样描述参数点位符的：

>参数占位符能阻止 SQL 注入攻击，不需要手动给参数加引号，如果用不同参数，通过 PDO::prepare() 和 PDOStatement::execute() 多次调用同一个 SQL 语句，将提升应用的性能 —— 驱动可以让客户端/服务器缓存查询和元信息。

而且，具名占位符方式还可以使得参数次序不再重要。

不过参数占位符也有其局限性，引用`PHP`官方文档 ：

>参数占位符仅能字面上展示完整的数据。不能是字面的一部分，不能是关键词，不能是标识符，不能是其他任意的范围。 举例说明：不能把多个值绑到单个参数里，然后在 SQL 语句里用 IN() 查询。
这些函数会对此方式传递的参数进行过滤，以防`SQL`注入。

```PHP
$db->where('user.id = :userid', [':userid' => 'user_info.user_id']);
// 错误，生成的条件为 user.id = 'user_info.user_id'
// 'user_info.user_id' 被处理成了一个字符串
```

也不支持`in`、`not in`这样的条件：

```PHP
$ids = [1, 3, 100];
$ids = implode(',', $ids);
$db->where('id in (:$ids));
// 错误，将会得到 id in ('1,3,100') ，而不是 id in (1, 3, 100)。
```

### 4. join()

方法签名：`join(string $join):DB`。

`join()`方法用于表关联。

`$join`参数是`SQL`中完整的`join`子句部分。

```PHP
$db->table('user')
   ->join('INNER JOIN user_info ON user.id = user_info.user_id')
   ->where('user.status = :status', [':status' => 'ENABLED']);
```

使用`join`子句连接表，要比直接在`table()`中指定多张表，在`where()`中指定表的关联关系要清晰明确。

上例对应的`table()`+`where()`版本如下：

```PHP
$db->table('user, user_info')
   ->where('user.id = user_info.user_id AND user.status = :status', [':status' => 'ENABLED'])
```

当有多表关联，且按照条件筛选数据时，后一种方法更趋混乱，更易出错。

#### 【注意】

由于连接有多种模式（内联，左联，右联，全联），本方法无法预先确定使用的是哪种联接方式，只能人为指定，且多表连接时`join ... on ...`需要在语句中多次指定。

因此与`DB`类中其他设置方法不同，`join()`方法的参数需要带有`[inner/left/right/full] join`和`on`关键字。（其它方法不需要带 `where`/`order by`/`group by`/`having`等关键字）

### 5. group()

方法签名：`group(string $group):DB`。

`group()`方法用于分组、筛选、聚合。

`$group`参数为分组字符串，语法与`SQL`的`GROUP BY`子句分组字符串部分基本相同。

```PHP
$db->table('user')->fields('count(*) total, age')->group('age')->select();
```

### 6. having()

方法签名：`having(string $having):DB`。

`having()`方法设置聚合条件。

`$having`参数为聚合条件字符串，语法与`SQL`的`HAVING`子句基本相同。

```PHP
$db->having('avg(price) > 30');
```

与`HAVING`子句不同的是，`$having`可以使用占位符。

```PHP
$db->having('avg(price) > ? and sum(amount) > ?', [100, 10000]);
//对应的实际聚合条件为 avg(price) > 100 and sum(amount) > 10000
```

```PHP
$db->having('avg(price) > :avg and sum(amount) > :total', ['total' => 10000, 'avg' => 100]);
//对应的实际聚合条件为 avg(price) > 100 and sum(amount) > 10000
```

### 7. order()

方法签名：`order(string $order):DB`。

`$order`参数为字符串类型，与`SELECT`语句的`ORDER BY`子句相当，规则也与该子句相同。

```PHP
$db->order('`order`.`id` DESC, user.username');

```

### 8. limit()

方法签名：`limit(int|string $rowCount, int|string $offset = 0)`。

`$rowCount`参数指定了要获取的行数，`$offset`参数指定起始偏移量。两个参数均可以是数字或数值字符串。

```PHP
$db->limit(30); //自第 1 条开始，取 30 条
$db->limit('30'); //自第 1 条开始，取 30 条
$db->limit(20, 40); //自第 41 条开始，取 20 条
$db->limit('20', 40); //自第 41 条开始，取 20 条
$db->limit(20, 40); //自第 41 条开始，取 20 条
$db->limit(20, '40'); //自第 41 条开始，取 20 条
```

### 七、执行方法的用法

### 1. insert()

方法签名：`insert(array $params):boolean|string`。

`insert()`方法用于向数据库表插入数据。

参数为关联数组，数组元素的键被识别为待插入的字段名，值被识别为相应的字段值。

```PHP
$params = ['user_name' => 'liwei', 'sex' => 'male', 'age' => 32];
$db->table('user')->insert($params);
// 解析后的 SQL 语句为 INSERT INTO user (user_name, sex, age) VALUES (:user_name, :sex, :age)
// 占位符对应的参数数组为 [':user_name' => 'liwei', ':sex' => 'male', ':age' => 32]
```

插入失败，返回`false`。

插入成功，有两种可能的返回值：

1. 插入数据行的自增`id`（表中有自增`id`）
2. 0（表中没有自增`id`）

`PHP`中`0`和`false`都是假值，在插入成功返回`0`的情况下，如下方式均无法做出正确判断：

```PHP
$res = $db->table('user')->insert(['user_name' => 'liwei']);
if (!$res) {
    echo '插入失败';
}
if (false == $res) {
    echo '插入失败';
}
if ($res) {
    echo '插入成功';
}
if (true == $res) {
    echo '插入成功';
}

```

需要根据全等判断或是否有错误产生来确定插入是否失败（为确保正确判断，请一直使用此方式做验证）：

```PHP
$res = $db->table('user')->insert(['user_name' => 'liwei']);
if (false === $res) {
    echo '插入失败';
}
if (false !== $res) {
    echo '插入成功';
}

//----------
if ($db->getError() !== null) {
    echo '插入失败';
} else {
    echo '插入成功';
}
```

### 2. getLastInsertId()

方法签名：`getLastInsertId():string`。

`getLastInsertId()`方法返回最后一次插入数据的自增`id`。

需要注意的是，**如果数据库表没有自增`id`，该方法返回`0`**。

```PHP
$db->table('user')->insert(['user_name' => 'liwei', 'sex' => 'male', 'age' => 32]);
$userId = $db->getLastInsertId();
```

### 3. delete()

方法签名：`delete():Boolean|Integer`。

`delete()`方法用于删除（物理删除）数据。

删除失败返回`false`，删除成功返回删除的行数。

如果删除动作没有错误，但是符合条件的行数为`0`，则`delete()`方法返回`0`，由于`0`也是假值，因此不能使用如下方式判断删除是否失败：

```PHP
$res = $db->where('user')->where('id = :id', [':id' => 9])->delete();
if (!$res) {
    echo '删除失败';
} else {
    echo '删除成功';
}
```

需要根据全等判断或是否有错误产生来确定删除是否失败（为确保正确判断，请一直使用此方式做验证）：

```PHP
$res = $db->where('user')->where('id = :id', [':id' => 9])->delete();
if (false === $res) {
    echo '删除失败';
} else {
    echo '删除成功';
}

//----------
if ($db->getError() !== null) {
    echo '删除失败';
} else {
    echo '删除成功';
}
```

**删除动作无法恢复，请确保使用此函数时，带有`where`子句。**

### 4. update()

`update()`方法用于更新记录。

它有两个签名：

1. `update(array $params):boolean|integer`。

2. `update(string $updateString)boolean|integer`。

如果参数为关联数组，数组元素的键被识别为待更新的字段名，值被识别为相应的字段值。

```PHP
$params = ['user_name' => 'wangermazi'];
$res = $db->table('user`)
   ->where('user_name = :user_name', [':user_name' => 'liwei'])
   ->update($params);
```

如果参数为字符串，则识别为`update`语句的`set`子句（不要包含`set`关键字）。

```PHP
$res = $db->table('user')
   ->where('user_name = :user_name', [':user_name' => 'liwei'])
   ->update("user_name = 'wangermazi'");
```

数组参数方式，会被解析为具名占位符传递给底层数据库类，出于安全考虑（见前文所述：占位符方式可以避免`SQL`注入），建议优先使用此方式。

提供字符串参数方式主要是由于参数数组方式（其实是底层数据库类的占位符模式）不支持待更新的字段值中包含字段名的形式。对于非常量数值更新，比如待更新的值包含数据库字段或计算值：在原值上自增自减，或使用单价和数量更新总金额等，只能使用此形式

```PHP
// 自增
$db->table('user')->update(['age' => 'age + 1']);
// 错误，语句被解析为 UPDATE user SET age = 'age + 1'

$db->table('user')->update('age = age + 1');
// 正确，语句被解析为 UPDATE user SET age = age + 1

//-----------------------------------------------------------------------

// 计算总金额
$db->table('product')->update(['amount' => 'price * number']);
// 错误，语句被解析为 UPDATE product SET amount = 'price * number'

$db->table('procuet')->update('amount = price * number);
// 正确，语句被解析为 UPDATE product SET amount = price * number
```

如果更新动作没有错误，但是符合条件的行数为`0`，则`update()`方法返回`0`，由于`0`也是假值，因此不能使用如下方式判断更新是否失败：

```PHP
$res = $db->table('user')->where('id = :id', [':id' => 9])->update('age = age + 1');
if (!$res) {
    echo '更新失败';
} else {
    echo '更新成功';
}
```

需要根据全等判断或是否有错误产生来确定更新是否失败（为确保正确判断，请一直使用此方式做验证）：

```PHP
$res = $db->table('user')->where('id = :id', [':id' => 9])->update('age = age + 1');
if (false === $res) {
    echo '更新失败';
} else {
    echo '更新成功';
}

//----------
if ($db->getError() !== null) {
    echo '更新失败';
} else {
    echo '更新成功';
}
```

### 5. select()

方法签名：`select(int $fetchMode = null):boolean|array`。

`select()`方法用于查询数据库。

查询成功失败返回`false`，查询成功返回符合条件的行的数组。数组中的每个元素对应一行，格式受参数`$fetchMode`影响。

参数`$fetchMode`的可选值为`PDO::FETCH_*`系列常量之一，其值影响到返回数据的格式。参见 [PHP 官方的 PDO 文档](hhttps://www.php.net/manual/zh/pdostatement.fetch.php)。此参数可以省略，省略后的返回值格式参见[`fetchMode`](#八fetchMode)。

```PHP
$rows = $db->table('user')
   ->fields('user_name, sex, age')
   ->where('status = :status', [':status' => 'ENABLED'])
   ->select();
if (!rows) {
    echo '查询失败';
}
...
// array(
//   '0' => {
//     'user_name' => 'liwei',
//     'sex' => 'male',
//     'age' => 32,
//   },
//   ...
// )
```

### 6. selectOne()

方法签名：`selectOne(int $fetchMode = null):boolean|array`。

`selectOne()`方法用于仅获取一行符合条件的数据。

查询失败返回`false`，查询成功返回一个数组，数组格式受`$fetchMode`参数影响。

参数`$fetchMode`的可选值为`PDO::FETCH_*`系列常量之一，其值影响到返回数据的格式。参见 [PHP 官方的 PDO 文档](hhttps://www.php.net/manual/zh/pdostatement.fetch.php)。此参数可以省略，省略后的返回值格式参见[`fetchMode`](#八fetchMode)。

```PHP
$rows = $db->table('user')
   ->fields('user_name, sex, age')
   ->where('status = :status', [':status' => 'ENABLED'])
   ->selectOne();
if (!rows) {
    echo '查询失败';
}
...
// array(
//   '0' => 'liwei',
//   'user_name' => 'liwei',
//   '1' => 'male',
//   'sex' => 'male',
//   '2' => 32,
//   'age' => 32,
// )
```

### 7. selectColumn()

方法签名：`selectColumn([integer $index]):boolean|string`。

`selectColumn()`用于获取单行数据中的某一列的值。

其参数为要获取的列在`fields`中指定的列的序号（如果`fields`为`*`，则依据数据库定义顺序），序号从`0`开始计数。参数可以省略，默认值为`0`。

查询失败返回`false`，查询成功，返回该列的值。

下面的代码用于获取数据库中`id = 9`的记录的用户名：

```PHP
$res = $db->table('user')
   ->where('id = :id', [':id' => 9])
   ->fields('user_name')
   ->selectColumn();
```

## 八、`fetchMode`

`fetchMode`会影响到获取数据的结果格式。

`SAF`的`DB`类仅支持`PDO`，因此其`fetchMode`的可取值也与`PDO`保持一致，且可以直接使用`PDO::FETCH_*`系列常量进行设置。参见 [PHP 官方的 PDO 文档](hhttps://www.php.net/manual/zh/pdostatement.fetch.php)。

`DB`类有四种情况可以影响到最终的`fetchMode`模式，依优先级分别为：

### 1. `select()`、`selectOne()`、`selectPage()`方法的`fetchMode`参数

可以在调用上面三个方法时，指定`$fetchMode`参数，返回值即以指定的模式返回。

如果省略此参数，则会沿用后续规则。

### 2. `setFetchMode()`方法指定模式

如果使用`setFetchMode(int $fetchMode)`方法指定`fetchMode`模式，会影响到该实例的所有`select*()`方法的默认返回值格式。

如果没有使用`setFetchMode()`方法指定模式，不带有`$fetchMode`参数的`select*()`方法的结果会沿用后续规则。

### 3. `setConnFetchMode()`方法指定模式

使用`setConnFetchMode(int $fetchMode)`方法，可以指定`数据库连接`的`fetchMode`，它会影响到使用相同连接的（未使用`setFetchMode()`指定`fetchMode`的）所有实例的`select()`方法的默认返回值格式。

如果没有使用`setFetchMode()`和`setConnFetchMode()`指定`fetchMode`，`select*()`方法的默认返回值会沿用后续规则。

### 4. `DB::setDefaultFetchMode()`方法指定模式

使用`DB::setDefaultFetchMode(int $fetchMode)`将影响所有（未使用`setFetchMode()`和`setConnFetchMode()`指定`fetchMode`的）实例（包括使用该方法之后生成的实例）的`select*()`方法的默认返回值格式。

**注意：如果没有使用上述这些方法指定`fetchMode`，默认的模式为`PDO::FETCH_ASSOC`，这一点与`PDO`的默认模式不同。**

## 九、分页及相关方法的用法

分页获取数据需要设定每页数据的条数、要获取的数据的页码：

```PHP
// 获取第二页的数据，每页20条。
$db->setPageSize(20)
   ->selectPage(2);
```

在前端展示数据时，还需要获得总条数、总页数、当前页、上一页页码、下一页页码。

```PHP
// 在前例的基础上
$totalRows = $db->totalRows(); // 总条数
$totalPages = $db->totalPages(); // 总页数
$currentPage = $db->page(); // 当前页页码
$prevPage = $db->prev(); // 上一页页码
$nextPage = $db->nextPage(); // 下一页页码
$pagerationInfo = $db->pagerationInfo() // 获取除分页数据集之外的所有分页信息
// [
//    'totalRows' => 1309, // 总条数
//    'totalPages' => 7, // 总页数
//    'currentPage' => 2, // 当前页页码
//    'prevPage' => 1, // 上一页页码
//    ’nextPage' => 3, // 下一页页码
// ]
```

### 1. setPageSize()

方法签名：`setPageSize(integer $pageSize):DB`。

`setPageSize()`方法用于指定每页的数据行数。此方法可以不调用，每页数据的默认值为20行。

### 2. selectPage()

方法签名：`selectPage([integer $currentPage], int $fetchMode = null):boolean|array`。

`selectPage()`方法用于获取一页数据。

参数`$currentPage`为需要获取数据的页码。此参数可以省略，默认值为`1`。

参数`$fetchMode`的可选值为`PDO::FETCH_*`系列常量之一，其值影响到返回数据的格式。参见 [PHP 官方的 PDO 文档](hhttps://www.php.net/manual/zh/pdostatement.fetch.php)。此参数可以省略，省略后的返回值格式参见[`fetchMode`](#fetchMode)。

查询失败返回`false`，查询成功返回数据行的数组。格式受参数`$fetchMode`影响。

```PHP
$rows = $db->table('user')
   ->fields('user_name, sex, age')
   ->where('status = :status', [':status' => 'ENABLED'])
   ->setPageSize(20)
   ->selectPage(3);
if (!rows) {
    echo '查询失败';
}
...
// array(
//   '0' => {
//     '0' => 'liwei',
//     'user_name' => 'liwei',
//     '1' => 'male',
//     'sex' => 'male',
//     '2' => 32,
//     'age' => 32,
//   },
//   ...
// )
```

此方法会产生一些副作用：设置当前页、统计总行数、计算总页数。这些副作用产生的结果，在调用相关方法获取相应数据时才会体现。

### 3. totaRows()

方法签名：`totalRows():integer`。

`totalRows()`方法返回数据的总行数。

### 4. totalPages()

方法签名：`totalPages():integer`。

`totalPages()`方法返回数据的总页数。

### 5. page()

方法签名：`page():integer`。

`page()`方法返回当前页面页码。

### 6. prev()

方法签名：`prev():integer`。

`prev()`方法返回上一页页码。如果当前页为第一页，返回`1`。

### 7. next()

方法签名：`next()`。

`next()`方法返回下一页页码。如果当前页为最后一页，返回最后一页页码。

### 8. pagerationInfo()

方法签名：`pagerationInfo():array`。

`pagerationInfo()`方法返回除分页数据集外的所有分页信息。

格式示例：

```PHP
[
   'totalRows' => 1309, // 总条数
   'totalPages' => 7, // 总页数
   'currentPage' => 2, // 当前页页码
   'prevPage' => 1, // 上一页页码
   'nextPage' => 3, // 下一页页码
]
```

## 十、事务及相关方法的用法

`DB`类支持事务。

```PHP
// 创建 DB 类实例
$db = DB::getInstance();
// 开启事务
$db->beginTransaction();
// 下单
$orderRes = $db->table('order')
   ->insert([...]);
if (false === $orderRes) {
    $db->rollback(); // 下单失败回滚
}
// 清洁 DB
$db->clear();
// 减库存
$decrementStokRes = $db->table('stok')
   ->where(...)
   ->update(...);
if (false === $decrementStokRes) {
    $db->rollback(); // 减库存失败回滚
}
$db->commit(); // 提交事务
```

**注意其中`$db->clear()`的使用**，事务操作必须使用同一个数据库连接，因此还能为多次数据库操作创建不同的`DB`实例，如下方式是错误的：

```PHP
// 创建 DB 类实例
$db = new DB();
// 开启事务
$db->beginTransaction();
// 下单
$orderRes = $db->table('order')
   ->insert([...]);
if (false === $orderRes) {
    $db->rollback(); // 下单失败回滚
}
// 创建新数据库实例
$db = new DB();
// 减库存
$decrementStokRes = $db->table('stok')
   ->where(...)
   ->update(...);
if (false === $decrementStokRes) {
    $db->rollback(); // 减库存失败回滚
}
$db->commit(); // 提交事务
```

但是`DB`类会持有表名、条件、字段列表等属性的特点，会使其在进行多次数据库操作时相互影响。

有两种办法规避，其一就是前面使用`$db->clear()`的例子。

另一种方法是使用`DB::getInstance()`重新获取清洁的`DB`类实例：

```PHP
// 获取清洁 DB 类实例
$db = DB::getInstance();
// 开启事务
$db->beginTransaction();
// 下单
$orderRes = $db->table('order')
   ->insert([...]);
if (false === $orderRes) {
    $db->rollback(); // 下单失败回滚
}
// 创建新数据库实例
$db = DB::getInstance();
// 减库存
$decrementStokRes = $db->table('stok')
   ->where(...)
   ->update(...);
if (false === $decrementStokRes) {
    $db->rollback(); // 减库存失败回滚
}
$db->commit(); // 提交事务
```

这在某些跨方法，甚至跨类进行数据库操作时，更方便。

获取同一`DB`实例的清洁版本，必须保证传递给`getInstance()`的参数设置完全一致，不同的配置将获取不同的`DB`类实例，这一点容易被忽略。因此在不跨方法、类进行数据库的事务操作时，推荐使用`$db->clear()`方法。

`SAF`框架支持同一数据库连接的嵌套事务：

* 仅最外层的提交和回滚实际提交至服务器。

* 任一层回滚会使外层提交全部失败、里层已经调用成功的提交也被忽略。

* 可以通过`DB::getLastError()`和`getError()`方法获取回滚失败的原因。

### 1, beginTransaction()

方法签名：`beginTransactionn():boolean`。

开启事务。执行成功返回`true`，失败返回`false`。

### 2. rollback()

方法签名：`rollback():boolean`。

回滚事务。执行成功返回`true`，失败返回`false`。

### 3. commit()

方法签名：`commit():boolean`。

提交事务。执行成功返回`true`,失败返回`false`。

## 十一、调试

可以使用`getSql()`方法和`getDebugInfo()`方法获取调试所需信息：

### 1. getSql()

方法签名：`getSql():string`。

`getSql()`方法返回拼装后待执行的`SQL`串（预执行前的`SQL`串，如果使用了占位符，则为带有占位符的`SQL`，而不是数据库实际执行的`SQL`）。

### 2. getDebugInfo()

方法签名：`getDebugInfo():string`。

`getDebugInfo()`方法返回一个包含正在使用的 SQL 查询、所用参数（Params）的数目、参数的清单、参数名、用一个整数表示的参数类型（paramtype）、键名或位置、值、以及在查询中的位置的字符串。

### 3. getActualSql()

方法签名：`getActualSql():string`。

`getActualSql()`方法返回一个已经将占位符替换为对应参数的`SQL`字符串。不过这只是简单的替换，没有进行防`SQL`注入解析，与实际执行的`SQL`不完全相同，仅供参考。

### 4. getError()

方法签名：`getError():array`。

`getError()`方法返回`DB`对象的`SQL`执行错误信息，其错误号和错误消息均来自`SQL`服务器。

格式如下：

```PHP
[
    'errorCode' => '40030',
    'errorInfo' => '这是一个虚拟的错误信息',
]
```

### 5. DB::getLastError()

方法签名：`DB::getLastError():array`。

与`getError()`方法不同，`DB::getLastError()`是一个**静态类方法**，它用于获取最后一次产生错误的`SQL`指令的错误信息（也许最后一次错误`SQL`与执行`DB::getLastError()`方法之间曾经有一个或者多个正确执行的`SQL`）。

## 十二、其他

`DB`类封装了常见的数据库操作，以降低直接使用底层数据库类的复杂性，提高开发效率。

有些特殊业务，使用封装后的方法或者难于实现，或者无法实现。为此`DB`类还提供了其他几个方法，可以直接使用底层数据库类（`DB`类封装的底层数据库类库为`PDO`）。

### 1. exec()

方法签名：`exec(string $sql):boolean|integer`。

`exec()`方法直接调用了`PDO`的`exec()`方法。参数为`SQL`串。

执行失败返回`false`，执行成功返回受影响的条数，如果没有行受到影响，返回`0`,注意区别`0`和`false`的区别（参见`insert()`、`update()`、`delect()`方法的说明）。

`exec()`方法适用于查询语句以外的其它`SQL`操作，比如：`insert`、`update`、`delete`等。

```PHP
// 表中插入数据时，某一字段取数据库中该字段的最大值，并＋1
$db->exec("INSERT INTO sys_menu (menu_name,menu_url,parent_id,menu_order) SELECT 'ccc','',0, (SELECT IFNULL((SELECT max(menu_order)from sys_menu),0))+1");
```

### 2. query()

方法签名：`query(string $sql):boolean|PDOStatement`。

`query()`方法直接调用了`PDO`的`query`方法。参数为`SQL`。

执行失败返回`false`，成功返回`PDOStatement`对象。它最易于使用的方式就是执行`SELECT`语句，得到的返回值，可以直接用于`foreach`循环，也可以直接将其作为`data`或`data`的一部分，返回给前端。

**推荐只将其用于这一种情况：**

```PHP
$res = $db->query(SELECT name, color, calories FROM fruit ORDER BY name);
foreach ($res as $row) {
    print $row['name'] . "\t";
    print $row['color'] . "\t";
    print $row['calories'] . "\n";
}
$result = Error::OK;
$result['data'] = $res;
$this->setResult($result);
```

如将其用于其他`SQL`语句，请参考 [PHP 官方的 PDO 文档](https://www.php.net/manual/zh/pdo.query.php)。

### `DB`类还提供了直接操作底层`PDO`类和`PDOStatement`类的机制

`dbh()`方法和`sth()`方法分别返回了`PDO`对象和`PDOStatement`对象，可用于需要直接使用底层机制的场景。

### 3. dbh()

方法签名：`dbh():PDO`。

返回`PDO`对象：

```PHP
// 表中插入数据时，某一字段取数据库中该字段的最大值，并＋1
$db->dbh()->exec("INSERT INTO sys_menu (menu_name,menu_url,parent_id,menu_order) SELECT 'ccc','',0, (SELECT IFNULL((SELECT max(menu_order)from sys_menu),0))+1");
```

`PDO`的完整参考请见 [PHP 官方的 PDO 文档](https://www.php.net/manual/zh/class.pdo.php)。

### 4. sth()

方法签名：`sth():PDOStatement`。

返回`PDOStatement`对象：

```PHP
$db->table('user')->update('age = age + 1');
// 获取影响行数
$rows = $db->sth()->rowCount();
```

请在调用了**执行方法**后，再调用`sth()`方法(在执行操作前`PDOStatement`对象还不存在)。

`PDOStatemnt`的完整参考请见 [PHP 官方的 PDOStatement 文档](https://www.php.net/manual/zh/class.pdostatement.php)。
