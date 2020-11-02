<?php
/**
 * 公共
 */
namespace app\api\controller;

use think\Controller;
use think\Request;
use Think\Db;
use think\Log;
use app\common\controller\Email;
class Common extends Controller
{
    // public $sendCodeTitle = 'ClassRoom注册验证码11';

    /**
     * 发送验证码
     * $type 1:注册 2:登陆
     */
    public function sendCode()
    {
        $email = input("email");
        $type = input("type",1);
		//检测手机号
		if (checkEmail($email) === false) {
			$this->ajaxReturn(V(0,'Email format error'));
		}
		//生成验证码
		$code = rand(1000,9999);
        $code = 8888
        $info = $this->addCode($code, $email, $type);
        exit(json_encode($info));
    }

    /**
     * 验证码
     * $type 1:注册 2:登陆
     */
    public function addCode($code, $email_num, $type=1)
    {
        //一分钟只能发送一条数据
        $email_where['email'] = $email_num;
        $email_where['type'] = $type;
        $res=Db::name('sms_log')->where($email_where)->order('create_time desc')->find();
        if ($res) {
            if ($res['create_time'] + 60 > time()) {
                return V(0,'It can only be sent once a minute');
            }
        }

        $data['email'] = $email_num;
        $data['code'] = $code;
        $data['content'] = '';
        $data['create_time']=time();
        $data['type'] = $type;

        switch ($type) {
            case '1':
                $sendCodeTitle = 'ClassRoom Registration verification code';
                break;
            case '2':
                $sendCodeTitle = 'ClassRoom Login verification code';
                break;
            default:
                $sendCodeTitle = 'ClassRoom Login verification code';
                break;
        }
        //开始发送短信
        if(Db::name('sms_log')->insert($data))
        {
            $email = new Email();
            $info = $email->sendMail($code, $email_num, $sendCodeTitle);
            return $info;
        }else{
            return V(0,'Server error');
        }
    }
}