<?php
use Lib\Core\ErrorCode;
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
$className = preg_replace_callback('|_(.)|', static function ($match) {
    return strtoupper($match[1]);
}, implode('\\', $scriptArray));
if (!class_exists($className)) {
    SafException::throw(ErrorCode::mapError(ErrorCode::API_NOT_EXISTS, ['api' => $scriptPath]));
}
$instance = new $className;
$instance->run();