<?php

/**
 * 登录/注册
 */

namespace app\api\controller;
use Think\Db;
use think\Request;
use app\api\logic\UserLogic;
use app\common\controller\ChatEasemob;
use app\api\logic\CommonLogic;
class Login extends ApiBase
{

	/**
     * @var \app\api\logic\OrderLogic
     */
    // private static $userLogic = null;
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::_initialize();
        // self::$userLogic = UserLogic::getInstance();
    }
    /**
     * 测试
     */
    public function test()
    {
    	$userLogic = new UserLogic();
		$info = $userLogic->createRandom(113, 1);
		p($info);exit;
    }
	/**
	 * 普通登录
	 * $email   手机号
	 * $code    验证码
	 */
	public function login()
	{
		$arr = array('user_type',"email","pwd","code");
        $data = Request::instance()->only($arr);
        $validate = validate('api/Login');
        if (!$validate->scene("login")->check($data)) {
            $this->ajaxReturn(V(0,$validate->getError()));
		}
		if (empty($data['user_type'])) {
			$data['uset_type'] = 1;
		}
		$userLogic = new UserLogic();
		$info = $userLogic->login($data);
		$this->ajaxReturn($info);
	}

	/**
	 * 注册
	 */
	public function register()
	{
		$arr = array('user_type',"first_name","last_name","email","pwd","c_pwd","code");
        $data = Request::instance()->only($arr);
        $validate = validate('api/Login');
        if (!$validate->scene("register")->check($data)) {
            $this->ajaxReturn(V(0,$validate->getError()));
		}
		if (empty($data['user_type'])) {
			$data['uset_type'] = 1;
		}
		if ($data['pwd'] != $data['c_pwd']) {
			$this->ajaxReturn(V(0,'Confirm that the password was entered incorrectly'));
		}
		Db::startTrans();
		$userLogic = new UserLogic();
		$info = $userLogic->register($data);
		if ($info['status'] == 0) {
			Db::rollback();
			$this->ajaxReturn($info);
		}
		// //添加学生关系 弃用 2020-10-15 只记录家长ID 不记录当前用户
		// $commonLogic = new CommonLogic();
		// $student = $commonLogic->addStudentRelation($info['data']['id'], $info['data']['id']);
		// if ($student['status'] == 0) {
		// 	Db::rollback();
		// 	$this->ajaxReturn($info);
		// }
		Db::commit();
		$this->ajaxReturn($info);
	}

	/**
	 * 三方登录
	 * $oauth_type 1:QQ 2:微信
	 */
	public function loginThirdParty()
	{
		
	}


	/**
	 * 生成token
	 */
	public function create_token($uid)
	{
		$token = self::$userLogic->buildToken($uid);
		if ($token) {
			//更新token
			$where['id'] = $uid;
			$update['token'] = $token;
			$update['create_time'] = time();
			$up = Db::name("user")->where($where)->update($update);
			if ($up) {
				return $token;
			}
		}
		return false;
	}
}
