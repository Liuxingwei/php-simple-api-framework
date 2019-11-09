<?php

namespace Application\Api\Patch\Example;

use Lib\Core\BaseApiInterface;
use Lib\Core\ErrorCodeTrait;

class Index implements BaseApiInterface
{
    use ErrorCodeTrait;

    public function run(array $params)
    {
        $result = $this->errCode->OK;
        $result['description'] = "I'm a PATCH request.";
        return $result;
    }
}
