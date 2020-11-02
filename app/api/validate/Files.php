<?php
/**
 * 文件验证
 */
namespace app\api\validate;

class Files extends \think\Validate {
    protected $rule = [
        'type' => 'require|between:1,2',
        'owner_id' => 'require|number',
        'folder_name' => 'require|check_folder',
        'file_name' => 'require',
        'file_url' => 'require',
        'file_size' => 'require|number',
    ];

    protected $message = [
    	'type.require' => 'Upload type error',
    	'type.between' => 'type Parameter error',
        'owner_id.require' => 'The upload user cannot be empty',
        'owner_id.number' => 'owner Not Numbers',
        'folder_name.require' => 'The folder name cannot be empty',
        'file_name.require' => 'The files name cannot be empty',
        'file_url.require' => 'The files address cannot be empty',
        'file_size.require' => 'The files size cannot be empty',
        'file_size.number' => 'files size Not Numbers',

    ];

    // 应用场景
    protected $scene = [
        'createFolder' => ['type','owner_id','folder_name'],
        'insertUserUpload' => ['type','owner_id','file_name','file_url','file_size'],

    ];

    /**
     * 文件夹名称验证
     */
    protected function check_folder($value)
    {
        $folder_name = _strlen($value);
        if ($folder_name > 10) {
            return "Folder names cannot exceed 10 characters";
        }
        return true;
    }

}