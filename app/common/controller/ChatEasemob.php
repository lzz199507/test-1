<?php

namespace app\common\controller;
use think\Controller;
use extend\emchat\Easemob;
require_once '../extend/emchat/Easemob.class.php';
/**
 * 环信聊天
 */
class ChatEasemob extends Controller {

    private static $emchat = null;
    public function __construct()
    {
        self::$emchat = new Easemob();
    }

    /**
     * 注册
     * $id:环信 ID ;也就是 IM 用户的唯一登录账号
     * $nick_name : 昵称
    */
    public function easemobRegister($id, $password, $nick_name)
    {
        $info = self::$emchat->createUser($id,$password,$nick_name);
        if (isset($info['error'])) {
            return V(0,'注册失败-easemob');
        } else {
            return V(1,'注册成功');
        }
    }

    /**
     * 发送消息给个人
     */
    public function easemobSendText($data)
    {
        $info = self::$emchat->sendText($data['from'], $data['target_type'], $data['target'], $data['msg']);
        if (isset($info['error'])) {
            return V(0,'环信发送失败');
        } else {
            return V(1,'成功');
        }
    }

    /**
     * 发送消息给个人(文件 图片、语音)
     * $type   1：文字 2：语音 3：图片 4：闪音 5：闪图 6：音色接唱  7:音色回应 
     */
    public function easemobSendFile($data, $file_info, $type='2')
    {
        $file_name = $file_info['file_name'];
        if ($type == 2 || $type == 4) {
            //语音消息
            $info = self::$emchat->sendAudio($file_info['path'], $data['from'], $data['target_type'], $data['target'], $file_name, $data['length'], $data['ext']);    
        } else {
            //图片消息
            $info = self::$emchat->sendImage($file_info['path'], $data['from'], $data['target_type'], $data['target'], $file_name, $data['ext']);
        }
        if (isset($info['error'])) {
            return V(0,'环信发送失败');
        } else {
            return V(1,'成功');
        }
    }


    /**
     * 自定义消息
     */
    public function easemobSendExtend($data)
    {
        $info = self::$emchat->sendExtend($data['from'], $data['target_type'], $data['target'], $data['msg'], $data['ext']);
        if (isset($info['error'])) {
            return V(0,'发送失败');
        } else{
            return V(1,'成功');
        }
    }


    /**
     * 加入黑名单
     */
    public function addUserForBlacklist($uid, $s_uid)
    {
        $info = self::$emchat->addUserForBlacklist($uid, $s_uid);
        if (isset($info['error'])) {
            return V(0,'失败');
        } else{
            return V(1,'成功');
        }
    }
    
    /**
     * 加入黑名单
     */
    public function deleteUserFromBlacklist($uid, $s_uid)
    {
        $info = self::$emchat->deleteUserFromBlacklist($uid, $s_uid);
        if (isset($info['error'])) {
            return V(0,'失败');
        } else{
            return V(1,'成功');
        }
    }

}
