<?php
namespace qq;

/**
 * 一堆问题 草泥马的
 * Class tool
 * @package qq
 */
class tool extends login{

    /**
     * 获取好友
     */
    public function haoyou(){
        $url = "https://mobile.qzone.qq.com/friend/mfriend_list?qzonetoken={$this->qzoneToken}&g_tk={$this->gtk}&res_uin={$this->uin}&res_type=normal&format=json&timestamp=1602180815964";
        $ret = get_curl($url,0,"https://user.qzone.qq.com/",$this->cookie,1);
        dump($ret);
    }

    /**
     * 获取 好友&分组
     */
    public function haoyou2(){
        $url = "https://user.qzone.qq.com/proxy/domain/r.qzone.qq.com/cgi-bin/tfriend/friend_mngfrd_get.cgi?uin={$this->uin}&g_tk={$this->gtk}";
        $json = get_curl($url, 0, 'https://user.qzone.qq.com/', $this->cookie);
        $arr = jsonp_decode($json, true);
        dump($arr);
        foreach ($arr['gpnames'] as $k => $v) {
            dump($v);
        }

        /** 获取接口2
        $url = "https://h5.qzone.qq.com/proxy/domain/r.qzone.qq.com/cgi-bin/tfriend/friend_show_qqfriends.cgi?uin={$uin}&follow_flag=1&groupface_flag=0&fupdate=1&g_tk={$gtk}";
        $json = get_curl($url, 0, 'http://user.qzone.qq.com/1968568204', $cookie);
        */
    }
    /**
     * 获取群成员
     */
    public function qun()
    {

        $this->cookie .= " p_uin=o{$this->uin};";
        $gid = "941060582"; //qq群
        $url = "https://qun.qq.com/cgi-bin/qun_mgr/search_group_members";
        $post = "gc={$gid}&st=0&end=40&sort=0&bkn={$this->gtk}";
        $referer = "https://qun.qq.com/member.html";
        $json = get_curl($url, $post, $referer, $this->cookie);
        $arr = jsonp_decode($json, true);

        dump($this->cookie);
        dump($arr);


        $url = "https://user.qzone.qq.com/proxy/domain/r.qzone.qq.com/cgi-bin/tfriend/qqgroupfriend_groupinfo.cgi?uin={$this->uin}&gid={$gid}&fupdate=1&type=1&qzonetoken={$this->qzoneToken}&g_tk={$this->gtk}";
        $json = get_curl($url, 0, 'https://user.qzone.qq.com/', $this->cookie);
        $arr = jsonp_decode($json, true);
        dump($arr);
//        foreach ($arr['data']['friends'] as $k => $v) {
//            echo $v['fuin'] . "<br>";
//        }
    }
}