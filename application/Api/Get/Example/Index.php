<?php

namespace Application\Api\Get\Example;

use Lib\Core\ErrorCode;
use Lib\Core\Interfaces\BaseApi;

class Index implements BaseApi
{
    public function run(array $params)
    {
        $result = ErrorCode::OK;
        $result['description'] = "I'm a GET request.";
        return $result;
    }
}
