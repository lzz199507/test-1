<?php

/**
 * 班级通知
 */

namespace app\api\controller;
use Think\Db;
use think\Request;
use app\api\logic\ClassLogic;
use app\api\logic\ClassInformLogic;
use app\api\logic\ClassApplyLogic;
class ClassInform extends ApiBase
{

	/**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
    }


	/**
     * 发布通知
     * @param  $class_ids: 多个ID用逗号隔开
     * @param  $student_ids: 二维数组 json格式 例:[{"class_id":1,"student_id":[1,2,4]}]
     * @param  $title: 标题
     * @param  $content: 内容
     * @param  $video_url: 视频地址
     * @param  $photo_urls: 图片地址 json存储 (不可以用 逗号或其它进行分隔) 例：["www.baidu.com","www.weibo.com"]
     * @param  $accessory_ids: 附件ID, 多个ID用 逗号分隔
     * @param  $timing_send_time : 定时发送时间
     */
    public function insertReleaseClassInform()
    {
        $arr = array('class_ids','student_ids','title','content','video_url','photo_urls','accessory_ids','timing_send_time');
        $data = Request::instance()->only($arr);
        $validate = validate('api/ClassInform');
        if (!$validate->scene("addInform")->check($data)) {
            $this->ajaxReturn(V(0,$validate->getError()));
        }
        $data['type'] = 1;
        $check_data = $this->checkInformData($data);
        if ($check_data['status'] == 0) {
            $this->ajaxReturn($check_data);
        }
        $data = [];
        $data = $check_data['data'];
        $classInformLogic = new ClassInformLogic();
        $info = $classInformLogic->createInform($data);
        $this->ajaxReturn($info);
    }

    /**
     * 发布作业
     * @param  $class_ids: 多个ID用逗号隔开
     * @param  $student_ids: 二维数组 json格式 例:[{"class_id":1,"student_id":[1,2,4]}]
     * @param  $title: 标题
     * @param  $content: 内容
     * @param  $video_url: 视频地址
     * @param  $photo_urls: 图片地址 json存储 (不可以用 逗号或其它进行分隔) 例：["www.baidu.com","www.weibo.com"]
     * @param  $accessory_ids: 附件ID, 多个ID用 逗号分隔
     * @param  $score: 空：不记分 其它：分数
     * @param  $end_time: 结束时间
     * @param  $timing_send_time : 定时发送时间
     */
    public function insertReleaseClassWork()
    {
        $arr = array('class_ids','student_ids','title','content','video_url','photo_urls','accessory_ids','score','end_time','timing_send_time');
        $data = Request::instance()->only($arr);
        $validate = validate('api/ClassInform');
        if (!$validate->scene("addClassWork")->check($data)) {
            $this->ajaxReturn(V(0,$validate->getError()));
        }
        $data['type'] = 2;
        $check_data = $this->checkInformData($data);
        if ($check_data['status'] == 0) {
            $this->ajaxReturn($check_data);
        }
        $data = [];
        $data = $check_data['data'];
        $classInformLogic = new ClassInformLogic();
        $info = $classInformLogic->createInform($data);
        $this->ajaxReturn($info);
    }

     /**
     * 发布调查
     * @param  $class_ids: 多个ID用逗号隔开
     * @param  $student_ids: 二维数组 json格式 例:[{"class_id":1,"student_id":[1,2,4]}]
     * @param  $title: 标题
     * @param  $content: 内容
     * @param  $video_url: 视频地址
     * @param  $photo_urls: 图片地址 json存储 (不可以用 逗号或其它进行分隔) 例：["www.baidu.com","www.weibo.com"]
     * @param  $accessory_ids: 附件ID, 多个ID用 逗号分隔
     * @param  $start_time: 结束时间
     * @param  $end_time: 结束时间
     * @param  $timing_send_time : 定时发送时间
     * @param  $survey_option: json存储例子:[{"title":"标题","photo":["www.baidu1.com","www.baidu2.com"],"audio_url":"音频地址",
        "option":[{"num":0,"title":"选项内容","photo":"图片地址 只能上传一张"}],"type":1}]

     *  $option[0]['num'] = 0;
        $option[0]['title'] = '选项内容';
        $option[0]['photo'] = '图片地址 只能上传一张';
        $data[0]['title'] = "标题";
        $photo[0] = 'www.baidu1.com';
        $photo[1] = 'www.baidu2.com';
        $data[0]['photo'] = $photo;
        $data[0]['audio_url'] = "音频地址";
        $data[0]['option'] = $option;
        $data[0]['type'] = 1;
     */
    public function insertReleaseClassSurvey()
    {
        
        $arr = array('class_ids','student_ids','title','content','video_url','photo_urls','accessory_ids','start_time','end_time','survey_option','timing_send_time');
        $data = Request::instance()->only($arr);
        $validate = validate('api/ClassInform');
        if (!$validate->scene("addClassSurvey")->check($data)) {
            $this->ajaxReturn(V(0,$validate->getError()));
        }
        if ($data['start_time'] >= $data['end_time']) {
            $this->ajaxReturn(V(0,'Error start or end time'));
        }
        $check_data = $this->checkInformData($data);
        if ($check_data['status'] == 0) {
            $this->ajaxReturn($check_data);
        }
        $data = [];
        $data = $check_data['data'];
        $classInformLogic = new ClassInformLogic();
        $data['type'] = 5;
        $info = $classInformLogic->createInform($data);
        $this->ajaxReturn($info);
    }

    /**
     * 发布讨论
     * @param  $class_ids: 多个ID用逗号隔开
     * @param  $student_ids: 二维数组 json格式 例:[{"class_id":1,"student_id":[1,2,4]}]
     * @param  $title: 标题
     * @param  $content: 内容
     * @param  $video_url: 视频地址
     * @param  $photo_urls: 图片地址 json存储 (不可以用 逗号或其它进行分隔) 例：["www.baidu.com","www.weibo.com"]
     * @param  $accessory_ids: 附件ID, 多个ID用 逗号分隔
     * @param  $end_time: 结束时间
     * @param  $timing_send_time : 定时发送时间 时间戳
     */
    public function insertReleaseClassDiscussion()
    {
        $arr = array('class_ids','student_ids','title','content','video_url','photo_urls','accessory_ids','end_time','timing_send_time');
        $data = Request::instance()->only($arr);
        $validate = validate('api/ClassInform');
        if (!$validate->scene("addClassDiscussion")->check($data)) {
            $this->ajaxReturn(V(0,$validate->getError()));
        }
        $data['type'] = 4;
        $check_data = $this->checkInformData($data);
        if ($check_data['status'] == 0) {
            $this->ajaxReturn($check_data);
        }
        $data = [];
        $data = $check_data['data'];
        $classInformLogic = new ClassInformLogic();
        $info = $classInformLogic->createInform($data);
        $this->ajaxReturn($info);
    }

    /**
     * 发布打卡任务
     * @param  $class_ids: 多个ID用逗号隔开
     * @param  $student_ids: 二维数组 json格式 例:[{"class_id":1,"student_id":[1,2,4]}]
     * @param  $title: 标题
     * @param  $content: 内容
     * @param  $video_url: 视频地址
     * @param  $photo_urls: 图片地址 json存储 (不可以用 逗号或其它进行分隔) 例：["www.baidu.com","www.weibo.com"]
     * @param  $accessory_ids: 附件ID, 多个ID用 逗号分隔
     * @param  $end_time: 结束时间
     * @param  $timing_send_time : 定时发送时间
     * @param  $sing_info:   json打卡数据
     * 例：$sing_info['frequency'] = 1,2,3,4,5,6,7 //参与周时间 周一，周二 …. 多个周逗号分隔
     *    $sing_info['duration_all'] = 21  //共发布天数
     *    $sing_info['duration_day'] = 3;  //签到次数
     *    $sing_info['sing_time_lsit'] = $sing_time_list;  //签到时间列表
     *
     *    $sing_time_list[0] = 1559787062.     //时间或时间戳
     */
    public function insertReleaseClassSign()
    {
        $arr = array('class_ids','student_ids','title','content','video_url','photo_urls','accessory_ids','end_time','timing_send_time','sing_info');
        $data = Request::instance()->only($arr);
        $validate = validate('api/ClassInform');
        if (!$validate->scene("addClassSign")->check($data)) {
            $this->ajaxReturn(V(0,$validate->getError()));
        }
        $data['type'] = 3;
        $check_data = $this->checkInformData($data);
        if ($check_data['status'] == 0) {
            $this->ajaxReturn($check_data);
        }
        $data = [];
        $data = $check_data['data'];
        $classInformLogic = new ClassInformLogic();
        $info = $classInformLogic->createInform($data);
        $this->ajaxReturn($info);
    }


    /**
     * 通知表数据验证
     */
    public function checkInformData($data)
    {
        if (!empty($data['video_url'])) {
            if (!empty($data['photo_urls']) || !empty($data['accessory_ids'])) {
                // $this->ajaxReturn(V(0,'Do not upload pictures or attachments'));
                return V(0,'Do not upload pictures or attachments');
            }
        }
        if (!empty($data['photo_urls']) || !empty($data['accessory_ids'])) {
            if (!empty($data['video_url'])) {
                // $this->ajaxReturn(V(0,'Do not upload video'));
                return V(0,'Do not upload video');
            }
        }
        if (empty($data['video_url'])) {
            $data['video_url'] = '';
        }
        if (empty($data['photo_urls'])) {
            $data['photo_urls'] = '';
        }
        if (empty($data['accessory_ids'])) {
            $data['accessory_ids'] = '';
        }
        if (empty($data['content'])) {
            $data['content'] = '';
        }
        if (empty($data['score'])) {
            $data['score'] = 0;
        }
        if (empty($data['timing_send_time'])) {
            $data['is_timing_send'] = 1;
        } else {
            if ($data['timing_send_time'] <= time()) {
                return V(0,'It cannot be less than the current time');
            }
            $data['is_timing_send'] = 2;
        }
        return V(1,'success', $data);
    }

    /**
     * 通知详情
     */
    public function getClassInformDetails()
    {
        $classInformLogic = new ClassInformLogic();
        $inform_id = input("inform_id",0);
        $class_id = input("class_id",0);
        if ($inform_id == 0) {
            $this->ajaxReturn(V(0,lang('inform id error')));
        }
        if ($class_id == 0) {
            $this->ajaxReturn(V(0,lang('class id error')));
        }
        $info = $classInformLogic->getClassInformDetails($inform_id, $class_id);
        $this->ajaxReturn($info);
    }
}








































