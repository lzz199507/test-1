<?php

namespace app\common\logic;

/**
 * 系统通用逻辑模型
 */
class LogicBase   {

    protected $name;
    protected $class;
    public function __construct() {
        // 当前类名
        $this->class = get_called_class();

        if (empty($this->name)) {
        // 当前模型名
        $name       = str_replace('\\', '/', $this->class);
        if(  endWith($name,'Logic')){
            $name=str_replace('Logic', '', $name);
        }


        $this->name = basename($name);
        if (config('class_suffix')) {
            $suffix     = basename(dirname($name));
            $this->name = substr($this->name, 0, -strlen($suffix));
        }


    }}
    /**
     * 获取单例
     * @return mixed|null
     */
    public static function  getInstance(){
        return new static();
    }
}
