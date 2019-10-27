<?php

namespace Lib\Validations;

use Lib\Validations\Utils\Error;

/**
 * 校验类接口
 */
abstract class AbstractValidation
{
    /**
     * 错误消息
     *
     * @var Error
     */
    protected $error;

    /**
     * 错误信息初始化
     *
     * @param Error $error
     * @return void
     */
    public function setError(Error $error)
    {
        $this->error = $error;
    }

    public function __construct()
    {
        $error = new Error();
        $this->setError($error);
    }

    /**
     * 校验方法
     *
     * @param array $params
     * @return bool
     */
    abstract public function check(array $params);

    /**
     * 校验结果获取
     *
     * @return Error
     */
    public function getError()
    {
        return $this->error;
    }
}
