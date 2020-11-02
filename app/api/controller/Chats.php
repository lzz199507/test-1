<?php 
namespace app\api\controller;
use Think\Db;
use app\api\logic\ChatLogic;
use app\api\logic\ChatMessageLogic;
use app\api\logic\UserLogic;
class Chats extends ApiBase
{
	private static $chatLogic = null;
	private static $chatMessageLogic = null;
	private static $userLogic = null;

	/**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
        self::$chatLogic = ChatLogic::getInstance();
        self::$chatMessageLogic = ChatMessageLogic::getInstance();
        self::$userLogic = UserLogic::getInstance();
    }
    /**
     * 发送消息
     * $type:  消息类型 1：文字 2：语音 3：图片 4：闪音 5：闪图 6：自定义消息
     * $message : 用户发送消息内容
     * $receive_id:  接收消息用户ID
     * $length    :  语音、闪音时间 （单位：秒）
     */
    public function sendMessage()
    {
        $type = input("type",1);
        $message = input("message",'');
        $receive_id = input("receive_id",'');
        if ($receive_id == '') {
            $this->ajaxReturn(V(0,'接收信息用户不能为空'));
        }
        //检测是否拉黑
        $is_check = $this->check_black(UID, $receive_id);
        if ($is_check['status'] == 0) {
            $this->ajaxReturn($is_check);
        }
        $data['type'] = $type;
        $data['user_id'] = UID;
        $data['receive_id'] = $receive_id;
        if ($type == 1) {
            //普通文本
            $check_message = $this->checkSendMessage($message);
            if ($check_message['status'] == 0) {
                $this->ajaxReturn($check_message);
            }
            $data['message'] = $message;
            $message = self::$chatLogic->sendTxtMessage($data);
        } else if ($type == 2 || $type == 4) {
            //语音 或 闪音
            $file = input('file_site');
            if (!$file) {
                $this->ajaxReturn(V(0,'文件错误'));
            }
            $file_info['path'] = $file;
            $file_info['file_name'] = $file;
            $length = input("length",'0');
            if ($length == 0) {
                $this->ajaxReturn(V(0,'音频文件时间不能为空'));
            }
            $data['length'] = $length;
            $message = self::$chatLogic->sendFileMessage($data, $file_info);
        } else if ($type == 3 || $type == 5) {
            //图片 或 闪图
            $file = input('file_site');
            if (!$file) {
                $this->ajaxReturn(V(0,'文件错误'));
            }
            $file_info['path'] = $file;
            $file_info['file_name'] = $file;
            $message = self::$chatLogic->sendFileMessage($data, $file_info);
        }
        $this->ajaxReturn($message);
    }



    /**
     * 消息检测
     */
    public function checkSendMessage($message)
    {
        if ($message == '') {
            return  V(0,'发送消息不能为空');
        }
        if (_strlen($message) > 50) {
            return V(0,'不可超过50字');
        }
        return V(1,'成功');
    }


    /**
     * 黑名单检测
     */
    public function check_black($user_id, $receive_id)
    {
        $where['user_id'] = $user_id;
        $where['black_user_id'] = $receive_id;
        $is_check = Db::name("black_list")->where($where)->count();
        if ($is_check) {
            return V(0,'您已将对加入黑名单,无法发送消息');
        }
        $where['user_id'] = $receive_id;
        $where['black_user_id'] = $user_id;
        $is_check = Db::name("black_list")->where($where)->count();
        if ($is_check) {
            return V(0,'对方已将你拉入黑名单');
        }
        return V(1,'成功');
    }

}