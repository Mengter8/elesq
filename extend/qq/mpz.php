<?php
/**
 * 名片赞
 */

namespace qq;
ini_set('max_execution_time', 600);

class mpz extends login
{
    /**
     * 一键回赞
     * @param int $page
     */
    public function mpz_likeMe($page = 0)
    {
        $url = "https://club.vip.qq.com/visitor/visit-me?g_tk={$this->gtk2}&page={$page}-" . time();
        $referer = 'https://club.vip.qq.com/visitor/index?_wv=4099&_nav_bgclr=ffffff&_nav_titleclr=ffffff&_nav_txtclr=ffffff&_nav_alpha=0';
        $json = get_curl($url, 0, $referer, $this->cookie);
        $arr = json_decode($json, true);
        foreach ($arr['visit']['visitorList'] as $v) {
            for ($i = 1; $i <= 20; $i++) {
                $ret = $this->mpz_like($v['uin']);
                $this->msg[] = $ret['message'];

                if ($ret['code']==5 ){
//                    break;
                }
            }
        }
    }

    /**
     * 我赞的
     * @param $page
     */
    public function mpz_meLike($page = 0)
    {
        $url = "https://club.vip.qq.com/visitor/visit-who2?g_tk={$this->gtk2}&page={$page}-" . time();
        $referer = 'https://club.vip.qq.com/visitor/index?_wv=4099&_nav_bgclr=ffffff&_nav_titleclr=ffffff&_nav_txtclr=ffffff&_nav_alpha=0';
        $json = get_curl($url, 0, $referer, $this->cookie);
        $arr = json_decode($json, true);
        dump($arr);
        echo $this->gtk2;
    }

    /**
     * @param $uin int 要赞的人
     */
    public function mpz_like($uin)
    {
        $referer = 'https://club.vip.qq.com/visitor/index?_wv=4099&_nav_bgclr=ffffff&_nav_titleclr=ffffff&_nav_txtclr=ffffff&_nav_alpha=0';

        $urls = "https://club.vip.qq.com/visitor/like?g_tk={$this->gtk2}&nav=0&uin={$uin}&t=" . time() . "391";
        $ret = get_curl($urls, 0, $referer, $this->cookie);
        $v = json_decode($ret, true);

        if ($v) {
//            dump($v);die();
            if (array_key_exists('retcode', $v) && $v['retcode'] == 0) {
                $msg = "{$this->uin} 赞 {$uin} - 点赞成功";
                return ['code' => 1, 'message' => $msg];
            } elseif (array_key_exists('retcode', $v) && $v['retcode'] == 10005) {
                $msg = "{$this->uin} 赞 {$uin} - 不能为自己点赞";
                return ['code' => 2, 'message' => $msg];
            } elseif (array_key_exists('retcode', $v) && $v['retcode'] == 10003) {
                $msg = "{$this->uin} 赞 {$uin} - 不允许陌生人赞";
                return ['code' => 3, 'message' => $msg];
            } elseif (array_key_exists('retcode', $v) && $v['retcode'] == 20001) {
                $msg = "{$this->uin} 赞 {$uin} - 每天最多给500个好友点赞";
                return ['code' => 4, 'message' => $msg];
            } elseif (array_key_exists('retcode', $v) && $v['retcode'] == 20003) {
                $msg = "{$this->uin} 赞 {$uin} - 今天已点赞上限";
                return ['code' => 5, 'message' => $msg];
            } elseif (array_key_exists('retcode', $v) && $v['retcode'] == 151) {
                $msg = "{$this->uin} 赞 {$uin} - 登录状态失效@skey";
                $this->fail = true;
                return ['code' => 6, 'message' => $msg];
            } elseif (array_key_exists('code', $v) && $v['code'] == -400) {
                $msg = "{$this->uin} 赞 {$uin} - 登录状态失效@g_tk";
                $this->fail = true;
                return ['code' => 7, 'message' => $msg];
            } elseif (array_key_exists('retcode', $v) && $v['retcode'] == -401) {
                $msg = "{$this->uin} 赞 {$uin} - 登录状态失效@401";
                $this->fail = true;
                return ['code' => 7, 'message' => $msg];
            } else {
                $msg = "{$this->uin} - {$v['retcode']} - error";
                return ['code' => -1, 'message' => $msg,'data'=>$v];
            }
        } else {
            $msg = "{$this->uin} 赞 {$uin} - 失败";
            return ['code' => 0, 'message' => $msg];
        }
    }
}