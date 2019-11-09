<?php

namespace Application\Api\Post\Example;

use Lib\Core\BaseApiInterface;
use Lib\Core\ErrorCodeTrait;

class Index implements BaseApiInterface
{
    use ErrorCodeTrait;

    public function run(array $params)
    {
        $result = $this->errCode->OK;
        $result['description'] = "I'm a POST request.";
        return $result;
    }
}
