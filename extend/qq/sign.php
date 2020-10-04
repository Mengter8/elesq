<?php
/**
 * 腾讯其他功能类
 */

namespace qq;

class sign extends login
{
    /**
     * 群签到
     * @param string $title 签到标题
     * @param string $poi 签到地址
     * @param string $category_id 分类ID
     * @param string $pic_id 图片ID
     * @param string $template_id 模板ID
     * @param int $mode 特殊模式 0关闭 1白名单 2黑名单
     * @param array $list 跳过数组
     */
    public function qunqd($title, $poi, $category_id, $pic_id, $template_id, $mode = 0, $list = [])
    {
        /**
         * https://qun.qq.com/cgi-bin/qiandao/gallery_list?bkn=20667328&category_ids=[9]&start=0&num=50
         **/
        //取QQ群
        $url = "http://qqweb.qq.com/cgi-bin/anony/get_group_lst?bkn={$this->gtk}";
        $json = get_curl($url, 0, 0, $this->cookie);
        $arr = json_decode($json, true);
        dump($arr);
        if (array_key_exists('ec', $arr) && $arr['ec'] == 0) {
            //循环签到
            foreach ($arr['gcs'] as $v) {
                $gid = $v['gc'];//群号
                $gname = $v['n'];//群名
                if ($mode == 1) {
                    if (!in_array($gid, $list)) {
                        $this->msg[] = "{$gname} - 白名单已跳过";
                        continue;
                    }
                } elseif ($mode == 2) {
                    if (in_array($gid, $list)) {
                        $this->msg[] = "{$gname} - 黑名单已跳过";
                        continue;
                    }
                }

                $gallery_info = urlencode('{"category_id":' . $category_id . ',"page":0,"pic_id":' . $pic_id . '}');

                $url = "https://qun.qq.com/cgi-bin/qiandao/sign/publish";
                $post = "bkn={$this->gtk}&template_data=&gallery_info={$gallery_info}&template_id={$template_id}&gc={$gid}&client=2&lgt=0&lat=0&poi={$poi}&pic_id=&text={$title}";
                $res = get_curl($url, $post, 0, $this->cookie);
                $arr = json_decode($res, true);
                if ($arr['retcode'] == 0) {
                    $this->msg[] = "{$gname} - 签到完成";
                } elseif ($arr['retcode'] == 10001) {
                    $this->msg[] = "{$gname} - 禁言已跳过";
                } else {
                    $this->msg[] = "{$gname} - 签到失败";
                }
            }
        } else {
            $this->fail = true;
            $this->msg[] = '当前登陆状态已失效';
        }
    }


    /**
     * 手游加速
     */
    public function gameSpeed()
    {
        $url = 'http://reader.sh.vip.qq.com/cgi-bin/common_async_cgi?g_tk=' . $this->gtk . '&plat=1&version=6.6.6&param=%7B%22key0%22%3A%7B%22param%22%3A%7B%22bid%22%3A13792605%7D%2C%22module%22%3A%22reader_comment_read_svr%22%2C%22method%22%3A%22GetReadAllEndPageMsg%22%7D%7D';
        $json = get_curl($url, 0, $url, $this->cookie);
        $arr = json_decode($json, true);
        if (array_key_exists('ecode', $arr) && $arr['ecode'] == 0) {
            $this->msg[] = 'QQ手游加速0.2天成功！';
        } else {
            $this->msg[] = 'QQ手游加速失败！' . $json;
        }
    }

    public function mobileSpeed()
    {
        //全天在线 移动加速
        $url = "https://cgi.vip.qq.com/online/set?ps_tk={$this->gtk2}&g_tk=" . getGTK2($this->skey) . "&type=Y&beg=0&end=24";
        $data = get_curl($url, 0, 'https://vip.qq.com/my/index', $this->cookie);
        $arr = json_decode($data, true);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = $this->uin . ' 手机QQ在线6小时一键加速成功！';
        } elseif ($arr['ret'] == -7) {
            $this->msg[] = '手机QQ等级加速失败！原因：SKEY已失效';
        } else {
            $this->msg[] = '手机QQ等级加速失败！原因：' . $arr['msg'];
            $this->msg[] = '提示：必须是QQ会员，且<a href="https://bd.qq.com/" target="_blank">绑定手机</a>才能使用此功能。';
        }
    }

    /**
     * 运动加速 0.2
     */
    public function yunDong()
    {
        $steps = rand(11111, 99999);
        $timestamp = time();
        $params = '{"reqtype":11,"mbtodayStep":' . $steps . ',"todayStep":' . $steps . ',"timestamp":' . $timestamp . '}';
        $url = 'https://yundong.qq.com/cgi/common_daka_tcp?g_tk=' . $this->gtk;
        $post = 'params=' . urlencode($params) . '&l5apiKey=daka.server&dcapiKey=daka_tcp';
        $refer = 'https://yundong.qq.com/daka/index?_wv=2098179&rank=1&steps=10000&asyncMode=1&type=&mid=105&timestamp=' . $timestamp;
        $data = get_curl($url, $post, $refer, $this->cookie);
        $arr = json_decode($data, true);
        if (array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = 'QQ运动打卡成功！QQ成长值+0.2天';
        } elseif ($arr['code'] == -10001) {
            $this->msg[] = '今天步数未达到打卡门槛，再接再厉！';
        } elseif ($arr['code'] == -10003) {
            $this->msg[] = '今天已经打过卡了，明天再来吧~';
        } elseif ($arr['code'] == -1001) {
            $this->msg[] = 'QQ运动打卡失败！原因：SKEY已失效';
            $this->fail = true;
        } else {
            $this->msg[] = 'QQ运动打卡失败！原因：' . $arr['emsg'];
        }
    }

    /**
     * QQ每日打卡
     */
    public function checkin()
    {
        //qq每日打卡
        $url = 'https://ti.qq.com/hybrid-h5/api/json/daily_attendance/SignIn';
        $post = json_encode(array('uin' => $this->uin, 'type' => 1, 'qua' => 'V1_AND_SQ_8.4.8_1492_YYB_D'));

        $addheader = array("Content-Type: application/json; charset=utf-8");

        $referer = "https://ti.qq.com/signin/public/indexv2.html?_wv=1090532257&_wwv=13";
        $data = get_curl($url, $post, $referer, $this->cookie, 0, 0, 0, $addheader);

        $arr = json_decode($data, true);
        dump($arr);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            if ($arr['data'] && $arr['data']['retCode'] == 0) {
                $this->msg[] = 'QQ每日打卡成功！已打卡' . $arr['data']['totalDays'] . '天';
            } elseif ($arr['data']['retCode'] == -1) {
                $this->msg[] = 'QQ每日打卡今日已完成！';
            } else {
                $this->msg[] = 'QQ每日打卡状态未知';
            }
        } elseif ($arr['ret'] == -200) {
            $this->msg[] = 'QQ打卡失败，您可能未获得测试资格';
        } elseif ($arr['ret'] == -3000) {
            $this->fail = true;
            $this->msg[] = 'QQ打卡失败，SKEY已失效';
        } else {
            $this->msg[] = 'QQ打卡失败！' . $arr['msg'];
        }
    }

    /**
     * 游戏大厅签到
     */
    public function gameSign()
    {
        $url = 'http://social.minigame.qq.com/cgi-bin/social/welcome_panel_operate?format=json&cmd=2&uin=' . $this->uin . '&g_tk=' . $this->gtk;
        $data = get_curl($url, 0, 'http://minigame.qq.com/appdir/social/cloudHall/src/index/welcome.html', $this->cookie);
        $arr = json_decode($data, true);
        if (array_key_exists('result', $arr) && $arr['result'] == 0) {
            if ($arr['do_ret'] == 11)
                $this->msg[] = '游戏大厅今天已签到！';
            else
                $this->msg[] = '游戏大厅签到成功！';
        } elseif ($arr['result'] == 1000005) {
            $this->fail = true;
            $this->msg[] = '游戏大厅签到失败！SKEY已失效。';
        } else {
            $this->msg[] = '游戏大厅签到失败！' . $arr['resultstr'];
        }
        $url = "https://w.gamecenter.qq.com/v1/cgi-bin/home/task/get-task-list?t=1590693914101&g_tk={$this->gtk2}&p_tk=g-c1xzXMe2mm1B3IzDTLvyhaobU7s--QDJrmSBLW21g_";
        $qqUa = "Mozilla/5.0 (iPhone; CPU iPhone OS 13_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 QQ/8.3.0.608 V1_IPH_SQ_8.3.0_1_APP_A Pixel/1125 Mi";
        get_curl($url, 0, $url, $this->cookie, 0, $qqUa);

        $url = 'https://info.gamecenter.qq.com/cgi-bin/gc_my_tab_async_fcgi?merge=1&ver=0&st=' . time() . '000&sid=&uin=' . $this->uin . '&number=0&path=489&plat=qq&gamecenter=1&_wv=1031&_proxy=1&gc_version=2&ADTAG=gamecenter&notShowPub=1&param=%7B%220%22%3A%7B%22param%22%3A%7B%22platform%22%3A1%2C%22tt%22%3A1%7D%2C%22module%22%3A%22gc_my_tab%22%2C%22method%22%3A%22sign_in%22%7D%7D&g_tk=' . $this->gtk;
        $data = get_curl($url, 0, $url, $this->cookie);
        $arr = json_decode($data, true);
        if (array_key_exists('ecode', $arr) && $arr['ecode'] == 0) {
            $arr = $arr['data']['0'];
            if (array_key_exists('retCode', $arr) && $arr['retCode'] == 0) {
                $this->msg[] = '手Q游戏中心签到成功！已连续签到' . $arr['retBody']['data']['cur_continue_sign'] . '天';
            } else {
                $this->msg[] = '手Q游戏中心签到失败！' . $arr['retBody']['message'];
            }
        } elseif ($arr['ecode'] == -120000) {
            $this->fail = true;
            $this->msg[] = '手Q游戏中心签到失败！SKEY已失效。';
        } else {
            $this->msg[] = '手Q游戏中心签到失败！' . $arr['data']['0']['retBody']['message'];
        }
    }


    /**
     * 安全盾签到
     */
    public function gameSafe()
    {
        $url = 'https://act.gamesafe.qq.com/cgi-bin/signin';
        $data = get_curl($url, 0, $url, $this->cookie);
        $arr = json_decode($data, true);
        dump($arr);
        if (array_key_exists('result', $arr) && $arr['result'] == 0) {
            $this->msg[] = '安全盾签到成功！获得安全盾+' . $arr['shields'] . ' 已连续签到' . $arr['days'];
        } elseif ($arr['result'] == 1029000500) {
            $this->msg[] = '安全盾今天已签到！';
        } else {
            $this->msg[] = '安全盾签到失败！' . $arr['result'] . $arr['errmsg'];
        }
    }

    public function book()
    {
        $url = "http://ubook.3g.qq.com/8/user/myMission?k1={$this->skey}&u1=o0{$this->uin}";
        $data = get_curl($url, 0, 'http://ubook.qq.com/8/mymission.html');
        $arr = json_decode($data, true);
        if ($arr['isLogin'] == 'true' && $arr['signMap']['code'] == 0) {
            $this->msg[] = '图书签到成功！';
        } elseif ($arr['signMap']['code'] == -2) {
            $this->msg[] = '图书今日已经签到！';
        } elseif ($arr['isLogin'] == 'false') {
            $this->fail = true;
            $this->msg[] = '图书签到失败！SKEY过期！';
        } else {
            $this->msg[] = '图书签到失败！数据异常';
        }

        $guid = md5($this->uin . time());
        $url = "https://novelsns.html5.qq.com/ajax?m=task&type=sign&aid=20&t=" . time() . "586";
        $data = get_curl($url, 0, 'https://bookshelf.html5.qq.com/discovery.html', 'Q-H5-ACCOUNT=' . $this->uin . '; Q-H5-SKEY=' . $this->skey . '; luin=' . $this->uin . '; Q-H5-USERTYPE=1; Q-H5-GUID=' . $guid . ';');
        $arr = json_decode($data, true);
//        dump($arr);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '小说书架签到成功！已连续签到' . $arr['continuousDays'] . '天,获得书豆' . $arr['beans'];
        } elseif ($arr['ret'] == -2) {
            $this->msg[] = '小说书架今天已签到！已连续签到' . $arr['continuousDays'] . '天,获得书豆' . $arr['beans'];
        } else {
            $this->msg[] = '小说书架签到失败！' . $arr['msg'];
        }

        $url = "https://novelsns.html5.qq.com/ajax?m=shareSignPageObtainBeans&aid=20&t=" . time() . "586";
        $data = get_curl($url, 0, 'https://bookshelf.html5.qq.com/discovery.html', 'Q-H5-ACCOUNT=' . $this->uin . '; Q-H5-SKEY=' . $this->skey . '; luin=' . $this->uin . '; Q-H5-USERTYPE=1; Q-H5-GUID=' . $guid . ';');
        $arr = json_decode($data, true);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '小说书架分享成功！获得书豆' . $arr['beans'];
        } else {
            $this->msg[] = '小说书架分享失败！' . $arr['msg'];
        }

        $url = 'http://reader.sh.vip.qq.com/cgi-bin/reader_page_csrf_cgi?merge=2&ditch=100020&cfrom=account&current=sign_index&tf=2&sid=' . $this->uin . '&client=1&version=qqreader_1.0.669.0001_android_qqplugin&channel=00000&_bid=2036&ChannelID=100020&plat=1&qqVersion=0&_from=sign_index&_=' . time() . '017&g_tk=' . $this->gtk . '&p_tk=&sequence=' . time() . '755';
        $post = 'param=%7B%220%22%3A%7B%22param%22%3A%7B%22tt%22%3A0%7D%2C%22module%22%3A%22reader_sign_manage_svr%22%2C%22method%22%3A%22UserTodaySign%22%7D%2C%221%22%3A%7B%22param%22%3A%7B%22tt%22%3A0%7D%2C%22module%22%3A%22reader_sign_manage_svr%22%2C%22method%22%3A%22GetSignGifts%22%7D%7D';
        $data = get_curl($url, $post, $url, $this->cookie);
        $arr = json_decode($data, true);
        if ($arr = $arr['data']['0']['retBody']) {
            if (array_key_exists('result', $arr) && $arr['result'] == 0) {
                $this->msg[] = '手机QQ阅读签到成功！获得书券' . $arr['data']['awards'][0]['awardNum'] . ',已连续签到' . $arr['data']['lastDays'] . '天';
            } else {
                $this->msg[] = '手机QQ阅读签到失败！' . $arr['message'];
            }
        } else {
            $this->msg[] = '手机QQ阅读签到失败！' . $data;
        }

        $url = 'http://reader.sh.vip.qq.com/cgi-bin/reader_page_csrf_cgi?merge=1&ditch=100020&cfrom=account&current=sign_index&tf=2&sid=' . $this->uin . '&client=1&version=qqreader_1.0.669.0001_android_qqplugin&channel=00000&_bid=2036&ChannelID=100020&plat=1&qqVersion=0&_from=sign_index&_=' . time() . '017&g_tk=' . $this->gtk . '&p_tk=&sequence=' . time() . '755';
        $post = 'param=%7B%220%22%3A%7B%22param%22%3A%7B%22tt%22%3A0%7D%2C%22module%22%3A%22reader_sign_manage_svr%22%2C%22method%22%3A%22GrantBigGift%22%7D%7D';
        $data = get_curl($url, $post, $url, $this->cookie);
        $arr = json_decode($data, true);
        if ($arr = $arr['data']['0']['retBody']) {
            if (array_key_exists('result', $arr) && $arr['result'] == 0) {
                $this->msg[] = '手机QQ阅读抽奖成功！获得奖品' . $arr['data']['newlyGift']['giftName'];
            } elseif ($arr['result'] == 1004) {
                $this->msg[] = '手机QQ阅读抽奖：需要连续签到5天才可以抽奖';
            } else {
                $this->msg[] = '手机QQ阅读抽奖失败！' . $arr['message'];
            }
        } else {
            $this->msg[] = '手机QQ阅读抽奖失败！' . $data;
        }
    }

    public function addbuluo($superkey, $bid)
    {
        $supertoken = (string)$this->getToken($superkey);
        $url = "http://ptlogin.qq.com/pt4_auth?daid=371&appid=715030901&auth_token=" . $this->getToken($supertoken);
        $data = get_curl($url, 0, 'http://ui.ptlogin2.qq.com/cgi-bin/login', 'superuin=o0' . $this->uin . '; superkey=' . $superkey . '; supertoken=' . $supertoken . ';');
        if (preg_match('/ptsigx=(.*?)&/', $data, $match)) {
            $url = 'http://ptlogin4.buluo.qq.com/check_sig?uin=' . $this->uin . '&ptsigx=' . $match[1] . '&daid=371&pt_login_type=4&service=pt4_auth&pttype=2&regmaster=&aid=715030901&s_url=http%3A%2F%2Fbuluo.qq.com%2Fp%2Fbarindex.html';
            $data = get_curl($url, 0, 'http://ui.ptlogin2.qq.com/cgi-bin/login', 0, 1);
            preg_match_all('/Set-Cookie: (.*);/iU', $data, $matchs);
            $this->cookie = '';
            foreach ($matchs[1] as $val) {
                if (substr($val, -1) == '=') continue;
                $this->cookie .= $val . '; ';
            }
            $url = 'http://buluo.qq.com/cgi-bin/bar/user/fbar';
            $post = 'bid=' . $bid . '&op=1&bkn=' . $this->gtk . '&r=0.395212' . time();
            $data = get_curl($url, $post, 'http://buluo.qq.com/mobile/mpz.html?_lv=' . $bid . '&_wv=257289&_bid=128', $this->cookie);
            $arr = json_decode($data, true);
            if (array_key_exists('retcode', $arr) && $arr['retcode'] == 0) {
                $this->msg[] = '部落关注成功！';
            } elseif ($arr['result'] == 100006) {
                $this->msg[] = '部落关注失败！p_skey已失效';
            } else {
                $this->msg[] = '部落关注失败！' . $data;
            }
        } else {
            $this->msg[] = '部落关注失败！superkey已失效';
        }
    }

    public function qqllq()
    {
        $guid = md5($this->uin . time());
        $this->qqllq_task('283658', '每日签到', 5, $guid);
        $url = 'https://i.browser.qq.com/static_data?guid=' . $guid . '&g_tk=' . $this->gtk2;
        $data = get_curl($url, 0, 'https://i.browser.qq.com/', $this->cookie);
        $arr = json_decode($data, true);
        dump($arr);
        if (array_key_exists('error', $arr) && $arr['error'] == 0) {
            foreach ($arr['data']['task_list'] as $row) {
                $this->qqllq_task($row['interface'], $row['title'], $row['score'], $guid);
            }
        } else {
            $this->msg[] = '获取QQ浏览器任务列表失败！';
        }
    }

    private function qqllq_task($actid, $title, $score, $guid)
    {
        $url = 'https://iyouxi3.vip.qq.com/ams3.0.php?g_tk=' . $this->gtk . '&actid=' . $actid . '&guid=' . $guid . '&fromat=json&_=' . time() . '7071';
        $data = get_curl($url, 0, 'http://i.browser.qq.com/', $this->cookie);
        $arr = json_decode($data, true);
        dump($arr);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = 'QQ浏览器 ' . $title . ' 成功！积分+' . $score;
        } elseif ($arr['ret'] == 10601) {
            $this->msg[] = 'QQ浏览器 ' . $title . ' 今天已完成！';
        } elseif ($arr['ret'] == 10002) {
            $this->fail = true;
            $this->msg[] = 'QQ浏览器 ' . $title . ' 失败！SKEY过期';
        } else {
            $this->msg[] = 'QQ浏览器 ' . $title . ' 失败！' . $arr['msg'];
        }
    }


    public function dongMan()
    {
        //抓包地址
        //https://cdn.vip.qq.com/club/client/comic/release/html/task_center_v2.html?_bid=354&_wv=1027&pos=111&from=6&platId=110&_wvx=3
        $url = "http://comic.vip.qq.com/cgi-bin/coupon_coin?merge=1&mqqVersion=&app_from=8&pageVersion=2f4b9e37edaf3332fe9b606adabe895484a8cb33_online&platId=110&from=6&pos=111&_wvx=3&read_params=&version=1&_=" . time() . "050&g_tk={$this->gtk}&p_tk=&sequence=" . time() . "219";
        $post = 'param=%7B%220%22%3A%7B%22param%22%3A%7B%22tt%22%3A0%2C%22useBd%22%3A1%2C%22cfrom%22%3A6%7D%2C%22module%22%3A%22comic_sign_in_svr%22%2C%22method%22%3A%22SignIn%22%2C%22timestamp%22%3A' . time() . '046%7D%7D';
        $data = get_curl($url, $post, $url, $this->cookie);
        $arr = json_decode($data, true);
        dump($arr);
        if ($arr = $arr['data']['0']['retBody']) {
            if (array_key_exists('result', $arr) && $arr['result'] == 0) {
                $this->msg[] = '手机QQ动漫签到成功！波豆+40，本月已累计签到' . $arr['data']['singedDayOfMonth'] . '天';
            } elseif ($arr['result'] == -120000) {
                $this->fail = true;
                $this->msg[] = '手机QQ动漫签到失败！SKEY已失效';
            } else {
                $this->msg[] = '手机QQ动漫签到失败！' . $arr['message'];
            }
        } else {
            $this->msg[] = '手机QQ动漫签到失败！' . $data;
        }
    }


    public function qzoneVip()
    {
        $data = get_curl('https://h5.qzone.qq.com/vipinfo/index?_wv=3&source=pc' . $this->gtk, 0, 0, $this->cookie);
        $arr['成长值'] = getSubstr($data, '"score":', ',"');
        $arr['速度(天)'] = getSubstr($data, '"speed":', ',"');
        $arr['升级所需(天)'] = getSubstr($data, '"upgrade_days":', ',"');
        $arr['已开通(天)'] = getSubstr($data, '"keep_vip_days":', ',"');//已开通舔
        $arr['开通到期'] = getSubstr($data, '"svipTime":"', '","') . '-' . getSubstr($data, '"vipTime":"', '","');
        dump($arr);
    }

    /**
     * 花藤签到
     */
    public function flower()
    {
        $url = 'https://h5.qzone.qq.com/proxy/domain/flower.qzone.qq.com/fcg-bin/cgi_plant?g_tk=' . $this->gtk2;
        $post = 'fl=1&fupdate=1&act=rain&uin=' . $this->uin . '&newflower=1&outCharset=utf%2D8&g%5Ftk=' . $this->gtk2 . '&format=json';
        $json = get_curl($url, $post, $url, $this->cookie);
        $arr = json_decode(str_replace("\n", '', $json), true);
        if (@array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = '浇水成功！';
        } elseif ($arr['code'] == -6002) {
            $this->msg[] = '今天浇过水啦！';
        } elseif ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = '浇水失败！原因:SKEY已过期！';
        } else {
            $this->msg[] = '浇水失败！' . $arr['message'];
        }

        $post = 'fl=1&fupdate=1&act=love&uin=' . $this->uin . '&newflower=1&outCharset=utf%2D8&g%5Ftk=' . $this->gtk2 . '&format=json';
        $json = get_curl($url, $post, $url, $this->cookie);
        $arr = json_decode(str_replace("\n", '', $json), true);
        if (@array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = '修剪成功！';
        } elseif ($arr['code'] == -6002) {
            $this->msg[] = '今天修剪过啦！';
        } elseif ($arr['code'] == -6007) {
            $this->msg[] = '您的爱心值今天已达到上限！';
        } elseif ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = '修剪失败！原因:SKEY已过期！';
        } else {
            $this->msg[] = '修剪失败！' . $arr['message'];
        }

        $post = 'fl=1&fupdate=1&act=sun&uin=' . $this->uin . '&newflower=1&outCharset=utf%2D8&g%5Ftk=' . $this->gtk2 . '&format=json';
        $json = get_curl($url, $post, $url, $this->cookie);
        $arr = json_decode(str_replace("\n", '', $json), true);
        if (@array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = '光照成功！';
        } elseif ($arr['code'] == -6002) {
            $this->msg[] = '今天日照过啦！';
        } elseif ($arr['code'] == -6007) {
            $this->msg[] = '您的阳光值今天已达到上限！';
        } elseif ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = '光照失败！原因:SKEY已过期！';
        } else {
            $this->msg[] = '光照失败！' . $arr['message'];
        }

        $post = 'fl=1&fupdate=1&act=nutri&uin=' . $this->uin . '&newflower=1&outCharset=utf%2D8&g%5Ftk=' . $this->gtk2 . '&format=json';
        $json = get_curl($url, $post, $url, $this->cookie);
        $arr = json_decode(str_replace("\n", '', $json), true);
        if (@array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = '施肥成功！';
        } elseif ($arr['code'] == -6005) {
            $this->msg[] = '暂不能施肥！';
        } elseif ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = '施肥失败！原因:SKEY已过期！';
        } else {
            $this->msg[] = '施肥失败！' . $arr['message'];
        }

        $url = 'https://h5.qzone.qq.com/proxy/domain/flower.qzone.qq.com/cgi-bin/cgi_pickup_oldfruit?g_tk=' . $this->gtk2;
        $post = 'mode=1&g%5Ftk=' . $this->gtk2 . '&outCharset=utf%2D8&fupdate=1&format=json';
        $json = get_curl($url, $post, $url, $this->cookie);
        $arr = json_decode(str_replace("\n", '', $json), true);
        if (@array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = '兑换神奇肥料成功！';
        } elseif ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = '兑换神奇肥料失败！原因:SKEY已过期！';
        } else {
            $this->msg[] = '兑换神奇肥料失败！' . $arr['message'];
        }

        $url = 'https://h5.qzone.qq.com/proxy/domain/flower.qzone.qq.com/cgi-bin/fg_pickup_fruit?g_tk=' . $this->gtk2;
        $post = 'format=json&outCharset=utf-8&random=23552.762577310205';
        $json = get_curl($url, $post, $url, $this->cookie);
        $arr = json_decode(str_replace("\n", '', $json), true);
        if (@array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = '摘果成功！';
        } elseif ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = '摘果失败！原因:SKEY已过期！';
        } else {
            $this->msg[] = '摘果失败！' . $arr['message'];
        }

        $url = 'https://h5.qzone.qq.com/proxy/domain/flower.qzone.qq.com/cgi-bin/cgi_show_userprop?p=0.37835920085705778&fupdate=1&format=json&g_tk=' . $this->gtk2;
        $json = get_curl($url, 0, $url, $this->cookie);
        $json = mb_convert_encoding($json, "UTF-8", "gb2312");
        $arr = json_decode(str_replace("\n", '', $json), true);
        if (@array_key_exists('code', $arr) && $arr['code'] == 0) {
            foreach ($arr['data']['prop'] as $row) {
                $url = 'https://h5.qzone.qq.com/proxy/domain/flower.qzone.qq.com/cgi-bin/cgi_exchange_prop?g_tk=' . $this->gtk2;
                $post = 'op_uin=' . $this->uin . '&propid=' . $row['propid'] . '&num=1&p=0.' . time() . '74383277&qzreferrer=http%3A%2F%2Frc.qzone.qq.com%2Fappstore%2Fdailycoupon%3Ffrom%3Dappstore.myInfoBoxBtn&fupdate=1&format=json';
                get_curl($url, $post, 'https://ctc.qzs.qq.com/qzone/client/photo/swf/RareFlower/FlowerVineLite.swf', $this->cookie);

                $url = 'https://h5.qzone.qq.com/proxy/domain/flower.qzone.qq.com/cgi-bin/cgi_use_mallprop?g_tk=' . $this->gtk2;
                $post = 'qzreferrer=http%3A%2F%2Fctc.qzs.qq.com%2Fqzone%2Fflower%2Ftool.html%23&propid=' . $row['propid'] . '&op_uin=' . $this->uin . '&p=0.' . time() . '84094803&format=json';
                $json = get_curl($url, $post, 'https://ctc.qzs.qq.com/qzone/client/photo/swf/RareFlower/FlowerVineLite.swf', $this->cookie);
                $json = mb_convert_encoding($json, "UTF-8", "gb2312");
            }
        }

        $url = 'https://h5.qzone.qq.com/proxy/domain/flower.qzone.qq.com/cgi-bin/fg_get_giftpkg?g_tk=' . $this->gtk2;
        $post = 'outCharset=utf-8&format=json';
        $json = get_curl($url, $post, 'https://ctc.qzs.qq.com/qzone/client/photo/swf/RareFlower/FlowerVineLite.swf', $this->cookie);
        $arr = json_decode(str_replace("\n", '', $json), true);

        if (@array_key_exists('code', $arr) && $arr['code'] == 0) {

            $this->msg[] = $arr['data']['vDailyGiftpkg'][0]['caption'] . ':' . $arr['data']['vDailyGiftpkg'][0]['content'];
            $giftpkgid = $arr['data']['vDailyGiftpkg'][0]['id'];
            $granttime = $arr['data']['vDailyGiftpkg'][0]['granttime'];
            $url = 'https://h5.qzone.qq.com/proxy/domain/flower.qzone.qq.com/cgi-bin/fg_use_giftpkg?g_tk=' . $this->gtk2;
            $post = 'giftpkgid=' . $giftpkgid . '&outCharset=utf%2D8&granttime=' . $granttime . '&format=json';
            $ret = get_curl($url, $post, 'https://ctc.qzs.qq.com/qzone/client/photo/swf/RareFlower/FlowerVineLite.swf', $this->cookie);

            $this->msg[] = $arr['data']['vSeriesLoginGiftpkg'][0]['caption'] . ':' . $arr['data']['vSeriesLoginGiftpkg'][0]['content'];
            $giftpkgid = $arr['data']['vSeriesLoginGiftpkg'][0]['id'];
            $granttime = $arr['data']['vSeriesLoginGiftpkg'][0]['granttime'];
            $post = 'giftpkgid=' . $giftpkgid . '&outCharset=utf%2D8&granttime=' . $granttime . '&format=json';
            $ret = get_curl($url, $post, 'https://ctc.qzs.qq.com/qzone/client/photo/swf/RareFlower/FlowerVineLite.swf', $this->cookie);
        } elseif ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = '领取每日登录礼包失败！原因:SKEY已过期！';
        } else {
            $this->msg[] = '领取每日登录礼包失败！' . $arr['message'];
        }

        $url = 'https://h5.qzone.qq.com/proxy/domain/flower.qzone.qq.com/cgi-bin/cgi_get_giftpkg?uin=' . $this->uin . '&t=0.8578207577979139&fupdate=1&g_tk=' . $this->gtk2;
        $json = get_curl($url, 0, 'https://ctc.qzs.qq.com/qzone/client/photo/swf/RareFlower/FlowerVineLite.swf', $this->cookie);

        $url = "https://h5.qzone.qq.com/proxy/domain/flower.qzone.qq.com/cgi-bin/fg_use_giftpkg?g_tk={$this->gtk2}";
        $post = "giftpkgid=1001&granttime=1591221675&format=json&outCharset=utf-8";
        $json = get_curl($url, $post, 0, $this->cookie);
        $json = str_replace("\n", '', $json);
        $arr = json_decode($json, true);
        if (@array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = '领取黄钻每月礼包成功！';
        } elseif ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = '领取黄钻每月登录礼包失败！原因:SKEY已过期！';
        } else {
            $this->msg[] = '领取黄钻每月登录礼包失败！' . $arr['message'];
        }
    }

    public function weiShi()
    {
        $url = 'https://h5.qzone.qq.com/weishi/jifen/main?_proxy=1&_wv=3&navstyle=2&titleh=55.0&statush=20.0';
        $data = get_curl($url, 0, 0, $this->cookie, 1);
        if (strpos($data['body'], '未找到有效登陆信息')) {
            $this->fail = true;
            $this->msg[] = $this->uin . ' QQ微视签到失败！SKEY过期';
            return;
        }
        $cookie = $this->cookie . '; ';
        preg_match_all('/Set-Cookie: (.*?);/i', $data['header'], $matchs);
        foreach ($matchs[1] as $val) {
            if (substr($val, -1) == '=') continue;
            $cookie .= $val . '; ';
        }
        $cookie = substr($cookie, 0, -2);

        $url = 'https://h5.qzone.qq.com/proxy/domain/activity.qzone.qq.com/fcg-bin/fcg_weishi_task_report_login?t=0.' . time() . '030444&g_tk=' . $this->gtk;
        $post = 'task_appid=weishi&task_id=SignIn&qua=_placeholder&format=json&uin=' . $this->uin . '&inCharset=utf-8&outCharset=utf-8';
        $data = get_curl($url, $post, 0, $cookie);
        $arr = json_decode($data, true);
        if (array_key_exists('code', $arr) && $arr['code'] == 0)
            $this->msg[] = $this->uin . ' QQ微视签到成功！';
        else
            $this->msg[] = $this->uin . ' QQ微视签到失败！' . $arr['message'];
    }


    public function qipao()
    {
        if (getRuntimeCache('cookie', "{$this->uin}.txt")) {
            $this->cookie = getRuntimeCache('cookie', "{$this->uin}.txt");
        } else {
            $this->qipao_login();
        }
//        dump($this->cookie);
        $str = '4271|4270|3433|3434|4171|3466|3467|4135|3719|3941|3565|4084|4083|4116|4117|4113|4112|3592|3729|3590|3591|3904|3732|3424|3772|2941|3656|3647|3022|3566|3601|3448|3447|3384|3241|3242|3247|3271|3264|2822|3139|3214|2903|3212|3103|3133|3097|3086|3087|3048|3045|3001|2982|2889|2859|500035|500023|600025|500010|2827|2826|2819|2814|2809|2792|2726|2765|2795|2669|2648|2625|2578|2575|2561|2562|2547|2531|2532|2533|2492|2507|2512|2423|2285|2279|2280|2281|2257|2210|2180|2145|2134|547|335|2001|2018|4316|3873|3563|4085|4086|3382|3263|3228|3119|3112|3096|3082|2946|2950|2908|2910|2913|2895|2880|2877|2860|2851|2843|2832|500034|600026|500011|2824|2783|2766|2767|2725|2764|2670|2668|2664|2665|2649|2650|2643|2645|2630|2626|2588|2579|2574|2553|2554|2523|2435|2195|2397|2380|2377|2363|2352|2335|2331|2327|2315|2311|2300|2298|2287|2286|2278|2270|2266|2254|2258|2218|2216|2215|2208|2181|2182|2158|2157|528|2133|368|2049|2131|233|2560|470';
        $strarr = explode('|', $str);
        $id = $strarr[array_rand($strarr)];
//        $id=6141;
        $ua = 'Mozilla/5.0 (Linux; Android 10; MI 9 Build/QKQ1.190825.002; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/66.0.3359.126 MQQBrowser/6.2 TBS/045008 Mobile Safari/537.36 V1_AND_SQ_8.2.0_1296_YYB_D QQ/8.2.0.4310 NetType/WIFI WebP/0.3.0 Pixel/1080 StatusBarHeight/75 SimpleUISwitch/0';

        $url = 'https://g.vip.qq.com/bubble/bubbleSetup?uin=' . $this->uin . '&adtag=%5Badtag%5D&client=androidQQ&version=8.2.0&platformId=2&_bid=undefined&_lv=0&format=json&t=' . time() . '214&id=' . $id . '&platformId=2&uin=' . $this->uin . '&g_tk=' . $this->gtk;
        $data = get_curl($url, 0, 'https://zb.vip.qq.com/sonic/bubble?_wv=16778243', $this->cookie, 0, $ua);
        $arr = json_decode($data, true);

        if (json_decode($data, true) && array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = '更换气泡成功！';
        } elseif ($arr['ret'] == 5002) {
            $this->msg[] = '气泡更换失败！需参与活动';
        } elseif ($arr['ret'] == 2002) {
            $this->msg[] = '气泡更换失败！你不是会员';
        } elseif ($arr['ret'] == -100001) {
            $this->fail = true;
            $this->qipao_login();
            $this->msg[] = '更换气泡失败！SKEY过期';
        } else {
            $this->qipao_login();
            $this->msg[] = '更换气泡失败！' . $arr['msg'];
        }
    }

    public function qipao_login()
    {

        $supertoken = (string)$this->getToken($this->superkey);
        $url = "https://ssl.ptlogin2.qq.com/pt4_auth?daid=18&aid=8000212&auth_token=" . $this->getToken($supertoken);
        $data = get_curl($url, 0, 'https://ui.ptlogin2.qq.com/cgi-bin/login', "superuin=o0{$this->uin}; superkey={$this->superkey}; supertoken={$supertoken};");
        if (preg_match('/ptsigx=(.*?)&/', $data, $match)) {
            $url = 'https://ptlogin2.vip.qq.com/check_sig?uin=' . $this->uin . '&ptsigx=' . $match[1] . '&daid=18&pt_login_type=4&service=pt4_auth&pttype=2&regmaster=&aid=8000212&s_url=https%3A%2F%2Fzb.vip.qq.com%2Fsonic%2Fbubble';
            $data = get_curl($url, 0, 'https://ui.ptlogin2.qq.com/cgi-bin/login', 0, 1);
            preg_match_all('/Set-Cookie: (.*);/iU', $data['header'], $matchs);
            $cookie = '';
            foreach ($matchs[1] as $val) {
                if (substr($val, -1) == '=') continue;
                $cookie .= "{$val}; ";
            }
            $this->cookie = substr($cookie, 0, -2);
            putRuntimeCache('cookie', "{$this->uin}.txt", $this->cookie);
            $this->qipao();
        } else {
            $this->fail = true;
            $this->msg[] = '更换气泡失败！superkey已失效';
        }
    }
}