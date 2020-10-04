<?php
/* *
 * 类名：EpaySubmit
 * 功能：彩虹易支付接口请求提交类
 * 详细：构造易支付接口表单HTML文本，获取远程HTTP数据
 */

namespace pay;

class AliPaySubmit extends AliPayCore
{

    private $alipay_config;
    private $alipay_gateway_new;

    function __construct($alipay_config)
    {
        $this->alipay_config = $alipay_config;
        $this->alipay_gateway_new = $this->alipay_config['api_url'] . 'submit.php?';
    }

    function AlipaySubmit($alipay_config)
    {
        $this->__construct($alipay_config);
    }

    /**
     * 生成要请求给支付宝的参数数组
     * @param array $para_temp 请求前的参数数组
     * @return array 要请求的参数数组
     */
    function buildRequestPara($para_temp)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = Parent::paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = Parent::argSort($para_filter);

        //生成签名结果
        $prestr = Parent::createLinkString($para_sort);

        $mysign = Parent::md5Sign($prestr, $this->alipay_config['key']);

        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mysign;
        $para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));

        return $para_sort;
    }


    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param array $para_temp 请求参数数组
     * @param string $method 提交方式。两个值可选：post、get
     * @param string $button_name 确认按钮显示文字
     * @return string 提交表单HTML文本
     */
    function buildRequestForm($para_temp, $method = 'POST', $button_name = '正在跳转')
    {
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);

        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->alipay_gateway_new . "_input_charset=" . trim(strtolower($this->alipay_config['input_charset'])) . "' method='" . $method . "'>";
        foreach ($para as $key => $val) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='" . $button_name . "'></form>";

        $sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";

        return $sHtml;
    }
}

?>