<?php

namespace Lib\Core;

use BadMethodCallException;

class BaseModel
{
    /**
     * DB 实例
     *
     * @var DB
     */
    protected $db;

    /**
     * 初始化 DB 属性
     *
     * @Inject
     * @param DB $db
     * @return void
     */
    public function __construct(DB $db)
    {
        $this->db = $db;
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
