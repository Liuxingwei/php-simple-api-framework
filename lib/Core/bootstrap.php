<?php
defined('ROOT') || define('ROOT', dirname(dirname(__DIR__)));
require_once ROOT . '/vendor/autoload.php';
$configFile = ROOT . '/conf/config.php';
$_ENV['config'] = file_exists($configFile) ? include $configFile : [];
if (file_exists(ROOT . '/conf/env.php')) {
    $env = include ROOT . '/conf/env.php';
    $_ENV['config'] = array_replace_recursive($_ENV['config'], $env);
}
defined('CONFIG') || define('CONFIG', $_ENV['config']);
$defaultTimezone = isset(CONFIG['default_timezone']) ? CONFIG['default_timezone'] : 'Asia/Shanghai';
date_default_timezone_set($defaultTimezone);
crossDomain: (function () {
    if (isset(CONFIG['cross_domain'])) {
        $crossDomain = CONFIG['cross_domain'];
        if (!isset($crossDomain['enable']) || !$crossDomain['enable']) {
            return;
        }
        if (isset($crossDomain['domain'])) {
            if ('*' === $crossDomain['domain']) {
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $url = parse_url($_SERVER['HTTP_REFERER']);
                    $domain = $url['scheme'] . '://' . $url['host'];
                    isset($url['port']) && $domain .= ':' . $url['port'];
                } else {
                    $domain = '*';
                }
            } else {
                $domain = $crossDomain['domain'];
            }
        } else {
            $domain = '*';
        }
        header("Access-Control-Allow-Origin: $domain");
        $methods = isset($crossDomain['methods']) ? $crossDomain['methods'] : 'POST, GET, OPTIONS, PUT, DELETE';
        header("Access-Control-Allow-Methods: $methods");
        $headers = isset($crossDomain['headers']) ? implode(', ', array_unique(explode(',', str_replace(' ', '', $crossDomain['headers'] . ', x-requested-with, content-type, debug')))) : 'x-requested-with, content-type, debug';
        header("Access-Control-Allow-Headers: $headers");
        header('Access-Control-Allow-Credentials: true');
    }
})();
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 500, $errno, $errfile, $errline);
});
set_exception_handler(function (Throwable $ex) {
    header('Content-type: application/json; charset=UTF-8');
    $response = [
        'code' => $ex->getCode(),
        'message' => $ex->getMessage(),
        'debug' => [
            'exception' => [
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ],
        ],
    ];
    $trace = json_encode($ex->getTrace(), JSON_UNESCAPED_UNICODE, 1024);
    if (JSON_ERROR_NONE === json_last_error()) {
        $response['debug']['exception']['trace'] = $ex->getTrace();
    } else {
        $response['debug']['exception']['trace'] = $ex->getTraceAsString();
    }

    $httpCodes = [
        "100",
        "101",
        "200",
        "202",
        "203",
        "204",
        "205",
        "206",
        "300",
        "301",
        "302",
        "303",
        "304",
        "305",
        "307",
        "400",
        "401",
        "402",
        "403",
        "404",
        "405",
        "406",
        "407",
        "408",
        "409",
        "410",
        "411",
        "412",
        "413",
        "414",
        "415",
        "416",
        "417",
        "500",
        "501",
        "502",
        "503",
        "504",
        "505"
    ];
    if (is_a($ex, '\Lib\Core\SafException') && in_array($ex->getCode(), $httpCodes)) {
        $httpCode = $ex->getCode();
    } else {
        $httpCode = 500;
    }
    \Lib\Core\Response::json($response, $httpCode);
});
