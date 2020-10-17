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
        return $qq->add(0, $serverId, $uin, 0, $skey, $pskey, $superkey);
    }

    public function getStatus(){
        $uin = request::param('uin');
        $qq = new \app\api\model\Qq();
        $res = $qq->getUinStatus($uin);
        if ($res){
            return resultJson(1,'获取成功',$res);
        } else {
            return resultJson(1,'获取失败',[]);
        }
    }
    public function getUserInfo(){
        $uin = request::param('uin');

        $user = new \app\api\model\User();
        $res = $user->getUserInfo($uin);
        if ($res){
            return resultJson(1,'获取成功',$res);
        } else {
            return resultJson(1,'获取失败',[]);
        }
    }
    public function getMyList(){
        $uin = request::param('uin');

        $user = new \app\api\model\Qq();
        $res = $user->getMyList($uin);
        if ($res){
            return resultJson(1,'获取成功',$res);
        } else {
            return resultJson(1,'获取失败',[]);
        }
    }
    /**
     * 业务查询
     */
    public function getPayInfo()
    {
        $uin = Request::get('uin');
        $res = (new Qq)->getByUin($uin);

        $skey = $res->skey;
        $res = get_curl("https://api.unipay.qq.com/v1/r/1450000172/wechat_query?cmd=7&session_id=uin&session_type=skey&openid={$uin}&openkey={$skey}");
        $arr = json_decode($res, true);
//        dump($arr);
        foreach ($arr['service'] as $key=>$vo) {
            if ($vo['start_time'] > $vo['end_time'] || $vo['end_time'] > date("Y-m-d H:i:s")) {
                $ret[] = ['service_name'=>$vo['service_name'],'start_time'=>$vo['start_time'],'end_time'=>$vo['end_time']];
            }
        }
        return resultJson(1,'获取成功',$ret);
    }
    public function getPayLevel(){
        //https://r.qzone.qq.com/cgi-bin/user/cgi_personal_card?uin=10001&remark=0&g_tk=1577542604

        /**
        _Callback(
        {"uin":10001,
        "qzone":1,
        "intimacyScore":0,
        "nickname":"pony",
        "realname":"",
        "smartname":"",
        "logolabel":"0",
        "commfrd":0,
        "friendship":0,
        "gender":1,
        "astro":7,
        "isFriend":0,
        "bitmap":"1a51d5c41433d301",
        "qqvip":9,
        "greenvip":6,
        "bluevip":4,
        "publicwalfare":1,
        "avatarUrl":"http://qlogo2.store.qq.com/qzone/10001/10001/100?0"});
         */
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
