<?php
namespace Lib\Core;

class ErrorCode
{
    const OK = ['code' => 200, 'message' => 'OK'];
    const API_NOT_EXISTS = ['code' => 404, 'message' => 'API {{:api}} 不存在'];
    const HTTP_METHOD_ERROR = ['code' => 500, 'message' => '仅支持 POST 和 GET 提交'];

    public static function mapMsg($msg, $params) {
        foreach ($params as $key => $value) {
            $msg = preg_replace("/{{:$key}}/", $value, $msg);
        }
        return $msg;
    }

    public static function mapError($error, $params) {
        $error['message'] = self::mapMsg($error['message'], $params);
        return $error;
    }
}
