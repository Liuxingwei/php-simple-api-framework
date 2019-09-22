<?php
namespace Application\Api\Example;

use Lib\Core\AbstractBaseApi;
use Lib\Core\ErrorCode;

abstract class Index extends AbstractBaseApi
{
    protected $httpMethod = 'GET';
    public function run() {
        $result = ErrorCode::OK;
        $this->responseJson($result);
    }
}

