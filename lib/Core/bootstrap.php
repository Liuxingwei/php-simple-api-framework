<?php
require_once __DIR__ . '/../../vendor/autoload.php';
$configFile = __DIR__ . '/../../conf/config.php';
if (file_exists($configFile)) {
    $_ENV['config'] = include __DIR__ . '/../../conf/config.php';
} else {
    $_ENV['config'] = [];
}
if (isset($_ENV['config']['runtime']) && $_ENV['config']['runtime'] != 'product') {
    header("Access-Control-Allow-Origin: http://localhost:8080");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
    header('Access-Control-Allow-Headers: x-requested-with, content-type, key, debug');
    header('Access-Control-Allow-Credentials: true');
}
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
                'trace' => $ex->getTrace(),
            ],
        ],
    ];

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
    if (in_array($ex->getCode(), $httpCodes)) {
        $httpCode = $ex->getCode();
    } else {
        $httpCode = 500;
    }
    \Lib\Core\Response::json($response, $httpCode);
});
