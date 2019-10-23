<?php

namespace Lib\Validations;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Length implements ValidationInterface
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
        if (key_exists($this->value, $params)) { 
            $value = $params[$this->value];
            $len = mb_strlen($value);
            if (null !== $this->max && $len > $this->max) {
                $this->error->code = '10003';
                $this->error->message = '参数 ' . $this->value . ' 不得长于 ' . $this->max;
                return false;
            }
            if (null !== $this->min && $len < $this->min) {
                $this->error->code = '10003';
                $this->error->message = '参数 ' . $this->value . ' 不得短于 ' . $this->min;
                return false;
            }
        }
        return true;
    }

    public function getError()
    {
        return $this->error;
    }
}