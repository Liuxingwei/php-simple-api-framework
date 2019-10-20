<?php

namespace Application\Api\Get\Test;

use Lib\Core\BaseApiInterface;
use Lib\Core\DB;
use Lib\Core\ErrorCode;
use Application\Validation;

class Index implements BaseApiInterface
{
    /**
     * 默认数据库
     *
     * @var DB
     */
    private $db;

    /**
     * 第二个数据库
     *
     * @var DB
     */
    private $secondDb;

    /**
     * 设置数据库
     *
     * @Inject
     * @param DB $db
     * @return void
     */
    public function setDb(DB $db)
    {
        $this->db = $db;
    }

    /**
     * 设置第二个数据库
     *
     * @Inject({"second_db"})
     * @param DB $db
     * @return void
     */
    public function setSecondDb($db)
    {
        $this->secondDb = $db;
    }

    /**
     * Undocumented function
     *
     * @Required({"abc", "def"})
     * @param array $request
     * @return void
     */
    public function run(array $request)
    {
        $this->db->table('t_user');
        $res = $this->db->where('user_id = 1')
            ->selectOne();
        $this->secondDb->table('users');
        $user = $this->secondDb->where('user_id = :user_id', [':user_id' => 'admin'])
            ->selectOne();
        $result = ErrorCode::OK;
        $result['data'] = [
            'user_info' => $res,
            'user' => $user,
        ];
        return $result;
    }
}
