<?php

namespace Lib\Validations;

/**
 * 校验参数不得为空
 * 
 * @Annotation
 * @Target({"METHOD"})
 */
class NotEmpty implements ValidationInterface
{
    /**
     * 待验证的参数名
     * 
     * @Required()
     * @var string
     */
    public $value;

    /**
     * 错误消息
     *
     * @var Error
     */
    private $error;

    public function __construct()
    {
        $this->error = new Error();
    }

    public function check($params)
    {
        if (key_exists($this->value, $params) && empty($params[$this->value])) {
            $this->error->code = '10002';
            $this->error->message = '参数 ' . $this->value . ' 不得为空';
            return false;
        }

        return true;
    }

    public function getError()
    {
        return $this->error;
    }
}
