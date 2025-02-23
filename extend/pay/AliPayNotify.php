<?php
/* *
 * 类名：EpayNotify
 * 功能：彩虹易支付通知处理类
 * 详细：处理易支付接口通知返回
 */

namespace pay;

class AliPayNotify extends AliPayCore
{

    private $alipay_config;
    private $http_verify_url;

    function __construct($alipay_config)
    {
        $this->alipay_config = $alipay_config;
        $this->http_verify_url = $this->alipay_config['api_url'] . 'api.php?';
    }

    function AlipayNotify($alipay_config)
    {
        $this->__construct($alipay_config);
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return string 验证结果
     */
    function verifyNotify()
    {
        if (empty($_GET)) {//判断POST来的数组是否为空
            return false;
        } else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            if (!empty($_POST["notify_id"])) {
                $responseTxt = $this->getResponse($_POST["notify_id"]);
            }
            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i", $responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return string 验证结果
     */
    function verifyReturn()
    {
        if (empty($_GET)) {//判断POST来的数组是否为空
            return false;
        } else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            if (!empty($_GET["out_trade_no"])) {
                $Response = $this->getResponse($_GET["out_trade_no"]);
            } else {
                $Response = false;
            }
            if ($isSign && $Response == true) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取返回时的签名验证结果
     * @param array $para_temp 通知返回来的参数数组
     * @param string $sign 返回的签名结果
     * @return string 签名验证结果
     */
    function getSignVeryfy($para_temp, $sign)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = Parent::paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = Parent::argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = Parent::createLinkString($para_sort);

        $isSgin = false;
        $isSgin = Parent::md5Verify($prestr, $sign, $this->alipay_config['key']);

        return $isSgin;
    }

    /**
     * 支付成功回调
     * @param int $out_trade_no 商户订单号
     * @return bool 支付结果
     */
    function getResponse($out_trade_no)
    {
        $get = http_build_query([
            'act' => 'order',
            'pid' => $this->alipay_config['partner'],
            'key' => $this->alipay_config['key'],
            'out_trade_no' => $out_trade_no,

        ]);
        $veryfy_url = $this->http_verify_url . $get;
        $responseTxt = get_curl($veryfy_url);
        if ($json = json_decode($responseTxt, true)) {
            if (array_key_exists('status',$json) && $json['status'] == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}

?>
