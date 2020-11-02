<?php
/**
 * 班级
 */
namespace app\api\validate;

class Classs extends \think\Validate {
    protected $rule = [
        'class_name' => 'require',
        'class_photo' => 'require',
        'subject_name' => 'require',
    ];

    protected $message = [
        'class_name.require' => "Class name Can't be empty",
        'class_photo.require' => 'Please upload Class subject',
        'subject_name.require' => "Subject name Can't be empty",

    ];

    // 应用场景
    protected $scene = [
        'addClass' => ['class_name','class_photo','subject_name'],

    ];

    

}