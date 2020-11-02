<?php

namespace app\common\controller;
use think\Controller;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
/**
 * Monolog 日志
 */
class MonoLog extends Controller {

    public $day_time;
    public $url;
    public $fun_name;
    /**
     * 
     */
    public function __construct()
    {
    	$url_info = $_SERVER['REQUEST_URI'];
    	$this->url['url'] = $url_info;
    	$list_url = explode("/", $url_info);
    	$this->fun_name = "方法: ".end($list_url);
    	$this->day_time = date("Y-m-d",time());
        
    }

    /**
     * debug
     * $info 数据
     */
    public function addDebug($info)
    {
        // 创建日志服务
		$logger = new Logger("debug");
		$stream = new StreamHandler('./monolog/'.$this->day_time.'/debug.log', Logger::DEBUG);
		$logger->pushHandler($stream);
		$logger->info($this->fun_name, $this->url, $info);
    } 
}
