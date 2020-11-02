<?php
/**
 * 班级
 */
namespace app\api\validate;

class ClassInform extends \think\Validate {
    protected $rule = [
        'class_ids' => 'require',
        'student_ids' => 'require',
        'title' => 'require',
        'start_time' => 'require|check_start_time',
        'end_time' => 'require|check_end_time',
        'survey_option' => 'require',
        'sing_info' => 'require',
    ];

    protected $message = [
        'class_ids.require' => "Classes cannot be empty",
        'student_ids.require' => 'Please select students',
        'title.require' => "Please fill in the title",
        'start_time.require' => "Please fill in the start time",
        'end_time.require' => "Please fill in the end time",
        'survey_option.require' => "Please add survey options",
        'sing_info.require' => "Please add Sign in information",

    ];

    // 应用场景
    protected $scene = [
        'addInform' => ['class_ids','student_ids','title'],
        'addClassWork' => ['class_ids','student_ids','title','end_time'],
        'addClassSurvey' => ['class_ids','student_ids','title','start_time','end_time','survey_option'],
        'addClassDiscussion' => ['class_ids','student_ids','title','end_time'],
        'addClassSign' => ['class_ids','student_ids','title','end_time','sing_info'],

    ];

    /**
     * 开始时间验证
     */
    protected function check_start_time($value)
    {
        if ($value <= time()) {
            return "Please fill in the correct start time";
        }
        return true;
    }
    /**
     * 结束时间验证
     */
    protected function check_end_time($value)
    {
        if ($value <= time()) {
            return "Please fill in the correct end time";
        }
        return true;
    }

    

}