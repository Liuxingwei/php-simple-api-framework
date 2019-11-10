<?php

namespace Application\Validations;

use Lib\Validations\AbstractValidation;

/**
 * 利用正则校验参数是否符合规则
 * @Annotation
 * @Target({"METHOD"})
 */
class MyValidation extends AbstractValidation
{
    /**
     * 要校验的参数名
     * @Required()
     * @var string
     */
    public $value;

    /**
     * 出错时的自定义消息
     * @var array
     */
    public $error = null;

    /**
     * 校验用的正则表达式
     * @var string
     */
    public $rule;

    public function check(array $params)
    {
        if (key_exists($this->value, $params)) { // 判断要校验的参数在给出的参数中是否存在，存在才需要校验
            if (preg_match('/' . $this->rule . '/', $params[$this->value])) { // 用给定的正则进行匹配，成功返回 true
                return true;
            } else { // 失败对 $this->err 进行设置，并返回 false
                $code = (key_exists('code', $this->error)) ? $this->error['code'] : 10086;
                $message = (key_exists('message', $this->error)) ? $this->error['message'] : '参数格式不符合要求';
                $this->err = [
                    'code' => $code,
                    'message' => $message
                ];
                return false;
            }
        } else { // 要校验的参数不存在，无需校验直接返回 true
            return true;
        }
    }
}
