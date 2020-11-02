<?php

namespace app\api\logic;

use think\Db;
use app\common\model\ClassModel;
use app\common\model\ClassTeacher;
use app\common\controller\EndroidQrCode;
/**
 * 班级
 */
class ClassLogic extends ApiBaseLogic
{

	public function __construct() {
        parent::__construct();
    }

    /**
     * 班级详情
     */
    public function getClassDetails($class_id)
    {
        $classModel = new ClassModel();
        $where['id'] = $class_id;
        $field = "id, class_num, class_name, class_photo, subject_name, school_name, num, qr_code, inform_num";
        $info = $classModel->getInfo($where, $field);
        return $info;
    }

    /**
     * 我加入的班级
     */
    public function listMyClass($user_id)
    {
        $classModel = new ClassModel();
        $classTeacherModel = new ClassTeacher();
        $where['user_id'] = $user_id;
        $where['is_del'] = 1;
        $where['is_exit'] = 1;
        $field = "user_id, class_id, authority_status, class_teacher_name, subject_name";
        $list = $classTeacherModel->getList($where,$field);
        if (empty($list)) {
            return [];
        }
        $my_class = [];
        $join_class = [];
        foreach ($list as $key => $val) {
            $class_where['id'] = $val['class_id'];
            $class_field = "class_name, class_photo, inform_num";
            $class_info = $classModel->getInfo($class_where, $class_field);
            $val['class_name'] = $class_info['class_name'];
            $val['class_photo'] = $class_info['class_photo'];
            if ($val['authority_status'] != 1) {
                $val['inform_num'] = $class_info['inform_num'];
            } else {
                $val['inform_num'] = 0;    
            }
            if ($val['authority_status'] == 3) {
                $my_class[$key] = $val;
            } else {
                $join_class[$key] = $val;
            }
            
        }
        $data['my_class'] = array_values($my_class);
        $data['join_class'] = array_values($join_class);
        return $data;
    }

    /**
     * 创建班级
     * $user_name: 老师名称
     */
    public function createClass($data, $user_name)
    {
        Db::startTrans();
        $classModel = new ClassModel();
        $add = $classModel->insertOne($data);
        if (!$add) {
            return V(0,'Failed to create class');
        }
        //增加班级老师数据
        $classTeacherModel = new ClassTeacher();
        $teacher_data['user_id'] = $data['user_id'];
        $teacher_data['class_id'] = $add;
        $teacher_data['authority_status'] = 3;
        $teacher_data['subject_name'] = $data['subject_name'];
        $teacher_data['class_teacher_name'] = $user_name;
        $teacher_data['create_time'] = time();
        $add_teacher = $classTeacherModel->insertOne($teacher_data);
        if (!$add_teacher) {
            Db::rollback();
            return V(0,'Failed to create class');
        }
        //生成班级唯一编号
        $class_num = $this->createRandom();
        if (!$class_num) {
            Db::rollback();
            return V(0,'Failed to create class');
        }
        //生成二维码
        $QrCode = new EndroidQrCode($class_num);
        $qr = $QrCode->createQrCode($class_num);
        if ($qr['status'] == 0) {
            Db::rollback();
            return $qr;
        }
        
        //更新班级二维码
        $where['id'] = $add;
        $update['qr_code'] = $qr['data'];
        $update['class_num'] = $class_num;
        $save_qr = $classModel->updateInfo($where, $update);
        if (!$save_qr) {
            Db::rollback();
            return V(0,'Class QR code generation failed. Please re-create class');
        }
        Db::commit();
        $class_data['id'] = $add;
        $class_data['qr_code'] = $qr['data'];
        return V(1,'success', $class_data);
    }

    /**
     * 生成唯一编码
     */
    public function createRandom($count=0)
    {
        ++$count;

        $classModel = new ClassModel();
        $num = createRandom(10);
        //查询编码是否重复
        $where['class_num'] = $num;
        $user_count = $classModel->getCount($where);
        if ($user_count) {
            if ($count > 20) {
                return false;
            }
            return $this->createRandom($count);
        } else {
            return $num;
        }
    }


    /**
     * 修改班级信息
     */
    public function updateClassInfo($data, $class_id)
    {
        $classModel = new ClassModel();
        $where['id'] = $class_id;
        $update = $classModel->updateInfo($where, $data);
        if ($update) {
            return V(1,'success');
        } else {
            return V(0,'error');
        }
    }
}

























