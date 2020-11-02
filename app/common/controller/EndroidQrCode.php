<?php

namespace app\common\controller;
use think\Controller;
use Endroid\QrCode\QrCode;
use app\common\controller\OpensslEncrypt;
/**
 * 二维码
 */
class EndroidQrCode extends Controller {

    public $qrCode;
    public function __construct($info)
    {
        //数据加密
        $encrypt = new OpensslEncrypt();
        $encrypt_data = json_encode($info);
        $encrypt_data = $encrypt->encrypt($encrypt_data);
        $this->qrCode = new QrCode($encrypt_data);;
        try {
            $this->qrCode->setSize('300');
            $this->qrCode->setWriterByName('png');
        } catch (Exception $e) {
            return V(0,'qrCode Error');
        }
    }

    /**
     * 生成二维码
     */
    public function createQrCode($user_id)
    {
        $path = "/uploads/qrcode/".time().$user_id.".png";
        $this->qrCode->writeFile(".".$path);
        $url = $this->qrCode->writeDataUri();
        if ($url) {
            return V(1,'success', $path);
        } else {
            return V(0,'qrCode error');
        }
    }

    

}
