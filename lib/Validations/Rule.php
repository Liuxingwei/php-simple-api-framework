<?php

namespace Lib\Validations;

use Lib\Validations\AbstractValidation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Rule extends AbstractValidation
{
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
                $code = (key_exists('code', $this->error)) ? $this->error['code'] : $this->errCode->PARAM_NOT_COMPLIANCE_RULE['code'];
                $message = (key_exists('message', $this->error))
                    ? $this->errCode->mapMsg($this->error['message'], ['param' => $params[$this->value]])
                    : $this->errCode->mapMsg($this->errCode->PARAM_NOT_COMPLIANCE_RULE['message'], ['param' => $params[$this->value]]);
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
