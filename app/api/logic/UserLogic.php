<?php

namespace app\api\logic;

use app\common\model\User;
use Firebase\JWT\JWT;
use think\Db;
use app\common\controller\EndroidQrCode;
use app\common\model\UserStudent;

/**
 * 用戶
 */
class UserLogic extends ApiBaseLogic
{
	private static $model = null;
    public $user_field = 'id, uuid, token, nick_name, sex, head_picture, status, user_type, unread_num,class_num';
	public function __construct() {
        parent::__construct();

        self::$model = User::getInstance();
    }
   	/**
     * 解析token
     */
    public function parseToken($token) {
        $key = API_KEY . JWT_KEY;   // 密钥
        $uid = 0;
        try {
            $decoded = JWT::decode($token, $key, array('HS256'));
        } catch (\Exception $e) {
            return null;
        }
        $decoded_array = (array)$decoded;

        if (!empty($decoded_array)) {
            if ($decoded_array['exp'] > time()) {
                $uid = $decoded_array['uid'];
            }else{
                Log::info( 'token:'.$decoded_array.'   time:'.time());
            }
        }
        $where['token'] = $token;
        $userModel = new User();
        $is_token = $userModel->where($where)->count();
        if (!$is_token) {
            return null;
        }
        if ($uid == 0) {
            return null;
        }
        $info = $this->getUserInfo($uid);
        if ($info) {
            return $info;
        } else {
            return null;
        }
    }

    /**
     * 生成token
     */
    public function buildToken($uid) {
        $key = API_KEY . JWT_KEY;       // 密钥

        $payload = ["qywl" => 'itqiyao', 'iat' => time(), 'exp' => 2147483647, 'uid' => $uid];

        return JWT::encode($payload, $key, 'HS256');
    }
    /**
     * 获取用户信息
     */
    public function getUserInfo($id, $field=null) {
        $where = ['id' => $id];
        if ($field == null) {
            $field = $this->user_field;
        }
        $userModel = new User();
        $info = $userModel->getInfo($where, $field);
        return $info;
    }

    /**
     * 修改个人信息
     */
    public function saveUserInfo($data)
    {
        $where['id'] = UID;
        if (isset($data['phone'])) {
            //检查手机号是或否重复
            $check_phone = self::$model->where($where)->value("phone");
            if ($check_phone == $data['phone']) {
                return V(0,'修改手机号不能重复');
            }
            //检测手机号是否已被注册
            $p_where['phone'] = $data['phone'];
            $phone_count = self::$model->where($p_where)->count();
            if ($phone_count) {
                return V(0,'该手机号已被注册');
            }
        }
        $userModel = new User();
        $save = $userModel->where($where)->update($data);
        return V(1,'修改成功');
    }
    /**
     * 登陆
     */
    public function login($data)
    {
        //验证码检测
        $check_sms = checkSmsCode($data['email'], $data['code'], 2);
        if ($check_sms['status'] == 0) {
            return $check_sms;
        }
        $userModel = new User();
        $where['password'] = pwd_md5($data['pwd']);
        $where['user_type'] = $data['user_type'];
        $where['email'] = $data['email'];
        $where['status'] = array("neq",2);
        $user_info = $userModel->where($where)->field($this->user_field)->find();
        if (empty($user_info)) {
            return V(0,'Incorrect account or password');
        }
        if ($user_info['status'] == 1) {
            return V(0,'The account is abnormal, unable to log in');
        }
        return V(1,'success', $user_info);
    }
    /**
     * 用户注册
     */
    public function register($data)
    {
        $userModel = new User();
        //检测手机号是否已经注册
        $check_email_where['email'] = $data['email'];
        $check_email_where['status'] = array("in","0,1");
        $check_phone = $userModel->where($check_email_where)->count();
        if ($check_phone) {
            return V(0,'This mailbox has been registered');
        }
        //验证码检测
        $check_sms = checkSmsCode($data['email'], $data['code'], 1);
        if ($check_sms['status'] == 0) {
            return $check_sms;
        }
        
        $nick_name = $data['first_name'].' '. $data['last_name'];
        $data['nick_name'] = $nick_name;
        $data['password'] = pwd_md5($data['pwd']);
        $data['create_time'] = time();
        $data['register_time'] = time();
        unset($data['code']);
        unset($data['c_pwd']);
        unset($data['pwd']);

        Db::startTrans();

        $add = $userModel->insertGetId($data);
        if (!$add) {
            return V(0,'Registration failed');
        }
        //更新token
        $this->saveUserToken($add);
        $user_info = $this->getUserInfo($add);
        //添加学生信息
        $student = $this->addStudent($add, $data);
        if (!$student) {
            Db::rollback();
            return V(0,'Registration failed');
        }
        //生成唯一ID
        $user_uuid = $this->createRandom($add, $data['user_type']);
        if (!$user_uuid) {
            Db::rollback();
            return V(0,'Registration failed');
        }
        Db::commit();    
        return V(1,'success', $user_info);
    }

    /**
     * 更新用户 token
     */
    public function saveUserToken($user_id)
    {
        $userModel = new User();
        $token = $this->buildToken($user_id);
        $where['id'] = $user_id;
        $update['token'] = $token;
        $update['user_last_login_time'] = time();
        $userModel->where($where)->update($update);
        return true;
    }

    /**
     * 生成二维码
     */
    public function QrCode($user_id)
    {
        $QrCode = new EndroidQrCode($user_id);
        $info = $QrCode->createQrCode($user_id);
        
        if ($info['status'] == 1) {
            $userModel = new User();
            $where['id'] = $user_id;
            $update['qrcode'] = $info['data'];
            $update_user = $userModel->where($where)->update($update);
            if (!$update_user) {
                return V(0,'Qrcode Error');
            }
        }
        return $info;
    }

    /**
     * 修改用户信息
     */
    public function updateUserInfo($data, $user_id)
    {
        $userModel = new User();
        if (isset($data['generalize_user_type'])) {
            $update['generalize_user_type'] = $data['generalize_user_type'];
        }
        if (isset($data['langue'])) {
            $langue_arr = [1,2,3];
            if (!in_array($data['langue'], $langue_arr)) {
                return V(0,'切换语言错误');
            }
            $update['langue'] = $data['langue'];
        }
        if (isset($data['nick_name'])) {
            $update['nick_name'] = $data['nick_name'];
        }
        if (isset($data['head_picture'])) {
            $update['head_picture'] = $data['head_picture'];
        }
        if (!empty($update)) {
            $where['id'] = $user_id;
            $save = $userModel->where($where)->update($update);
            return V(1,'修改成功');
        }
        return V(0,'暂无修改');
    }

    /**
     * 生成唯一编码
     * $user_type 1:学生 2: 家长 3:老师
     * $count 防止无限递归
     */
    public function createRandom($user_id, $user_type, $count=0)
    {
        ++$count;

        $userModel = new User();
        $num = createRandom(6);
        //查询编码是否重复
        $where['uuid'] = $num;
        $where['user_type'] = $user_type;
        $user_count = $userModel->getCount($where);
        if ($user_count) {
            if ($count > 20) {
                return false;
            }
            return $this->createRandom($user_id, $user_type, $count);
        } else {
            $up_where['id'] = $user_id;
            $up_where['user_type'] = $user_type;
            $user_update['uuid'] = $num;
            //增加用户唯一编码
            $save = $userModel->updateInfo($up_where, $user_update);
            return $save;
        }

    }

    /**
     * 添加学生信息
     * $user_id： 学生ID
     * $data:
     */
    public function addStudent($user_id, $user_data)
    {
        $userStudentModel = new UserStudent();
        $data['user_id'] = $user_id;
        $data['real_name'] = $user_data['nick_name'];
        $data['create_time'] = time();
        $info = $userStudentModel->insertOne($data);
        return $info;
    }
}
