<?php

namespace Application\Api\Get\Example;

use Lib\Core\BaseApiInterface;
use Lib\Core\ErrorCode;

class Index implements BaseApiInterface
{
    public function run(array $params)
    {
        $result = ErrorCode::OK;
        $result['description'] = "I'm a GET request.";
        return $result;
    }
}
