<?php
namespace Application\Example;

use Lib\AbstractBaseApi;
use Lib\ErrorCode;

class Index extends AbstractBaseApi
{
    protected $httpMethod = 'GET';
    public function run() {
        $result = ErrorCode::OK;
        $this->responseJson($result);
    }
}
