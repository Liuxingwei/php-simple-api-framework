<?php

namespace Application\Api\Post\Example;

use Lib\Core\ErrorCode;
use Lib\Core\Interfaces\BaseApi;

class Index implements BaseApi
{
    public function run(array $params)
    {
        $result = ErrorCode::OK;
        $result['description'] = "I'm a POST request.";
        return $result;
    }
}
