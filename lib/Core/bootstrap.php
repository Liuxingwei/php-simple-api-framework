<?php
require_once __DIR__ . '/../../vendor/autoload.php';
$configFile = __DIR__ . '/../../conf/config.php';
if (file_exists($configFile)) {
    $_ENV['config'] = include __DIR__ . '/../../conf/config.php';
} else {
    $_ENV['config'] = [];
}
if ($_ENV['config']['runtime'] != 'product') {
    header("Access-Control-Allow-Origin: http://localhost:8080");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
    header('Access-Control-Allow-Headers: x-requested-with, content-type, key, debug');
    header('Access-Control-Allow-Credentials: true');
}
if (!isset($_ENV['config']['runtime']) || 'debug' != $_ENV['config']['runtime']) {
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
                ]
            ]
        ];
        \Lib\Core\Response::json($response, $ex->getCode());
    });
}
