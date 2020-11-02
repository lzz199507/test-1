<?php
/**
 * 反馈验证
 */
namespace app\api\validate;

class Feedback extends \think\Validate {
    protected $rule = [
        'type' => 'require',
        'content' => 'require|check_content',
        'contact' => 'require',
    ];

    protected $message = [
        'content.require' => '反馈内容不能为空',
        'type.require' => '问题类别不能为空',
        'contact.require' => '联系方式不能为空',

    ];

    // 应用场景
    protected $scene = [
        'addFeedback' => ['type','content','contact'],

    ];

    /**
     * 反馈内容验证
     */
    protected function check_content($value)
    {
        $nick_name = _strlen($value);
        if ($nick_name > 50) {
            return "反馈内容不可超过50个字符";
        }
        return true;
    }

}