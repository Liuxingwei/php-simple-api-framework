<?php

namespace Lib\Validations;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Required extends AbstractValidation
{
    /**
     * 校验指定的参数是否存在
     *
     * @param array $params
     * @return bool
     */
    public function check(array $params)
    {
        if (!key_exists($this->value, $params)) {
            $this->err = $this->errCode->mapError($this->errCode->PARAM_MUST_EXISTS, ['param' => $this->value]);
            return false;
        }

        return true;
    }
}
