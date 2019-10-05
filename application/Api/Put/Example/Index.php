<?php

namespace Application\Api\Put\Example;

use Lib\Core\AbstractBaseApi;
use Lib\Core\ErrorCode;

class Index extends AbstractBaseApi
{
    public function run()
    {
        $result = ErrorCode::OK;
        $result['description'] = "I'm a PUT request.";
        $this->responseJson($result);
    }
}
