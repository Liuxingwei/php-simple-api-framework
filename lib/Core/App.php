<?php

namespace Lib\Core;

use DI\ContainerBuilder;
use DI\Container;
use Lib\Core\ErrorCode;
use Lib\Core\Response;
use Lib\Core\SafException;
use ReflectionClass;

class App
{
    /**
     * @var Container
     */
    static public $container;

    /**
     * Api 类的类名
     *
     * @var string
     */
    static public $className;

    /**
     * Request 实例
     *
     * @var Request
     */
    static public $request;

    /**
     * 错误消息对象
     *
     * @var ErrorCode
     */
    static public $errCode;

    static final public function run()
    {
        self::initContainer();
        self::initErrCode();
        self::route();
        self::validationParams();
        self::_run();
    }

    private static final function _run()
    {
        $instance = self::$container->get(self::$className);
        $content = $instance->run(self::$request->getParams());
        $responseType = property_exists($instance, 'responseType') ? $instance->responseType : 'json';
        $responseOption = property_exists($instance, 'errorCode') ? $instance->errorCode : null;
        $responseOption = property_exists($instance, 'headers') ? $instance->headers : $responseOption;
        Response::response($content, $responseType, $responseOption);
    }

    private static final function initContainer()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAnnotations(true);
        file_exists(ROOT . '/conf/di_config.php') && $containerBuilder->addDefinitions(ROOT . '/conf/di_config.php');
        if (isset(CONFIG['di_config'])) {
            if (is_string(CONFIG['di_config'])) {
                $containerBuilder->addDefinitions(CONFIG['di_config']);
            }
            if (is_array(CONFIG['di_config'])) {
                foreach (CONFIG['di_config'] as $definition) {
                    $containerBuilder->addDefinitions($definition);
                }
            }
        }
        self::$container = $containerBuilder->build();
    }

    private static final function initErrCode()
    {
        self::$errCode = self::$container->get(ErrorCode::class);
    }

    private static final function validationParams()
    {
        self::$request = self::$container->get('Lib\Core\Request');
        $annotationReader = self::$container->get('Doctrine\Common\Annotations\SimpleAnnotationReader');
        $annotationReader->addNamespace('Lib\Validations');
        if (isset(CONFIG['validation_namespaces'])) {
            foreach (CONFIG['validation_namespaces'] as $validationNamespace) {
                $annotationReader->addNamespace($validationNamespace);
            }
        }
        $reflClass = new ReflectionClass(self::$className);
        $runMethod = $reflClass->getMethod('run');
        $annotations = $annotationReader->getMethodAnnotations($runMethod);
        foreach ($annotations as $annotation) {
            self::$container->injectOn($annotation);
            if (false === $annotation->check(self::$request->getParams())) {
                SafException::throw($annotation->getError());
            }
        }
    }

    private static final function route()
    {
        if (!isset($_SERVER['PATH_INFO'])) {
            echo 'Please access detail API.';
            exit(0);
        }
        $scriptPath = $_SERVER['PATH_INFO'];
        $scriptArray = explode('/', $scriptPath);
        array_shift($scriptArray);

        if (isset(CONFIG['api_path'])) {
            $apiPath = explode('/', CONFIG['api_path']);
            for ($i = 0; $i < count($apiPath); $i++) {
                if (empty($apiPath[$i])) {
                    continue;
                }
                if (strtolower($apiPath[$i]) != strtolower($scriptArray[0])) {
                    SafException::throw(self::$errCode->API_PATH_ERROR);
                }
                array_shift($scriptArray);
            }
        }

        foreach ($scriptArray as $key => $value) {
            $scriptArray[$key] = ucfirst($value);
        }
        self::$className = '\\Application\\Api\\' . ucfirst(strtolower($_SERVER['REQUEST_METHOD'])) . '\\' . preg_replace_callback('|-(.)|', static function ($match) {
            return strtoupper($match[1]);
        }, implode('\\', $scriptArray));
        if (!class_exists(self::$className) || (new ReflectionClass(self::$className))->isAbstract() || !is_subclass_of(self::$className, 'Lib\Core\BaseApiInterface')) {
            SafException::throw(self::$errCode->mapError(self::$errCode->API_NOT_EXISTS, ['api' => $scriptPath]));
        }
    }
}
