<?php
/**
 * vip签到类
 */

namespace qq;
class vipSign extends login
{
    /**
     * 大会员签到
     */
    public function bigVip()
    {
        $url = 'https://vip.qzone.qq.com/fcg-bin/v2/fcg_vip_task_checkin?t=0.' . time() . '082161&g_tk=' . $this->gtk;
        $post = 'appid=qq_big_vip&op=CheckIn&uin=' . $this->uin . '&format=json&inCharset=utf-8&outCharset=utf-8';
        $data = get_curl($url, $post, 0, $this->cookie);
        $arr = json_decode($data, true);
        if (array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = $this->uin . ' 大会员签到成功！当前已签到' . $arr['data']['curCheckInDays'] . '天';

        } elseif ($arr['ret'] == -3000) {
            $this->fail = true;
            $this->msg[] = $this->uin . ' 大会员签到失败！SKEY过期';
        } else {
            $this->msg[] = $this->uin . ' 大会员签到失败！' . $arr['message'];
        }


        //登录我的访客
        $url = 'https://h5.qzone.qq.com/qzone/visitor?_wv=3&_wwv=1024&_proxy=1';
        get_curl($url, 0, 0, $this->cookie);

        $url = 'https://h5.qzone.qq.com/webapp/json/QQBigVipTask/CompleteTask?t=0.' . time() . '906319&g_tk=' . $this->gtk2;
        $post = 'outCharset=utf-8&iAppId=0&llTime=' . time() . '&format=json&iActionType=6&strUid=' . $this->uin . '&uin=' . $this->uin . '&inCharset=utf-8';
        $data = get_curl($url, $post, 0, $this->cookie);
        $arr = json_decode($data, true);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = $this->uin . ' 登录我的访客成功！';

        } elseif ($arr['ret'] == -3000) {
            $this->fail = true;
            $this->msg[] = $this->uin . ' 登录我的访客失败！SKEY过期';
        } else
            $this->msg[] = $this->uin . ' 登录我的访客失败！' . $arr['msg'];
    }

    /**
     * qq会员签到
     */
    public function vip()
    {
        $json = get_curl("https://iyouxi3.vip.qq.com/ams3.0.php?g_tk={$this->gtk}&actid=403490&_c=page&_=" . time(), 0, 'https://vip.qq.com/', $this->cookie);
        $arr = json_decode($json, TRUE);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '会员签到2019 签到成功';
        } elseif ($arr['ret'] == 10601) {
            $this->msg[] = '会员签到2019 今日已签到';
        } elseif ($arr['ret'] == 10002) {
            $this->fail = true;
            $this->msg[] = '会员签到2019 签到失败！SKEY过期';
        } else {
            $this->msg[] = '会员签到2019 签到失败！' . $arr['msg'];
        }

        $json = get_curl("https://iyouxi.vip.qq.com/ams3.0.php?_c=page&actid=23314&callback=vipSignNew.signCb&g_tk={$this->gtk}&cachetime=" . time(), 0, 'https://vip.qq.com/', $this->cookie);
        $arr = jsonp_decode($json, true);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '会员签到 签到成功';
        } elseif ($arr['ret'] == 10601) {
            $this->msg[] = '会员签到 今日已签到';
        } elseif ($arr['ret'] == 10002) {
            $this->fail = true;
            $this->msg[] = '会员签到 签到失败！SKEY过期';
        } else {
            $this->msg[] = '会员签到 签到失败！' . $arr['msg'];
        }


        $json = get_curl("https://iyouxi3.vip.qq.com/ams3.0.php?_c=page&actid=79968&format=json&g_tk=" . $this->gtk . "&cachetime=" . time(), 0, 'https://vip.qq.com/', $this->cookie);

        $arr = json_decode($json, TRUE);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '会员面板签到 签到成功';
        } elseif ($arr['ret'] == 10601) {
            $this->msg[] = '会员面板签到 今日已签到';
        } elseif ($arr['ret'] == 10002) {
            $this->fail = true;
            $this->msg[] = '会员面板签到 签到失败！SKEY过期';
        } else {
            $this->msg[] = '会员面板签到 签到失败！' . $arr['msg'];
        }
        $json = get_curl('https://iyouxi3.vip.qq.com/ams3.0.php?actid=52002&rand=0.27489888' . time() . '&g_tk=' . $this->gtk . '&format=json', 0, 'https://vip.qq.com/', $this->cookie);

        $arr = json_decode($json, TRUE);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '会员手机端签到 签到成功';
        } elseif ($arr['ret'] == 10601) {
            $this->msg[] = '会员手机端签到 今日已签到';
        } elseif ($arr['ret'] == 10002) {
            $this->fail = true;
            $this->msg[] = '会员手机端签到 签到失败！SKEY过期';
        } else {
            $this->msg[] = '会员手机端签到 签到失败！' . $arr['msg'];
        }
        $json = get_curl('https://iyouxi4.vip.qq.com/ams3.0.php?_c=page&actid=239151&isLoadUserInfo=1&format=json&g_tk=' . $this->gtk, 0, 'https://vip.qq.com/', $this->cookie);

        $arr = json_decode($json, TRUE);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '会员积分签到 签到成功';
        } elseif ($arr['ret'] == 10601) {
            $this->msg[] = '会员积分签到 今日已签到';
        } elseif ($arr['ret'] == 10002) {
            $this->fail = true;
            $this->msg[] = '会员积分签到 签到失败！SKEY过期';
        } else {
            $this->msg[] = '会员积分签到 签到失败！' . $arr['msg'];
        }

        $json = get_curl('https://iyouxi3.vip.qq.com/ams3.0.php?_c=page&actid=23074&format=json&g_tk=' . $this->gtk, 0, 'https://vip.qq.com/', $this->cookie);

        $arr = json_decode($json, TRUE);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '会员积分手机端签到 签到成功';
        } elseif ($arr['ret'] == 10601) {
            $this->msg[] = '会员积分手机端签到 今日已签到';
        } elseif ($arr['ret'] == 10002) {
            $this->fail = true;
            $this->msg[] = '会员积分手机端签到 签到失败！SKEY过期';
        } else {
            $this->msg[] = '会员积分手机端签到 签到失败！' . $arr['msg'];
        }
        $json = get_curl('https://pay.qun.qq.com/cgi-bin/group_pay/good_feeds/gain_give_stock?gain=1&bkn=' . $this->gtk, 0, 'https://m.vip.qq.com/act/qun/jindou.html', $this->cookie);
        $arr = json_decode($json, TRUE);
        if (array_key_exists('ec', $arr) && $arr['ec'] == 0) {
            $this->msg[] = 'QQ群领金豆 签到成功';
        } elseif ($arr['ec'] == 1010) {
            $this->msg[] = 'QQ群领金豆 今天已经领取过金豆了';
        } elseif ($arr['ec'] == 1) {
            $this->fail = true;
            $this->msg[] = 'QQ群领金豆 签到失败！SKEY过期';
        } else {
            $this->msg[] = 'QQ群领金豆 签到失败！' . $arr['em'];
        }
        $json = get_curl('https://iyouxi3.vip.qq.com/ams3.0.php?g_tk=' . $this->gtk . '&actid=27754&_=' . time(), 0, 'https://vip.qq.com/', $this->cookie);
        $arr = json_decode($json, TRUE);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '超级会员每月成长值 签到成功';
        } elseif ($arr['ret'] == 10601) {
            $this->msg[] = '超级会员每月成长值 今日已签到';
        } elseif ($arr['ret'] == 10002) {
            $this->fail = true;
            $this->msg[] = '超级会员每月成长值 签到失败！SKEY过期';
        } else {
            $this->msg[] = '超级会员每月成长值 签到失败！' . $arr['msg'];
        }
        $json = get_curl('https://iyouxi3.vip.qq.com/ams3.0.php?g_tk=' . $this->gtk . '&actid=239568', 0, 'https://vip.qq.com/', $this->cookie);
        $arr = json_decode($json, TRUE);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '超级会员每月积分 签到成功';
        } elseif ($arr['ret'] == 10601) {
            $this->msg[] = '超级会员每月积分 今日已签到';
        } elseif ($arr['ret'] == 10002) {
            $this->fail = true;
            $this->msg[] = '超级会员每月积分 签到失败！SKEY过期';
        } else {
            $this->msg[] = '超级会员每月积分 签到失败！' . $arr['msg'];
        }
        $json = get_curl('https://iyouxi3.vip.qq.com/ams3.0.php?g_tk=' . $this->gtk . '&actid=239371', 0, 'https://vip.qq.com/', $this->cookie);
        $arr = json_decode($json, TRUE);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '超级会员每周薪水 签到成功';
        } elseif ($arr['ret'] == 10601) {
            $this->msg[] = '超级会员每周薪水 今日已签到';
        } elseif ($arr['ret'] == 10002) {
            $this->fail = true;
            $this->msg[] = '超级会员每周薪水 签到失败！SKEY过期';
        } else {
            $this->msg[] = '超级会员每周薪水 签到失败！' . $arr['msg'];
        }
        $json = get_curl('https://iyouxi3.vip.qq.com/ams3.0.php?g_tk=' . $this->gtk . '&actid=22887&_c=page&format=json&_=' . time(), 0, 'https://vip.qq.com/', $this->cookie);
        $arr = json_decode($json, true);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '每周邀请好友积分 签到成功';
        } elseif ($arr['ret'] == 10601) {
            $this->msg[] = '每周邀请好友积分 今日已签到';
        } elseif ($arr['ret'] == 10002) {
            $this->fail = true;
            $this->msg[] = '每周邀请好友积分 签到失败！SKEY过期';
        } else {
            $this->msg[] = '每周邀请好友积分 签到失败！' . $arr['msg'];
        }
    }


    /**
     * 黄钻会员签到
     */
    public function qzoneVip()
    {
        $url = 'https://vip.qzone.qq.com/fcg-bin/v2/fcg_mobile_vip_site_checkin?t=0.89457' . time() . '&g_tk=' . $this->gtk . '&qzonetoken=423659183';
        $post = 'uin=' . $this->uin . '&format=json';
        $json = get_curl($url, $post, 0, $this->cookie);
        $arr = json_decode($json, true);
        if (array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = '黄钻官网签到 签到成功';
        } elseif ($arr['code'] == -10000) {
            $this->msg[] = '黄钻官网签到 今日已签到';
        } elseif ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = '黄钻官网签到 签到失败！SKEY过期';
        } else {
            $this->msg[] = '黄钻官网签到 签到失败！' . $arr['message'];
        }

        $url = "https://activity.qzone.qq.com/fcg-bin/fcg_huangzuan_daily_signing?g_tk={$this->gtk}";
        $post = "option=sign&uin={$this->uin}&format=json";
        $json = get_curl($url, $post, 0, $this->cookie);
        $json = mb_convert_encoding($json, "UTF-8", "GB2312");
        $json = str_replace(array("\r\n", "\r", "\n", "\t"), "", $json);
        $arr = json_decode($json, true);
        if (array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = '手机面板签到 签到成功';
        } elseif ($arr['code'] == -90001) {
            $this->msg[] = "手机面板签到 {$arr['message']}";
        } elseif ($arr['code'] == -10000) {
            $this->msg[] = '手机面板签到 今日已签到';
        } elseif ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = '手机面板签到 签到失败！SKEY过期';
        } else {
            $this->msg[] = '手机面板签到 签到失败！' . $arr['message'];
        }

        $url = "https://activity.qzone.qq.com/fcg-bin/fcg_qzact_present?t=0.026718469850362192&g_tk={$this->gtk2}";
        $post = "format=json&actid=2921&ruleid=18649&uin=1543797310&inCharset=utf-8&outCharset=utf-8";
        $jsonp = get_curl($url, $post, 'https://h5.qzone.qq.com/vip/home', $this->cookie);
        $arr = json_decode($jsonp, true);

        if (array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = '黄钻每日签到 签到成功';
        } elseif ($arr['code'] == -10000) {
            $this->msg[] = '黄钻每日签到 今日已签到';
        } elseif ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = '黄钻每日签到 签到失败！SKEY过期';
        } else {
            $this->msg[] = '黄钻每日签到 签到失败！' . $arr['message'];
        }
    }

    /**
     * 蓝钻会员签到
     */
    public function gameVip()
    {
        $url = "https://app.gamevip.qq.com/cgi-bin/gamevip_prepay/gamevip_draw_privilege?pv=p_salary&g_tk={$this->gtk}&qappid=1000001183";
        $json = get_curl($url, 0, $url, $this->cookie);
        $json = mb_convert_encoding($json, "UTF-8", "GB2312");
        $arr = json_decode($json, true);
        if (array_key_exists('result', $arr) && $arr['result'] == 0) {
            $this->msg[] = '欢乐豆 领取成功';
        } elseif ($arr['result'] == 1000) {
            $this->msg[] = '欢乐豆 您不是蓝钻';
        } elseif ($arr['result'] == 1000005) {
            $this->fail = true;
            $this->msg[] = '欢乐豆 领取失败！SKEY过期';
        } else {
            $this->msg[] = '欢乐豆 领取失败！' . $arr['resultstr'];
        }

        $url = "https://app.gamevip.qq.com/cgi-bin/gamevip_prepay/gamevip_draw_privilege?pv=p_scorecard&g_tk={$this->gtk}&qappid=1000001183";
        $json = get_curl($url, 0, $url, $this->cookie);
        $json = mb_convert_encoding($json, "UTF-8", "GB2312");
        $arr = json_decode($json, true);
        if (array_key_exists('result', $arr) && $arr['result'] == 0) {
            $this->msg[] = '7张双倍积分卡 领取成功';
        } elseif ($arr['result'] == 1000) {
            $this->msg[] = '7张双倍积分卡 您不是蓝钻';
        } elseif ($arr['result'] == 1000005) {
            $this->fail = true;
            $this->msg[] = '7张双倍积分卡 领取失败！SKEY过期';
        } else {
            $this->msg[] = '7张双倍积分卡 领取失败！' . $arr['resultstr'];
        }

        $url = "https://app.gamevip.qq.com/cgi-bin/gamevip_prepay/gamevip_draw_privilege?pv=p_bugle&g_tk={$this->gtk}&qappid=1000001183";
        $json = get_curl($url, 0, $url, $this->cookie);
        $json = mb_convert_encoding($json, "UTF-8", "GB2312");
        $arr = json_decode($json, true);
        if (array_key_exists('result', $arr) && $arr['result'] == 0) {
            $this->msg[] = '小喇叭 领取成功';
        } elseif ($arr['result'] == 1000) {
            $this->msg[] = '小喇叭 您不是蓝钻';
        } elseif ($arr['result'] == 1000005) {
            $this->fail = true;
            $this->msg[] = '小喇叭 领取失败！SKEY过期';
        } else {
            $this->msg[] = '小喇叭 领取失败！' . $arr['resultstr'];
        }
        $url = "https://app.gamevip.qq.com/cgi-bin/gamevip_prepay/gamevip_draw_privilege?pv=p_pet&g_tk={$this->gtk}&qappid=1000001183";
        $json = get_curl($url, 0, $url, $this->cookie);
        $json = mb_convert_encoding($json, "UTF-8", "GB2312");
        $arr = json_decode($json, true);
        if (array_key_exists('result', $arr) && $arr['result'] == 0) {
            $this->msg[] = '游戏宝宝 领取成功';
        } elseif ($arr['result'] == 1000) {
            $this->msg[] = '游戏宝宝 您不是蓝钻';
        } elseif ($arr['result'] == 1000005) {
            $this->fail = true;
            $this->msg[] = '游戏宝宝 领取失败！SKEY过期';
        } else {
            $this->msg[] = '游戏宝宝 领取失败！' . $arr['resultstr'];
        }

        $url = "https://app.gamevip.qq.com/cgi-bin/gamevip_prepay/gamevip_draw_privilege?pv=p_heroskin&g_tk={$this->gtk}&qappid=1000001183";
        $json = get_curl($url, 0, $url, $this->cookie);
        $json = mb_convert_encoding($json, "UTF-8", "GB2312");
        $arr = json_decode($json, true);
        if (array_key_exists('result', $arr) && $arr['result'] == 0) {
            $this->msg[] = '英雄杀皮肤 领取成功';
        } elseif ($arr['result'] == 1000) {
            $this->msg[] = '英雄杀皮肤 您不是蓝钻';
        } elseif ($arr['result'] == 1000005) {
            $this->fail = true;
            $this->msg[] = '英雄杀皮肤 领取失败！SKEY过期';
        } else {
            $this->msg[] = '英雄杀皮肤 领取失败！' . $arr['resultstr'];
        }
        $url = "https://app.gamevip.qq.com/cgi-bin/gamevip_prepay/gamevip_draw_privilege?pv=p_bankercard&g_tk={$this->gtk}&qappid=1000001183";
        $json = get_curl($url, 0, $url, $this->cookie);
        $json = mb_convert_encoding($json, "UTF-8", "GB2312");
        $arr = json_decode($json, true);
        if (array_key_exists('result', $arr) && $arr['result'] == 0) {
            $this->msg[] = '欢乐斗牛抢庄卡 领取成功';
        } elseif ($arr['result'] == 1000) {
            $this->msg[] = '欢乐斗牛抢庄卡 您不是蓝钻';
        } elseif ($arr['result'] == 1000005) {
            $this->fail = true;
            $this->msg[] = '欢乐斗牛抢庄卡 领取失败！SKEY过期';
        } else {
            $this->msg[] = '欢乐斗牛抢庄卡 领取失败！' . $arr['resultstr'];
        }
        $url = "https://app.gamevip.qq.com/cgi-bin/gamevip_prepay/gamevip_draw_privilege?pv=p_doublecard&g_tk={$this->gtk}&qappid=1000001183";
        $json = get_curl($url, 0, $url, $this->cookie);
        $json = mb_convert_encoding($json, "UTF-8", "GB2312");
        $arr = json_decode($json, true);
        if (array_key_exists('result', $arr) && $arr['result'] == 0) {
            $this->msg[] = '欢乐斗地主加倍卡 领取成功';
        } elseif ($arr['result'] == 1000) {
            $this->msg[] = '欢乐斗地主加倍卡 您不是蓝钻';
        } elseif ($arr['result'] == 1000005) {
            $this->fail = true;
            $this->msg[] = '欢乐斗地主加倍卡 领取失败！SKEY过期';
        } else {
            $this->msg[] = '欢乐斗地主加倍卡 领取失败！' . $arr['resultstr'];
        }
    }

    /**
     * 腾讯视频会员签到
     */
    public function videoVip()
    {
        $url = 'https://vip.video.qq.com/fcgi-bin/comm_cgi?name=hierarchical_task_system&cmd=2&_=' . time() . '8906';
        $data = get_curl($url, 0, $url, $this->cookie);
        preg_match('/QZOutputJson=\((.*?)\)/is', $data, $json);
        $arr = json_decode($json[1], true);
//        dump($arr);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = $this->uin . ' 腾讯视频VIP会员签到成功！获得' . $arr['checkin_score'] . '成长值';
        } elseif ($arr['ret'] == -10006) {
            $this->fail = true;
            $this->msg[] = $this->uin . ' 腾讯视频VIP会员签到失败！SKEY已失效';
        } elseif ($arr['ret'] == -10019) {
            $this->msg[] = $this->uin . ' 你不是腾讯视频VIP会员，无法签到';
        } else {
            $this->msg[] = $this->uin . ' 腾讯视频VIP会员签到失败！' . $arr['msg'];
        }
    }
}
