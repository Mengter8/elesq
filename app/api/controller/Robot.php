<?php
declare (strict_types=1);

namespace app\api\controller;

use app\model\Qq;
use app\model\Server;
use qq\mpz;
use think\facade\Request;
use app\model\User;

class Robot
{
    public function getUin()
    {
        $uin = Request::get('uin');
        $res = User::field('uid')->getByQq($uin);
        if ($res) {
            return json(['code' => 1]);
        } else {
            return json(['code' => 0]);
        }
    }

    /**
     * 获取登录二维码
     */
    public function getQrCode()
    {
        $serverId = Request::param('serverId');
        if ($ret = (new Server())->getId($serverId)) {
            $Qlogin = new \qq\Qlogin($ret['api']);
            $getQrCode = $Qlogin->getQrCode();
        } else {
            return resultJson(0, '服务器ID错误');
        }
        return json($getQrCode);
    }

    /**
     * 获取登录状态
     */
    public function getQrLogin()
    {
        $serverId = Request::param('serverId');
        $qrsig = Request::param('qrsig');
        $login_sig = Request::param('login_sig');
        if ($ret = (new Server())->getId($serverId)) {
            $Qlogin = new \qq\Qlogin($ret['api']);
            $getQrCode = $Qlogin->getQrLogin($qrsig, $login_sig);
        } else {
            return resultJson(0, '服务器ID错误');
        }
        return json($getQrCode);
    }

    /**
     * 获取服务器列表ID 等等 对接机器人
     */

    public function update()
    {
        $serverId = Request::param('serverId');

        $uin = request::param('uin');

        $skey = request::param('skey');
        $pskey = request::param('pskey');
        $superkey = request::param('superkey');
        $qq = new Qq();
//        $res = $qq->getByUin($uin);
//        if ($res) {
//            $uid = $res['uid'];
//        } else {
//            $uid = 1;
//        }
        return $qq->add(0, $serverId, $uin, 0, $skey, $pskey, $superkey);

    }

    public function mpz()
    {
        $uin = Request::get('uin');
        if (!(new Qq)->getByUin($uin)) {
            return json(['code' => 0, 'msg' => '请先添加QQ再使用']);
        }
        $res = Qq::whereStatus(1)->select();
        foreach ($res as $qq) {
            $do = new \qq\mpz($qq['uin'], $qq['skey'], $qq['pskey']);
            $msg[] = $do->mpz_like($uin);
            if ($do->fail == true) {
                Qq::update(['status' => 0], ['uin' => $qq['uin']]);
            }
        }
        return json(['code' => 1, 'count' => count($res), 'data' => $msg]);
    }

    /**
     * 名片赞回赞
     */
    public function likeMe()
    {
        $uin = Request::get('uin');
        $res = (new Qq)->getByUin($uin);
        $do = new mpz($uin, $res['skey'], $res['pskey']);
        $do->mpz_likeMe(0);
//        $do->mpz_likeMe(20);
//        $do->mpz_likeMe(40);
//        $do->mpz_likeMe(60);
//        $do->mpz_likeMe(80);
//        $do->mpz_likeMe(100);
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
    }
}
