<?php

namespace Application\Api\Delete\Example;

use Lib\Core\BaseApiInterface;
use Lib\Core\ErrorCode;

class Index implements BaseApiInterface
{
    public function run(array $request)
    {
        $result = ErrorCode::OK;
        $result['description'] = "I'm a DELETE request.";
        return $result;
    }
}
