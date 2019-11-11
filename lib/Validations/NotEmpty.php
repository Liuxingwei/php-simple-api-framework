<?php

namespace Lib\Validations;

/**
 * 校验参数不得为空
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class NotEmpty extends AbstractValidation
{
    /**
     * 是否去首尾空格
     *
     * @var boolean
     */
    public $trim = false;

    /**
     * 校验方法
     *
     * @param array $params
     * @return bool
     */
    public function check(array $params)
    {
        if (key_exists($this->value, $params)) {
            if (true === $this->trim) {
                $value = trim($params[$this->value]);
            } else {
                $value = $params[$this->value];
            }
            if (empty($value)) {
                $this->err = $this->errCode->mapError($this->errCode->PARAM_MUST_NOT_EMPTY, ['param' => $this->value]);
                return false;
            }
        }
        return true;
    }
}
