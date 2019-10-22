<?php

use DI\ContainerBuilder;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
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
AnnotationRegistry::registerLoader('class_exists');
$annotationReader = new SimpleAnnotationReader();
$annotationReader->addNamespace('Lib\Validations');
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
AnnotationRegistry::reset();
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAnnotations(true);
$diConfigFile = isset(CONFIG['di_config']) ? CONFIG['di_config'] : dirname(__DIR__) . '/conf/di_config.php';
file_exists($diConfigFile) && $containerBuilder->addDefinitions($diConfigFile);
$container = $containerBuilder->build();
$instance = $container->get($className);
$content = $instance->run($request->getParams());
$responseType = property_exists($instance, 'responseType') ? $instance->responseType : 'json';
$responseCode = property_exists($instance, 'errorCode') ? $instance->errorCode : null;
Response::response($content, $responseType, $responseCode);
