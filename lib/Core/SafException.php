<?php

namespace Lib\Core;

use Exception;

/**
 * 自定义异常类，用于抛出符合json格式，可用于框架输出的消息
 */
class SafException extends Exception
{
    /**
     * 抛出框架定义的错误
     *
     * @param array $errorCode 需要包含 code 和 message 元素。
     * @return void
     */
    public static function throw($errorCode)
    {
        throw new SafException($errorCode['message'], $errorCode['code']);
    }
}
