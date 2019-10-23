<?php

use DI\ContainerBuilder;
use Lib\Core\ErrorCode;
use Lib\Core\Response;
use Lib\Core\SafException;

global $startTime;
$startTime = floor(microtime(true) * 1000);
require_once dirname(__DIR__) . '/lib/Core/bootstrap.php';

/** API 路由开始 */
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
/** API 路由结束 */

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAnnotations(true);
$diConfigFile = isset(CONFIG['di_config']) ? CONFIG['di_config'] : dirname(__DIR__) . '/conf/di_config.php';
file_exists($diConfigFile) && $containerBuilder->addDefinitions($diConfigFile);
$container = $containerBuilder->build();

/** 参数校验开始 */
$request = $container->get('Lib\Core\Request');
$annotationReader = $container->get('Doctrine\Common\Annotations\SimpleAnnotationReader');
$annotationReader->addNamespace('Lib\Validations');
if (isset(CONFIG['validation_namespaces'])) {
    foreach (CONFIG['validation_namespaces'] as $validationNamespace) {
        $annotationReader->addNamespace($validationNamespace);
    }
}
$reflClass = new ReflectionClass($className);
$runMethod = $reflClass->getMethod('run');
$annotations = $annotationReader->getMethodAnnotations($runMethod);
foreach ($annotations as $annotation) {
    if (false === $annotation->check($request->getParams())) {
        $error = ErrorCode::PARAM_ERROR;
        $error['message'] = $annotation->getError()->message;
        SafException::throw($error);
    }
}
/** 参数校验结束 */

/** API 调用开始 */

$instance = $container->get($className);
$content = $instance->run($request->getParams());
$responseType = property_exists($instance, 'responseType') ? $instance->responseType : 'json';
$responseCode = property_exists($instance, 'errorCode') ? $instance->errorCode : null;
Response::response($content, $responseType, $responseCode);
/** API 调用结束 */
