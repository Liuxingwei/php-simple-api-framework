<?php
namespace Lib\Core;

class Response
{
    public static function json($result, $status = 200) {
        $result = self::handlerDebugInfo($result);
        header('Content-type: application/json; charset=UTF-8', true, $status);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 计算运行时长（毫秒）
     * @param float $startTime
     * @return int
     */
    public static function calcRunDuring()
    {
        $endTime = ceil(microtime(true) * 1000);
        global $startTime;
        $during = $endTime - $startTime;
        return $during;
    }

    /**
     * 处理debug信息，在设置开启并提供了debug参数的情况下，为debug信息附加运行时长（毫秒）和主要的 request 信息（uri、get数组、post数组），并允许输出。
     * @param $result
     * @param int $during
     * @return mixed
     */
    private static function handlerDebugInfo($result)
    {
        if (!isset($_ENV['config']['debug']) || !$_ENV['config']['debug'] || !isset($_SERVER['HTTP_DEBUG'])) {
            if (isset($result['debug'])) {
                unset($result['debug']);
            }
        } else {
            $during = self::calcRunDuring();
            $request = [
                'uri' => $_SERVER['REQUEST_URI'],
                'GET' => $_GET,
                'POST' => $_POST,
                'SERVER' => $_SERVER,
            ];
            if (!isset($result['debug'])) {
                $result['debug'] = [
                    'millisecond' => $during,
                    'request' => $request,
                ];
            } else {
                $result['debug']['millisecond'] = $during;
                $result['debug']['request'] = $request;
            }
            $result['debug']['DBError'] = DB::getLastError();
        }
        return $result;
    }
}