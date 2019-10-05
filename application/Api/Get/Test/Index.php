<?php

namespace Application\Api\Get\Test;

use Lib\Core\AbstractBaseApi;
use Lib\Core\DB;

class Index extends AbstractBaseApi
{
    public function run()
    {
        $db = DB::getInstance('user');
    }
}
