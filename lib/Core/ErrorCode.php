<?php

namespace Lib\Core;

/**
 * 错误消息类
 */
class ErrorCode
{
    const OK = ['code' => 200, 'message' => 'OK'];
    const API_NOT_EXISTS = ['code' => 404, 'message' => 'API {{:api}} 不存在'];
    const HTTP_METHOD_ERROR = ['code' => 500, 'message' => '仅支持 POST 和 GET 提交'];
    const PARAM_ERROR = ['code' => 400, 'message' => '参数错误'];
    const API_PATH_ERROR = ['code' => 500, 'message' => 'API 路径错误'];

    /**
     * 替换消息字符串中的参数
     *
     * @param string $msg
     * @param array $params
     * @return string
     */
    public static function mapMsg($msg, $params)
    {
        foreach ($params as $key => $value) {
            $msg = preg_replace("/{{:$key}}/", $value, $msg);
        }
        return $msg;
    }

    /**
     * 生成可输出的 ErrorCode
     *
     * @param array $error
     * @param array $params
     * @return array
     */
    public static function mapError($error, $params)
    {
        $error['message'] = self::mapMsg($error['message'], $params);
        return $error;
    }
}
