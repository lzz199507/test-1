<?php

namespace app\api\logic;

use app\common\model\User;
use app\common\model\Order;
use think\Db;

/**
 * 订单
 */
class OrderLogic extends ApiBaseLogic
{
	private static $model = null;
    private static $userModel = null;

	public function __construct() {
        parent::__construct();

        self::$model = Order::getInstance();
        self::$userModel = User::getInstance();
    }
    /**
     * 生成订单
     * $pay_type 1:支付宝 2：微信
     * $type    1:会员充值
     */
    public function createOrder($pay_type, $goods_id, $type, $price, $uid)
    {
        $order_sn = order_sn($uid);
        $data['user_id'] = $uid;
        $data['order_sn'] = $order_sn;
        $data['goods_id'] = $goods_id;
        $data['price'] = $price;
        $data['type'] = $type;
        $data['add_time'] = time();
        $data['pay_type'] = $pay_type;
        $info = self::$model->insert($data);
        if ($info) {
            return V(1,'订单生成成功',$order_sn);
        } else {
            return V(0,'订单生成失败');
        }
    }
}
