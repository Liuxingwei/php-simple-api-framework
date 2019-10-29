<?php

namespace Application\Model;

use Lib\Core\BaseModel;
use Lib\Core\DBFactory;

class SafExample extends BaseModel
{
    /**
     * 初始化数据库
     *
     * @Inject
     * @param DBFactory $dbFactory
     * @return void
     */
    public function __construct(DBFactory $dbFactory)
    {
        parent::__construct($dbFactory);
        $this->table('saf_example');
    }
}
