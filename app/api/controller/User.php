<?php

/**
 * 用户信息
 */

namespace app\api\controller;
use Think\Db;
use think\Request;
use app\api\logic\UserLogic;
class User extends ApiBase
{
	/**
     * @var \app\api\logic\OrderLogic
     */
    private static $logic = null;
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
        self::$logic = UserLogic::getInstance();
    }

    /**
     * 获取个人资料
     */
    public function getUserInfo()
    {
        
    }
}