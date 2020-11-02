<?php

/**
 * 文件
 */

namespace app\api\controller;
use Think\Db;
use think\Request;
use app\api\logic\FilesLogic;
class Files extends ApiBase
{
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 创建文件夹
     * @param  $type :1:用户 2:班级
     * @param  $owner_id: 用户ID/班级ID
     * @param  $folder_name: 文件夹名称
     * @param  $pid: 上级文件夹ID
     */
    public function createFolder()
    {
        $filesLogic = new FilesLogic();
        $arr = array('type','owner_id','folder_name','pid');
        $data = Request::instance()->only($arr);
        $validate = validate('api/Files');
        if (!$validate->scene("createFolder")->check($data)) {
            $this->ajaxReturn(V(0,$validate->getError()));
        }
        if ($data['type'] == 1) {
            $data['owner_id'] = UID;
        }
        $data['create_user_id'] = UID;
        $info = $filesLogic->createFolder($data);
        $this->ajaxReturn($info);
    }

    /**
     * 用户上传
     * @param  $owner_id :班级ID / 用户ID 
     * @param  $folder_id: 文件夹ID 0:根目录 其他：文件夹ID
     * @param  $file_name: 文件名称
     * @param  $file_photo:文件封面图
     * @param  $file_url: 文件地址
     * @param  $file_size: 文件大小 kb
     * @param  $type:      1:用户 2：班级
     */
    public function insertUserUpload()
    {
        $filesLogic = new FilesLogic();
        $arr = array('owner_id','folder_id','file_name','file_photo','file_url','file_size','type');
        $data = Request::instance()->only($arr);
        $validate = validate('api/Files');
        if (!$validate->scene("insertUserUpload")->check($data)) {
            $this->ajaxReturn(V(0,$validate->getError()));
        }
        if (empty($data['folder_id'])) {
            $data['folder_id'] = 0;
        }
        $info = $filesLogic->insertUserUpload($data);
        $this->ajaxReturn($info);
    }

    /**
     * 获取用户文件列表
     * @param  $search : 搜索文件内容
     * @param  $file_type: 0:全部 1:文本(txt) 2:图片 3:压缩包 4:视频 5:音频 6:pdf或word
     * @param  $folder_id: 0:根目录 其他：文件夹ID
     * @param  $p:  分页
     */
    public function listUserFile()
    {
        $filesLogic = new FilesLogic();
        $p = input("p",1);
        $file_type = input("file_type",0);
        $folder_id = input("folder_id",0);
        $search = input("search",'');
        $files_list = $filesLogic->listUserFile($search, $file_type, $folder_id, $p);
        if ($p == 1 && empty($search) && $file_type == 0) {
            $folder_list = $filesLogic->listUserFileFolder($folder_id);
        } else {
            $folder_list = [];
        }
        $list['files_list'] = $files_list;
        $list['folder_list'] = $folder_list;
        $this->ajaxReturn(V(1,'success', $list));
    }
}





























