<?php
/**
 * 用户验证
 */
namespace app\api\validate;

class Login extends \think\Validate {
    protected $rule = [
        'first_name' => 'require',
        'last_name' => 'require',
        'email' => 'require|check_email',
        'pwd' => 'require',
        'c_pwd' => 'require',
        'code' => 'require'
    ];

    protected $message = [
        'first_name.require' => 'Please fill out the FirstName',
        'last_name.require' => 'Please fill out the LastName',
        'email.require' => 'Please fill out the Email',
        'pwd.require' => 'Please fill out the Password',
        'c_pwd.require' => 'Please fill out the ConfirmPassword',
        'code.require' => 'Please fill out the Code',

    ];

    // 应用场景
    protected $scene = [
        'register' => ['first_name', 'last_name','email','pwd','c_pwd','code'],
        'login' => ['email','pwd','code'],

    ];

    /**
     * 验证邮箱
     */
    protected function check_email($value)
    {
        $check_email = checkEmail($value);
        if ($check_email == false) {
            return "Incorrect email address format";
        }
        return true;
    }
    /**
     * 验证密码
     */
    protected function check_pwd($value)
    {
        $check_pwd = checkPassword($value);
        if ($check_pwd['code'] < 2) {
            return $check_pwd['msg'];
        }
        return true;
    }


}