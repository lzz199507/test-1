<?php

namespace app\common\controller;
use think\Controller;
/**
 * 对称加密
 */
class OpensslEncrypt extends Controller {

    private $v = '8ebc2cbb4c21a29d'; //class
    private $k = 'cc78ec6fb82c9796'; //line_class
    
    /**
     * 加密字符串
     */
    public static function encrypt($strContent){
        $ssl = new OpensslEncrypt;
        $strEncrypted = openssl_encrypt($strContent,"AES-128-CBC", $ssl->k,OPENSSL_RAW_DATA, $ssl->v);
        return base64_encode($strEncrypted);
    }
    /**
     * 解密字符串
     */
    public static function decrypt($strEncryptCode){
        $ssl = new OpensslEncrypt;
        $strEncrypted = base64_decode($strEncryptCode);
        return openssl_decrypt($strEncrypted,"AES-128-CBC",$ssl->k,OPENSSL_RAW_DATA,$ssl->v);
    }


    

}
