<?php

namespace Lib\Core;

abstract class AbstractBaseApi
{

    /**
     * 用户的提交数据
     * 根据 $httpMethod 指定的方法提取相应的 HTTP 提交数据，则与 $_GET相同，否则与 $_POST 相同。
     *
     * @var array
     */
    protected $httpParams = [];

    public final function __construct()
    {
        $this->mapParams();
        $this->init();
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
        if ('GET' === $_SERVER['REQUEST_METHOD'] || 'DELETE' === $_SERVER['REQUEST_METHOD']) {
            $this->httpParams = $_GET;
        } else if (isset($_SERVER['CONTENT_TYPE']) && 'application/json' === $_SERVER['CONTENT_TYPE']) {
            $this->httpParams = json_decode(file_get_contents('php://input'), true);
        } else if ('POST' == $_SERVER['REQUEST_METHOD']) {
            $this->httpParams = $_POST;
        } else if (isset($_SERVER['CONTENT_TYPE']) && 'application/x-www-form-urlencoded' === $_SERVER['CONTENT_TYPE']) {
            $content = file_get_contents('php://input');
            $kvString = explode('&', $content);
            array_walk($kvString, function ($source) {
                $kv = explode('=', $source);
                $this->httpParams[$kv[0]] = $kv[1];
            });
        }
        $this->httpParams['BODY'] = file_get_contents('php://input');
        return true;
    }

    /**
     * 初始化钩子方法，用于在 __construct 中调用
     * 这里放置了一个默认的空函数实现，子函数可以 override 它，做一些自己的初始化工作
     *
     * @return void
     */
    protected function init()
    { }

    /**
     * 输出 json 结果
     *
     * @param array $result 待输出的数据
     * @param integer $httpStatus
     * @return void
     */
    public final function responseJson($result, $httpStatus = 200)
    {
        Response::json($result, $httpStatus);
    }
}
