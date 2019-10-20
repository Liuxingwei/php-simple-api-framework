<?php

namespace Lib\Core;

class Request
{
    private $httpParams;

    public function __construct()
    {
        $this->mapParams();
    }

    /**
     * 参数初始化方法
     * 将参数匹配至 $httpParams。
     */
    public function mapParams()
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

    public function getParams()
    {
        return $this->httpParams;
    }
}
