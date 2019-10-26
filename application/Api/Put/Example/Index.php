<?php

namespace Application\Api\Put\Example;

use Lib\Core\ErrorCode;
use Lib\Core\Interfaces\BaseApi;

class Index implements BaseApi
{
    public function run(array $params)
    {
        $result = ErrorCode::OK;
        $result['description'] = "I'm a PUT request.";
        return $result;
    }
}
