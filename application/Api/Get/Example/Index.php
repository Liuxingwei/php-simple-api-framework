<?php

namespace Application\Api\Get\Example;

use Lib\Core\BaseApiInterface;
use Lib\Core\ErrorCodeTrait;

class Index implements BaseApiInterface
{
    use ErrorCodeTrait;

    public function run(array $params)
    {
        $result = $this->error->OK;
        $result['description'] = "I'm a GET request.";
        return $result;
    }
}
