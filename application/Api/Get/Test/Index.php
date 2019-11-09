<?php

namespace Application\Api\Get\Test;

use Application\Model\SafExample;
use Lib\Core\BaseApiInterface;
use Lib\Core\BaseModel;
use Lib\Core\DB;
use Lib\Core\ErrorCodeTrait;
use Lib\Validations\Required;

class Index implements BaseApiInterface
{
    use ErrorCodeTrait;

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

    private $secondDb1;

    /**
     * Mode 示例
     *
     * @var BaseModel
     */
    private $model;

    private $model1;

    /**
     * Model 示例
     *
     * @var SafExample
     */
    private $safExample;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $safExample1;

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
    public function setSecondDb(DB $db)
    {
        $this->secondDb = $db;
    }

    /**
     * Undocumented function
     *
     * @Inject({"second_db"})
     * @param DB $db
     * @return void
     */
    public function setSecondDb1(DB $db)
    {
        $this->secondDb1 = $db;
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
     * Undocumented function
     *
     * @Inject
     * @param BaseModel $model
     * @return void
     */
    public function setModel1(BaseModel $model)
    {
        $this->model1 = $model;
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
     * Undocumented function
     *
     * @Inject
     * @param SafExample $safExample
     * @return void
     */
    public function setSafExample1(SafExample $safExample)
    {
        $this->safExample1 = $safExample;
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
        $this->db->table('good');
        $res = $this->db->where('id = 1')
            ->selectOne();
        $this->secondDb->table('task_user701_step10002_prod');
        $user = $this->secondDb->select();
        $this->safExample->table('hello');
        $result = $this->errCode->OK;
        $result['data'] = [
            'user_info' => $res,
            'user' => $user,
        ];
        return $result;
    }
}
