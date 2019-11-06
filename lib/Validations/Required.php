<?php

namespace Lib\Validations;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Required extends AbstractValidation
{
    /**
     * 校验规则
     *
     * @Required()
     * @var string
     */
    public $value;


    /**
     * 校验指定的参数是否存在
     *
     * @param array $params
     * @return bool
     */
    public function check(array $params)
    {
        if (!key_exists($this->value, $params)) {
            $this->err = $this->error->mapError($this->error->PARAM_MUST_EXISTS, ['param' => $this->value]);
            return false;
        }

        return true;
    }
}
