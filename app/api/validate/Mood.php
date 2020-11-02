<?php
/**
 * 心情
 */
namespace app\api\validate;

class Mood extends \think\Validate {
    protected $rule = [
        'content' => 'require|check_content',
    ];

    protected $message = [
        'content.require' => '发布内容不能为空',

    ];

    // 应用场景
    protected $scene = [
        'addPublishMood' => ['content'],
        'addPublishMood' => ['content'],

    ];

    /**
     * 瓶子内容验证
     */
    protected function check_content($value)
    {
        $content = _strlen($value);
        if ($content < 2 || $content > 50) {
            return "发布内容为2-50个字符";
        }
        return true;
    }

}