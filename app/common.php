<?php
// 应用公共文件

//mb_convert_encoding($ret, 'utf-8','GB2312');

function getSubstr($str, $leftStr, $rightStr)
{
    $left = strpos($str, $leftStr);
    //echo '左边:'.$left;
    $right = strpos($str, $rightStr, $left);
    //echo '<br>右边:'.$right;
    if ($left < 0 or $right < $left) return '';
    return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
}

/**
 * by Mengter
 * @param $t string superkey
 * @return int
 */
function getAuthToken($t)
{
    $e = 0;
    for ($i = 1; $i <= mb_strlen($t); $i++) {
        $e = (33 * $e) + intval(get_bianma(mb_substr($t, $i - 1, 1)));
    }
    return $e % 4294967296;
}

function get_bianma($str)//等同于js的charCodeAt()
{
    $result = array();
    for ($i = 0, $l = mb_strlen($str, 'utf-8'); $i < $l; ++$i) {
        $result[] = uniord(mb_substr($str, $i, 1, 'utf-8'));
    }
    return join(",", $result);
}

function uniord($str, $from_encoding = false)
{
    $from_encoding = $from_encoding ? $from_encoding : 'UTF-8';
    if (strlen($str) == 1)
        return ord($str);
    $str = mb_convert_encoding($str, 'UCS-4BE', $from_encoding);
    $tmp = unpack('N', $str);
    return $tmp[1];
}

function get_curl($url, $post = 0, $referer = 1, $cookie = 0, $header = 0, $ua = 0)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $httpheader[] = "Accept: application/json";
    $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
    $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
    $httpheader[] = "Connection: close";

    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    if ($header) {
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
    }
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    if ($referer) {
        if ($referer == 1) {
            curl_setopt($ch, CURLOPT_REFERER, 'http://m.qzone.com/infocenter?g_f=');
        } else {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
    }
    if ($ua) {
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    } else {
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/5.5 Mobile Safari/533.1');
    }

    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $ret = curl_exec($ch);
    if ($header) {
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($ret, 0, $headerSize);
        $body = substr($ret, $headerSize);
        unset($ret);
        $ret['header'] = $header;
        $ret['body'] = $body;
    }
    curl_close($ch);
    return $ret;
}

function getGTK($skey)
{
    $len = strlen($skey);
    $hash = 5381;
    for ($i = 0; $i < $len; $i++) {
        $hash += ($hash << 5 & 2147483647) + ord($skey[$i]) & 2147483647;
        $hash &= 2147483647;
    }
    return $hash & 2147483647;
}

function getGTK2($skey)
{
    $salt = 5381;
    $md5key = 'tencentQQVIP123443safde&!%^%1282';
    $hash = array();
    $hash[] = ($salt << 5);
    for ($i = 0; $i < strlen($skey); $i++) {
        $ASCIICode = mb_convert_encoding($skey[$i], 'UTF-32BE', 'UTF-8');
        $ASCIICode = hexdec(bin2hex($ASCIICode));
        $hash[] = (($salt << 5) + $ASCIICode);
        $salt = $ASCIICode;
    }
    $md5str = md5(implode($hash) . $md5key);
    return $md5str;
}

function getGTK3($skey)
{
    $salt = 108;
    $md5key = 'tencent.mobile.qq.csrfauth';
    $hash = array();
    $hash[] = ($salt << 5);
    for ($i = 0; $i < strlen($skey); $i++) {
        $ASCIICode = mb_convert_encoding($skey[$i], 'UTF-32BE', 'UTF-8');
        $ASCIICode = hexdec(bin2hex($ASCIICode));
        $hash[] = (($salt << 5) + $ASCIICode);
        $salt = $ASCIICode;
    }
    $md5str = md5(implode($hash) . $md5key);
    return $md5str;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0)
{
    //> 相当于intval函数，对用户输入进行一次过滤
    $type = $type ? 1 : 0;  //> 参数设计0 | 1为后面返回ip准备
    //> 采用静态变量，保存本次HTTP请求不再去获取相同的ip
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    //> 判断用户是否通过代理服务器访问（用户可能伪造ip地址）
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //> 正确的代理是通过逗号分隔的字符串 explode分隔字符串成数组
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        //> 搜索'unknown'是否在$arr中（值搜索），如果存在返回对应的键；否则返回false（unknown可能是一些系统原因造成的）
        $pos = array_search('unknown', $arr);
        if (false !== $pos) unset($arr[$pos]);
        //> 去除字符串开始和结尾的空格或其他。
        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));  //> sprintf()：格式化指定的变量
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

function get_qqnick($uin)
{
    //https://api.unipay.qq.com/v1/r/1450000186/wechat_query?cmd=1&pf=mds_storeopen_qb-__mds_qqclub_tab_-html5&pfkey=pfkey&from_h5=1&from_https=1&openid=openid&openkey=openkey&session_id=hy_gameid&session_type=st_dummy&qq_appid=&offerId=1450000186&sandbox=&provide_uin=1543797310
    //https://r.qzone.qq.com/fcg-bin/cgi_get_portrait.fcg?get_nick=1&uins=9147218
    //https://api.unipay.qq.com/v1/r/1450000186/wechat_query?cmd=1&openid=openid&openkey=openkey&session_id=hy_gameid&session_type=st_dummy&provide_uin=9147218
    //https://users.qzone.qq.com/fcg-bin/cgi_get_portrait.fcg?get_nick=1&uins=9147218
//    preg_match('/[1-9][0-9]{4,}/',$uin,$match);
//
//
//    if ($match){
//        $uin=$match[0];
//    }

    $ret = get_curl("https://r.qzone.qq.com/fcg-bin/cgi_get_portrait.fcg?get_nick=&uins={$uin}");

    $ret = mb_convert_encoding($ret, "UTF-8", "GBK");
    $ret = str_replace(array("portraitCallBack(", ")"), array('', ''), $ret);

    $nickname = json_decode($ret, true);
//    dump($nickname);
    if ($nickname) {
        return $nickname[$uin][6];
    } else {
        return "[NULL]";
    }
}


//function get_qqnick($uin)
//{
//    if ($data = get_curl("http://users.qzone.qq.com/fcg-bin/cgi_get_portrait.fcg?get_nick=1&uins=" . $uin)) {
//        $data = str_replace(array('portraitCallBack(', ')'), array('', ''), $data);
//        $data = mb_convert_encoding($data, "UTF-8", "GBK");
//        $row = json_decode($data, true);
//        return $row[$uin][6];
//    }
//}

/**
 * Jsonp转json
 * @param string $jsonp jsonp字符串
 * @param bool $assoc array/object
 * @return array
 */
function jsonp_decode($jsonp, $assoc = false)
{
    //删除换行
    $jsonp = str_replace(array("\r\n", "\r", "\n"), "", $jsonp);

    $json = preg_replace('/.+?({.+}).+/', '$1', $jsonp);
    return json_decode($json, $assoc);
}

/**
 * 获取任务列表
 * @return array[]
 */
function getTask()
{
    return [
        [
            'id' => 'auto',
            'name' => '自动更新',
            'title' => '自动更新状态',
            'tip' => '[24H自动] 当QQ状态失效时，自动更新QQ在线状态',
            'vip' => false,
            'onclick' => "x.btn('状态更新无法关闭','本站已为所有用户开启了打码更新功能<br>保障您的QQ在线状态一直处于正常在线');",
            'minute' => 1,
        ], ['id' => 'zan',
            'name' => '说说点赞',
            'title' => '好友动态点赞',
            'tip' => '[24H自动] 为QQ空间好友发表的动态进行点赞',
            'vip' => false,
            'second' => 20,
        ], ['id' => 'reply',
            'name' => '说说秒评',
            'title' => '好友动态秒评',
            'tip' => '[24H自动] 为QQ空间好友发表的动态进行秒评',
            'vip' => false,
            'second' => 20,
        ], ['id' => 'qunqd',
            'name' => '群签到',
            'title' => 'QQ群签到',
            'tip' => '[24H自动] QQ群签到',
            'vip' => false,
            'hours' => 24,
        ], ['id' => 'qipao',
            'name' => '百变气泡',
            'title' => '百变气泡',
            'tip' => '[24H自动] 百变气泡',
            'vip' => false,
            'second' => 5,
        ], ['id' => 'checkin',
            'name' => 'QQ打卡',
            'title' => 'QQ打卡',
            'tip' => '[24H自动] QQ打卡',
            'vip' => true,
            'hours' => 12,
        ], ['id' => 'gameSign',
            'name' => '腾讯游戏签到',
            'title' => '腾讯游戏签到',
            'tip' => '[24H自动] 完成游戏大厅签到+手Q游戏中心签到+精品页游签到+道聚城+DNF社区签到+安全盾签到',
            'vip' => false,
            'hours' => 12,
        ], ['id' => 'book',
            'name' => '腾讯图书签到',
            'title' => '腾讯图书签到',
            'tip' => '[24H自动] 图书签到',
            'vip' => false,
            'hours' => 12,
        ], ['id' => 'flower',
            'name' => '花藤代养',
            'title' => '花藤代养',
            'tip' => '[24H自动] 完成QQ空间花藤日照+浇水+修剪+施肥+摘果+使用神奇化肥',
            'vip' => false,
            'hours' => 12,
        ], ['id' => 'weishi',
            'name' => '微视签到',
            'title' => '微视签到',
            'tip' => '[24H自动] 微视签到',
            'vip' => false,
            'second' => 20,
        ], ['id' => 'gameSpeed',
            'name' => 'QQ手游',
            'title' => 'QQ手游加速',
            'tip' => '[24H自动] QQ手游加速',
            'vip' => true,
            'hours' => 12,
        ], ['id' => 'mobileSpeed',
            'name' => '手机加速',
            'title' => '手机加速',
            'tip' => '[24H自动] 手机加速',
            'vip' => true,
            'hours' => 12,
        ], ['id' => 'yunDong',
            'name' => '运动打卡加速',
            'title' => '运动打卡加速',
            'tip' => '[24H自动] 运动打卡加速',
            'vip' => true,
            'hours' => 12,
        ], ['id' => 'vip',
            'name' => '会员签到',
            'title' => 'QQ会员签到',
            'tip' => '[24H自动] 会员签到领取成长值',
            'vip' => true,
            'hours' => 12,
        ], ['id' => 'bigVip',
            'name' => '大会员签到',
            'title' => 'QQ大会员签到',
            'tip' => '[24H自动] 大会员签到领取成长值',
            'vip' => true,
            'hours' => 12,
        ], ['id' => 'qzoneVip',
            'name' => '黄钻签到',
            'title' => 'QQ黄钻签到',
            'tip' => '[24H自动] 黄钻签到领取成长值',
            'vip' => true,
            'hours' => 12,
        ], ['id' => 'gameVip',
            'name' => '蓝钻签到',
            'title' => 'QQ蓝钻签到',
            'tip' => '[24H自动] 领取每月蓝钻专属礼包',
            'vip' => true,
            'hours' => 12,
        ], ['id' => 'videoVip',
            'name' => '腾讯视频会员签到',
            'title' => '腾讯视频会员签到',
            'tip' => '[24H自动] 腾讯视频签到',
            'vip' => true,
            'hours' => 12,
        ]];
}

function findTask($type)
{
    foreach (getTask() as $value) {
        if ($value['id'] == $type) {
            return $value;
        }
    }
    return false;
}

function getQunSign()
{
    /**
     * https://qun.qq.com/qqweb/m/qun/checkin/index.html?_bid=2485&_wv=67108867&gc=941060582&state=1&from=troop_profile
     */
    return [
        [
            'id' => 1,
            'category_id' => 9,
            'pic_id' => 178,
            'text' => '坚持戴口罩',
            'name' => '坚持戴口罩',
        ], [
            'id' => 2,
            'template_id' => 8,
            'text' => '今天我会瘦，你呢',
            'name' => '运势',
        ], [
            'id' => 3,
            'template_id' => 1,
            'text' => '你那里下雨了吗',
            'name' => '天气',
        ], [
            'id' => 4,
            'category_id' => 9,
            'pic_id' => 125,
            'text' => '自律使我自由',
            'name' => '每天运动',
        ], [
            'id' => 5,
            'category_id' => 7,
            'pic_id' => 175,
            'template_id' => 4,
            'text' => '勇往直前',
            'name' => '勇往直前',
        ], [
            'id' => 6,
            'category_id' => 7,
            'pic_id' => 47,
            'template_id' => 4,
            'text' => '晚安',
            'name' => '晚安',
        ], [
            'id' => 7,
            'category_id' => 7,
            'pic_id' => 48,
            'template_id' => 4,
            'text' => '不想睡啊',
            'name' => '晚安2',
        ], [
            'id' => 8,
            'category_id' => 7,
            'pic_id' => 46,
            'template_id' => 4,

            'text' => '洗洗睡吧',
            'name' => '洗洗睡吧',
        ], [
            'id' => 9,
            'category_id' => 7,
            'pic_id' => 73,
            'template_id' => 4,
            'text' => '滴，老司机卡',
            'name' => '老司机卡',
        ], [
            'id' => 10,
            'category_id' => 7,
            'pic_id' => 72,
            'template_id' => 4,
            'text' => '滴，VIP卡',
            'name' => 'VIP至尊卡',
        ], [
            'id' => 11,
            'category_id' => 7,
            'pic_id' => 71,
            'template_id' => 4,
            'text' => '滴，熬夜卡',
            'name' => '熬夜卡',
        ]
    ];
}

function qunFindId($id)
{
    foreach (getQunSign() as $value) {
        if ($id == 0) {
            $key = array_rand(getQunSign(), 1);
            return getQunSign()[$key];
        } elseif ($value['id'] == $id) {
            return $value;
        }
    }
    return false;
}

function getPrice()
{
    return [
        [
            'id' => '1',
            'name' => '包月VIP - 1个月',
            'class' => 'ml5',
            'price' => 0.1,
            'day' => 30 * 1,
        ], [
            'id' => '2',
            'name' => '包季VIP - 3个月',
            'class' => 'ml5',
            'price' => 15,
            'discount' => 8,
            'day' => 30 * 3,
        ], [
            'id' => '3',
            'name' => '半年VIP - 6个月',
            'class' => 'ml5',
            'price' => 30,
            'discount' => 8,
            'day' => 30 * 6,
        ], [
            'id' => '4',
            'name' => '年费VIP - 12个月',
            'class' => 'ml5 zi',
            'price' => 60,
            'discount' => 8,
            'day' => 30 * 12,
        ]];
}

function priceFindId($id)
{
    foreach (getPrice() as $value) {
        if ($value['id'] == $id) {
            if (isset($value['discount'])) {
                $value['newPrice'] = $value['price'] * ($value['discount'] / 10);
            }
            return $value;
        }
    }
    return false;
}

function getAgent()
{
    $result = [
        [
            'level' => '1',
            'name' => '铜牌代理',
            'price' => 40,
            'give' => 10,
            'discount' => 8,
        ], [
            'level' => '2',
            'name' => '银牌代理',
            'price' => 60,
            'give' => 30,
            'discount' => 6,
        ], [
            'level' => '3',
            'name' => '金牌代理',
            'price' => 80,
            'give' => 50,
            'discount' => 4,
        ], [
            'level' => '4',
            'name' => '钻石代理',
            'price' => 100,
            'give' => 70,
            'discount' => 2,
        ]];
    return array_reverse($result);
}

/**
 * 查询代理等级
 * @param $level
 * @return bool|mixed
 */
function findAgentLevel($level)
{
    foreach (getAgent() as $value) {
        if ($value['level'] == $level) {
            return $value;
        }
    }
    return false;
}

/**
 * 获取会员信息
 * @param $startTime
 * @param $endTime
 * @param int $agentLevel
 * @return array
 */
function getVipLevel($startTime, $endTime,$agentLevel = 0)
{
    if ($agentLevel != 0) {
        $info['title'] = findAgentLevel($agentLevel)['name'];
        $info['chat_color'] = "cheng bold";
        $info['chat_color_m'] ='huang';
    } else {
        if (time() > $endTime) {
            $info['title'] = '已过期'; //标题
            $info['color'] = 'hui';//手机会员中心模板颜色
            $info['chat_color'] = 'hui';//聊天室文字颜色
            $info['chat_color_m'] = 'bai';//聊天室文字颜色
            $info['diff'] = 0;//剩余时间
        } else {
            $diff = data_Diff($startTime, $endTime);
            if ($diff >= 1 && $diff < 30) {
                $info['color'] = 'lan';
                $info['chat_color'] = 'lan';
                $info['title'] = '体验VIP';
                $info['chat_color_m'] ='bai';
            } elseif ($diff < 90) {
                $info['color'] = 'lan';
                $info['chat_color'] = 'lan bold';
                $info['chat_color_m'] ='lan';
                $info['title'] = '包月VIP';
            } elseif ($diff < 365) {
                $info['color'] = 'zi';
                $info['chat_color'] = 'zi bold';
                $info['title'] = '包季VIP';
                $info['chat_color_m'] ='lan';
            } elseif ($diff >= 365) {
                $info['color'] = 'huang';
                $info['chat_color'] = 'zi bold';
                $info['title'] = '年费VIP';
                $info['chat_color_m'] ='fen';

            }
            $info['diff'] = ceil(($endTime - time()) / 86400);
        }
    }

    return $info;
}

/**
 * 读取临时文件
 * @param string $type 类型
 * @param string $filename 文件名称
 * @return bool|string 返回文件的内容
 */
function getRuntimeCache($type, $filename)
{
    $path = root_path() . "runtime" . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $filename;
    if (file_exists($path)) {
        return file_get_contents($path);
    } else {
        return false;
    }
}

/**
 * 写入临时文件
 * @param string $type 类型
 * @param string $filename 文件名称
 * @param mixed $content 写入的内容
 * @return string 返回文件路径
 */
function putRuntimeCache($type, $filename, $content)
{
    $path = root_path() . "runtime" . DIRECTORY_SEPARATOR . $type;
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }

    $path = root_path() . "runtime" . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $filename;
    file_put_contents($path, $content);
    return $path;
}

/**
 * 计算时间差
 * @param $timestamp
 * @param $timestamp2
 * @return float|int
 */
function data_Diff($timestamp, $timestamp2 = NULL)
{
    if (!is_int($timestamp)) {
        $timestamp = strtotime($timestamp);
    }
    if (is_null($timestamp2)) {
        $timestamp2 = time();
    } elseif (!is_int($timestamp2)) {
        $timestamp2 = strtotime($timestamp2);
    }

    return ceil(($timestamp2 - $timestamp) / 86400);
}


function resultJson($code, $message, $data = NULL)
{
    return json([
        'code' => $code,
        'message' => $message,
        'data' => $data
    ]);
}

function resultArray($code, $message, $data = NULL)
{
    return [
        'code' => $code,
        'message' => $message,
        'data' => $data
    ];
}