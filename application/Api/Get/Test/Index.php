<?php

namespace Application\Api\Get\Test;

use Application\Model\SafExample;
use Lib\Core\BaseApiInterface;
use Lib\Core\BaseModel;
use Lib\Core\DB;
use Lib\Core\ErrorCode;
use Lib\Validations\Required;

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
     * Mode 示例
     *
     * @var BaseModel
     */
    private $model;

    /**
     * Model 示例
     *
     * @var SafExample
     */
    private $safExample;

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
     * 初始化 Model
     *
     * @Inject
     * @param BaseModel $model
     * @return void
     */
    public function setModel(BaseModel $model)
    {
        $this->model = $model;
    }

    /**
     * 初始化示例 Model
     *
     * @Inject
     * @param SafExample $safExample
     * @return void
     */
    public function setSafExample(SafExample $user)
    {
        $this->safExample = $user;
    }

    /**
     * 业务运行方法
     *
     * @Required("user_id")
     * @Limit("user_name", min=4, max=9.1)
     * @param array $request
     * @return void
     */
    public function run(array $request)
    {

        $this->model->table('user');
        $this->db->table('user');
        $res = $this->db->where('id = 1')
            ->selectOne();
        $this->secondDb->table('task_user701_step10002_prod');
        $user = $this->secondDb->select();
        $result = ErrorCode::OK;
        $result['data'] = [
            'user_info' => $res,
            'user' => $user,
        ];
        return $result;
    }
}
