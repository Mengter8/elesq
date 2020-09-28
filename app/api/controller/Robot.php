<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\model\Qq;
use qq\mpz;
use qq\sign;
use think\facade\Request;
use app\model\User;

class Robot
{
    public function getUin(){
        $uin = Request::get('uin');
        $res = User::field('uid')->getByQq($uin);
        if ($res){
            return json(['code'=>1]);
        } else {
            return json(['code'=>0]);
        }
    }

    /**
     * 获取服务器列表ID 等等 对接机器人
     */

    public function update() {
        $uin = request::post('uin');
        $res = User::field('uid,uin')->getByQq($uin)->toArray();
        $uid = $res['uid'];
        $res = (new Qq)->getByUin($uin)->toArray();
        if ($res['pwd']){
            $pwd = $res['pwd'];
        }else{
            $pwd = '';
        }
        $data = [
            'uid' => $uid,
            'uin' => $uin,
            'pwd' => $pwd,
            'nick'=> get_qqnick($uin),
            'skey' => request::post('skey'),
            'pskey' => request::post('pskey'),
            'superkey' => request::post('superkey'),
            'status' => 1,
            'fail' => 0
        ];
        if ($res){
            $res = Qq::update($data,['uin'=>$uin]);
            if ($res) {
                return json(['code'=>1,'message'=>'更新成功']);
            }
        } else {
            $res = Qq::create($data);
            if ($res) {//bug
                return json(['code'=>1,'message'=>'添加成功']);
            }
        }
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
        return json(['code' => 1, 'count' => count($res),'data'=>$msg]);
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
