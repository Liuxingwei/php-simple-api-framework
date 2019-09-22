<?php
namespace Lib\Core;

abstract class AbstractBaseApi
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
        $this->httpMethodCheck();
        $this->mapParams();
        $this->init();
    }

    /**
     * 校验 HTTP METHOD 是否合法，是否与 API 定义的 HTTP METHOD 相符
     * 如果不合法或不相符，则直接异常跳出
     * 
     * @return void
     */
    private final function httpMethodCheck()
    {
        $httpMethod = \strtoupper($_SERVER['REQUEST_METHOD']);
        $exceptHttpMethod = \strtoupper($this->httpMethod);
        if ($httpMethod !== 'POST' && $httpMethod !== 'GET') {
            SafException::throw(ErrorCode::HTTP_METHOD_ERROR);
        }
        if ($httpMethod !== $exceptHttpMethod) {
            SafException::throw(ErrorCode::API_NOT_EXISTS);
        }
    }

    /**
     * 核心业务处理。
     * 所有 API 仅需实现该方法。
     * @return mixed
     */
    abstract public function run();

    /**
     * 参数初始化方法
     * 将参数匹配至 $httpParams。
     */
    private final function mapParams()
    {
        if ('POST' == \strtoupper($this->httpMethod)) {
            $this->httpParams = $_POST;
        } else {
            $this->httpParams = $_GET;
        }
        return true;
    }

    /**
     * 初始化钩子方法，用于在 __construct 中调用
     * 这里放置了一个默认的空函数实现，子函数可以 override 它，做一些自己的初始化工作
     *
     * @return void
     */
    protected function init() {
    }

    /**
     * 输出 json 结果
     *
     * @param array $result 待输出的数据
     * @param integer $httpStatus 
     * @return void
     */
    public final function responseJson($result, $httpStatus = 200) {
        Response::json($result, $httpStatus);
    }
}
