<?php

namespace Lib\Core;

use InvalidArgumentException;
use PDO;
use PDOStatement;

/**
 * 数据库操作类
 * 此类仅适用于 MySQL
 */
class DB
{

    /**
     * 单例数据库连接实例集合
     *
     * @var array
     */
    static private $connections = [];

    /**
     * PDO 对象
     * @var PDO
     */
    private $dbh;

    /**
     * PDOStatement 对象
     * @var PDOStatement
     */
    private $sth;

    /**
     * 查询结果要返回的字段，与 Select 语句中的 fields 段对应，格式为用逗号间隔的字符串，可以使用 × 通配符。
     * @var string
     */
    protected $fields;

    /**
     * 排序，与 order by 子句对应
     * @var string
     */
    protected $order;

    /**
     * 条件，与 where 子句对应
     * @var string
     */
    protected $where;

    /**
     * 填充了参数的 条件，不过这只是做了简单的参数匹配，没有处理防注入，可能与实际语句不完全吻合
     *
     * @var string
     */
    protected $actualWhere;

    /**
     * 表名, 在查询语句中，可以是逗号间隔的多个表名
     * @var string
     */
    protected $table;

    /**
     * join 子句
     * @var string
     */
    protected $join;

    /**
     * 拼接完成的 SQL 语句
     * @var string
     */
    protected $sql;

    /**
     * 填充了参数的 SQL，不过这只是做了简单的参数匹配，没有处理防注入，可能与实际语句不完全吻合
     *
     * @var string
     */
    protected $actualSql;

    /**
     * 用于查询条件的实际参数
     * @var array
     */
    protected $whereParams = [];

    /**
     * 用于插入的实际参数
     * @var array
     */
    protected $insertParams = [];

    /**
     * 用于更新的实际参数
     * @var array
     */
    protected $updateParams = [];

    /**
     * 用于having的实际参数
     * @var array
     */
    protected $havingParams = [];

    /**
     * 查询到的结果总数
     * @var int
     */
    protected $count;

    /**
     * 要查询的条数
     *
     * @var string
     */
    protected $limit;

    /**
     * 分页时每页的条数，默认为20
     * @var int
     */
    protected $pageSize = 20;

    /**
     * 当前页数
     * @var int
     */
    protected $page;

    /**
     * 查询结果总页数
     * @var int
     */
    protected $totalPages;

    /**
     * 分组，相当于 Select 语句的 group by 子句
     * @var string
     */
    protected $group;

    /**
     * 聚合筛选，相当于 Select 语句的 having 子名
     *
     * @var string
     */
    protected $having;

    /**
     * 存放最后一次 SQL 执行出错时错误的类变量
     *
     * @var array
     */
    private static $lastError;

    /**
     * 存放 SQL 执行出错的错误实例变量
     *
     * @var array
     */
    private $error;

    /**
     * 获取数据时的 fetch 模式，此变量仅影响当前实例
     *
     * @var int|null
     */
    private $fetchMode = null;

    /**
     * 数据库连接数组的键，用于标识使用了相同配置参数的数据库连接，实现（分组单例）
     *
     * @var string
     */
    private $key;

    /**
     * DB的默认fetch模式，此变量影响全部（未指定connFetchModes的实例和fetchMode）的实例
     *
     * @var int
     */
    private static $defaultFetchMode = null;

    /**
     * 连接的默认模式数组，每个DB连接可能有一个对应的fetch模式，存在在本变量的相同key值元素中，此变量影响（未指定fetchMode）相同数据库连接的实例。
     *
     * @var array
     */
    private static $connFetchModes = [];

    /**
     * 连接的事务状态数组
     *
     * @var array
     */
    private static $transactions = [];

    /**
     * 初始化DB库
     *
     * 参数说明及示例：
     *   参数可变，顺序无关，可用参数有两个：
     *   $dbConfig 参数为数组，数组各项对应了初始化 PDO 时需要的 dsn 信息项
     *   $table 参数为字符串，设置表名，如果为空则不置表名
     *   示例：
     *   $config = [
     *     'dbms' => 'mysql', // 数据库类型，必填
     *     'host' => '192.168.1.30', // 数据库服务器ip，必填
     *     'port' => '3306', // 服务器数据库服务端口，可选，默认为 3306
     *     'user' => 'root', // 连接用户名，必填
     *     'password' => '123456', // 连接密码，必填
     *     'dbname' => 'mydb', // 默认数据库，可选
     *     'encoding' => 'gbk', // 数据库字符编码，可选，默认为 UTF8MB4
     *     'standAlone' => true, // 【可选】标识该实例是否使用独立的数据库连接，不与其他同配置的 DB 类共享
     *   ];
     *   $db = new DB($config);
     *   $db = new DB('test');
     * @param ... $params
     * @return DB 数据库类实例
     */
    static public function getInstance(...$params)
    {
        if (count($params) === 0) {
            $table = null;
            $dbConfig = null;
        } else if (count($params) === 1) {
            if (is_array($params[0])) {
                $dbConfig = $params[0];
                $table = null;
            } else if (is_string($params[0])) {
                $table = $params[0];
                $dbConfig = null;
            } else {
                throw new InvalidArgumentException('获取DB类实例方法参数错误', '500');
            }
        } else if (count($params) === 2) {
            if (is_array($params[0]) && is_string($params[1])) {
                $dbConfig = $params[0];
                $table = $params[1];
            } else if (is_string($params[1]) && is_array($params[0])) {
                $table = $params[0];
                $dbConfig = $params[1];
            } else {
                throw new InvalidArgumentException('获取DB类实例方法参数错误', '500');
            }
        } else {
            throw new InvalidArgumentException('获取DB类实例方法参数错误', '500');
        }
        if ($table) {
            $class = self::tableName2ClassName($table);
            if (class_exists($class)) {
                return new $class($dbConfig);
            } else {
                $db = new DB($dbConfig);
                $db->table($table);
                return $db;
            }
        } else {
            return new DB($dbConfig);
        }
    }


    /**
     * 初始化DB库
     *
     * 参数说明及示例：
     *   $dbConfig 参数为数组，数组各项对应了初始化 PDO 时需要的 dsn 信息项，示例如下：
     *   $config = [
     *     'dbms' => 'mysql', // 数据库类型，必填
     *     'host' => '192.168.1.30', // 数据库服务器ip，必填
     *     'port' => '3306', // 服务器数据库服务端口，可选，默认为 3306
     *     'user' => 'root', // 连接用户名，必填
     *     'password' => '123456', // 连接密码，必填
     *     'dbname' => 'mydb', // 默认数据库，可选
     *     'encoding' => 'gbk', // 数据库字符编码，可选，默认为 UTF8MB4
     *     'standAlone' => true, // 【可选】标识该实例是否使用独立的数据库连接，不与其他同配置的 DB 类共享
     *   ];
     *   $db = new DB($config);
     *   如果不提供 $dbConfig 参数，则会试图使用 DB_CONFIG 常量
     * @param array $dbConfig 数据库连接参数（dsn），参见示例
     */
    public function __construct($dbConfig = null)
    {
        if (null === $dbConfig && !defined('DB_CONFIG')) {
            throw new InvalidArgumentException('未提供数据库配置', 500);
        }
        $dbConfig = $dbConfig !== null ? $dbConfig : DB_CONFIG;
        $key = self::generateKey($dbConfig);
        $this->setKey($key);
        $dsn = $dbConfig['dbms'] . ':';
        $dsnParams = [];
        if (isset($dbConfig['dbname'])) {
            $dsnParams[] = 'dbname=' . $dbConfig['dbname'];
        }
        if (isset($dbConfig['host'])) {
            $dsnParams[] = 'host=' . $dbConfig['host'];
        }
        if (isset($dbConfig['port'])) {
            $dsnParams[] = 'port=' . $dbConfig['port'];
        } else {
            $dsnParams[] = 'port=3306';
        }
        $dsn .= implode(';', $dsnParams);

        if (isset($dbConfig['encoding'])) {
            $encoding = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . $dbConfig['encoding'] . '\''];
        } else {
            $encoding = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8MB4\''];
        }
        if (!isset(self::$connections[$this->getKey()])) {
            self::$connections[$this->getKey()] = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], $encoding);
            $fetchMode = self::$defaultFetchMode ?: PDO::FETCH_ASSOC;
            self::$connections[$this->getKey()]->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $fetchMode);
        }
        $this->dbh = self::$connections[$this->getKey()];

        if ('Lib\DB' !== ($className = get_class($this)) && is_null($this->table)) {
            $this->table($this->className2TableName($className));
        }
    }

    /**
     * 设置实例的数据库连接的key
     *
     * @param string $key
     * @return void
     */
    private function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * 获取实例的数据库连接的key
     *
     * @return string
     */
    private function getKey()
    {
        return $this->key;
    }

    /**
     * 设置DB级别的fetchMode默认值
     *
     * @param int $fetchMode 可取值参见 PDO::FETCH*
     * @return void
     */
    public static function setDefaultFetchMode($fetchMode)
    {
        self::$defaultFetchMode = $fetchMode;
        foreach (self::$connections as $key => $value) {
            if (!isset(self::$connFetchModes[$key])) {
                $value->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $fetchMode);
            }
        }
    }

    /**
     * 设置数据库连接的fetchMode默认值
     *
     * @param int $fetchMode 可取值参见 PDO::FETCH*
     * @return void
     */
    public function setConnFetchMode($fetchMode)
    {
        self::$connFetchModes[$this->getKey()] = $fetchMode;
        self::$connections[$this->getKey()]->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $fetchMode);
    }


    /**
     * 设置当前实例的 fetch 模式
     *
     * @param int $fetchMode 可取值参见 PDO::FETCH*
     * @return void
     */
    public function setFetchMode($fetchMode)
    {
        $this->fetchMode = $fetchMode;
    }

    /**
     * 获取 fetch 模式
     * 如果传递参数不为 null，返回传入值；
     * 如果传递的参数为 null，但 $this->fetchMode 不为 null，返回 $this->fetchMode;
     * 如果 $this->fetchMode 也为 null，返回 $this->dbh 对象的 PDO::ATTR_DEFAULT_FETCH_MODE
     *
     * @param int $fetchMode
     * @return int
     */
    protected function getFetchMode($fetchMode = null)
    {
        if (null !== $fetchMode) {
            return $fetchMode;
        }
        if (null !== $this->fetchMode) {
            return $this->fetchMode;
        }
        return $this->dbh()->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
    }

    public function className2TableName($className)
    {
        $tablePart = lcfirst(substr($className, strripos($className, '\\') + 1));
        $tableName = preg_replace_callback('|([A-Z])|', static function ($match) {
            return '_' . strtoupper($match[1]);
        }, $tablePart);
        return strtolower($tableName);
    }

    public static function tableName2ClassName($tableName)
    {
        return '\\Model\\' . ucfirst(preg_replace_callback('|_(.)|', static function ($match) {
            return strtoupper($match[1]);
        }, $tableName));
    }

    /**
     * @param $dbConfig
     * @return array
     */
    private static function generateKey($dbConfig)
    {
        $key = $dbConfig['dbms'];
        if (isset($dbConfig['dbname'])) {
            $key .= '_' . $dbConfig['dbname'];
        }
        if (isset($dbConfig['host'])) {
            $key .= '_' . $dbConfig['host'];
        }
        if (isset($dbConfig['port'])) {
            $key .= '_' . $dbConfig['port'];
        } else {
            $key .= '_' . '3306';
        }

        if (isset($dbConfig['encoding'])) {
            $key .= '_' . $dbConfig['encoding'];
        } else {
            $key .= '_' . 'UTF8MB4';
        }
        $key .= '_' . $dbConfig['user'] . '_' . $dbConfig['password'];
        if (isset($dbConfig['standAlone']) && true === $dbConfig['standAlone']) {
            $key .= uniqid('_', true);
        }
        return $key;
    }

    /**
     * 清除被改变的初始化属性，还原清洁对象
     *
     * @return \Lib\DB 用于链式调用
     */
    public function clear()
    {
        $this->sth = null;
        $this->fields = null;
        $this->order = null;
        $this->where = null;
        $this->join = null;
        $this->sql = null;
        $this->whereParams = [];
        $this->insertParams = [];
        $this->updateParams = [];
        $this->havingParams = [];
        $this->limit = null;
        $this->count = null;
        $this->pageSize = 20;
        $this->page = null;
        $this->totalPages = null;
        $this->group = null;
        $this->having = null;
        $this->error = null;
        $this->fetchMode = null;
        $this->actualWhere = null;
        $this->actualSql = null;
        return $this;
    }

    /**
     * 获取 DB 类中实际使用的 PDO 对象，在某些特殊情况下，可以直接使用该对象（不推荐）
     *
     * 示例：
     *   $pdo = $db->dbh();
     *   $pdo->exec($sql);
     * 或者
     *   $db->dbh()->exec($sql)
     *
     * @return PDO
     */
    public function dbh()
    {
        return $this->dbh;
    }

    /**
     * 返回经 prepare 后的 PDOStatement 对象
     * 可以利用此对象，使用不同的参数调用同一预处理后的语句，提高 SQL 效率。
     *
     * @return PDOStatement
     */
    public function sth()
    {
        return $this->sth;
    }

    /**
     * 设置查询语句要返回的列（字段）
     * 参数说明及示例：
     *   $fields 参数为字符串类型，与 SELECT 语句的返回列（字段）部分对应，为以逗号间隔的列（字段）名。
     *   例如：$db->fields('id, name, age');
     *
     *   可以使用通配符。
     *   例如：$db->fields('a.*, b.id, c.name');
     *
     *   可以为列（字段）指定别名。
     *   例如：$db->fields('old_record_number orn, new_record_number nrn');
     *
     *   可以为列（字段）指定表名、数据库名前缀。
     *   例如：$db->fields('org.user.name oun, org.user.age oua');
     *
     *   如果列（字段）或其前缀的库、表名与 MySQL关键字冲突，需要人为使用使用 “`”（PC键盘数字键前的字符，不是单引号）挺住：
     *   例如：$db->fields('`order`.`key` order_key, `order`.`show` order_show');
     *
     * @param string $field 查询语句要返回的列（字段）
     * @return DB 返回当前类实例对象，可以用于链式操作
     */
    public function fields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * 设置查询语句的排序
     * 参数说明与示例：
     *   $order 参数为字符串类型，与 SELECT 语句的 ORDER BY 子句相当，规则也与该子句相同。
     *   可以为列（字段）指定表名、数据库名前缀
     *   如果列（字段）或其前缀的库、表名与 MySQL关键字冲突，需要人为使用使用 “`”（PC键盘数字键前的字符，不是单引号）挺住。
     *   示例：$db->order('`order`.`id` DESC, user.username');
     * @param string $order 排序字符串
     * @return DB 返回当前类实例对象，可以用于链式操作
     */
    public function order($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * 设置查询、修改、删除的条件
     * 参数说明与示例:
     *   $where 参数为条件字符串，语法与 SQL 的 WHERE 子句基本相同。
     *   示例：$db->where('creator_id = 3 and status = 2');
     *   与 WHERE 子名不同的是，$where 可以使用占位符。
     *   占位符分为两种： ? 点位符和由 : 开头的具名占位符。
     *   当使用占位符时，须使用 $params 参数为占位符提供具体的值。
     *
     *   对于 ? 占位符，$params 为数值索引数组，按索引次序依次替换占位符。
     *   示例：$db->where('creator_id = ? and status = ?', [6, 2]); 对应的实际查询条件为 creator_id = 3 and status = 2
     *
     *   对于具名占位符，$params 为关联数组，按数组元素的键替换相应占位符。(注意，具名占位符必须以 ： 开头)
     *   示例：$db->where('creator_id = :userid and status = :status', [':status' => 2, ':userid' => 3]); 对应的实际查询条件为 creator_id = 3 and status = 2
     * @param $where string 条件字符串，可以包含占位符
     * @param $params array 如果 $where 使用了占位符，$params 参数即为实际值。如果 $where 没有使用占位符，此参数可省略。
     * @return DB 返回当前类实例对象，可以用于链式操作
     */
    public function where($where, $params = [])
    {
        if (!is_null($where) && $where !== '' && $where !== false) {
            $this->where = $where;
        } else {
            $this->where = null;
        }
        $this->whereParams = $params;
        $this->actualWhere = $this->mapParams($where, $params);
        return $this;
    }

    /**
     * 匹配 SQL 中的参数，生成匹配后的 SQL
     *
     * @param string $string 要匹配的字符串
     * @param array $params 匹配入字符串中的参数数组
     * @return string 匹配后的 SQL
     */
    private function mapParams($string, $params)
    {
        if (!is_numeric(array_key_first($params))) {
            uksort($params, function ($prev, $next) {
                $prevLen = mb_strlen($prev);
                $nextLen = mb_strlen($next);
                if ($prevLen > $nextLen) {
                    return -1;
                } else if ($prevLen < $nextLen) {
                    return 1;
                }
                return 0;
            });
        }
        foreach ($params as $key => $value) {
            $replaceValue = "'" . $value . "'";
            $count = 1;
            if (is_numeric($key)) {
                $string = str_replace('?', $replaceValue, $string, $count);
            } else {
                $string = str_replace($key, $replaceValue, $string, $count);
            }
        }
        return $string;
    }

    /**
     * 指定要操作的表名
     * 参数说明与示例：
     *   $table 参数为表名字符串，与 SQL 中的表名部分对应，语法也相同。
     *   示例：$db->table('user, userinfo, `order`');
     * @param  string $table 表名列表与 SQL 中的表名部分对应，语法也相同。

     * @return DB 返回当前类实例对象，可以用于链式操作
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }


    /**
     * 指定表间关联
     *
     * 参数说明与示例：
     *   $join 参数与 SELECT 语句中 JOIN 子句对应，语法也相同。
     *   由于连接有多种模式（内联，左联，右联），本方法无法预先确定使用的是哪种联接方式，只能人为指定，且多表连接时 join ... on ... 需要在语句中多次指定。
     *   因此与 DB 类中其他设置方法不同，join 方法的参数需要带有 [inner/left/right] join 和 on 关键字。（其它方法不需要带 where/order by/group by 等关键字）
     *   示例：$db->join('LEFT JOIN `order` ON `order`.`userid` = `user`.`id` JOIN `user_info` ON `user`.`id` = `userinfo`.`userid`);
     *
     * @param  string $join
     * @return DB 返回当前类实例对象，可以用于链式操作
     */
    public function join($join)
    {
        $this->join = $join;
        return $this;
    }

    /**
     * 向表中插入数据
     * 参数说明与示例：
     *   $vals 参数是要插入数据的 map 数组（关联数组），键对应要插入的字段，值对应该字段要插入的值。
     *   示例：$db->table('user')->insert(['username' => 'zhangsan', 'age' => 21, 'sex' => '女']);
     *   注意：
     *     本方法失败时返回 false，成功时可能返回自增主键（表中有自增主键），也可能返回0（表中无自增主键）。
     *     因此不能使用 if ($db->insert(....)) 或 false == $db->insert(...) 判断插入动作是否成功。
     *     在 PHP 中 0 是假值，仅在严格相等判断时，才与 false 有区别，要用 false === $db->insert(...) 作判断。
     * @param array 要插入数据的 map 数组（关联数组），键对应要插入的字段，值对应该字段要插入的值。
     * @return Boolean|int 如果插入失败，返回 false。插入成功，或者返回新插入行的自增主键（有自增主键），或者返回 0 （无自增主键）。
     */
    public function insert($vals)
    {
        $this->sql = "INSERT INTO " . $this->table . " (";
        $this->fields = array();
        $placeholders = array();
        $this->insertParams = array();
        foreach ($vals as $field => $value) {
            $this->fields[] = '`' . $field . '`';
            $placeholders[] = ':' . $field;
            $this->insertParams[':' . $field] = $value;
        }
        $this->sql .= implode(',', $this->fields) . ') VALUES(' . implode(',', $placeholders) . ')';

        $this->actualSql = $this->mapParams($this->sql, $this->insertParams);

        $this->sth = $this->dbh()->prepare($this->sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if (!$this->sth()->execute($this->insertParams) || $this->sth()->rowCount() < 1) {
            $this->catchError();
            return false;
        }
        return $this->dbh()->lastInsertId();
    }

    /**
     * 更新表中数据
     * 参数说明与示例：
     *   $vals 要更新的字段和值构成的关联数组，其中键为字段名，值为更新后的值
     *   示例：$db->table('user')->update(['update_time' => date('Y-m-d H:i:s')]);
     *   注意：
     *     1.如果是在原字段值的基础上进行修改，类似 UPDATE `user` SET `price` = `price` + 1 一类，无法使用本方法，可以使用本类的 exec 方法，直接执行 SQL。
     *     2.本方法失败返回 false，成功返回影响行数，但是在没有行的值更改的情况下，返回的 0,不属于更新失败，需要根据业务决定是否区别对待。
     *       如若区别对待，不能直接用 if ($db->update(....)) 或 false == $db->update(...) 进行判断。
     *       因为 PHP 中 0 是假值，仅在严格相等判断时，才与 false 有区别，要用 false === $db->update(...) 作判断。
     * @param array $vals 要更新的字段和值构成的关联数组，其中键为字段名，值为更新后的值
     * @return Boolean|int 更新失败返回 false，成功返回影响行数。
     */
    public function update($vals)
    {
        $this->sql = "UPDATE " . $this->table . " SET ";
        if (is_array($vals)) {
            $replaces = array();
            $this->updateParams = array();
            foreach ($vals as $field => $value) {
                $placeholder = array_key_exists(':' . $field, $this->whereParams) ? ':update_' . $field : ':' . $field;
                $replaces[] = '`' . $field . '` = ' . $placeholder;
                $this->updateParams['' . $placeholder] = $value;
            }
            $this->sql .= implode(',', $replaces);
        } else {
            $this->sql .= ' ' . $vals;
        }
        $this->sql .=  ' WHERE ' . $this->where;

        $this->actualSql = $this->mapParams($this->sql, $this->combineUpdateParams());

        $this->sth = $this->dbh()->prepare($this->sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$this->sth()->execute($this->combineUpdateParams())) {
            $this->catchError();
            return false;
        }
        $row = $this->sth()->rowCount();
        return $row;
    }

    /**
     * 拼装update的参数
     *
     * @return array 拼装后的参数数组
     */
    private function combineUpdateParams()
    {
        return $this->updateParams + $this->whereParams;
    }

    /**
     * 删除
     * delete() 方法没有参数，删除条件来自于 where() 方法。
     * 示例：$db->where('id = ?', [35])->delete();
     * 本方法失败返回 false，成功返回影响行数，但是在没有符合条件的行的被删除的情况下，返回的 0,不属于更新失败，需要根据业务决定是否区别对待。
     *   如若区别对待，不能直接用 if ($db->delete(....)) 或 false == $db->delete(...) 进行判断。
     *   因为 PHP 中 0 是假值，仅在严格相等判断时，才与 false 有区别，要用 false === $db->delete(...) 作判断。
     * @return 失败返回 false，成功返回影响行数。
     */
    public function delete()
    {
        $this->sql = "DELETE FROM " . $this->table;
        if (!is_null($this->where)) {
            $this->sql .= ' WHERE ' . $this->where;
        }
        $this->actualSql = $this->mapParams($this->sql, $this->whereParams);

        $this->sth = $this->dbh()->prepare($this->sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$this->sth()->execute($this->whereParams)) {
            $this->catchError();
            return false;
        }
        $row = $this->sth()->rowCount();
        return $row;
    }

    /**
     * 获取一行
     * selectOne() 方法没有参数，查询条件来自于 where() 方法。
     * 示例：$db->table('user')->where('id = :userid', [':userid' => $userid])->selectOne();
     * 返回一个索引为结果集列名和以0开始的列号的数组，例如仅查询一个 username 字段，结果为 [0 => 'xxx', 'username' => 'xxx']
     *
     * @param null|int $fetchMode
     * @return array 一个索引为结果集列名和以0开始的列号的数组
     */
    public function selectOne($fetchMode = null)
    {
        $this->sql = $this->generateBaseSql();

        $this->actualSql = $this->mapParams($this->sql, $this->combineSelectParams());

        $this->sth = $this->dbh()->prepare($this->sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$this->sth()->execute($this->combineSelectParams())) {
            $this->catchError();
            return false;
        }
        return $this->sth()->fetch($this->getFetchMode($fetchMode));
    }

    /**
     * 指定要获取数据的 limit 子句内容
     *
     * @param int|string $rowCount 要获取的行数
     * @param int|string $offset 起始位置，不指定则为 0
     * @return DB
     */
    public function limit($rowCount, $offset = 0)
    {
        $this->limit = [
            'offset' => $offset,
            'rowCount' => $rowCount
        ];
        return $this;
    }

    /**
     * 获取结果集
     * select() 方法没有参数，查询条件来自于 where() 方法。
     * 示例：$db->table('user')->where('status = :status', [':status' => 'DISABLED'])->select();
     *
     * @param null|int $fetchMode
     * @return array 返回一个包含结果集中所有符合条件行的数组。该数组的每一行为一个索引为列名和以0开始的列号的数组。
     */
    public function select($fetchMode = null)
    {
        $this->sql = $this->generateBaseSql();

        if (null !== $this->limit) {
            $this->sql .= ' LIMIT ' . $this->limit['offset'] . ', ' . $this->limit['rowCount'];
        }

        $this->actualSql = $this->mapParams($this->sql, $this->combineSelectParams());

        $this->sth = $this->dbh()->prepare($this->sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if (!$this->sth()->execute($this->combineSelectParams())) {
            $this->catchError();
            return false;
        }
        return $this->sth()->fetchAll($this->getFetchMode($fetchMode));
    }

    /**
     * 组装查询参数
     *
     * @return array
     */
    private function combineSelectParams()
    {
        return $this->whereParams + $this->havingParams;
    }

    /**
     * 获取一行中的指定列
     * 参数说明与示例：
     *   要获取的列在 fields() 方法中指定的列中的序号，从 0 开始计，例如：
     *   $db->table('user')->fields('id, username, age')->selectColumn(1);
     *   获取的即是 username。
     * @param int $index 要获取的列在 fields() 方法中指定的列中的序号，从 0 开始计
     * @return Mixted 返回值类型与列类型相关
     */
    public function selectColumn($index)
    {
        $this->sql = $this->generateBaseSql();

        $this->actualSql = $this->mapParams($this->sql, $this->combineSelectParams());

        $this->sth = $this->dbh()->prepare($this->sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if (!$this->sth()->execute($this->combineSelectParams())) {
            $this->catchError();
            return false;
        }
        return $this->sth()->fetchColumn($index);
    }

    /**
     * 设置分页时每页的行数
     * 示例参见 selectPage() 方法
     * @see \Lib\DB::selectPage()
     * @param Imteger $pageSize 每页的行数
     * @return DB 返回当前类实例对象，可以用于链式操作
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    /**
     * 分页获取数据
     * 示例：
     *   分页查询用户列表，每面15条，查询第3页
     *   $db->table('user')->setPageSize(15)->selectPage(3);
     * @param int $page 要获取的数据的页码
     * @return array 返回一个包含结果集中所有符合条件行的数组。该数组的每一行为一个索引为列名和以0开始的列号的数组。
     */
    public function selectPage($page = 1, $fetchMode = null)
    {
        $this->page = $page;

        if ($this->group) {
            $this->sql = $this->generateBaseSql(' SQL_CALC_FOUND_ROWS ' . $this->getFields());
        } else {
            $this->sql = $this->generateBaseSql();
        }

        $offset = ($this->page - 1) * $this->pageSize;
        $rowCount = $this->pageSize;
        $this->sql .= ' LIMIT ' . $offset . ', ' . $rowCount;

        $this->actualSql = $this->mapParams($this->sql, $this->combineSelectParams());

        $this->sth = $this->dbh()->prepare($this->sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if (!$this->sth()->execute($this->combineSelectParams())) {
            $this->catchError();
            return false;
        }
        $res = $this->sth()->fetchAll($this->getFetchMode($fetchMode));
        $this->count();
        $this->calcPages();
        return $res;
    }

    /**
     * 计算符合条件的结果集的总行数
     * 示例：
     *   计算状态为可用的用户的总数
     *   $db->table('user')->where("status = 'ENABLED'")->count();
     * @return int 结果集总行数
     */
    public function count()
    {
        $this->count = 0;
        if ($this->group) {
            $countSql = 'SELECT FOUND_ROWS()';
        } else {
            $countSql = $this->generateBaseSql('count(*) total');
        }

        $this->sth = $this->dbh()->prepare($countSql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if (!$this->sth()->execute($this->combineSelectParams())) {
            $this->catchError();
            return $this;
        };
        $this->count = $this->sth()->fetchColumn(0);
        return $this;
    }

    /**
     * 计算总页数
     * 示例：
     *   计算状态为可用的用户列表总页数，按每页15行计算
     *   $db->table('user')->where("status = 'ENABLED'")->setPageSize(15)->calcPages();
     * @return int 符合条件的用户列表总页数
     */
    public function calcPages()
    {
        $this->totalPages = ceil($this->count / $this->pageSize);
        return $this->totalPages;
    }

    /**
     * 获取总页数
     * 示例：
     *   如果已经使用 calcPages() 方法或 selectPage() 方法，可以直接使用 totalPages() 方法获取总页数。
     *   $db->table('user')->where("status = 'ENABLED'")->setPageSize(15)->selectPage(3);
     *   $db->totalPages();
     * @return int 总页数
     */
    public function totalPages()
    {
        return $this->totalPages;
    }

    /**
     * 获取当前页码
     * 示例：
     *   $db->table('user')->where("status = 'ENABLED'")->setPageSize(15)->selectPage(3);
     *   $db->page();
     * @return int 当前页码
     */
    public function page()
    {
        return $this->page;
    }

    /**
     * 获取结果集总行数
     * 示例：
     *   $db->table('user')->where("status = 'ENABLED'")->setPageSize(15)->selectPage(3);
     *   $db->totalPages();
     * @return int 结果集总行数
     */
    public function totalRows()
    {
        return $this->count;
    }

    /**
     * 获取每页的行数设置
     * 示例：
     *   $db->table('user')->setPageSize(15);
     *   $db->pageSize();
     * @return int 每页的行数设置
     */
    public function pageSize()
    {
        return $this->pageSize;
    }


    /**
     * 获取前一页页码
     * 示例：
     *   $db->table('user')->where("status = 'ENABLED'")->setPageSize(15)->selectPage(3);
     *   $db->prev();
     * @return int 前一页页码
     */
    public function prev()
    {
        if ($this->page == 1) {
            return $this->page;
        } else {
            return $this->page - 1;
        }
    }

    /**
     * 获取后一页页码
     * 示例：
     *   $db->table('user')->where("status = 'ENABLED'")->setPageSize(15)->selectPage(3);
     *   $db->next();
     * @return int 后一页页码
     */
    public function next()
    {
        if ($this->page == $this->totalPages) {
            return $this->page;
        } else {
            return $this->page + 1;
        }
    }

    /**
     * 返回除分页数据集外的所有分页信息
     * 包含：
     *  [
     *      'totalRows' => 1309, // 总条数
     *      'totalPages' => 7, // 总页数
     *      'currentPage' => 2, // 当前页页码
     *      'prevPage' => 1, // 上一页页码
     *      'nextPage' => 3, // 下一页页码
     *  ]
     *
     * @return array 除分页数据集外的所有分页信息
     */
    public function pagerationInfo()
    {
        $res = [
            'totalRows' => $this->totalRows(), // 总条数
            'totalPages' => $this->totalPages(), // 总页数
            'currentPage' => $this->page(), // 当前页页码
            'prevPage' => $this->prev(), // 上一页页码
            'nextPage' => $this->next(), // 下一页页码
        ];
        return $res;
    }

    /**
     * 设置查询分组
     * 参数示例与说明：
     *   $group 参数与 GROUP BY 子句的分组字符串部分对应，语法也一致。
     *   示例：
     *   $db->table('user')->fields('count(*) total, age')->group('age')->select();
     * @param string $group 分组规则，与 GROUP BY 子句的分组字符串部分对应，语法相同
     * @return DB 返回当前类实例对象，可以用于链式操作
     */
    public function group($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * 设置聚合条件
     * 参数示例与说明：
     *  $having 参数为聚合条件字符串，语法与 SQL 的 HAVING 子句基本相同。
     *   示例：$db->having('avg(price) > 30');
     *   与 HAVING 子名不同的是，$having 可以使用占位符。
     *   占位符分为两种： ? 点位符和由 : 开头的具名占位符。
     *   当使用占位符时，须使用 $params 参数为占位符提供具体的值。
     *
     *   对于 ? 占位符，$params 为数值索引数组，按索引次序依次替换占位符。
     *   示例：$db->having('avg(price) > ? and sum(amount) > ?', [100, 10000]); 对应的实际查询条件为 avg(price) > 100 and sum(amount) > 10000
     *
     *   对于具名占位符，$params 为关联数组，按数组元素的键替换相应占位符。(注意，具名占位符必须以 ： 开头)
     *   示例：$db->having('avg(price) > :avg and sum(amount) > :total', ['total' => 10000, 'avg' => 100]); 对应的实际查询条件为 avg(price) > 100 and sum(amount) > 10000
     * @param string $having 聚合规则，与 HAVING 子句的条件串部分对应，语法相同
     * @param $params array 如果 $having 使用了占位符，$params 参数即为实际值。如果 $having 没有使用占位符，此参数可省略。
     * @return DB 返回当前类实例对象，可以用于链式操作
     */
    public function having($having, $params = [])
    {
        if (!is_null($having) && $having !== '' && $having !== false) {
            $this->having = $having;
        } else {
            $this->having = null;
        }
        $this->havingParams = $params;
        return $this;
    }

    /**
     * 获取 Select 语句的提取列，如果没有特别指定，则默认为 *
     *
     * @return string fields 串
     */
    protected function getFields()
    {
        if (null !== $this->fields) {
            return $this->fields;
        } else {
            return '*';
        }
    }

    /**
     * 生成基础 SQL
     * @param $fields string 如果为 null，则取类实例的 fields 值
     * @return string
     */
    private function generateBaseSql($fields = null)
    {
        if (null == $fields) {
            $fields = $this->getFields();
        }

        $baseSql = 'SELECT ' . $fields . ' FROM ' . $this->table;

        if (!is_null($this->join)) {
            $baseSql .= ' ' . $this->join;
        }

        if (!is_null($this->where)) {
            $baseSql .= ' WHERE ' . $this->where;
        }

        if (!is_null($this->group)) {
            $baseSql .= ' GROUP BY ' . $this->group;
        }

        if (!is_null($this->having)) {
            $baseSql .= ' HAVING ' . $this->having;
        }

        if (!is_null($this->order)) {
            $baseSql .= ' ORDER BY ' . $this->order;
        }
        return $baseSql;
    }

    /**
     * 返回拼接后的 SQL 字符串，用于调试。
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * 返回匹配了参数的 SQL 字符串，用于调试
     *
     * @return void
     */
    public function getActualSql()
    {
        return $this->actualSql;
    }

    /**
     * 获取debug信息
     * 返回一个包含一条预处理语句包含的信息的字符串。
     * 提供正在使用的 SQL 查询、所用参数（Params）的数目、参数的清单、参数名、用一个整数表示的参数类型（paramtype）、键名或位置、值、以及在查询中的位置。
     *
     * @return string debug 信息
     */
    public function getDebugInfo()
    {
        if (null === $this->sth()) {
            return "请先调用执行方法，如insert()、update()、delete()或查询类方法。";
        }
        ob_start();
        if (!$this->sth()->debugDumpParams()) {
            ob_end_clean();
            return "底层数据库类获取debug信息失败";
        }
        $res = ob_get_clean();
        return $res;
    }

    /**
     * 启动事务
     * @return bool
     */
    public function beginTransaction()
    {
        $key = $this->getKey();
        if (isset(self::$transactions[$key]) && self::$transactions[$key]['count'] > 0) {
            self::$transactions[$key]['count']++;
            return true;
        } else {
            $res = $this->dbh()->beginTransaction();
            if (true === $res) {
                self::$transactions[$key] = [
                    'count' => 1,
                    'isRollback' => false,
                    'info' => ''
                ];
            } else {
                $this->catchError();
            }
            return $res;
        }
    }

    private function checkTransaction()
    {
        $key = $this->getKey();
        if (!isset(self::$transactions[$key])) {
            $this->setError([
                'errorCode' => 'HY001',
                'errorInfo' => [
                    'info' => '未使用 beginTransaction 开启事务'
                ]
            ]);
            return false;
        }

        if (self::$transactions[$key]['count'] <= 0) {
            $this->setError([
                'errorCode' => 'HY002',
                'errorInfo' => '事务配对错误'
            ]);
            return false;
        }
        return true;
    }

    /**
     * 提交事务
     * @return bool
     */
    public function commit()
    {
        $key = $this->getKey();
        if (false === $this->checkTransaction()) {
            return false;
        }
        self::$transactions[$key]['count']--;

        if (self::$transactions[$key]['isRollback']) {
            $this->setError([
                'errorCode' => 'HY002',
                'errorInfo' => [
                    'info' => self::$transactions[$key]['info']
                ]
            ]);
            return false;
        }
        if (self::$transactions[$key]['count'] > 0) {
            return true;
        } else if (0 === self::$transactions[$key]['count']) {
            $res = $this->dbh()->commit();
            if (false === $res) {
                $this->catchError();
            }
            return $res;
        }
    }

    /**
     * 回滚事务
     * @return bool
     */
    public function rollBack()
    {
        $key = $this->getKey();
        if (false === $this->checkTransaction()) {
            return false;
        }
        self::$transactions[$key]['count']--;
        $res = true;

        if (!self::$transactions[$key]['isRollback']) {
            self::$transactions[$key]['isRollback'] = true;
            self::$transactions[$key]['info'] = '事务已经在第 ' . self::$transactions[$key]['count'] . ' 层事件回滚';
        }

        if (0 === self::$transactions[$key]['count']) {
            $res = $this->dbh()->rollBack();
            if (false === $res) {
                $this->catchError();
            }
        }

        return $res;
    }

    /**
     * 直接执行 SQL
     * @param string $sql 要执行的 SQL
     * @return int
     */
    public function exec($sql)
    {
        $this->sql = $sql;
        $res = $this->dbh()->exec($sql);
        if (!$res) {
            $this->catchError();
        }
        return $res;
    }

    /**
     * 直接执行 SQL，并返回 PDOStatement 对象
     * @param string $sql
     * @return PDOStatement
     */
    public function query($sql)
    {
        $this->sql = $sql;
        $res = $this->dbh()->query($sql);
        if (!$res) {
            $this->catchError();
        }
        return $res;
    }

    /**
     * 获取最后插入的数据的id
     * @return string 最后插入数据有id
     */
    public function getLastInsertId()
    {
        return $this->dbh()->lastInsertId();
    }

    /**
     * 设置错误信息
     *
     * @param array $error 必须包含 errorCode 和 errorInfo ，否则会出错
     * @return void
     */
    private function setError($error)
    {
        $this->error = self::$lastError = $error;
    }

    /**
     * 在 SQL 执行出错时，捕获错误码和错误信息
     *
     * @return void
     */
    private function catchError()
    {
        if (null !== $this->sth() && $this->sth()->errorCode() !== '00000') {
            $this->setError([
                'errorCode' => $this->sth()->errorCode(),
                'errorInfo' => $this->sth()->errorInfo()
            ]);
        }
        if (null !== $this->dbh() && $this->dbh()->errorCode() !== '00000') {
            $this->setError([
                'errorCode' => $this->dbh()->errorCode(),
                'errorInfo' => $this->dbh()->errorInfo()
            ]);
        }
    }

    /**
     * 获取 SQL 执行错误
     * @return array
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 获取最后一次 SQL 的错误信息
     *
     * @return null|array
     */
    public static function getLastError()
    {
        return self::$lastError;
    }
}
