<?php
declare (strict_types=1);

namespace app\index\controller;

use app\model\Order;
use pay\AliPayNotify;
use pay\AliPaySubmit;

class Shop
{
    protected $middleware = ['app\index\middleware\CheckLoginUser'];

    private $aliPay = [
        'partner' => '104888',
        'key' => '5747F8CAA645F2EFEB1AE1B7D5487441',
        'sign_type' => 'MD5',
        'input_charset' => 'utf-8',
        'api_url' => 'http://pay.alyzf.cn/',

    ];

    /**
     * 开通VIP
     */
    public function vip()
    {
        $user = new \app\model\User();
        $user->updateMyInfo();
        return autoTemplate();
    }

    /**
     * 开通代理
     */
    public function agent()
    {
        $user = new \app\model\User();
        $user->updateMyInfo();
        return autoTemplate();
    }

    /**
     * 卡密激活
     */
    public function cdKey()
    {
        return autoTemplate();
    }

    public function cdKeyAjax()
    {
        return resultJson(0, 'No.', ['title' => '无效卡密', 'content' => '卡密格式不正确，请检查您的卡密']);
    }

    /**
     * 账户充值
     */
    public function money()
    {
        (new \app\model\User())->updateMyInfo();
        return autoTemplate();
    }

    /**
     * 查询价格
     */
    public function price()
    {
        $action = input('act');
        switch ($action) {
            case 'vip':
                $id = input('id');
                $res = priceFindId($id);
                break;
            case 'agent':
                $level = input('level');
                $res = findAgentLevel($level);
                break;
            default :
                return resultJson(-1, 'Error');
        }
        if ($res) {
            return resultJson(1, 'Ok', $res);
        } else {
            return resultJson(0, '未知错误');
        }
    }

    /**
     * 提交支付
     */
    public function submit()
    {
        $user = new \app\model\User();
        $user->updateMyInfo();
        $action = input('act');
        $id = input('id');
        $type = input('type'); //支付方式

        switch ($action) {
            case 'vip':
                $res = priceFindId($id);
                break;
            case 'agent':
                $level = input('level');
                $res = findAgentLevel($level);
                if (session('user.agent') >= $res['level']) {
                    return "<script>x.btn('充值失败','开通代理等级不得小于当前代理等级!');</script>";
                }
                break;
            case 'money' :
                $res['price'] = input('post.money');
                $res['name'] = "余额充值 - {$res['price']}元";
                break;
            default :
                return resultJson(-1, 'Error');
        }
        switch ($type) {
            case 'alipay':
                $method = '支付宝';
                break;
            case 'wxpay':
                $method = '微信';
                break;
            case 'qqpay':
                $method = 'QQ';
                break;
            case 'user':
                $method = '账户余额';
                break;
            default :
                return 'error';
        }
        if ($type == 'user') { //账户余额充值
            if (session('user.money') < $res['price']) {
                return "<script>x.btn('余额不足','您的账户余额不足，请先充值或选择其它支付方式!');</script>";
            } else {
                //扣除余额
                $user->decUserMoney(session('user.uid'), $res['price'], $res['name']);
                //自动开通
                $res['type'] = $action;
                $open = new class() {
                    public $dataset;
                    public $uid;
                    public $method;
                };
                $open->dataset = $res;
                $open->uid = session('user.uid');
                $open->method = $method;
                (new Order())->autoOpen($open);
                return "<script>x.btn('充值成功','OK!');</script>";
            }
        }
        //创建订单
//        dump($res);die();
        $res['type'] = $action;
        $order = new Order();
        $orderId = $order->createOrder($res['name'], $method, $res['price'], $res);

        $uid = session('user.uid');
        $notify_url = (string)url('shop/notify_url')->domain('www');
        $return_url = (string)url('shop/return_url')->domain('www');

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "pid" => $this->aliPay['partner'],
            "type" => $type,
            "notify_url" => $notify_url,
            "return_url" => $return_url,
            "out_trade_no" => $orderId,
            "name" => "UID:{$uid} - {$res['name']}",
            "money" => $res['price'],
            "sitename" => config('conf.title'),
        );
        $aliPaySubmit = new AlipaySubmit($this->aliPay);
        return $aliPaySubmit->buildRequestForm($parameter);
    }


    /**
     * 异步通知地址
     */
    public function notify_url()
    {
//        dump(input());
        $alipayNotify = new AlipayNotify($this->aliPay);
        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) {//验证成功
            $out_trade_no = input('get.out_trade_no');
            $order = new Order();
            $res = $order->getOrderId($out_trade_no);
            if ($res) {
                if ($_GET['trade_status'] == 'TRADE_SUCCESS' && $res) {
                    if ($res['status'] == 0) {
                        $order->updateOrder($out_trade_no, [
                            'status' => 1,
                            'pay_time' => time()
                        ]);
                        //自助开通
                        $order->autoOpen($res);
                        return resultMsg('补单成功', "开通{$res['name']}成功，感谢您的购买");
                    } else {
                        $order->autoOpen($res);

                        return resultMsg('补单失败', "该订单正常，无需补单", 'close hong');
                    }
                } else {
                    return resultMsg('支付失败', "错误信息:" . input('get.trade_status', 'close hong'));
                }
            } else {
                return resultMsg('支付失败', "错误信息: 没有找到该订单", 'close hong');
            }
        } else {
            return resultMsg('订单效验失败', '请重试', 'close hong');
        }
    }

    /**
     * 跳转通知地址
     */
    public function return_url()
    {
//        dump(input());
        $aliPayNotify = new AlipayNotify($this->aliPay);
        $verify_result = $aliPayNotify->verifyReturn();
        if ($verify_result) {//验证成功
            $out_trade_no = input('get.out_trade_no');
            $order = new Order();
            $res = $order->getOrderId($out_trade_no);
            if ($res) {
                if ($_GET['trade_status'] == 'TRADE_SUCCESS') {
                    if ($res['status'] == 0) {
                        $order->updateOrder($out_trade_no, [
                            'status' => 1,
                            'pay_time' => time()
                        ]);
                        //自助开通
                        $order->autoOpen($res);
                    }
                    if ($res['dataset']['type'] == 'money') {
                        return resultMsg('支付成功', "您的余额充值已到账，充值金额：{$res['dataset']['price']}元");
                    } else {
                        return resultMsg('支付成功', "开通{$res['name']}成功，感谢您的购买");
                    }
                } else {
                    return resultMsg('支付失败', "错误信息:" . input('get.trade_status'), 'close hong');
                }
            } else {
                return resultMsg('支付失败', "错误信息: 没有找到该订单", 'close hong');
            }
        } else {
            return resultMsg('订单效验失败', '请重试', 'close hong');
        }
    }
}
