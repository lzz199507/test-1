<?php

namespace app\api\logic;

use think\Db;
use app\common\model\Files;
use app\common\model\ClassUser;
use app\common\model\ClassModel;
use app\common\model\ClassInform;
use app\common\model\ClassTeacher;
use app\common\model\ClassSingTime;
use app\common\model\ClassInformUser;
use app\common\model\ClassInformClass;
use app\common\model\ClassSurveyOption;
/**
 * 班级通知
 */
class ClassInformLogic extends ApiBaseLogic
{

	public function __construct() {
        parent::__construct();
    }

    /**
     * 班级首页(通知)列表
     * @param    $class_id: 班级ID
     * @param    $user_type:1:学生 2: 家长 3:老师
     * @param    $p: 分页
     */
    public function listClassHome($class_id, $user_type,$p, $type=0)
    {
    	$classInformModel = new ClassInform();
        $classInformClassModel = new ClassInformClass();
        $where['i.is_del'] = 1;
        $where['i.is_timing_send'] = 1;
    	$where['us.class_id'] = $class_id;
        if ($type != 0) {
            $where['i.type'] = $type;
        }
    	if ($user_type == 1) {
    		//学生
    		$where['us.user_id'] = UID;
    	} else if ($user_type == 2) {
    		//家长
    		$ids = $this->getClassPatriarchStudentIdList($class_id, UID);
    		$where['us.user_id'] = array("in", $ids);
    		if (empty($ids)) {
    			return [];
    		}
    	}
    	$field = "i.id, i.user_id, i.type, i.title, i.create_time, us.type as is_read, us.class_id";
        $order = "sort desc, id desc";
    	$list = $classInformModel->alias("i")
    			->join("qy_class_inform_user us","i.id = us.inform_id")
    			->field($field)
                ->order($order)
    			->where($where)
    			->page($p, 10)
    			->select();
    	return $list;
    }

    /**
     * 获取班级内家长所对应的学生
     */
    public function getClassPatriarchStudentIdList($class_id, $patriarch_id)
    {
    	$classUserModel = new ClassUser();
    	$where['class_id'] = $class_id;
    	$where['user_id'] = $patriarch_id;
    	$where['type'] = 2;
    	$ids = $classUserModel->getColumnList($where,'user_id');
    	return $ids;
    }
    /**
     * 添加通知数据
     * @param  $class_ids: 多个ID用逗号隔开
     * @param  $student_ids: 二维数组 json格式 例:[{"class_id":1,"student_id":[1,2,4]}]
     * @param  $title: 标题
     * @param  $content: 内容
     * @param  $video_url: 视频地址
     * @param  $photo_urls: 图片地址 json存储 (不可以用 逗号或其它进行分隔) 例：["www.baidu.com","www.weibo.com"]
     * @param  $accessory_ids: 附件ID, json存储 
     * @param  $score: 空：不记分 其它：分数
     * @param  $end_time: 结束时间
     * @param  $sing_info:   json打卡数据
     * @param  $survey_option: json存储 例子:[{"title":"\u6807\u9898","photo":"\u56fe\u7247json\u5b58\u50a8","audio_url":"\u97f3\u9891\u5730\u5740","option":[{"num":0,"title":"\u9009\u9879\u5185\u5bb9","photo":"\u56fe\u7247\u5730\u5740 \u53ea\u80fd\u4e0a\u4f20\u4e00\u5f20"}],"type":1}]
     * $option[0]['num'] = 0;
        $option[0]['title'] = '选项内容';
        $option[0]['photo'] = '图片地址 只能上传一张';
        $data[0]['title'] = "标题";
        $photo[0] = 'www.baid.com';
        $photo[0] = 'www.baid1.com';
        $data[0]['photo'] = $photo;//   json存储
        $data[0]['audio_url'] = "音频地址";
        $data[0]['option'] = $option;
        $data[0]['type'] = 1;
     */
    public function createInform($info)
    {
        $classInformModel = new ClassInform();
        $classInformUserModel = new ClassInformUser();
        $classInformClassModel = new ClassInformClass();
        $class_data = $this->dataProcessing($info);
        $data = $class_data['data'];
        $student_all = $class_data['student_all'];
        $class_ids = $class_data['class_ids'];

        Db::startTrans();
        $inform_id = $classInformModel->insertOne($data);
        if (!$inform_id) {
            return V(0,'error');
        }
        $status = 0;
        for ($i=0; $i < count($class_ids); $i++) { 
            $status = 0;
            $infom_class_data[$i]['class_id'] = $class_ids[$i];
            $infom_class_data[$i]['inform_id'] = $inform_id;
            $infom_class_data[$i]['create_time'] = time();
            $student_ids = [];
            //添加学生
            //获取学生ID
            for ($t=0; $t < count($student_all); $t++) { 
                if ($student_all[$t]['class_id'] == $class_ids[$i]) {
                    $student_ids = $student_all[$t]['student_id'];
                }
            }
            $student_data = [];
            for ($st=0; $st < count($student_ids); $st++) { 
                $student_data[$st]['user_id'] = $student_ids[$st];
                $student_data[$st]['inform_id'] = $inform_id;
                $student_data[$st]['class_id'] = $class_ids[$i];
                $student_data[$st]['type'] = 0;
                $student_data[$st]['create_time'] = time();
            }
            if (empty($student_data)) {
                break;
            }
            $add_student_inform = $classInformUserModel->insertAll($student_data);
            if (!$add_student_inform) {
                break;
            }
            $status = 1;
        }
        if ($status == 1) {
            switch ($data['type']) {
                case '5':
                    //调查
                    $survey_option = $this->createClassSurvey($info['survey_option'], $inform_id);
                    if (!$survey_option) {
                        Db::rollback();
                        return V(0,'Please resubmit');
                    }
                    break;
                case '3':
                    //打卡
                    $sing_info = $this->createClassSing($inform_id, $info['sing_info']);
                    if (!$sing_info) {
                        Db::rollback();
                        return V(0,'Please resubmit');
                    }
                    break;
            }
            //添加记录
            $add_infom_class = $classInformClassModel->insertAll($infom_class_data);
            if (!$add_infom_class) {
                Db::rollback();
                return V(0,'Please resubmit');
            }
            Db::commit();    
            return V(1,'success');
        } else {
            Db::rollback();
            return V(0,'Please resubmit');
        }
    }

    /**
     * 数据处理
     */
    public function dataProcessing($info)
    {
        $student_all = json_decode($info['student_ids'], true);
        $class_ids = explode(",", $info['class_ids']);
        $data['user_id'] = UID;
        $data['type'] = $info['type'];
        $data['title'] = $info['title'];
        $data['content'] = $info['content'];
        $data['photo_urls'] = $info['photo_urls'];
        $data['video_url'] = $info['video_url'];
        // $data['audio_url'] = $info['audio_url'];
        $data['accessory_ids'] = $info['accessory_ids'];
        $data['create_time'] = time();
        if (!empty($info['timing_send_time'])) {
            $data['timing_send_time'] = $info['timing_send_time'];
        }
        if (!empty($info['is_timing_send'])) {
            $data['is_timing_send'] = $info['is_timing_send'];
        }
        $class_data['data'] = $data;
        $class_data['student_all'] = $student_all;
        $class_data['class_ids'] = $class_ids;
        return $class_data;
    }


    /**
     * 添加调查
     * @param  $inform_id: 通知表ID
     * @param  $data: json存储 例子:[{"title":"\u6807\u9898","photo":"\u56fe\u7247json\u5b58\u50a8","audio_url":"\u97f3\u9891\u5730\u5740","option":[{"num":0,"title":"\u9009\u9879\u5185\u5bb9","photo":"\u56fe\u7247\u5730\u5740 \u53ea\u80fd\u4e0a\u4f20\u4e00\u5f20"}],"type":1}]
     * $option[0]['num'] = 0;
        $option[0]['title'] = '选项内容';
        $option[0]['photo'] = '图片地址 只能上传一张';
        $data[0]['title'] = "标题";
        $photo[0] = 'www.baid.com';
        $photo[0] = 'www.baid1.com';
        $data[0]['photo'] = $photo;//   json存储
        $data[0]['audio_url'] = "音频地址";
        $data[0]['option'] = $option;
        $data[0]['type'] = 1;
     */
    public function createClassSurvey($data, $inform_id)
    {
        $classSurveyOptionModel = new ClassSurveyOption();
        $option = json_decode($data,true);
        $survey_option = [];
        foreach ($option as $key => $val) {
            $survey_option[$key]['inform_id'] = $inform_id;
            $survey_option[$key]['title'] = $val['title'];
            $survey_option[$key]['photo'] = json_encode($val['photo']);
            $survey_option[$key]['audio_url'] = $val['audio_url'];
            $survey_option[$key]['option'] = json_encode($val['option']);
            $survey_option[$key]['type'] = $val['type'];
        }
        $add = $classSurveyOptionModel->insertAll($survey_option);
        return $add;
    }

    /**
     * 添加打卡
     * @param  $inform_id: 通知表ID
     * @param  $data:   json打卡数据
     * 例：$data['frequency'] = 1,2,3,4,5,6,7 //参与周时间 周一，周二 …. 多个周逗号分隔
     *    $data['duration_all'] = 21  //共发布天数
     *    $data['duration_day'] = 3;  //签到次数
     *    $data['sing_time_lsit'] = $sing_time_list;  //签到时间列表
     *
     *    $data[0] = 1559787062.     //时间或时间戳
     */
    public function createClassSing($inform_id, $data)
    {
        $classSingTimeModel = new ClassSingTime();
        $data = json_decode($data,true);
        $sing_time_list = $data['sing_time_list'];
        for ($i=0; $i < count($sing_time_list); $i++) { 
            $sing_time_data[$i]['inform_id'] = $inform_id;
            $sing_time_data[$i]['sing_time'] = $sing_time_list[$i];
            $sing_time_data[$i]['sing_day'] = $i+1;
            $sing_time_data[$i]['sing_day_num'] = 0;
            $sing_time_data[$i]['create_time'] = time();
        }
        $add = $classSingTimeModel->insertAll($sing_time_data);
        return $add;
    }


    /**
     * 获取通知详情
     */
    public function getClassInformDetails($inform_id, $class_id)
    {
        $filesModel = new Files();
        $classInformModel = new ClassInform();
        $where['i.id'] = $inform_id;
        $where['i.is_del'] = 1;
        $where['i.is_timing_send'] = 1;
        $where['ic.class_id'] = $class_id;
        $where['t.class_id'] = $class_id;
        $field = "i.id, i.user_id, i.title, i.content, i.photo_urls, i.video_url, i.audio_url, i.accessory_ids, i.praise_num, i.create_time, c.class_name, t.class_teacher_name";
        $info = $classInformModel->alias("i")
                ->join("qy_class_inform_class ic","i.id = ic.inform_id",'left')
                ->join("qy_class c", "c.id = ic.class_id")
                ->join("qy_class_teacher t", "t.user_id = i.user_id")
                ->where($where)
                ->field($field)
                ->find();
        if (empty($info)) {
            return V(0, lang("data error"));
        }
        return V(1,'success', $info);
    }
}

























