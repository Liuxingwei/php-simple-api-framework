<?php

namespace Application\Api\Post\Example;

use Lib\Core\AbstractBaseApi;
use Lib\Core\ErrorCode;

class Index extends AbstractBaseApi
{
    public function run()
    {
        $result = ErrorCode::OK;
        $result['description'] = "I'm a POST request.";
        $this->responseJson($result);
    }
}
