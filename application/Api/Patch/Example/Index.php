<?php

namespace Application\Api\Patch\Example;

use Lib\Core\BaseApiInterface;
use Lib\Core\ErrorCode;

class Index implements BaseApiInterface
{
    public function run(array $params)
    {
        $result = ErrorCode::OK;
        $result['description'] = "I'm a PATCH request.";
        return $result;
    }
}
