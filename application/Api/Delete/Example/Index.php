<?php

namespace Application\Api\Delete\Example;

use Lib\Core\BaseApiInterface;
use Lib\Core\ErrorCodeTrait;

class Index implements BaseApiInterface
{
    use ErrorCodeTrait;

    public function run(array $request)
    {
        $result = $this->error->OK;
        $result['description'] = "I'm a DELETE request.";
        return $result;
    }
}
