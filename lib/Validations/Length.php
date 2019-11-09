<?php

namespace Lib\Validations;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Length extends AbstractValidation
{
    /**
     *
     * @Required()
     * @var string
     */
    public $value;
    /**
     * 上限
     *
     * @var int
     */
    public $max;

    /**
     * 下限
     *
     * @var int
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
            $value = $params[$this->value];
            $len = mb_strlen($value);
            if (null !== $this->max && $len > $this->max) {
                $this->err = $this->errCode->mapError($this->errCode->PARAM_MUST_NOT_LONG_THAN, ['param' => $this->value, 'max' => $this->max]);
                return false;
            }
            if (null !== $this->min && $len < $this->min) {
                $this->err = $this->errCode->mapError($this->errCode->PARAM_MUST_NOT_SHORT_THAN, ['param' => $this->value, 'min' => $this->min]);
                return false;
            }
        }
        return true;
    }
}
