<?php

namespace Application\Api\Delete\Example;

use Lib\Core\ErrorCode;
use Lib\Core\Interfaces\BaseApi;

class Index implements BaseApi
{
    public function run(array $request)
    {
        $result = ErrorCode::OK;
        $result['description'] = "I'm a DELETE request.";
        return $result;
    }
}
