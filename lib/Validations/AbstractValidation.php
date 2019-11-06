<?php

namespace Lib\Validations;

use Lib\Core\ErrorCodeTrait;
use Lib\Validations\Utils\Error;

/**
 * 校验类接口
 */
abstract class AbstractValidation
{
    use ErrorCodeTrait;

    protected $err;

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
        return $this->err;
    }
}
