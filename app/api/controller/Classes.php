<?php

/**
 * 班级
 */

namespace app\api\controller;
use Think\Db;
use think\Request;
use app\api\logic\ClassLogic;
use app\api\logic\ClassInformLogic;
use app\api\logic\ClassApplyLogic;
class Classes extends ApiBase
{
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取我的班级
     */
    public function listMyClass()
    {
        $classLogic = new ClassLogic();
        $list = $classLogic->listMyClass(UID);
        $this->ajaxReturn(V(1, 'success', $list));
    }

    /**
     * 创建班级
     */
    public function createClass()
    {
        $arr = array('class_name','class_photo','school_name','subject_name');
        $data = Request::instance()->only($arr);
        $validate = validate('api/Classs');
        if (!$validate->scene("addClass")->check($data)) {
            $this->ajaxReturn(V(0,$validate->getError()));
        }
        $classLogic = new ClassLogic();
        $data['user_id'] = UID;
        $info = $classLogic->createClass($data,$this->userInfo['nick_name']);
        $this->ajaxReturn($info);
    }


    /**
     * 搜索班级
     */
    public function listSearchClass()
    {
        $classApplyLogic = new ClassApplyLogic();
        $search_data = input("search_data",'');
        if ($search_data == '') {
            $this->ajaxReturn(V(0,'Parameter error'));
        }
        $list = $classApplyLogic->listSearchClass($search_data, UID);
        $this->ajaxReturn($list);
    }
    /**
     * 申请加入班级
     * @param $class_id    班级ID
     * @param $search_data 搜索班级的信息 (用来判断是 班级编号加入还是 用户邮箱加入)
     */
    public function insertApplyJoinClass()
    {
        $classApplyLogic = new ClassApplyLogic();
        $arr = array('search_data','class_id');
        $data = Request::instance()->only($arr);
        if (!$data['class_id']) {
            $this->ajaxReturn(V(0,'class ID error'));
        }
        $info = $classApplyLogic->insertApplyJoinClass($data, UID, $this->userInfo['user_type']);
        $this->ajaxReturn($info);
    }
    /**
     * 查看班级申请列表
     * @param  $type 1:申请 2：退出
     */
    public function listClassApply()
    {
        $class_id = input("class_id",0);
        $type = input("type",1);
        $p = input("p",1);
        if ($class_id == 0) {
            $this->ajaxReturn(V(0,'Parameter error'));
        }
        $classApplyLogic = new ClassApplyLogic();
        $list = $classApplyLogic->listClassApply($class_id, $type, $p);
        $this->ajaxReturn(V(1,'success', $list));
    }

    /**
     * 处理班级申请 
     * 同意/拒绝
     * @param  $class_id    班级ID
     * @param  $ids   申请表ID 多个ID 用逗号 分割
     * @param  $type  1:同意 2:拒绝
     */
    public function disposeClassApply()
    {
        $class_id = input("class_id", 0);
        $ids = input("ids",0);
        $type = input("type",1);
        if ($class_id == 0) {
            $this->ajaxReturn(V(0,'Parameter error'));
        }
        if ($ids == 0) {
            $this->ajaxReturn(V(0,'Parameter error-1'));
        }
        $classApplyLogic = new ClassApplyLogic();
        $info = $classApplyLogic->disposeClassApply($class_id, $ids, $type);
        $this->ajaxReturn($info);
    }

    /**
     * 处理家长班级邀请
     * 同意\拒绝
     * @param $apply_id :申请表ID（申请邀请用一个表）
     * @param $type : 1:同意 2:拒绝
     */
    public function disposePatriarchClass()
    {
        $apply_id = input("apply_id",0);
        $type = input("type",1);
        if ($apply_id == 0) {
            $this->ajaxReturn(V(0,'Parameter error'));
        }
        $classApplyLogic = new ClassApplyLogic();
        $info = $classApplyLogic->disposePatriarchClass($apply_id, $type);
        $this->ajaxReturn($info);
    }

    /**
     * 获取班级详情
     */
    public function getClassDetails()
    {
        $class_id = input("class_id",0);
        if ($class_id == 0) {
            $this->ajaxReturn(V(0,'Parameter error'));
        }
        $classLogic = new ClassLogic();
        $info = $classLogic->getClassDetails($class_id);
        $this->ajaxReturn(V(1,'success', $info));
    }

    /**
     * 班级首页(通知)列表(通知、作业、调查等)
     */
    public function listClassHome()
    {
        $p = input("p",1);
        $class_id = input("class_id",0);
        if ($class_id == 0) {
            $this->ajaxReturn(V(0,'Parameter error'));
        }
        $classInformLogic = new ClassInformLogic();
        $list = $classInformLogic->listClassHome($class_id, $this->userInfo['user_type'],$p);
        $this->ajaxReturn(V(1,'success', $list));
    }

    /**
     * 班级作业列表
     */
    public function listClassHomeWork()
    {
        $p = input("p",1);
        $class_id = input("class_id",0);
        if ($class_id == 0) {
            $this->ajaxReturn(V(0,'Parameter error'));
        }
        $classInformLogic = new ClassInformLogic();
        $list = $classInformLogic->listClassHome($class_id, $this->userInfo['user_type'],$p, 2);
        $this->ajaxReturn(V(1,'success', $list));
    }

    /**
     * 修改班级信息
     */
    public function updateClassInfo()
    {
        $classLogic = new ClassLogic();
        $arr = array('class_id','class_name','class_photo','school_name','subject_name');
        $data = Request::instance()->only($arr);
        $class_id = $data['class_id'];
        unset($data['class_id']);
        if (empty($class_id)) {
            $this->ajaxReturn(V(0,'Parameter error'));
        }
        foreach ($data as $key => $val) {
            if (empty($data[$key])) {
                unset($data[$key]);
            }
        }
        if (empty($data)) {
            $this->ajaxReturn(V(0,'error'));
        }
        $info = $classLogic->updateClassInfo($data, $class_id);
        $this->ajaxReturn($info);
    }

    
}



























