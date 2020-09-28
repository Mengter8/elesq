<?php
declare (strict_types=1);

namespace app\model;

use think\Model;

class Order extends Model
{
    protected $type = [
        'dataset' => 'json',
    ];

    /**
     * 创建订单
     * @param string $name 商品名称
     * @param string $method 支付方式
     * @param float $money 金额
     * @param array $data JSON数组
     * @return int 生成好的订单号
     */
    public function createOrder($name, $method, $money, $data)
    {
        $self = new static();
        $orderId = date("YmdHis") . mt_rand(100, 999);

        $self->create([
            'id' => $orderId,
            'uid' => session('user.uid'),
            'name' => $name,
            'method' => $method,
            'money' => $money,
            'status' => 0,
            'dataset' => $data,
            'create_time' => time(),
            'pay_time' => NULL,
        ]);
        return $orderId;
    }

    /**
     * 更新订单
     * @param int $id
     * @param array $data
     * @return Order|bool
     */
    public function updateOrder($id, $data)
    {
        $self = new static();
        if ($result = $self->where('id', '=', $id)->update($data)) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 获取订单ID数据
     * @param int $id
     */
    public function getOrderId($id)
    {
        if ($result = $this->where('id', '=', $id)->find()) {
            return $result->toArray();
        } else {
            return false;
        }
    }

    /**
     * 自动开通 = =
     * @param $res
     */
    public function autoOpen($res)
    {
        $user = new User();
        switch ($res['dataset']['type']) {
            case 'vip':
                $user->addUserVipTime($res['uid'], $res['dataset']['day'], "{$res['method']}开通");
                break;
            case 'agent':
                $user->addUserAgent($res['uid'], $res['dataset']['level']);
                $user->addUserMoney($res['uid'], $res['dataset']['give'],'开通代理赠送'); //赠送余额
                break;
            case 'money' :
                $user->addUserMoney($res['uid'], $res['dataset']['price'],"{$res['method']}充值");
                break;
            default :
                break;
        }
    }

    public function getMoneyLog($listRows){

    }
}
