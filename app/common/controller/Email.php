<?php

namespace app\common\controller;
use think\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
/**
 * Email
 */
class Email extends Controller {

    public $mail;
    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        try {
            //服务器配置
            $this->mail->CharSet ="UTF-8";                     //设定邮件编码
            $this->mail->SMTPDebug = 0;                        // 调试模式输出
            $this->mail->isSMTP();                             // 使用SMTP
            $this->mail->Host = 'smtp.163.com';                // SMTP服务器
            $this->mail->SMTPAuth = true;                      // 允许 SMTP 认证
            $this->mail->Username = 'lzz199506@163.com';                // SMTP 用户名  即邮箱的用户名
            $this->mail->Password = 'BKEUYMHNTYKFUQNI';             // SMTP 密码  部分邮箱是授权码(例如163邮箱)
            $this->mail->SMTPSecure = 'ssl';                    // 允许 TLS 或者ssl协议
            $this->mail->Port = 465;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持
        } catch (Exception $e) {
            return V(0,'Email failed to send:'.$mail->ErrorInfo);
        }
    }

    /**
     * 发送邮件
     */
    public function sendMail($code='1234', $send_mail, $subject)
    {
        $this->mail->setFrom('lzz199506@163.com', 'Mailer');  //发件人
        $this->mail->addAddress($send_mail);  // 收件人
       

        //Content
        $this->mail->isHTML(true);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
        $mail_html = $this->getCodeTemplate($code);
        $this->mail->Subject = $subject;
        $this->mail->Body    = $mail_html;
        $this->mail->AltBody = '如果邮件客户端不支持HTML则显示此内容, 验证码为'.$code;
        $send_mail = $this->mail->send();
        if ($send_mail == 1) {
            return V(1,'Email has been sent');
        } else {
            return V(0,'Email failed to send');
        }
    }

    /**
     * 验证码模版
     */
    public function getCodeTemplate($code='1234')
    {
        $html = '<base target="_blank" /><style type="text/css">::-webkit-scrollbar{ display: none; }</style><style id="cloudAttachStyle" type="text/css">#divNeteaseBigAttach, #divNeteaseBigAttach_bak{display:none;}</style><style id="blockquoteStyle" type="text/css">blockquote{display:none;}</style><style type="text/css">body{font-size:14px;font-family:arial,verdana,sans-serif;line-height:1.666;padding:0;margin:0;overflow:auto;white-space:normal;word-wrap:break-word;min-height:100px}td, input, button, select, body{font-family:Helvetica, "Microsoft Yahei", verdana} pre {white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word;width:95%}th,td{font-family:arial,verdana,sans-serif;line-height:1.666}img{ border:0}
        header,footer,section,aside,article,nav,hgroup,figure,figcaption{display:block}
        blockquote{margin-right:0px}</style><table width="700" border="0" align="center" cellspacing="0" style="width:700px;"> <tbody><tr><td><div style="width:700px;margin:0 auto;border-bottom:1px solid #ccc;margin-bottom:30px;"><table border="0" cellpadding="0" cellspacing="0" width="700" height="39" style="font:12px Tahoma, Arial, 宋体;">
                    <tbody><tr><td width="210"></td></tr></tbody></table>
            </div><div style="width:680px;padding:0 10px;margin:0 auto;"> <div style="line-height:1.5;font-size:14px;margin-bottom:25px;color:#4d4d4d;"> <strong style="display:block;margin-bottom:15px;">尊敬的用户：<span style="color:#f60;font-size: 16px;"></span>您好！</strong> <strong style="display:block;margin-bottom:15px;">请在验证码输入框中输入：<span style="color:#f60;font-size: 24px">'.$code.'</span>，以完成操作。</strong></div>
            </div> <div style="width:700px;margin:0 auto;"> <div style="padding:10px 10px 0;border-top:1px solid #ccc;color:#747474;margin-bottom:20px;line-height:1.3em;font-size:12px;"><p>此为系统邮件，请勿回复<br />请保管好您的邮箱，避免账号被他人盗用
                    </p><p>企耀网络</p></div></div></td></tr></tbody></table><br />';
        
        return $html;
    }

    

}
