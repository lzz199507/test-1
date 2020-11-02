<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 核心中文语言包
return [
    //
    // 'Your account has been logged in on another device'         => '您的账号已在其它设备登录',
    'login overdue'                                             => '您的账号已在其它设备登录',
    'please log'                                                => '请登录',
    'submit bug'                                                => '提交方式有误',
    'user disable'                                              => '该用户已被禁用',
    'user there no'                                             => '用户不存在',
    'user cannot empty'                                         => '接收信息用户不能为空',
    'parameter error'                                           => '参数错误',
    'class id error'                                            => '班级ID错误',
    'error start or end time'                                   => '开始或结束时间错误',
    'not upload picture attachments'                            => '不能上传图片或附件',
    'not upload video'                                          => '请勿上传视频',
    'not less RT'                                               => '不能小于当前时间',
    'email format error'                                        => '电子邮件格式错误',
    'once minute'                                               => '每分钟只能发送一次',
    'server error'                                              => '服务器错误',
    'confirm password error'                                    => '确认输入的密码不正确',
    'parents cannot apply'                                      => '父母不可以申请加入',
    'already class'                                             => '你已经在班级里了',
    'invite class'                                              => '邀请去上课',
    'join class success'                                        => '加入班级成功',
    'quit class success'                                        => '退出班级成功',
    'refuse apply'                                              => '申请拒绝',
    'please resubmit'                                           => '请重新提交',
    'failed create class'                                       => '创建班级失败',
    'class qr error'                                                  => '班级二维码生成失败。请重新创建班级',
    'register failed'                                           => '注册失败',
    'account password error'                                    => '帐户或密码不正确',
    'unusual account'                                           => '帐号不正常，无法登录',
    'repetition register email'                                 => '这个邮箱已注册',
    'qr error'                                                  => '二维码生成错误',
    "class name empty"                                          => '班级名不能为空',
    'please upload class subject'                               => '请上传课程主题',
    "subject name empty"                                        => '课程名称不能为空',
    'classes empty'                                             => '班级不能为空',
    'please select students'                                    => '请选择学生',
    'please title'                                              => '请填写标题',
    'please start time'                                         => '请填写开始时间',
    'please end time'                                           => '请填写结束时间',
    'please survey options'                                     => '请添加调查选项',
    'please sign'                                               => '请添加签到信息',
    'please correct start time'                                 => '请填写正确的开始时间',
    'Please correct end time'                                   => '请填写正确的结束时间',
    'upload type error'                                         => '上传类型错误',
    'type parameter error'                                      => '类型参数错误',
    'upload user empty'                                         => '上传用户不能为空',
    'owner not numbers'                                         => 'owner 不是数字',
    'folder name empty'                                         => '文件夹名称不能为空',
    'files name empty'                                          => '文件名不能为空',
    'files address empty'                                       => '文件地址不能为空',
    'files size empty'                                          => '文件大小不能为空',
    'files size not numbers'                                    => '文件大小而不是数字',
    'please fill first name'                                    => '请填写名',
    'please fill last name'                                     => '请填写您的姓',
    'please fill email'                                         => '请填写邮箱',
    'please fill password'                                      => '请填写密码',
    'please fill confirm pwd'                                   => '请填写确认密码',
    'please fill code'                                          => '请填写验证码',
    'email address format'                                      => '电邮地址格式不正确',
    "data error"                                                => '数据不存在',
    "class id error"                                            => '班级ID错误',
    "inform id error"                                           => '通知ID错误',





    // 系统错误提示
    'Undefined variable'                                        => '未定义变量',
    'Undefined index'                                           => '未定义数组索引',
    'Undefined offset'                                          => '未定义数组下标',
    'Parse error'                                               => '语法解析错误',
    'Type error'                                                => '类型错误',
    'Fatal error'                                               => '致命错误',
    'syntax error'                                              => '语法错误',

    // 框架核心错误提示
    'dispatch type not support'                                 => '不支持的调度类型',
    'method param miss'                                         => '方法参数错误',
    'method not exists'                                         => '方法不存在',
    'module not exists'                                         => '模块不存在',
    'controller not exists'                                     => '控制器不存在',
    'class not exists'                                          => '类不存在',
    'property not exists'                                       => '类的属性不存在',
    'template not exists'                                       => '模板文件不存在',
    'illegal controller name'                                   => '非法的控制器名称',
    'illegal action name'                                       => '非法的操作名称',
    'url suffix deny'                                           => '禁止的URL后缀访问',
    'Route Not Found'                                           => '当前访问路由未定义',
    'Undefined db type'                                         => '未定义数据库类型',
    'variable type error'                                       => '变量类型错误',
    'PSR-4 error'                                               => 'PSR-4 规范错误',
    'not support total'                                         => '简洁模式下不能获取数据总数',
    'not support last'                                          => '简洁模式下不能获取最后一页',
    'error session handler'                                     => '错误的SESSION处理器类',
    'not allow php tag'                                         => '模板不允许使用PHP语法',
    'not support'                                               => '不支持',
    'redisd master'                                             => 'Redisd 主服务器错误',
    'redisd slave'                                              => 'Redisd 从服务器错误',
    'must run at sae'                                           => '必须在SAE运行',
    'memcache init error'                                       => '未开通Memcache服务，请在SAE管理平台初始化Memcache服务',
    'KVDB init error'                                           => '没有初始化KVDB，请在SAE管理平台初始化KVDB服务',
    'fields not exists'                                         => '数据表字段不存在',
    'where express error'                                       => '查询表达式错误',
    'not support data'                                          => '不支持的数据表达式',
    'no data to update'                                         => '没有任何数据需要更新',
    'miss data to insert'                                       => '缺少需要写入的数据',
    'miss complex primary data'                                 => '缺少复合主键数据',
    'miss update condition'                                     => '缺少更新条件',
    'model data Not Found'                                      => '模型数据不存在',
    'table data not Found'                                      => '表数据不存在',
    'delete without condition'                                  => '没有条件不会执行删除操作',
    'miss relation data'                                        => '缺少关联表数据',
    'tag attr must'                                             => '模板标签属性必须',
    'tag error'                                                 => '模板标签错误',
    'cache write error'                                         => '缓存写入失败',
    'sae mc write error'                                        => 'SAE mc 写入错误',
    'route name not exists'                                     => '路由标识不存在（或参数不够）',
    'invalid request'                                           => '非法请求',
    'bind attr has exists'                                      => '模型的属性已经存在',
    'relation data not exists'                                  => '关联数据不存在',
    'relation not support'                                      => '关联不支持',
    'chunk not support order'                                   => 'Chunk不支持调用order方法',
    'closure not support cache(true)'                           => '使用闭包查询不支持cache(true)，请指定缓存Key',

    // 上传错误信息
    'unknown upload error'                                      => '未知上传错误！',
    'file write error'                                          => '文件写入失败！',
    'upload temp dir not found'                                 => '找不到临时文件夹！',
    'no file to uploaded'                                       => '没有文件被上传！',
    'only the portion of file is uploaded'                      => '文件只有部分被上传！',
    'upload File size exceeds the maximum value'                => '上传文件大小超过了最大值！',
    'upload write error'                                        => '文件上传保存错误！',
    'has the same filename: {:filename}'                        => '存在同名文件：{:filename}',
    'upload illegal files'                                      => '非法上传文件',
    'illegal image files'                                       => '非法图片文件',
    'extensions to upload is not allowed'                       => '上传文件后缀不允许',
    'mimetype to upload is not allowed'                         => '上传文件MIME类型不允许！',
    'filesize not match'                                        => '上传文件大小不符！',
    'directory {:path} creation failed'                         => '目录 {:path} 创建失败！',

    // Validate Error Message
    ':attribute require'                                        => ':attribute不能为空',
    ':attribute must be numeric'                                => ':attribute必须是数字',
    ':attribute must be integer'                                => ':attribute必须是整数',
    ':attribute must be float'                                  => ':attribute必须是浮点数',
    ':attribute must be bool'                                   => ':attribute必须是布尔值',
    ':attribute not a valid email address'                      => ':attribute格式不符',
    ':attribute not a valid mobile'                             => ':attribute格式不符',
    ':attribute must be a array'                                => ':attribute必须是数组',
    ':attribute must be yes,on or 1'                            => ':attribute必须是yes、on或者1',
    ':attribute not a valid datetime'                           => ':attribute不是一个有效的日期或时间格式',
    ':attribute not a valid file'                               => ':attribute不是有效的上传文件',
    ':attribute not a valid image'                              => ':attribute不是有效的图像文件',
    ':attribute must be alpha'                                  => ':attribute只能是字母',
    ':attribute must be alpha-numeric'                          => ':attribute只能是字母和数字',
    ':attribute must be alpha-numeric, dash, underscore'        => ':attribute只能是字母、数字和下划线 及破折号-',
    ':attribute not a valid domain or ip'                       => ':attribute不是有效的域名或者IP',
    ':attribute must be chinese'                                => ':attribute只能是汉字',
    ':attribute must be chinese or alpha'                       => ':attribute只能是汉字、字母',
    ':attribute must be chinese,alpha-numeric'                  => ':attribute只能是汉字、字母和数字',
    ':attribute must be chinese,alpha-numeric,underscore, dash' => ':attribute只能是汉字、字母、数字和下划线 及破折号-',
    ':attribute not a valid url'                                => ':attribute不是有效的URL地址',
    ':attribute not a valid ip'                                 => ':attribute不是有效的IP地址',
    ':attribute must be dateFormat of :rule'                    => ':attribute必须使用日期格式 :rule',
    ':attribute must be in :rule'                               => ':attribute必须在 :rule 范围内',
    ':attribute be notin :rule'                                 => ':attribute不能在 :rule 范围内',
    ':attribute must between :1 - :2'                           => ':attribute只能在 :1 - :2 之间',
    ':attribute not between :1 - :2'                            => ':attribute不能在 :1 - :2 之间',
    'size of :attribute must be :rule'                          => ':attribute长度不符合要求 :rule',
    'max size of :attribute must be :rule'                      => ':attribute长度不能超过 :rule',
    'min size of :attribute must be :rule'                      => ':attribute长度不能小于 :rule',
    ':attribute cannot be less than :rule'                      => ':attribute日期不能小于 :rule',
    ':attribute cannot exceed :rule'                            => ':attribute日期不能超过 :rule',
    ':attribute not within :rule'                               => '不在有效期内 :rule',
    'access IP is not allowed'                                  => '不允许的IP访问',
    'access IP denied'                                          => '禁止的IP访问',
    ':attribute out of accord with :2'                          => ':attribute和确认字段:2不一致',
    ':attribute cannot be same with :2'                         => ':attribute和比较字段:2不能相同',
    ':attribute must greater than or equal :rule'               => ':attribute必须大于等于 :rule',
    ':attribute must greater than :rule'                        => ':attribute必须大于 :rule',
    ':attribute must less than or equal :rule'                  => ':attribute必须小于等于 :rule',
    ':attribute must less than :rule'                           => ':attribute必须小于 :rule',
    ':attribute must equal :rule'                               => ':attribute必须等于 :rule',
    ':attribute has exists'                                     => ':attribute已存在',
    ':attribute not conform to the rules'                       => ':attribute不符合指定规则',
    'invalid Request method'                                    => '无效的请求类型',
    'invalid token'                                             => '令牌数据无效',
    'not conform to the rules'                                  => '规则错误',
];