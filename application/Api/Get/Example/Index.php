<?php

namespace Application\Api\Get\Example;

use Lib\Core\AbstractBaseApi;
use Lib\Core\ErrorCode;

class Index extends AbstractBaseApi
{
    protected $httpMethod = 'GET';
    public function run()
    {
        $result = ErrorCode::OK;
        $result['description'] = "I'm a GET request.";
        $this->responseJson($result);
    }
}
