<?php
namespace Application\Example;

use Lib\Core\AbstractBaseApi;
use Lib\Core\ErrorCode;

class Index extends AbstractBaseApi
{
    protected $httpMethod = 'GET';
    public function run() {
        $result = ErrorCode::OK;
        $this->responseJson($result);
    }
}

