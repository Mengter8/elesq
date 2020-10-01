<?php
declare (strict_types=1);

namespace app\index\controller;

use app\model\Qq;
use app\model\Server;
use think\facade\View;
use think\facade\Request;

class Qlogin
{
    protected $middleware = ['app\index\middleware\CheckLoginUser'];
    private $res;
    /**
     * @var \qq\Qlogin
     */
    private $Qlogin;

    public function __construct()
    {
        $this->initialize();
    }

    protected function initialize()
    {
        $this->Qlogin = new \qq\Qlogin();

        $this->uin = Request::param('uin');
        $serverId = Request::param('serverId');
        if ($this->uin) {
            $this->res = (new Qq())->queryUinForServer($this->uin);
            if ($this->res) {
                $this->Qlogin->setLoginApi($this->res['api']);
            }
        }
        if (isset($serverId)) {
            if (defined('proxy') == false) {
                if ($ret = (new Server())->getId($serverId)) {
                    $this->Qlogin->setLoginApi($ret['api']);
                } else  {
                    return resultJson(0, '服务器ID错误');
                }
            }
        }
    }

    public function test()
    {
        $serverId = input('get.serverId');


        $uin = "941334412";
        $pwd = "123456789.";
//        $uin = "1543797310";
//        $pwd = "elesq.cn";
        $uin = "2665627212";
        $pwd = "xjh123.0";

        $this->Qlogin->setLoginApi((new Server())->getId($serverId)['api']);
        echo $this->Qlogin->get_curl_proxy("https://202020.ip138.com");

        $checkvc = $this->Qlogin->checkvc($uin);
        dump($checkvc);
        if ($checkvc['code'] == 1){

            //可直接登录
            $vcode = $checkvc['data']['vcode'];
            $pt_verifysession = $checkvc['data']['pt_verifysession'];
            $cookie = $checkvc['data']['cookie'];

        } else {
            //开始循环 滑块验证
            $cap_cd = $checkvc['data']['cap_cd'];
            $cookie = $checkvc['data']['cookie'];

            for ($i = 1; $i <= 5; $i++) {
                if (!isset($sess, $sid)) {
                    $getvc = $this->Qlogin->getvc($uin, $cap_cd);
                    $sid = $getvc['data']['sid'];
                    $sess = $getvc['data']['sess'];
                    $collectname = $getvc['data']['collectname'];
                } else {
                    $getvc = $this->Qlogin->getvc($uin, $cap_cd, $sess, $sid);
                    $sess = $getvc['data']['sess'];
                }
//                    dump($getvc);
                if ($getvc['code'] == 0) {
                    echo "GetVC Error!";
                }
                $dovc = $this->Qlogin->dovc($uin, $getvc['data']['ans'], $cap_cd, $sess, $sid, $collectname);
                if ($dovc['code'] == 0) {
                    //验证通过
                    $vcode = $dovc['data']['vcode'];
                    $pt_verifysession = $dovc['data']['pt_verifysession'];
                    echo("{$uin} 验证通过滑块");
                    break;
                } else {
                    echo( "{$uin} {$dovc['message']}");
                }
            }
        }
        if (isset($vcode, $pt_verifysession, $cookie)) {
            dump($vcode, $pt_verifysession, $cookie);
            $pwd = strtoupper(md5($pwd));
            $res = get_curl("https://api.elesq.cn/common/getPCode.html", "uin={$uin}&pwd={$pwd}&vcode={$vcode}");
            $json = json_decode($res, true);
            $p = $json['data'];
            dump($res);
            $qqlogin = $this->Qlogin->qqlogin($uin, $p, $vcode, $pt_verifysession, $cookie);
            dump($qqlogin);
        } else {
            echo "error";
        }


    }

    /**
     * 检测是否需要验证码
     */
    public function checkvc()
    {
        $uin = Request::post('uin');
        $checkvc = $this->Qlogin->checkvc($uin);
        return json($checkvc);
    }

    /**
     * 获取滑块验证码
     */
    public function getvc()
    {
        $uin = Request::post('uin');
        $cap_cd = Request::post('cap_cd');
        $sess = Request::post('sess');
        $sid = Request::post('sid');
        $getvc = $this->Qlogin->getvc($uin, $cap_cd, $sess, $sid);
        return json($getvc);

    }

    /**
     * 验证验证码
     */
    public function dovc()
    {
        $uin = Request::post('uin');
        $ans = Request::post('ans');
        $cap_cd = Request::post('cap_cd');
        $sess = Request::post('sess');
        $sid = Request::post('sid');
        $collectname = Request::post('collectname');
        $dovc = $this->Qlogin->dovc($uin, $ans, $cap_cd, $sess, $sid, $collectname);
        return json($dovc);

    }

    /**
     * 登录
     */
    public function qqlogin()
    {
        $uin = Request::post('uin');
        $pwd = Request::post('pwd');
        $p = Request::post('p');
        $vcode = Request::post('vcode');
        $pt_verifysession = Request::post('pt_verifysession');
        $cookie = Request::post('cookie');
        $sms_code = Request::post('sms_code');
        $sms_ticket = Request::post('sms_ticket');
        $qqlogin = $this->Qlogin->qqlogin($uin, $p, $vcode, $pt_verifysession, $cookie, $sms_code, $sms_ticket);
        return json($qqlogin);

    }

    /**
     * 发送验证码
     */
    public function sms_code_send()
    {
        $uin = Request::post('uin');
        $sms_ticket = Request::post('sms_ticket');
        $cookie = Request::post('cookie');
        $sms_code_send = $this->Qlogin->sms_code_send($uin, $sms_ticket, $cookie);
        return json($sms_code_send);

    }

    /**
     * 验证验证码登录
     */
    public function sms_code_verify()
    {
        $serverId = Request::get('serverId');
        $uin = Request::get('uin');
        $p = Request::get('p');
        $sms_ticket = Request::get('sms_ticket');
        $cookie = Request::get('cookie');
        $phone = Request::get('phone');
        $vcode = Request::get('vcode');
        $pt_verifysession = Request::get('pt_verifysession');
        View::assign([
            'serverId' => $serverId,
            'uin' => $uin,
            'p' => $p,
            'phone' => $phone,
            'vcode' => $vcode,
            'cookie' => $cookie,
            'sms_ticket' => $sms_ticket,
            'pt_verifysession' => $pt_verifysession
        ]);
        return autoTemplate();
    }

    /**
     * 获取登录二维码
     */
    public function getQrCode()
    {
        $getQrCode = $this->Qlogin->getQrCode();
        return json($getQrCode);
    }

    /**
     * 获取登录状态
     */
    public function getQrLogin()
    {
        $qrsig = Request::post('qrsig');
        $loginsig = Request::post('loginsig');
        $getQrLogin = $this->Qlogin->getQrLogin($qrsig, $loginsig);
        return json($getQrLogin);

    }

    /**
     * 更新状态
     */
    public function update()
    {
        $uid = session('user.uid');
        $sid = request::post('serverId');
        $uin = Request::post('uin');
        $pwd = Request::post('pwd');
        $skey = request::post('skey');
        $pskey = request::post('pskey');
        $superkey = request::post('superkey');
        $qq = new Qq();
        return $qq->add($uid,$sid,$uin,$pwd,$skey,$pskey,$superkey);
    }

    /**
     * VIEW
     */
    public function qr()
    {
        $server = new Server();
        $uin = Request::param('uin');

        if (!empty($uin)) {
            View::assign([
                'sid' => $this->res['sid'],
                'name' => $this->res['name']
            ]);
        }
        View::assign([
            'uin' => $uin,
            'serverList' => $server->getServerList(),
        ]);
        return autoTemplate();
    }

    public function login()
    {
        $server = new Server();
        $uin = Request::param('uin');
        if (!empty($uin)) {
            View::assign([
                'uin' => $this->res['uin'],
                'pwd' => $this->res['pwd'],
                'sid' => $this->res['sid'],
                'name' => $this->res['name']
            ]);
        }
        View::assign([
            'serverList' => $server->getServerList(),
        ]);
        return autoTemplate();
    }
}
