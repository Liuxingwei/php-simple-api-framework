<?php

namespace Lib\Core;

/**
 * 错误消息类
 */
class ErrorCode
{
    private $err;

    private $language = 'cn';

    public function __construct($defineDir)
    {
        $this->err = require_once $defineDir . '/default.php';
        $defineFile = $defineDir . '/' . $this->language . '.php';
        if (file_exists($defineFile)) {
            $define = require $defineFile;
            $this->err = array_replace_recursive($this->err, $define);
        }
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function __get($name)
    {
        if (isset($this->err[$name])) {
            return $this->err[$name];
        }
    }

    /**
     * 替换消息字符串中的参数，返回替换后的消息
     *
     * @param string $msg
     * @param array $params
     * @return string
     */
    public function mapMsg($msg, $params)
    {
        foreach ($params as $key => $value) {
            $msg = preg_replace("/{{:$key}}/", $value, $msg);
        }
        return $msg;
    }

    /**
     * 生成可输出的 ErrorCode 数组
     *
     * @param array $error
     * @param array $params
     * @return array
     */
    public function mapError($error, $params)
    {
        $error['message'] = $this->mapMsg($error['message'], $params);
        return $error;
    }
}