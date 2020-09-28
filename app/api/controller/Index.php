<?php
declare (strict_types=1);

namespace app\api\controller;

use app\model\Qq;
use Swoole\Client;
use qq\mpz;
use qq\qzone;
use qq\sign;
use think\facade\Request;

class Index
{
    public function index()
    {
        return '404';
    }


    /**
     * 获取 好友&分组
     */
    public function haoyou()
    {
        $uin = Request::get('uin');
        $skey = Request::get('skey');
        $pskey = Request::get('pskey');
        $gtk = getGTK($skey);
        $cookie = "uin=o{$uin}; skey={$skey}; p_skey={$pskey};";

        $url = "https://user.qzone.qq.com/proxy/domain/r.qzone.qq.com/cgi-bin/tfriend/friend_mngfrd_get.cgi?uin={$uin}&g_tk={$gtk}";
        $json = get_curl($url, 0, 'https://user.qzone.qq.com/', $cookie);
        $arr = jsonp_decode($json, true);
        dump($arr);
        foreach ($arr['gpnames'] as $k => $v) {
            dump($v);
        }
        /*
         * 获取接口2
        $url = "https://h5.qzone.qq.com/proxy/domain/r.qzone.qq.com/cgi-bin/tfriend/friend_show_qqfriends.cgi?uin={$uin}&follow_flag=1&groupface_flag=0&fupdate=1&g_tk={$gtk}";
        $json = get_curl($url, 0, 'http://user.qzone.qq.com/1968568204', $cookie);
        */
    }

    /**
     * 获取群成员
     */
    public function qun()
    {
        $uin = Request::get('uin');
        $res = (new Qq)->getByUin($uin);
        $qzone = new qzone($uin, $res['skey'], $res['pskey']);
//        $qzone = new qzone($uin, "@LOvc3WWsh", "PuOZRQnrJWQ*cQx2Y*n9*NkHGydzIC2At3okwdcSAsQ_");


        $qzone->cookie .= "p_uin=o{$uin};";
        $gid = "7489038"; //qq群
        $url = "https://qun.qq.com/cgi-bin/qun_mgr/search_group_members";
        $post = "gc={$gid}&st=0&end=40&sort=0&bkn={$qzone->gtk}";
        $referer = "https://qun.qq.com/member.html";
        $json = get_curl($url, $post, $referer, $qzone->cookie);
        dump(json_decode($json,true));


        $url = "https://user.qzone.qq.com/proxy/domain/r.qzone.qq.com/cgi-bin/tfriend/qqgroupfriend_groupinfo.cgi?uin={$uin}&gid={$gid}&fupdate=1&type=1&qzonetoken={$qzone->qzoneToken}&g_tk={$qzone->gtk}";
        $json = get_curl($url, 0, 'https://user.qzone.qq.com/', $qzone->cookie);
        $arr = jsonp_decode($json, true);
        dump($arr);
        foreach ($arr['data']['friends'] as $k => $v) {
            echo $v['fuin'] . "<br>";
        }
    }


    public function swoole() {
        $client = new Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $ret = $client->connect("127.0.0.1", 9501);

        if(empty($ret)){
            echo 'error!connect to swoole_server failed';
        } else {
            echo 'is ok!';
            $client->send('test');
        }
    }
}
