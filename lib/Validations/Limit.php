<?php

namespace Lib\Validations;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Limit extends AbstractValidation
{
    /**
     * 上限
     *
     */
    public $max;

    /**
     * 下限
     *
     */
    public $min;

    /**
     * 校验方法
     *
     * @param array $params
     * @return bool
     */
    public function check(array $params)
    {
        if (key_exists($this->value, $params)) {
            $value = floatval($params[$this->value]);
            if (false === $value) {
                $this->err = $this->errCode->PARAM_MUST_IS_NUMBER;
                return false;
            }
            if (null !== $this->max && $value > $this->max) {
                $this->err = $this->errCode->mapError($this->errCode->PARAM_MUST_NOT_MORE_THAN, ['param' => $this->value, 'max' => $this->max]);
                return false;
            }
            if (null !== $this->min && $value < $this->min) {
                $this->err = $this->errCode->mapError($this->errCode->PARAM_MUST_NOT_LITTLE_THAN, ['param' => $this->value, 'min' => $this->min]);
                return false;
            }
        }
        return true;
    }
}
