<?php

namespace app\api\logic;

use think\Db;
use app\common\model\ClassModel;
use app\common\model\User;
use app\common\model\Files;
use app\common\model\FilesFolder;
/**
 * 文件
 */
class FilesLogic extends ApiBaseLogic
{

	public function __construct() {
        parent::__construct();
    }

    /**
     * 创建文件夹
     */
    public function createFolder($data)
    {
    	$filesFolderModel = new FilesFolder();
    	$data['create_time'] = time();
    	$info = $filesFolderModel->insertOne($data);
    	if ($info) {
    		return V(1,'success');
    	} else {
    		return V(0,'error');
    	}
    }

    /**
     * 获取用户文件夹列表
     */
    public function listUserFileFolder($folder_id=0)
    {
    	$filesFolderModel = new FilesFolder();
    	$where['owner_id'] = UID;
    	$where['type'] = 1;
    	$where['pid'] = $folder_id;
    	$field = "id, folder_name";
    	$order = "create_time desc";
    	$list = $filesFolderModel->getList($where, $field, 0, $order);
    	return $list;
    }

    /**
     * 获取用户文件列表
     * @param  $search : 搜索文件内容
     * @param  $file_type: 0:全部 1:文本(txt) 2:图片 3:压缩包 4:视频 5:音频 6:pdf或word
     * @param  $folder_id: 0:根目录 其他：文件夹ID
     * @param  $p:  分页
     */
    public function listUserFile($search, $file_type, $folder_id, $p)
    {
    	$filesModel = new Files();
    	if (!empty($search)) {
    		$where['file_name'] = array("like","%".$search."%");
    	}
    	if ($file_type != 0) {
    		$where['file_type'] = $file_type;
    	}
    	$where['owner_id'] = UID;
    	$where['type'] = 1;
    	$where['is_del'] = 1;
    	$field = "id, file_name, file_photo, file_url, file_size, file_type";
    	$order = "id desc";
    	$list = $filesModel->getList($where, $field, $p, $order);
    	return $list;
    }


    /**
     * 文件上传
     * @param  $owner_id :班级ID / 用户ID 
     * @param  $folder_id: 文件夹ID 0:根目录 其他：文件夹ID
     * @param  $file_name: 文件名称
     * @param  $file_photo:文件封面图
     * @param  $file_url: 文件地址
     * @param  $file_size: 文件大小 kb
     * @param  $type:      1:用户 2：班级
     */
    public function insertUserUpload($data)
    {
        $filesModel = new Files();
        $field = "used_storage_size, storage_size";
        $where['id'] = $data['owner_id'];
        Db::startTrans();
        if ($data['type'] == 1) {
            $data['owner_id'] = UID;
            //获取用户存储空间
            $where['id'] = UID;
            $storageModel = new User();
        } else {
            //获取班级存储空间
            $storageModel = new ClassModel();
        }
        $storage = $storageModel->getInfo($where, $field);
        //计算储存空间
        $storage_size = $storage['used_storage_size'] + $data['file_size'];
        if ($storage_size >= $storage['storage_size']) {
            return V(0,'储存空间不足');
        }
        $data['upload_user_id'] = UID;
        $files_info = $this->checkFilesType($data['file_name']);
        $data['file_type'] = $files_info['file_type'];
        $data['file_suffix'] = $files_info['file_suffix'];
        $data['create_time'] = time();
        $add = $filesModel->insertOne($data);
        if ($add) {
            //修改已用储存空间
            $storage_update_data['used_storage_size'] = $storage['used_storage_size'] + $data['file_size'];
            if ($storageModel->updateInfo($where,$storage_update_data)) {
                Db::commit();    
                return V(1,'success');
            } else {
                Db::rollback();
                return V(0,'error');    
            }
        } else {
            Db::rollback();
            return V(0,'error');
        }
    }

    /**
     * 文件类型检测
     */
    public function checkFilesType($file_name)
    {
        $photo_suffix = ['JPG','JPEG','PNG'];
        $package_suffix = ['ZIP','TAR'];
        $video_suffix = ['MP4','3GP','3GPP','RM','RMVB','AVI','WMV','MOV'];
        $audio_suffix = ['MP3','AAC','AAC+','WMA','RA'];
        $office_suffix = ['DOC','DOCM','DOCX','ODT','PDF','RTF','WPS','XPS','CSV','DBF','ODS','XLA','XLS','XLSB','XLSM','XLSX','XPS'];
        $suffix = explode(".", $file_name);
        $file_suffix = strtoupper(end($suffix));
        if ($file_suffix == 'txt') {
            $file_type = 1;
        } else if (in_array($file_suffix, $photo_suffix)) {
            $file_type = 2;
        } else if (in_array($file_suffix, $package_suffix)) {
            $file_type = 3;
        } else if (in_array($file_suffix, $video_suffix)) {
            $file_type = 4;
        } else if (in_array($file_suffix, $video_suffix)) {
            $file_type = 5;
        } else if (in_array($file_suffix, $office_suffix)) {
            $file_type = 6;
        } else {
            $file_type = 0;
        }
        $data['file_type'] = $file_type;
        $data['file_suffix'] = $file_suffix;
        return $data;
    }

}

























