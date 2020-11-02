<?php

/**
 * 首页
 */

namespace app\api\controller;
use Think\Db;
use think\Request;
use app\api\logic\BottleLogic;
use app\api\logic\ToneUserLogic;
use app\common\model\BottleContent;
class Index extends ApiBase
{
	/**
     * @var \app\api\logic\OrderLogic
     */
    private static $bottleLogic = null;
    private static $bottleContentModel = null;
    private static $toneUserLogic = null;
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
        self::$bottleLogic = BottleLogic::getInstance();
        self::$toneUserLogic = ToneUserLogic::getInstance();
        self::$bottleContentModel = BottleContent::getInstance();
    }
    
}