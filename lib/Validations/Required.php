<?php

namespace Lib\Validations;

use Lib\Validations\Classes\AbstractValidation;

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
            $this->error->code = '10001';
            $this->error->message = '参数 ' . $this->value . ' 必须';
            return false;
        }

        return true;
    }
}
