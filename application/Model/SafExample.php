<?php

namespace Application\Model;

use Lib\Core\BaseModel;
use Lib\Core\DB;

class SafExample extends BaseModel
{
    /**
     * 初始化数据库
     *
     * @Inject
     * @param DB $db
     * @return void
     */
    public function __construct(DB $db)
    {
        parent::__construct($db);
        $this->table('saf_example');
    }
}
