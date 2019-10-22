<?php

namespace Lib\Validations;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Required implements ValidationInterface
{
    /**
     * 校验规则
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

    /**
     * 校验指定的参数是否存在
     *
     * @param array $params
     * @return bool
     * @throws 
     */
    public function check($params)
    {
        if (!key_exists($this->value, $params)) {
            $this->error->code = '10001';
            $this->error->message = '参数 ' . $this->value . ' 必须';
            return false;
        }

        return true;
    }

    /**
     * 获取出错时的错误
     *
     * @return Error
     */
    public function getError()
    {
        return $this->error;
    }
}
