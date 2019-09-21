<?php
namespace Lib\Core;

use Exception;

abstract class AbstractBaseApi implements BaseApiInterface
{
    /**
     * 表示 HTTP METHOD 的字符串
     * 由于 PHP 的限制，本框架仅支持 GET 和 POST 提交。因此 $httpMethod 变量仅接受 GET 和 POST，不区分大小写
     *
     * @var string
     */
    protected $httpMethod;

    /**
     * 用户的提交数据
     * 根据 $httpMethod 指定的方法提取相应的 HTTP 提交数据，则与 $_GET相同，否则与 $_POST 相同。
     * 
     * @var array
     */
    protected $httpParams;

    public final function __construct()
    {
        $this->init();
    }

    /**
     * 核心业务处理。
     * 所有 API 仅需实现该方法。
     * @return mixed
     */
    abstract public function run();

    /**
     * API初始化方法
     * 对提交方式进行校验，并在通过校验后，将参数匹配至 $httpParams。
     */
    protected function init()
    {
        $httpMethod = \strtoupper($_SERVER['REQUEST_METHOD']);
        $exceptHttpMethod = \strtoupper($this->httpMethod);
        if ($httpMethod !== 'POST' && $httpMethod !== 'GET') {
            SafException::throw(ErrorCode::HTTP_METHOD_ERROR);
        }
        if ($httpMethod !== $exceptHttpMethod) {
            SafException::throw(ErrorCode::API_NOT_EXISTS);
        }
        if ('POST' == $httpMethod) {
            $this->httpParams = $_POST;
        } else {
            $this->httpParams = $_GET;
        }
        return true;
    }

    public final function responseJson($result, $httpStatus = 200) {
        Response::json($result, $httpStatus);
    }
}
