<?php

namespace app\api\logic;

use think\Db;
use app\common\model\UserStudentRelation;
/**
 * 公共
 */
class CommonLogic extends ApiBaseLogic
{

	public function __construct() {
        parent::__construct();
    }

    /**
     * 添加学生关系
     * $user_id : 家长ID
     * $student_id 学生ID
     */
    public function addStudentRelation($user_id, $student_id)
    {
        $userStudentRelationModel = new UserStudentRelation();
        $data['user_id'] = $user_id;
        $data['student_id'] = $student_id;
        $data['relation_id'] = 1;
        $data['relation_name'] = '自己';
        $data['create_time'] = time();
        $insert = $userStudentRelationModel->insertOne($data);
        if (!$insert) {
            return V(0,'Registration failed code: 200001');
        }
        return V(1,'success');
    }
}