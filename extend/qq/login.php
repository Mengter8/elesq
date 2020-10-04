<?php
/**
 * QQ功能继承类
 */
namespace qq;
class login {
    public $uin;
    public $skey;
    private $pskey;

    public $gtk;
    public $gtk2;
    public $cookie;

    public $msg;
    public $fail;
    /**
     * @var string
     */
    public $qzoneToken;
    /**
     * @var null
     */
    public $superkey;


    public function __construct($uin, $skey = null, $pskey = null,$superkey = null)
    {
        $this->uin = $uin;
        $this->skey = $skey;
        $this->pskey = $pskey;
        $this->superkey = $superkey;
        $this->gtk = getGTK($skey);
        $this->gtk2 = getGTK($pskey);
        $this->cookie = "uin=o{$uin}; skey={$skey}; p_skey={$pskey};";

        $this->qzoneToken = $this->getQzoneToken();
    }

    public function checkLogin(){
        $url = "https://h5.qzone.qq.com/webapp/json/mqzone_feeds/getActiveFeeds?qzonetoken={$this->qzoneToken}&g_tk={$this->gtk2}";
        $post = 'res_type=0&res_attach=&refresh_type=2&format=json&attach_info=';
        $json = get_curl($url, $post, 1, $this->cookie);
        $arr = json_decode($json, true);
        if ($arr['ret'] === 0 && $arr['code'] === 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取QzoneToken
     * @return string qzonetoken
     */
    public function getQzoneToken()
    {
        $ret = getRuntimeCache("qq", "{$this->uin}.txt");

        if ($ret) {
            return $ret;
        } else {
            $json = get_curl('https://h5.qzone.qq.com/mqzone/index', 0, 0, $this->cookie);
            preg_match('/\(function\(\){ try{*.return "(.*?)";} catch\(e\)/i', $json, $match);
            if (isset($match[1])) {
                $qzoneToken = $match[1];
                putRuntimeCache("qq", "{$this->uin}.txt", $qzoneToken);
                return $qzoneToken;
            } else {
                return '获取qzonetoken失败';
            }
        }
    }

    public function getGTK($skey)
    {
        $len = strlen($skey);
        $hash = 5381;
        for ($i = 0; $i < $len; $i++) {
            $hash += ($hash << 5 & 2147483647) + ord($skey[$i]) & 2147483647;
            $hash &= 2147483647;
        }
        return $hash & 2147483647;
    }

    public function getGTK2($skey)
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
    private function getGTK3($skey)
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

    public function getToken($token)
    {
        $len = strlen($token);
        $hash = 0;
        for ($i = 0; $i < $len; $i++) {
            $hash = fmod($hash * 33 + ord($token[$i]), 4294967296);
        }
        return $hash;
    }
}