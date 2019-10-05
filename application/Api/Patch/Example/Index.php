<?php

namespace Application\Api\Patch\Example;

use Lib\Core\AbstractBaseApi;
use Lib\Core\ErrorCode;

class Index extends AbstractBaseApi
{
    public function run()
    {
        $result = ErrorCode::OK;
        $result['description'] = "I'm a PATCH request.";
        $this->responseJson($result);
    }
}
