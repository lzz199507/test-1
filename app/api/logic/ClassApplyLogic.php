<?php

namespace app\api\logic;

use think\Db;
use app\common\model\User;
use app\common\model\ClassModel;
use app\common\model\ClassApply;
use app\common\model\ClassTeacher;
use app\common\model\ClassUser;
use app\common\model\UserStudent;
use app\common\model\UserStudentRelation;
use app\common\model\UserInform;
use app\common\model\ClassSearchLog;
use app\common\model\MessagesUser;
/**
 * 班级申请
 */
class ClassApplyLogic extends ApiBaseLogic
{

	public function __construct() {
        parent::__construct();
    }

    /**
     * 班级搜索
     */
    public function listSearchClass($search_data, $user_id)
    {
        $classModel = new ClassModel();
        $classTeacher = new classTeacher();
        $classSearchLog = new ClassSearchLog();
        $list = [];
        //检测是否是邮箱
        if (checkEmail($search_data)) {
            $type = 1;
            //是
            $where['user_email'] = $search_data;
            $teacher_class_ids = $classTeacher->getColumnList($where, 'class_id');
            if ($teacher_class_ids) {
                $class_where['id'] = array("in",$teacher_class_ids);
                $class_field = "id, class_name";
                $list = $classModel->getList($class_where, $class_field);
            }
        } else {
            $type = 2;
            //否
            $where['class_num'] = $search_data;
            $field = "id, class_name";
            $list = $classModel->getList($where,$field);
        }

        if (!empty($list)) {
            $classTeacherModel = new ClassTeacher();
            foreach ($list as $key => $val) {
                $teacher_where['class_id'] = $val['id'];
                $teacher_where['authority_status'] = 3;
                $teacher_name = $classTeacherModel->where($teacher_where)->value("class_teacher_name");
                $list[$key]['teacher_name'] = $teacher_name;
            }
        }
        $search_log['user_id'] = $user_id;
        $search_log['search_data'] = $search_data;
        $search_log['type'] = $type;
        $search_log['create_time'] = time();
        $classSearchLog->insertOne($search_log);
        return V(1,'success', $list);
    }


    /**
     * 申请加入班级
     * $user_type 1:学生 2: 家长 3:老师
     */
    public function insertApplyJoinClass($data, $user_id, $user_type)
    {
        $classModel = new ClassModel();
        $classApplyModel = new ClassApply();
        $classUserModel = new ClassUser();
        $classTeacherModel = new ClassTeacher();
        if ($user_type == 2) {
            return V(0,'Parents cannot apply');
        }

         //检测是否已在班级里
        $class_count_where['user_id'] = $user_id;
        $class_count_where['class_id'] = $data['class_id'];
        $class_count_where['is_exit'] = 1;
        if ($user_type == 3) {
            $is_class = $classTeacherModel->getCount($class_count_where);
        } else {
            $is_class = $classUserModel->getCount($class_count_where);
        }
        if ($is_class) {
            return V(0,'You are already in class');
        }
        $add_data['user_id'] = $user_id;
        $add_data['class_id'] = $data['class_id'];
        $add_data['type'] = $user_type;
        if ($user_type == 3) {
            //增加教师
            $add_data['status'] = 1;
        } else if($user_type == 1){
            //增加学生
            // $add_data['num'] = $data['num'];
            $add_data['status'] = 1;
        } else {
            //增加家长(不使用, 2020/10/15)
            $add_data['status'] = 1;
        }

        Db::startTrans();

        $add_data['is_exit'] = 1;
        $add_data['create_time'] = time();
        $add = $classApplyModel->insertOne($add_data);
        if ($add) {
            if (checkEmail($data['search_data'])) {
                //邮箱
                //增加 班级申请数量
                $class_where['id'] = $data['class_id'];
                $classModel->where($class_where)->setInc("inform_num",1);
            } else {
                //班级号
                if ($user_type == 1 || $user_type == 3) {
                    //加入班级 教师或学生
                    $join_class = $this->joinClass($data['class_id'], $user_id, $user_type, $add);
                    if ($join_class) {
                        //发送消息
                        $inform_data['send_id'] = 0;
                        $inform_data['user_id'] = $user_id;
                        $inform_data['type'] = 2;
                        $inform_data['message_content'] = 'success';
                        $inform_data['create_time'] = time();
                        $this->addMessage($inform_data, $user_id, 2);
                    } else {
                        Db::rollback();
                        return V(0,'error');
                    }
                }
            }
            if ($user_type == 1) {
                //邀请家长
                $invite_data['class_id'] = $data['class_id'];
                $invite_data['user_id'] = $user_id;
                $this->invitePatriarch($invite_data);
            }
            Db::commit();  
            return V(1,'success');
        } else {
            Db::rollback();
            return V(0,'error');
        }
    }

    /**
     * 邀请家长
     * class_id：班级ID
     * user_id：用户ID
     */
    public function invitePatriarch($data)
    {
        $userStudentRelationModel = new UserStudentRelation();
        $classApplyModel = new ClassApply();
        //查询 家长ID
        $patriarch_where['student_id'] = $data['user_id'];
        $patriarch_where['is_del'] = 1;
        $patriarch_field = "user_id, relation_name";
        $patriarch_order = "is_default desc";
        $patriarch_info = $userStudentRelationModel->getInfo($patriarch_where, $patriarch_field, $patriarch_order);
        if (empty($patriarch_info)) {
            return true;
        }
        $invite_data['user_id'] = $patriarch_info['user_id'];
        $invite_data['class_id'] = $data['class_id'];
        $invite_data['student_id'] = $data['user_id'];
        $invite_data['type'] = 2;
        $invite_data['relation_name'] = $patriarch_info['relation_name'];
        $invite_data['apply_type'] = 2;
        $invite_data['create_time'] = time();
        //发送邀请
        $send_invite = $classApplyModel->insertOne($invite_data);
        if ($send_invite) {
            $inform_data['send_id'] = 0;
            $inform_data['user_id'] = $patriarch_info['user_id'];
            $inform_data['type'] = 2;
            $inform_data['message_content'] = 'Invite to class';
            $inform_data['create_time'] = time();
            $this->addMessage($inform_data, $patriarch_info['user_id'], 2);
        }
        return $send_invite;
    }

    /**
     * 获取申请列表
     * @param $is_exit 1:进入 2：退出
     */
    public function listClassApply($class_id, $is_exit, $p)
    {
        $classApplyModel = new ClassApply();
        if ($is_exit == 1) {
            $where['a.type'] = array("in","1,3");
        } else {
            $where['a.type'] = array("in","1,2,3");
        }
        $where['a.is_exit'] = $is_exit;
        $where['a.class_id'] = $class_id;

        $where['a.status'] = 1;
        $field = "a.id,a.user_id, a.type, a.relation_name, u.nick_name, u.head_picture";
        $list = $classApplyModel->alias("a")
                                ->where($where)
                                ->join("qy_user u","a.user_id = u.id")
                                ->field($field)
                                ->page($p, 10)
                                ->select();
        foreach ($list as $key => $val) {
            if ($val['type'] == 1) {
                //学生
                $list[$key]['type_name'] = 'student';
            } else if ($val['type'] == 2) {
                //家长 
                $list[$key]['type_name'] = $val['relation_name'];
            } else {
                $list[$key]['type_name'] = 'teacher';
            }
        }
        return $list;
    }

    /**
     * 处理班级申请
     * @param  $class_id    班级ID
     * @param  $ids   申请表ID 多个ID 用逗号 分割
     * @param  $type  1:同意 2:拒绝
     */
    public function disposeClassApply($class_id, $ids, $type)
    {
        $classApplyModel = new ClassApply();
        $ids = explode(",", $ids);
        $where['id'] = array("in", $ids);
        $where['class_id'] = $class_id;
        $where['status'] = 1;
        if ($type == 1) {
            //同意
            Db::startTrans();
            $field = "id, user_id, type, num, student_id, relation_name, is_exit";
            $list = $classApplyModel->where($where)->field($field)->select();
            if (empty($list)) {
                return V(1,'success');
            }
            $inform_data = [];
            $apply_ids = [];
            foreach ($list as $key => $val) {
                $inform_ids[$key] = $val['user_id'];
                $apply_ids[$key] = $val['id'];
                $inform_data[$key]['send_id'] = 0;
                $inform_data[$key]['user_id'] = $val['user_id'];
                $inform_data[$key]['create_time'] = time();
                $inform_data[$key]['message_content'] = "empty data";
                if ($val['is_exit'] == 1) {
                    //进入班级
                    $info = $this->joinClass($class_id, $val['user_id'], $val['type'], $val['id'],$val['student_id'], $val['relation_name']);
                    $inform_data[$key]['message_content'] = "Join the class successfully";
                } else {
                    //退出班级
                    $info = $this->exitClass($class_id, $val['user_id'], $val['type'], $val['id']);
                    $inform_data[$key]['message_content'] = "Quit the class successfully";
                }
                if (!$info) {
                    break;
                }
            }
            if ($info) {
                $this->addMessage($inform_data, $inform_ids);
                //更新状态
                $apply_where['id'] = array("in", $apply_ids);
                $apply_update['status'] = 2;
                $apply_update['operation_id'] = UID;
                $classApplyModel->where($apply_where)->update($apply_update);
                Db::commit(); 
                return V(1,'success');
            } else {
                Db::rollback();
                return V(0,'error -1');
            }
        } else {
            //拒绝
            $ids = $classApplyModel->getColumnList($where,'user_id');
            foreach ($ids as $key => $val) {
                $inform_data['send_id'] = 0;
                $inform_data['user_id'] = $val;
                $inform_data['type'] = 2;
                $inform_data['message_content'] = 'turn down apply';
                $inform_data['create_time'] = time();
            }
            $this->addMessage($inform_data, $ids);
            $apply_update['status'] = 3;
            $apply_update['reject_content'] = 'turn down apply';
            $apply_update['operation_id'] = UID;
            $save_apply = $classApplyModel->updateInfo($where, $apply_update);
            if ($save_apply) {
                return V(1,'success');
            } else {
                return V(0,'error');
            }
        }
    }
    /**
     * 加入班级
     * @param $class_id 班级ID
     * @param $user_id 用户ID
     * @param $type 1:学生 2: 家长 3:老师
     * @param $apply_id: 申请表ID
     * @param $student_id 学生ID （家长使用）
     * @param $relation_name  关系名称 (家长使用)
     */
    public function joinClass($class_id, $user_id, $type, $apply_id, $student_id=0, $relation_name='')
    {
        if ($type == 3) {
            $userModel = new User();
            $classTeacher = new ClassTeacher();
            //老师
            $data['user_id'] = $user_id;
            $data['class_id'] = $class_id;
            $data['authority_status'] = 1;
            $class_teacher_name = $userModel->where(['id'=>$user_id])->value("nick_name");
            $data['class_teacher_name'] = $class_teacher_name;
            $data['create_time'] = time();
            $info = $classTeacher->insert($data);
        } else {
            $classUserModel = new ClassUser();
            $userStudentModel = new UserStudent();
            $userStudentRelationModel = new UserStudentRelation();
            //学生/家长
            if ($type == 1) {
                //学生
                $data[0]['class_id'] = $class_id;
                $data[0]['user_id'] = $user_id;
                $data[0]['type'] = 1;
                $data[0]['student_id'] = 0;
                $data[0]['relation_name'] = 0;
                $data[0]['create_time'] = time();
                $s_where['student_id'] = $user_id;
                // $s_order = "is_default desc";
                // $s_field = "user_id, relation_name";
                // $student_relation = $userStudentRelationModel->where($s_where)->order($s_order)->field($s_field)->find();
                // $data[1]['class_id'] = $class_id;
                // $data[1]['user_id'] = $student_relation['user_id'];
                // $data[1]['type'] = 2;
                // $data[1]['student_id'] = $user_id;
                // $data[1]['relation_name'] = $student_relation['relation_name'];
                // $data[1]['create_time'] = time();
                $info = $classUserModel->insertAll($data);
            } else {
                // $s_where['user_id'] = $user_id;
                // $s_where['student_id'] = $student_id;
                // $s_field = "relation_name";
                // $student_relation = $userStudentRelationModel->where($s_where)->order($s_order)->field($s_field)->find();
                // //查询关系是否存在, 如果不存在新增
                // if (empty($student_relation)) {
                //     //获取关系名称
                //     $relation_name = $student_relation['relation_name'];
                //     //新增关系
                //     $student_relation_data['user_id'] = $user_id;
                //     $student_relation_data['student_id'] = $student_id;
                //     $student_relation_data['relation_name'] = $relation_name;
                //     $student_relation_data['create_time'] = time();
                //     $userStudentRelationModel->insert($student_relation_data);
                // } else {
                //     if ($student_relation['relation_name'] != $relation_name) {
                //         //修改关系
                //         $student_relation_update['relation_name'] = $relation_name;
                //         $student_relation_where['user_id'] = $user_id;
                //         $student_relation_where['student_id'] = $student_id;
                //         $userStudentRelationModel->where($student_relation_where)->update($student_relation_update);
                //     }
                // }
                //家长
                $data['class_id'] = $class_id;
                $data['user_id'] = $user_id;
                $data['type'] = 2;
                $data['student_id'] = $student_id;
                $data['relation_name'] = $relation_name;
                $data['create_time'] = time();
                $info = $classUserModel->insert($data);
            }
        }
        if ($info) {
            $classApplyModel = new ClassApply();
            //修改申请表状态
            $apply_where['id'] = $apply_id;
            $apply_update['status'] = 2;
            $apply_update['operation_id'] = UID;
            $save_apply = $classApplyModel->updateInfo($apply_where, $apply_update);
            return $save_apply;
        }
        return $info;
    }
    /**
     * 退出班级
     * @param $class_id 班级ID
     * @param $user_id 用户ID
     * @param $type 1:学生 2:教师 3: 家长
     */
    public function exitClass($class_id, $user_id, $type)
    {
        $where['user_id'] = $user_id;
        $where['class_id'] = $class_id;
        $update['is_exit'] = 0;
        $update['exit_time'] = time();
        $update['remove_user_id'] = UID;
        $update['remove_content'] = '退出';
        if ($type == 2) {
            $classTeacher = new ClassTeacher();
            //老师
            $info = $classTeacher->where($where)->update($update);
        } else {
            $classUserModel = new ClassUser();
            //学生或家长
            if ($type == 1) {
                //学生退出
                //获取学生家长并退出
                $patriarch_where['student_id'] = $user_id;
                $patriarch_ids = $classUserModel->where($patriarch_where)->column("user_id");
                array_push($patriarch_ids, $user_id);
                $where['user_id'] = array("in",$patriarch_ids);
                $this->updateClassNum($user_id, 2);
            }
            $info = $classUserModel->where($where)->update($update);
        }
        return $info;
    }

    /**
     * 更新学生加入班级数量
     * @param $user_id 学生ID
     * @param $type 1:增加 2:减少
     */
    public function updateClassNum($user_id, $type)
    {
        $userStudentModel = new UserStudent();
        $where['user_id'] = $user_id;
        if ($type == 1) {
            $update = $userStudentModel->where($where)->setInc("class_num",1);
        } else {
            $update = $userStudentModel->where($where)->setDec("class_num",1);
        }
        return $update;
    }

    /**
     * 增加消息通知
     */
    public function addMessage($data, $inform_ids, $type=1)
    {
        $userModel = new User();
        $MessagesUserModel = new MessagesUser();
        if ($type == 1) {
            //增加消息
            $add_inform = $MessagesUserModel->insertAll($data);
            //增加用户消息数量
            $user_where['id'] = array("in",$inform_ids);
        } else {
            //增加消息
            $add_inform = $MessagesUserModel->insert($data);
            $user_where['id'] = $inform_ids;
        }
        
        $userModel->where($user_where)->setInc("unread_num",1);
        return 1;
    }

    /**
     * 处理家长班级邀请
     * 同意\拒绝
     * @param $apply_id :申请表ID（申请邀请用一个表）
     * @param $type : 1:同意 2:拒绝
     */
    public function disposePatriarchClass($apply_id, $type)
    {
        $classApplyModel = new ClassApply();
        if ($type == 2) {
            //拒绝
            $where['id'] = $apply_id;
            $update['status'] = 3;
            $update['reject_content'] = 'unknown';
            $update['operation_id'] = UID;
            $update_apply = $classApplyModel->updateInfo($where, $update);
            $inform_data['message_content'] = 'refuse';
        } else {
            //同意
            //获取邀请信息
            $apply_where['id'] = $apply_id;
            $apply_field = 'id, class_id, student_id, relation_name';
            $apply_info = $classApplyModel->getInfo($apply_where, $apply_field);
            $update_apply = $this->joinClass($apply_info['class_id'], UID, 2, $apply_id, $apply_info['student_id'], $apply_info['relation_name']);
            $inform_data['message_content'] = 'success';
        }
        if (!$update_apply) {
            return V(0,'error');
        }
        $inform_data['send_id'] = 0;
        $inform_data['user_id'] = UID;
        $inform_data['type'] = 2;
        $inform_data['create_time'] = time();
        $this->addMessage($inform_data, UID, 2);
        return V(1,'success');
    }
}





























