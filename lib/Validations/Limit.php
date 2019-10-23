<?php

namespace Lib\Validations;

use Doctrine\Common\Annotations\AnnotationException;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Limit implements ValidationInterface
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
     */
    public $max;

    /**
     * 下限
     *
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
            $value = floatval($params[$this->value]);
            if (false === $value) {
                $this->error->code = '10004';
                $this->error->message = '参数 ' . $this->value . ' 必须是数字';
                return false;
            }
            if (null !== $this->max && $value > $this->max) {
                $this->error->code = '10004';
                $this->error->message = '参数 ' . $this->value . ' 不得大于 ' . $this->max;
                return false;
            }
            if (null !== $this->min && $value < $this->min) {
                $this->error->code = '10004';
                $this->error->message = '参数 ' . $this->value . ' 不得小于 ' . $this->min;
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
