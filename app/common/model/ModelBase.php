<?php

namespace app\common\model;

use think\Model;
use think\Db;
use think\Cache;

/**
 * 模型基类
 */
class ModelBase extends Model {

    // 查询对象
    private static $ob_query = null;

    public static function getInstance() {
        return new static();
    }

    /**
     * 数据单条查询 
     */
    public function getInfo($where,$field='*', $order="id desc")
    {
        $info = $this->where($where)->field($field)->order($order)->find();
        return $info;
    }
    /**
     * 查询某个字段
     */
    public function getValue($where, $value, $order='id desc')
    {
        $info = $this->where($where)->value($value);
        return $info;
    }
    /**
     * 数据查询 count 
     */
    public function getCount($where)
    {
        $count = $this->where($where)->count();
        return $count;
    }

    /**
     * 查询多条数据
     * $p 0:不需要分页 其他：分页
     */
    public function getList($where, $field="*", $p=0, $order="create_time desc")
    {
        if ($p == 0) {
            $list = $this->where($where)->field($field)->order($order)->select();
        } else {
            $list = $this->where($where)->field($field)->page($p, 10)->order($order)->select();
        }
        return $list;
    }
    /**
     * 查询多条数据 join
     * $p 0:不需要分页 其他：分页
     */
    public function getJoinList($where, $field="*", $p=0, $order="create_time desc")
    {
        if ($p == 0) {
            $list = $this->where($where)->field($field)->order($order)->select();
        } else {
            $list = $this->where($where)->field($field)->page($p, 10)->order($order)->select();
        }
        return $list;
    }
    /**
     * 获取数据集
     */
    public function getColumnList($where, $column)
    {
        $list = $this->where($where)->column($column);
        return $list;
    }
    /**
     * 添加一条数据
     * $type 1: 默认 2:获取ID
     */
    public function insertOne($data)
    {
        $info = $this->insertGetId($data);
        return $info;
    }

    /**
     * 更新数据
     */
    public function updateInfo($where, $data)
    {
        $update = $this->where($where)->update($data);
        return $update;
    }



}
