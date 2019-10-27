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
     * 待验证的参数名
     *
     * @Required()
     * @var string
     */
    public $value;

    /**
     * 校验方法
     *
     * @param array $params
     * @return bool
     */
    public function check(array $params)
    {
        if (key_exists($this->value, $params) && empty($params[$this->value])) {
            $this->error->code = '10002';
            $this->error->message = '参数 ' . $this->value . ' 不得为空';
            return false;
        }

        return true;
    }
}
