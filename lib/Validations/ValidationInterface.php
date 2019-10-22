<?php

namespace Lib\Validations;

use Error;

/**
 * 校验类接口
 */
interface ValidationInterface
{
    /**
     * 校验方法
     *
     * @param array $params
     * @return bool
     */
    public function check(array $params);

    /**
     * 校验结果获取
     *
     * @return Error
     */
    public function getError();
}
