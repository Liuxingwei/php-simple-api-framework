<?php

namespace Lib\Core;

class Response
{
    public static function json($result, $status = 200)
    {
        $result = self::handlerDebugInfo($result);
        header('Content-type: application/json; charset=UTF-8', true, $status);
        $res = json_encode($result, JSON_UNESCAPED_UNICODE, 1024);
        if (json_last_error() === JSON_ERROR_UTF8) {
            $result['message'] = iconv('GBK', 'UTF-8', $result['message']);
            $res = json_encode($result, JSON_UNESCAPED_UNICODE, 1024);
        }
        if (json_last_error() !== JSON_ERROR_NONE) {
            $res = json_encode(['code' => 500, 'message' => 'json 解析失败：' . json_last_error() . '; ' . json_last_error_msg()], JSON_UNESCAPED_UNICODE);
        }
        echo $res;
    }

    public static function html($result)
    {
        echo $result;
    }

    public static function xml($result, $status = 200)
    {
        $result = self::handlerDebugInfo($result);
        header('Content-Type: application/xml; charset=UTF-8', true, $status);
        $xml = self::arrayToXml($result);
        echo $xml;
    }

    public static function text($result)
    {
        header('Content-Type: text/plain; charset=UTF-8', true, 200);
        echo $result;
    }

    public static function javascript($result)
    {
        header('Content-Type: application/javascript; charset=UTF-8', true, 200);
        echo $result;
    }

    public static function x_javascript($result)
    {
        header('Content-Type: application/x-javascript; charset=UTF-8', true, 200);
        echo $result;
    }

    public static function stream($result, $headers)
    {
        foreach ($headers as $header) {
            header($header);
        }
        echo $result;
    }

    private static function arrayToXml(array $arr)
    {
        $xml = '<?xml version="1.0" encoding="utf-8" ?>';
        $xml .= self::arrayToXmlSnippet($arr);
        return $xml;
    }

    private static function arrayToXmlSnippet(array $arr)
    {
        $xml = '';
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
            } else if (is_array($val)) {
                $xml .= '<' . $key . '>' . self::arryToXmlSnippet($val) . '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
                // $xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
            }
        }
        return $xml;
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
        if (!isset(CONFIG['debug']) || !CONFIG['debug'] || !isset($_SERVER['HTTP_DEBUG'])) {
            if (isset($result['debug'])) {
                unset($result['debug']);
            }
        } else {
            $during = self::calcRunDuring();
            $request = [
                'URI' => $_SERVER['REQUEST_URI'],
                'GET' => $_GET,
                'POST' => $_POST,
                'SERVER' => $_SERVER,
                'RAW' => file_get_contents('php://input'),
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

    public static function response($content, $type = 'json', $option = null)
    {
        return call_user_func(array(__CLASS__, $type), $content, $option);
    }
}
