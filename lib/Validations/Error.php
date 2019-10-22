<?php

namespace Lib\Validations;

use InvalidArgumentException;

/**
 * 校验错误实体
 */
class Error
{
    /**
     * 错误编码
     *
     * @var int
     */
    private $code = null;

    /**
     * 错误消息
     *
     * @var string
     */
    private $message = null;

    /**
     * 为错误编码和错误消息赋值
     *
     * @param string $name
     * @param int|string $value
     * @throws InvalidArgumentException
     */
    public function __set($name, $value)
    {
        if (property_exists(__CLASS__, $name)) {
            $this->$name = $value;
        } else {
            throw InvalidArgumentException('Property "' . $name . '" not exists.');
        }
    }

    /**
     * 获取错误编码和错误消息
     *
     * @param string $name
     * @return int|string
     * @throws InvalidArgumentException
     */
    public function __get($name)
    {
        if (property_exists(__CLASS__, $name)) {
            return $this->$name;
        } else {
            throw InvalidArgumentException('Property "' . $name . '" not exsists.');
        }
    }
}
