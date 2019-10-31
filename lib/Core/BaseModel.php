<?php

namespace Lib\Core;

use BadMethodCallException;

/**
 * @Scope
 */
class BaseModel
{
    /**
     * DB 实例
     *
     * @var DB
     */
    protected $db;

    /**
     * 表名
     *
     * @var string
     */
    protected $table;

    /**
     * 初始化 DB 属性
     *
     * @Inject
     * @param DBFactory $db
     * @return void
     */
    public function __construct(DBFactory $dBFactory)
    {
        $this->db = $dBFactory->create();
        if ($this->table) {
            $this->table($this->table);
        }
    }

    public function __call($name, $arguments)
    {
        if ($this->db && method_exists($this->db, $name)) {
            call_user_func_array(array($this->db, $name), $arguments);
        } else {
            throw new BadMethodCallException('Call to undefined method ' . get_class($this) . '::' . $name . '()');
        }
    }
}
