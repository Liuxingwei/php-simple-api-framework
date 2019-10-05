<?php

namespace Application\Api\Delete\Example;

use Lib\Core\AbstractBaseApi;
use Lib\Core\ErrorCode;

class Index extends AbstractBaseApi
{
    public function run()
    {
        $result = ErrorCode::OK;
        $result['description'] = "I'm a DELETE request.";
        $this->responseJson($result);
    }
}
