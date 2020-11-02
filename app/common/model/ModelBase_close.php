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
     * 更新缓存版本号
     */
    public function updateCacheVersion() {

        set_cache_version($this->name);
    }

    /**
     * 状态获取器
     */
    public function getStatusTextAttr() {

        $status = [DATA_DELETE => '删除', DATA_DISABLE => '禁用', DATA_NORMAL => '启用'];

        return $status[$this->data[DATA_STATUS_NAME]];
    }

    /**
     * 设置数据
     */
    public function setInfo($data = [], $where = [], $sequence = null) {

        $pk = $this->getPk();

        $return_data = null;

        if (empty($data[$pk])) {

            $return_data = $this->allowField(true)->save($data, $where, $sequence);

            $return_data && $this->updateCacheVersion();
        } else {

            is_object($data) && $data = $data->toArray();

            !empty($data[TIME_CT_NAME]) && is_string($data[TIME_CT_NAME]) && $data[TIME_CT_NAME] = strtotime($data[TIME_CT_NAME]);

            $default_where[$pk] = $data[$pk];

            $return_data = $this->updateInfo(array_merge($default_where, $where), $data);
        }

        return $return_data;
    }

    /**
     * 新增数据
     */
    public function addInfo($data = [], $is_return_pk = true) {

        $data[TIME_CT_NAME] = TIME_NOW;

        $return_data = $this->save($data);//insert($data, false, $is_return_pk);

        $return_data && $this->updateCacheVersion();

        if ($is_return_pk) {
            return $this->getLastInsID();//返回主键id
        } else {
            return $return_data;//返回是否成功
        }


    }

    /**
     * 更新数据
     */
    public function updateInfo($where = [], $data = []) {

        $data[TIME_UT_NAME] = TIME_NOW;

        $return_data = $this->allowField(true)->where($where)->update($data);

        $return_data && $this->updateCacheVersion();

        return $return_data;
    }

    /**
     * 统计数据
     */
    public function stat($where = [], $stat_type = 'count', $field = 'id') {

        return $this->where($where)->$stat_type($field);
    }

    /**
     * 设置数据列表
     */
    public function setList($data_list = [], $replace = false) {

        $return_data = $this->saveAll($data_list, $replace);

        $return_data && $this->updateCacheVersion();

        return $return_data;
    }

    /**
     * 设置某个字段值
     */
    public function setFieldValue($where = [], $field = '', $value = '') {

        return $this->updateInfo($where, [$field => $value]);
    }

    /**
     * 删除数据
     */
    public function deleteInfo($where = [], $is_true = false) {

        if ($is_true) {

            $return_data = $this->where($where)->delete();

            $return_data && $this->updateCacheVersion();
        } else {
            $return_data = $this->setFieldValue($where, DATA_STATUS_NAME, DATA_DELETE);
        }

        return $return_data;
    }


    /**
     *
     * 数据非物理删除
     * @param $field 根据某个字段 一般为id
     * @param $ids 删除的ids
     * @param array $data
     * @return ModelBase
     */
    public function falseDelete($field,$ids,$data = []){
        $return_data = $this->allowField(true)->whereIn($field,$ids)->update($data);
        $return_data && $this->updateCacheVersion();

        return $return_data;
    }

    /**
     * 删除数据
     * 重写父类方法，添加缓存部分
     */
    public static function destroy($data) {
        $count = parent::destroy($data);

        $model = new static();
        $count && $model->updateCacheVersion();
        return $count;
    }

    /**
     * 获取某个列的数组
     */
    public function getColumn($where = [], $field = '', $key = '') {

        return Db::name($this->name)->where($where)->column($field, $key);
    }

    /**
     * 获取某个字段的值
     */
    public function getValue($where = [], $field = '', $default = null, $force = false) {

        return Db::name($this->name)->where($where)->value($field, $default, $force);
    }

    /**
     * 获取单条数据
     */
    public function getInfo($where = [], $field = true, $join = null, $data = null) {

        $cache_tag = get_cache_tag($this->name, $join);

        $cache_key = get_cache_key($this->name, $where, $field, null, null, $join, null, null, $data, $cache_tag);

        empty($join) ? self::$ob_query = $this->where($where)->field($field) : self::$ob_query = $this->join($join)->where($where)->field($field);

        return $this->getResultData($cache_key, $cache_tag, DATA_DISABLE, $data);
    }


    /**
     * 获取列表数据
     */
    public function getList($where = [], $field = true, $order = '', $paginate = 0, $join = [], $group = '', $limit = null, $data = null) {

        empty($join) && !isset($where[DATA_STATUS_NAME]) && $where[DATA_STATUS_NAME] = ['neq', DATA_DELETE];

        self::$ob_query = $this->where($where)->order($order)->field($field);

        !empty($join) && self::$ob_query = self::$ob_query->join($join);

        !empty($group) && self::$ob_query = self::$ob_query->group($group);

        !empty($limit) && self::$ob_query = self::$ob_query->limit($limit);

        $cache_tag = get_cache_tag($this->name, $join);
        if (DATA_DISABLE === $paginate) : $paginate = DB_LIST_ROWS; endif;

        $cache_key = get_cache_key($this->name, $where, $field, $order, $paginate, $join, $group, $limit, $data, $cache_tag);

        return $this->getResultData($cache_key, $cache_tag, $paginate, $data);
    }

    /**
     * 获取结果数据
     */
    public function getResultData($cache_key = '', $cache_tag = '', $paginate = 0, $data = null) {

        $result_data = null;

        $is_auto_cache = config('is_auto_cache');
        $cache = cache($cache_key);
        if ($is_auto_cache && $cache) {

            $result_data = unserialize($cache);

            !empty($result_data) && set_cache_statistics_number(CACHE_EXE_HIT_KEY);

        } else {

            $backtrace = debug_backtrace(false, 2);

            array_shift($backtrace);

            $function = $backtrace[0]['function'];

            if ($function == 'getList' || $function == 'getListSimple') {

                if ($paginate) {
                    $paginate != false && IS_POST && $paginate = input('list_rows', DB_LIST_ROWS);

                    $result_data = false !== $paginate ? self::$ob_query->paginate($paginate) : self::$ob_query->select($data);
                } else {
                    $result_data = self::$ob_query->select($data);
                }

            } else {
                $result_data = self::$ob_query->find($data);
//                echo Db::getLastSql();
//                p($data);
//                p($result_data);die;
            }

            $is_auto_cache && cache($cache_key, serialize($result_data), config('CACHE_OUT_TIME'), $cache_tag);
        }

        !empty($result_data) && $is_auto_cache && set_cache_statistics_number(CACHE_EXE_NUMBER_KEY);

        self::$ob_query->removeOption();

        return $result_data;
    }


    /**
     * lyh 修改时间
     * 获取列表数据 格式适应Bootstrap Table
     */
    public function getBtList($order = '', $offset = 0, $limit = 10, $where = [], $field = true, $join = [], $group = '') {

        self::$ob_query = $this->where($where)->order($order)->field($field);

        !empty($join) && self::$ob_query = self::$ob_query->join($join);

        !empty($group) && self::$ob_query = self::$ob_query->group($group);

        !empty($limit) && self::$ob_query = self::$ob_query->limit($offset, $limit);


        $is_auto_cache = config('is_auto_cache');
        $cache_key = '';
        $cache_tag = '';
        if ($is_auto_cache) {
            $cache_tag = get_cache_tag($this->name, $join);

            $cache_key = get_cache_key($this->name, $where, $field, $order, $limit, $join, $group, $limit, null, $cache_tag);

        }
        if($limit<=0){
            $limit=10;
        }
        //计算页码
        $page = intval($offset / $limit + 1);
        //封装$paginate 参数
        $paginate_config = ['page' => $page, 'list_rows' => $limit];

        $result = $this->getResultDataForBt($cache_key, $cache_tag, $paginate_config, $where, $join, $group);

        $data = ['total' => $result['total'], 'rows' => $result['data']];
        //echo $this->getLastSql();

        return $data;

    }

    /**
     * 获取列表数据 格式适应Bootstrap Table
     */
    public function getApiList($order = '', $where = [], $field = true, $offset = 0, $limit = 10, $join = [], $group = '') {

        self::$ob_query = $this->where($where)->order($order)->field($field);

        !empty($join) && self::$ob_query = self::$ob_query->join($join);

        !empty($group) && self::$ob_query = self::$ob_query->group($group);

        !empty($limit) && self::$ob_query = self::$ob_query->limit($offset, $limit);


        $is_auto_cache = config('is_auto_cache');
        $cache_key = '';
        $cache_tag = '';
        if ($is_auto_cache) {
            $cache_tag = get_cache_tag($this->name, $join);

            $cache_key = get_cache_key($this->name, $where, $field, $order, $limit, $join, $group, $limit, null, $cache_tag);

        }

        $result = $this->getResultDataForApi($cache_key, $cache_tag);
        return $result;

    }

    /**
     * 获取结果数据
     */
    private function getResultDataForBt($cache_key = '', $cache_tag = '', $paginate_config = null, $where = [], $join = [], $group = '') {

        $result_data = null;
        $total = 0;
        $is_auto_cache = config('is_auto_cache');

        if ($is_auto_cache) $cache = cache($cache_key);
        if ($is_auto_cache && $cache) {

            $result_data = unserialize($cache);

            !empty($result_data) && set_cache_statistics_number(CACHE_EXE_HIT_KEY);

        } else {
            $result_data = self::$ob_query->paginate(null, false, $paginate_config);
            $result_data = array('data' => $result_data->items(), 'total' => $result_data->total());
            $is_auto_cache && cache($cache_key, serialize($result_data), config('CACHE_OUT_TIME'), $cache_tag);
        }

        !empty($result_data) && $is_auto_cache && set_cache_statistics_number(CACHE_EXE_NUMBER_KEY);

        self::$ob_query->removeOption();

        return $result_data;
    }

    /**
     * 获取结果数据
     */
    private function getResultDataForApi($cache_key = '', $cache_tag = '') {

        $result_data = null;

        $is_auto_cache = config('is_auto_cache');

        if ($is_auto_cache) $cache = cache($cache_key);
        if ($is_auto_cache && $cache) {

            $result_data = unserialize($cache);

            !empty($result_data) && set_cache_statistics_number(CACHE_EXE_HIT_KEY);

        } else {
            //查询数据
            $result_data = self::$ob_query->select();
            $cache_key .= 'api';
            $is_auto_cache && cache($cache_key, serialize($result_data), config('CACHE_OUT_TIME'), $cache_tag);
        }

        !empty($result_data) && $is_auto_cache && set_cache_statistics_number(CACHE_EXE_NUMBER_KEY);

        self::$ob_query->removeOption();

        return $result_data;
    }
}
