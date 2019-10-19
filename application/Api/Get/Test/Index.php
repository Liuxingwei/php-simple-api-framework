<?php

namespace Application\Api\Get\Test;

use Lib\Core\AbstractBaseApi;
use Lib\Core\DB;
use Lib\Core\ErrorCode;

class Index extends AbstractBaseApi
{
    public function run()
    {
        $db = DB::getInstance('t_user');
        $res = $db->where('user_id = 10000')
            ->selectOne();
        $result = ErrorCode::OK;
        $this->responseJson($result);
    }
}
