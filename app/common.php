<?php

/**
 *  应用公共文件
 */
use think\Db;
use think\Session;

//用于测试打印数组数据
function p($arr) {
    header('content-type:text/html;charset=utf-8');
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
}

/**
 * 返回JSON统一格式
 */
function V($status = 0, $msg = '', $data = array()) {
    if (is_null($data)) {
        $data = array();
    }
    return array('status' => intval($status), 'msg' => $msg, 'data' => $data);
}
/**
 * 根据附件表的id返回url地址
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function geturl($id)
{
	if ($id) {
		$geturl = \think\Db::name("attachment")->where(['id' => $id])->find();
		if($geturl['status'] == 1) {
			//审核通过
			return $geturl['filepath'];
		} elseif($geturl['status'] == 0) {
			//待审核
			return '/uploads/xitong/beiyong1.jpg';
		} else {
			//不通过
			return '/uploads/xitong/beiyong2.jpg';
		} 
    }
    return false;
}




/**
 * 替换手机号码中间四位数字
 * @param  [type] $str [description]
 * @return [type]      [description]
 */
function hide_phone($str){
    $resstr = substr_replace($str,'****',3,4);  
    return $resstr;  
}


/**
 * 检查手机号码格式
 * @param $mobile 手机号码
 */
function check_mobile($mobile) {
    if ( !empty($mobile) ) {
        if( preg_match("/^1[3456789]\d{9}$/", $mobile) ){
            return true;
        }
    }
    return false;
}

/**
 * 计算中英文字符串长度的方法
 */
function _strlen($str)  
{  
    preg_match_all("/./us", $str, $matches);  
    return count(current($matches));  
}



/**
 * 图片上传
 * @param $image
 */
function upload($image){
    // 获取表单上传文件 例如上传了001.jpg
    $file = $image;
    // 移动到框架应用根目录/public/uploads/ 目录下
    $info = $file->validate(['size'=>1024 * 1024 * 10,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'photo');
    if($info){
        return V(1,"uploads".DS."photo".DS.$info->getSaveName());
    }else{
        // 上传失败获取错误信息
        return V(0,$file->getError());
    }
}

/**
 * 图片上传
 * @param $image
 */
function upload_img($image){
    // 获取表单上传文件 例如上传了001.jpg
    $file = $image;
    // 移动到框架应用根目录/public/uploads/ 目录下
    $info = $file->validate(['size'=>1024 * 1024 * 10,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'photo'.DS.'chat_img');
    if($info){
        $data['path'] = "uploads".DS."photo".DS."chat_img".DS.$info->getSaveName();
        $data['file_name'] = $info->getSaveName();
        return V(1,"上传成功", $data);
    }else{
        // 上传失败获取错误信息
        return V(0,$file->getError());
    }
}

/**
 * mp3上传
 * @param $file
 */
function upload_audio($file){
    // 移动到框架应用根目录/public/uploads/ 目录下
    $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'photo'.DS.'chat_file');
    if($info){
        $data['path'] = "uploads".DS."photo".DS."chat_file".DS.$info->getSaveName();
        $data['file_name'] = $info->getSaveName();
        return V(1,"上传成功", $data);
    }else{
        // 上传失败获取错误信息
        return V(0,$file->getError());
    }
}

//第一个是原串,第二个是 部份串
function endWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}

/**
 * 获取缓存标签
 */
function get_cache_tag($name, $join = null) {

    $table_string = strtolower($name);

    if (!empty($join)) {

        foreach ($join as $v) {

            $names = explode(' ', $v[0]);

            $table_name = str_replace('_', '', str_replace(SYS_DB_PREFIX, '', $names[0]));

            $table_string .= strtolower($table_name);
        }
    }

    $auto_cache_info = cache(AUTO_CACHE_KEY);

    empty($auto_cache_info[CACHE_TABLE_KEY][$table_string]) && $auto_cache_info[CACHE_TABLE_KEY][$table_string][CACHE_VERSION_KEY] = DATA_DISABLE;

    $auto_cache_info[CACHE_TABLE_KEY][$table_string][CACHE_LAST_GET_TIME_KEY] = TIME_NOW;

    cache(AUTO_CACHE_KEY, $auto_cache_info, config('CACHE_OUT_TIME'));

    return $table_string;
}

/**
 * 获取缓存key
 */
function get_cache_key($name, $where, $field, $order, $paginate, $join, $group, $limit, $data, $cache_tag) {

    $auto_cache_info = cache(AUTO_CACHE_KEY);


    $version = '';

    if (!empty($join)) {

        foreach ($join as $v) {

            $names = explode(' ', $v[0]);

            $table_name = strtolower(str_replace('_', '', str_replace(SYS_DB_PREFIX, '', $names[0])));

            $version .= $auto_cache_info[CACHE_TABLE_KEY][$table_name][CACHE_VERSION_KEY];
        }
    } else {

        $version .= $auto_cache_info[CACHE_TABLE_KEY][strtolower($name)][CACHE_VERSION_KEY];
    }

    $param = request()->param();

    //compact 创建一个包含变量名和它们的值的数组
    //根据所传的变量生成一个唯一的key
    $key = md5(serialize(compact('name', 'where', 'field', 'order', 'paginate', 'join', 'group', 'limit', 'data', 'param', 'version')));

    $auto_cache = check_cache_key($key, $auto_cache_info, $cache_tag);

    cache(AUTO_CACHE_KEY, $auto_cache, config('CACHE_OUT_TIME'));

    return $key;
}

/**
 * 检查缓存key
 */
function check_cache_key($key = null, $auto_cache_info = null, $cache_tag = null) {
    if (!is_array($auto_cache_info[CACHE_CACHE_KEY])) {
        return $auto_cache_info;
    }
    if (count($auto_cache_info[CACHE_CACHE_KEY]) >= $auto_cache_info[CACHE_MAX_NUMBER_KEY]) {

        unset($auto_cache_info[CACHE_CACHE_KEY][DATA_DISABLE]);

        $auto_cache_info[CACHE_CACHE_KEY] = array_values($auto_cache_info[CACHE_CACHE_KEY]);
    }

    if (!in_array($key, $auto_cache_info[CACHE_CACHE_KEY])) {

        $auto_cache_info[CACHE_CACHE_KEY][] = $key;

        isset($auto_cache_info[CACHE_TABLE_KEY][$cache_tag][CACHE_NUMBER_KEY]) ? $auto_cache_info[CACHE_TABLE_KEY][$cache_tag][CACHE_NUMBER_KEY]++ : $auto_cache_info[CACHE_TABLE_KEY][$cache_tag][CACHE_NUMBER_KEY] = DATA_NORMAL;
        isset($auto_cache_info[CACHE_NUMBER_KEY]) ? $auto_cache_info[CACHE_NUMBER_KEY]++ : $auto_cache_info[CACHE_NUMBER_KEY] = DATA_NORMAL;
    }

    return $auto_cache_info;
}


/**
  * 今天开始时间 和结束时间
  * @param int begintime 开始时间
  * @param int endtime 结束时间
  */
function today_time($time = "")
{
    if (empty($time)) {
        $time = time();
    }
    $begintime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m',$time),date('d',$time),date('Y',$time))));
    $endtime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m',$time),date('d',$time)+1,date('Y',$time))-1));
    return array("begintime"=>$begintime,"endtime"=>$endtime);
}

/**
 * 准备工作完毕 开始计算年龄函数
 * @param  $birthday 出生时间 uninx时间戳
 * @param  $time 当前时间
 **/
function getAge($birthday)
{
    if (empty($birthday)) {
        $birthday = time();
    } else {
        $birthday = strtotime($birthday);
    }
    //格式化出生时间年月日
    $byear = date("Y", $birthday);
    $bmonth = date("m", $birthday);
    $bday = date("d", $birthday);
    //格式化当前年月日
    $tyear = date("Y");
    $tmonth = date("m");
    $tday = date("d");
    //开始计算年龄
    $age = $tyear - $byear;
    if ($bmonth > $tmonth || $bmonth == $tmonth && $bday > $tday) {
        $age--;
    }
    return $age;
}

/**
 * 时间格式化
 * @param $time 时间戳
 * @param string $format 输出格式
 */
function time_format($time = NULL, $format = 'Y-m-d H:i') {
    if (empty($time)) {
        $time = time();
    }
    $time = intval($time);
    return date($format, $time);
}

/**
 * 验证码 检测
 */
function checkSmsCode($email, $code, $type=1)
{
    //检测手机号
    if (empty($email)) {
        return V(0,'The mailbox cannot be empty');
    }
    if (checkEmail($email) === false) {
        return V(0,'Email format error');
    }
    if (empty($code)) {
        return V(0,'The captcha cannot be empty');
    }
    $where['email'] = $email;
    $where['code'] = $code;
    $where['type'] = $type;
    $where['status'] = 0;
    $info = Db::name("sms_log")->where($where)->order("id desc")->find();
    if ($info) {
        $time = time() - $info['create_time'];
        if ($time > 900) {
            return V(0,'The captcha has expired');
        } else {
            return V(1,'success');
        }
    } else {
        return V(0,'Verification code or mailbox error');
    }
}

//获取指定长度的随机字符串
function getRandChar($length=32){
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol)-1;

    for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }
    return $str;
}
/*
 *获取当前服务器的IP
*/
function get_client_ip()
{
    if ($_SERVER['REMOTE_ADDR']) {
        $cip = $_SERVER['REMOTE_ADDR'];
    } elseif (getenv("REMOTE_ADDR")) {
        $cip = getenv("REMOTE_ADDR");
    } elseif (getenv("HTTP_CLIENT_IP")) {
        $cip = getenv("HTTP_CLIENT_IP");
    } else {
        $cip = "127.0.0.1";
    }
    return $cip;
}

/**
 * 打印支付log
 * @param null $msg
 * @param null $name
 */
function pay_log($msg=null, $name=null)
{
    // 日志文件名：日期.txt
    $path = ROOT_PATH.DS.'public'. DS .'logs'. DS .date("YmdHis").$name.'.txt';

    file_put_contents($path, $msg.PHP_EOL,FILE_APPEND);
}

/**
 * @param $birthday : 用户生日(2017-12-20)
 * @return false|string
 * 根据生日计算年龄
 */
function birthday($birthday) {
    if (!$birthday) {
        return false;
    }
    if ($birthday < 1) {
        $birthday = date("Y-m-d",time());
    }
    $birthdays = explode("-", $birthday);
    if (count($birthdays) < 2) {
        $birthday = date("Y-m-d",$birthday);
    }
    list($year, $month, $day) = explode("-", $birthday);
    $year_diff = date("Y") - $year;
    $month_diff = date("m") - $month;
    $day_diff = date("d") - $day;
    if ($day_diff < 0 || $month_diff < 0) {
        $year_diff--;
    }
    if ($year_diff == -1) {
        $year_diff = 0;
    }

    return $year_diff;
}

/**
 * ios 文件上传
 */
function ios_upload($file)
{
    header("Content-Type: application/octet-stream");
    $byte = $file; 
    $byte = str_replace(' ','',$byte);   //处理数据
    $byte = str_ireplace("<",'',$byte);
    $byte = str_ireplace(">",'',$byte); 
    $byte = pack("H*",$byte);      //16进制转换成二进制
    $rand = rand(1,999).date("YmdHis");
    $img_name = uniqid($rand).".jpg";
    $photo = 'uploads'.DS.'photo'.DS.date("Ymd",time()).DS;
    if (file_exists($photo)) {
        $photo = $photo.$img_name;
    } else {
        mkdir($photo,0777);
        $photo = $photo.$img_name;
    }
    file_put_contents($photo,$byte);//写入文件中！
    return $photo;
}




/**
 * 设置session
 */
function add_session ($uid) 
{
    Session::set($uid,time());
}

/**
 * 获取session
 */
function get_session($uid)
{
    $session_time = Session::get($uid);
    $time = time() - $session_time;
    if ($time > 5) {
        return true;
    } else {
        return false;
    }
}


/**
 * 邮箱正则表达式
 */
function checkEmail($email)
{
    if ( !empty($email) ) {
        $preg_match = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/";
        if( preg_match($preg_match, $email) ){
            return true;
        }
    }
    return false;
}

/*
* 检查密码复杂度
*/
function checkPassword($pwd) {
    if ($pwd == null) {
        return ['code' => 0, 'data' => '', 'msg' => 'The password cannot be empty'];
    }
    $pwd = trim($pwd);
    if (!strlen($pwd) >= 6) {//必须大于6个字符
        return ['code' => 1, 'data' => '', 'msg' => 'Password must be greater than 6 characters'];
    }
    if (preg_match("/^[0-9]+$/", $pwd)) { //必须含有特殊字符 密码不能全是数字，请包含数字，字母大小写或者特殊字符
        return ['code' => 2, 'data' => '', 'msg' => 'Passwords should not be all Numbers, Include Numbers, alphabetic case, or special characters'];
    }
    if (preg_match("/^[a-zA-Z]+$/", $pwd)) {//密码不能全是字母，请包含数字，字母大小写或者特殊字符
        return ['code' => 3, 'data' => '', 'msg' => 'Passwords should not be all letters, Include Numbers, letter case, or special characters'];
    }
    if (preg_match("/^[0-9A-Z]+$/", $pwd)) {//请包含数字，字母大小写或者特殊字符
        return ['code' => 4, 'data' => '', 'msg' => 'Please include Numbers, letter case, or special characters'];
    }
    if (preg_match("/^[0-9a-z]+$/", $pwd)) {//请包含数字，字母大小写或者特殊字符
        return ['code' => 5, 'data' => '', 'msg' => 'Please include Numbers, letter case, or special characters'];
    }
    return ['code' => 6, 'data' => '', 'msg' => 'Password complexity is verified'];
}


/**
 * 密码加密
 */
function pwd_md5($pwd)
{
    $key = 'classRoom';
    $pwd = $key.$pwd.$key;
    $password = md5(sha1($pwd));
    return $password;
}



/**
 * 随机数生成
 */
function createRandom($length=6)
{
    // 字符集，可任意添加你需要的字符
    $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 
    'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's', 
    't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D', 
    'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O', 
    'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z', 
    '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', 
    '@','#', '$', '%', '^', '&', '*', '(', ')', '-', '_', 
    '[', ']', '{', '}', '<', '>', '~', '`', '+', '=', ',', 
    '.', ';', ':', '/', '?', '|');
    // 在 $chars 中随机取 $length 个数组元素键名
    $keys = array_rand($chars, $length); 
    $num = '';
    for($i = 0; $i < $length; $i++)
    {
        // 将 $length 个数组元素连接成字符串（ascll解析成数字）
        $num .= ord($chars[$keys[$i]]);
    }
    // $num = 1137383848788333812;
    //数字修改为数组
    $len_num = strlen($num);
    $num_arr = [];
    for ($i=0; $i < $len_num; $i++) { 
        $num_arr[$i] = $num[$i];
    }
    //随机排序数组
    shuffle($num_arr);
    //获取 $length 位数
    $r_num = null;
    for ($i=0; $i < $length; $i++) { 
        $num_type = 0;
        if ($i == 0) {
            if ($num_arr[$i] == 0) {
                $num_type = 1;
            }
        }
        if ($num_type == 1) {
            $r_num .= mt_rand(1,9);
        } else {
            $r_num .= $num_arr[$i];
        }
    }
    return $r_num;
}





































