<?php
/**
 * 基类
 */
namespace app\api\controller;

use app\api\logic\UserLogic;
// use app\common\controller\ControllerBase;
use think\Controller;
use think\Request;
use Think\Db;
use think\Log;

class ApiBase extends Controller{

    private static $userLogic = null;
    public $userInfo=null; //设置用户数据
    public $page = 1;
    // 请求参数
    protected $param;
    /**
     * 排除登录列表，字符串 '*' 表示排除该控制器下所有的操作
     */
    private static $exception_list = [
        'Login' => '*',
        'WxPay' => '*',
        'Notify' => '*',
        'User' => '*',
    ];

    /**
     * 是否在排除登录列表中
     */
    private static function except() {
        foreach (self::$exception_list as $controller => $actionList) {
            if (strtolower($controller) == strtolower(CONTROLLER_NAME)) {
                if ($actionList === '*' || in_array(strtolower(ACTION_NAME),
                        array_map('strtolower', $actionList))) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * 初始化方法
     */
    public function __construct() {
        // 初始化请求信息
        $this->initRequestInfo();
        // parent::__construct();
        self::$userLogic = UserLogic::getInstance();

        if (isset($_SERVER['HTTP_TOKEN'])) {
            $token = $_SERVER['HTTP_TOKEN'];
        } else {
            $token = input("token");
        }
        $user_info = self::$userLogic->parseToken($token);
        // App 登录接口验证
        if (!self::except()) {
            if (!$user_info) {
                if (IS_POST) {
                    if ($token) {
                        $this->ajaxReturn(V(-1, '您的账号已在其它设备登录'));    
                    } else {
                        $this->ajaxReturn(V(-1, '请登录'));
                    }
                } else {
                    $this->ajaxReturn(V(0,'提交方式有误'));
                }
            }
            if ($user_info['status'] == -1) {
                if (IS_POST) {
                    $this->ajaxReturn(V(0, '该用户已被禁用'));
                } else {
                    $this->ajaxReturn(V(0,'提交方式有误'));
                }
            }
        }
        // 定义 用户UID 常量
        define('UID', $user_info['id']);
        Log::info('user_info:'.$user_info);
        $this->userInfo=$user_info;
    }

    protected function get_user_status($id){
        $info = Db::name('user')->where(['id'=>$id])->field(['id','status'])->find();
        if(empty($info) || count($info) == 0){
            return V(0,'用户不存在');
        }
        if($info['status'] == -1){
            return V(0,'该用户已经被禁用');
        }
    }
    /**
     * Ajax方式返回数据到客户端
     */
    protected function ajaxReturn($data) {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }

    /**
     * 初始化请求信息
     */
    final private function initRequestInfo() {
        $request = Request::instance();
        defined('IS_POST') or define('IS_POST', $request->isPost());
        defined('IS_GET') or define('IS_GET', $request->isGet());
        defined('IS_AJAX') or define('IS_AJAX', $request->isAjax());
        defined('MODULE_NAME') or define('MODULE_NAME', $request->module());
        defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', $request->controller());
        defined('ACTION_NAME') or define('ACTION_NAME', $request->action());
        defined('URL') or define('URL', strtolower($request->controller() . SYS_DS_PROS . $request->action()));
        defined('URL_MODULE') or define('URL_MODULE', strtolower($request->module()) . SYS_DS_PROS . URL);

        $this->param = $request->param();
    }
}