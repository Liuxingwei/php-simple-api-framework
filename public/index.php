<?php

use Lib\Core\ErrorCode;
use Lib\Core\Request;
use Lib\Core\Response;
use Lib\Core\SafException;

global $startTime;
$startTime = floor(microtime(true) * 1000);
require_once dirname(__DIR__) . '/lib/Core/bootstrap.php';
if (!isset($_SERVER['PATH_INFO'])) {
    echo 'Please access detail API.';
    exit(0);
}
$scriptPath = $_SERVER['PATH_INFO'];
$scriptArray = explode('/', $scriptPath);

foreach ($scriptArray as $key => $value) {
    $scriptArray[$key] = ucfirst($value);
}
$className = '\\Application\\Api\\' . ucfirst(strtolower($_SERVER['REQUEST_METHOD'])) . preg_replace_callback('|-(.)|', static function ($match) {
    return strtoupper($match[1]);
}, implode('\\', $scriptArray));
if (!class_exists($className) || (new ReflectionClass($className))->isAbstract() || !method_exists($className, 'run')) {
    SafException::throw(ErrorCode::mapError(ErrorCode::API_NOT_EXISTS, ['api' => $scriptPath]));
}
$request = new Request();
$instance = new $className;
$content = $instance->run($request->getParams());
$responseType = property_exists($instance, 'responseType') ? $instance->responseType : 'json';
$responseCode = property_exists($instance, 'errorCode') ? $instance->errorCode : null;
Response::response($content, $responseType, $responseCode);
