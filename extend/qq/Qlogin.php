<?php

namespace qq;

use think\Exception;

class Qlogin
{
    private $ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36';

    private $referrer;
    public $loginApi;

    public function setLoginApi($url)
    {
        $this->loginApi = $url;
    }

    /**
     * 检测是否需要验证码
     * @param $uin
     * 返回cap_cd
     * @return array
     */
    public function checkvc($uin)
    {
        $tokenid = (string)$this->random();
        $url = "https://xui.ptlogin2.qq.com/cgi-bin/xlogin?proxy_url=https%3A//qzs.qq.com/qzone/v6/portal/proxy.html&daid=5&&hide_title_bar=1&low_login=0&qlogin_auto_login=1&no_verifyimg=1&link_target=blank&appid=549000912&style=22&target=self&s_url=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&pt_qr_app=%E6%89%8B%E6%9C%BAQQ%E7%A9%BA%E9%97%B4&pt_qr_link=https%3A//z.qzone.com/download.html&self_regurl=https%3A//qzs.qq.com/qzone/v6/reg/index.html&pt_qr_help_link=https%3A//z.qzone.com/download.html&pt_no_auth=0";
        $data = $this->get_curl_proxy($url, 0, 0, 0, 1);
        $cookie = '';
        preg_match_all('/Set-Cookie: (.*?);/i', $data['header'], $matchs);
        foreach ($matchs[1] as $val) {
            $cookie .= $val . '; ';
        }

        preg_match("/pt_login_sig=(.*?);/", $cookie, $match);
        $pt_login_sig = $match[1];

        $url2 = "https://ssl.ptlogin2.qq.com/check?regmaster=&pt_tea=2&pt_vcode=1&uin={$uin}&appid=549000912&js_ver=20061814&js_type=1&login_sig={$pt_login_sig}&u1=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&r=0.6425160512034402&pt_uistyle=40";
        $data = $this->get_curl_proxy($url2, 0, $url, $cookie, 1);
        if (preg_match("/ptui_checkVC\('(.*?)'\)/", $data['body'], $arr)) {
            preg_match_all('/Set-Cookie: (.*);/iU', $data['header'], $matchs);
            foreach ($matchs[1] as $val) {
                $cookie .= $val . '; ';
            }
            $r = explode("','", $arr[1]);
            $code = $r[0];
            if ($code == 0) {
                //无需验证码 直接登录
                return resultArray(1, '无需滑块，直接登录', ['uin' => $uin, 'vcode' => $r[1], 'pt_verifysession' => $r[3], 'cookie' => $cookie]);
            } elseif ($code == 1) {
                //需要滑块验证码
                return resultArray(2, '请滑块验证', ['uin' => $uin, 'cap_cd' => $r[1], 'cookie' => $cookie]);
            } else {
                return resultArray($code, '未知错误', $data);
            }
        } else {
            return resultArray(0, '获取验证码失败', $data);
        }
    }

    /**
     * 获取滑块验证码
     * @param $uin
     * @param $cap_cd
     * @param $sess
     * @param $sid
     * @return array
     */
    public function getvc($uin, $cap_cd, $sess = NULL, $sid = NULL)
    {
//        dump($sess);
        if ($sess == NULL) {
            //预处理 获取sess sid参数

            $url = "https://t.captcha.qq.com/cap_union_prehandle?aid=549000912&captype=&curenv=inner&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=&color=&showtype=embed&fb=1&theme=&lang=2052&ua=" . urlencode(base64_encode($this->ua)) . "&enableDarkMode=0&grayscale=1&cap_cd={$cap_cd}&uid={$uin}&wxLang=&subsid=1&callback=_aq_56296&sess=";
            $referer = "https://xui.ptlogin2.qq.com/cgi-bin/xlogin?proxy_url=https%3A//qzs.qq.com/qzone/v6/portal/proxy.html&daid=5&&hide_title_bar=1&low_login=0&qlogin_auto_login=1&no_verifyimg=1&link_target=blank&appid=549000912&style=22&target=self&s_url=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&pt_qr_app=%E6%89%8B%E6%9C%BAQQ%E7%A9%BA%E9%97%B4&pt_qr_link=https%3A//z.qzone.com/download.html&self_regurl=https%3A//qzs.qq.com/qzone/v6/reg/index.html&pt_qr_help_link=https%3A//z.qzone.com/download.html&pt_no_auth=0";

            $data = $this->get_curl_proxy($url, 0, $referer);
            $arr = jsonp_decode($data, true);
            $sess = $arr['sess'];
            $sid = $arr['sid'];
            if (!isset($sess, $sid)) return resultArray(-3, '获取验证码参数失败');

            //获取验证码
            $url = "https://t.captcha.qq.com/cap_union_new_show?aid=549000912&captype=&curenv=inner&protocol=https&clientype=1&disturblevel=&apptype=2&noheader=0&color=&showtype=&fb=1&theme=&lang=2052&ua=" . base64_encode($this->ua) . "&enableDarkMode=0&grayscale=1&subsid=2&sess={$sess}&fwidth=0&sid={$sid}&forcestyle=undefined&wxLang=&tcScale=1&uid={$uin}&cap_cd={$cap_cd}&rnd=" . rand(100000, 999999) . "&TCapIframeLoadTime=225&prehandleLoadTime=108&createIframeStart=" . time() . "317";
            $data = $this->get_curl_proxy($url, 0, $referer);

            if (preg_match('/spt:\"(\d+)\"/', $data, $Number)) {
                $height = $Number[1];
                preg_match("/sess:\"([0-9a-zA-Z\*\_\-]+)\"/", $data, $match1);
                $sess = $match1[1];
                preg_match('/collectdata:\"([0-9a-zA-Z]+)\"/', $data, $collectname);
                preg_match('/&image=(\d+)\"/', $data, $imageid);

                $imgA = $this->getvcpic($uin, $imageid[1], $cap_cd, $sess, $sid, 1);//验证缺口图片
                $imgB = $this->getvcpic($uin, $imageid[1], $cap_cd, $sess, $sid, 0);//原图
                $imgC = $this->getvcpic($uin, $imageid[1], $cap_cd, $sess, $sid, 2);//缺口图片
                $width = $this->captcha($imgA, $imgB, $imgC, $height);
                $ans = $width . ',' . $height . ';';
                return resultArray(1, 'OK', ['sess' => $sess, 'ans' => $ans, 'sid' => $sid, 'collectname' => $collectname[1]]);
            } else {
                return resultArray(0, '获取验证码失败');
            }
        } else {
//            重新获取图片验证 重构OK
            $url = "https://t.captcha.qq.com/cap_union_new_getsig";
            $post = "aid=549000912&captype=&curenv=inner&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=&color=&showtype=embed&fb=1&theme=&lang=2052&ua=" . base64_encode($this->ua) . "&enableDarkMode=0&grayscale=1&subsid=2&sess={$sess}&fwidth=0&sid={$sid}&forcestyle=undefined&wxLang=&tcScale=1&noBorder=noborder&uid={$uin}&cap_cd={$cap_cd}&rnd=" . rand(100000, 999999) . "&TCapIframeLoadTime=23&prehandleLoadTime=192&createIframeStart=" . time() . "953&rand=" . rand(100000, 999999);
            $referer = "https://t.captcha.qq.com/cap_union_new_show?aid=549000912&captype=&curenv=inner&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=&color=&showtype=embed&fb=1&theme=&lang=2052&ua=" . base64_encode($this->ua) . "&enableDarkMode=0&grayscale=1&subsid=2&sess={$sess}&fwidth=0&sid={$sid}&forcestyle=undefined&wxLang=&tcScale=1&noBorder=noborder&uid={$uin}&cap_cd={$cap_cd}&rnd=" . rand(100000, 999999) . "&TCapIframeLoadTime=253&prehandleLoadTime=407&createIframeStart=" . time() . "190";
            $data = $this->get_curl_proxy($url, $post, $referer);
            $arr = json_decode($data, true);

            if ($arr['initx'] && $arr['inity']) {
                $sess = $arr['sess'];
                $height = $arr['inity'];

                $imageid = substr($arr['cdnPic1'], strpos($arr['cdnPic1'], '&image=') + 7);
                $imgA = $this->getvcpic2($uin, $cap_cd, $sess, $sid, 1);//验证缺口图片
                $imgB = $this->getvcpic2($uin, $cap_cd, $sess, $sid, 0);//原图
                $imgC = $this->getvcpic2($uin, $cap_cd, $sess, $sid, 2);//缺口图片
                $width = $this->captcha($imgA, $imgB, $imgC, $height);
                $ans = $width . ',' . $height . ';';
                return resultArray(2, 'new Ok', ['sess' => $sess, 'ans' => $ans]);
            } else {
                return resultArray(0, '获取验证码失败');
            }
        }
    }

    /**
     * 验证验证码
     * @param $uin
     * @param $ans
     * @param $cap_cd
     * @param $sess
     * @param $sid
     * @param $collectname
     * @return array
     */
    public function dovc($uin, $ans, $cap_cd, $sess, $sid, $collectname)
    {
        $collectname = !empty($collectname) ? $collectname : 'collect';

        $width = explode(',', $ans);
        $width = $width[0];
        $collect = $this->getcollect($width, $sid);
        $collect['tlg'] = strlen($collect['collectdata']);
        $url = 'https://ssl.captcha.qq.com/dfpReg?0=Mozilla%2F5.0%20(Windows%20NT%2010.0%3B%20WOW64)%20AppleWebKit%2F537.36%20(KHTML%2C%20like%20Gecko)%20Chrome%2F69.0.3497.100%20Safari%2F537.36&1=zh-CN&2=1.5&3=1.6&4=24&5=8&6=-480&7=1&8=1&9=1&10=u&11=function&12=u&13=Win32&14=0&15=f14d5531d44759dfdac2c422c0276dde&16=408d1e375fc96dcedebc2b02d580bac6&17=a1f937b6ee969f22e6122bdb5cb48bde&18=10x2x102x69&19=702efbf2d84a0bfb7224d4a1bfe36e0a&20=872136824912136824&21=1%3B&22=1%3B1%3B1%3B1%3B1%3B1%3B1%3B0%3B1%3Bobject27UTF-8&23=0&24=10%3B1&25=126a2202136b27316760a4f9c2c2e1a9&26=48000_2_1_0_2_explicit_speakers&27=d7959e801195e05311be04517d04a522&28=ANGLE(Intel(R)UHDGraphics620Direct3D11vs_5_0ps_5_0)&29=60f09e9c459c29f92ce6fc61751ea45b&30=9c04b80df743b5904a3835fbc06a476e&31=0&32=0&33=0&34=0&35=0&36=0&37=0&38=0&39=0&40=0&41=0&42=0&43=0&44=0&45=0&46=0&47=0&48=0&49=0&50=0&fesig=5744539509613183248&ut=391&appid=0&refer=https%3A%2F%2Fssl.captcha.qq.com%2Fcap_union_new_show&domain=ssl.captcha.qq.com&fph=&fpv=0.0.15&ptcz=';
        $data = $this->get_curl_proxy($url, 0, $this->referrer);
        $arr = json_decode($data, true);
        $fpsig = $arr['fpsig'];

        //提交验证
        $url = "https://t.captcha.qq.com/cap_union_new_verify";
        $post = "aid=549000912&captype=&curenv=inner&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=&color=&showtype=embed&fb=1&theme=&lang=2052&ua=" . base64_encode($this->ua) . "&enableDarkMode=0&grayscale=1&subsid=2&sess={$sess}&fwidth=0&sid={$sid}&forcestyle=undefined&wxLang=&tcScale=1&noBorder=noborder&uid={$uin}&cap_cd={$cap_cd}&rnd=" . rand(100000, 999999) . "&TCapIframeLoadTime=21&prehandleLoadTime=106&createIframeStart=" . time() . "437&cdata=0&ans={$ans}&vsig=&websig=&subcapclass=&{$collectname}={$collect['collectdata']}&tlg={$collect['tlg']}&fpinfo=fpsig={$fpsig}&eks={$collect['eks']}&vlg=0_0_1";
        $data = $this->get_curl_proxy($url, $post, $this->referrer);
        $arr = json_decode($data, true);

//        if (!isset($arr['errorCode'])) {
//            dump($arr);
//            dump($data);
//        }
        if (array_key_exists('errorCode', $arr) && $arr['errorCode'] == 0) {
            return resultArray(0, '验证通过', ['vcode' => $arr['randstr'], 'pt_verifysession' => $arr['ticket']]);
        } elseif ($arr['errorCode'] == 50) {
            return resultArray(50, '滑块验证失败');
        } elseif ($arr['errorCode'] == 12) {
            return resultArray(12, '网络恍惚请重试');
        } else {
            return resultArray(9, $arr['errMessage']);
        }
    }

    /**
     * 登录
     * @param $uin
     * @param $p
     * @param $vcode
     * @param $pt_verifysession
     * @param $cookie
     * @param null $sms_code
     * @param null $sms_ticket
     * @return array
     */
    public function qqlogin($uin, $p, $vcode, $pt_verifysession, $cookie, $sms_code = NULL, $sms_ticket = NULL)
    {
        if (strpos($vcode, '!')) {
            $v1 = 0;
        } else {
            $v1 = 1;
        }
        preg_match("/pt_login_sig=(.*?);/", $cookie, $match);
        $pt_login_sig = $match[1];

        preg_match("/ptdrvs=(.*?);/", $cookie, $match);
        $ptdrvs = $match[1];
        $url = "https://ssl.ptlogin2.qq.com/login?ptdrvs={$ptdrvs}&verifycode={$vcode}&u={$uin}&p={$p}&pt_randsalt=2&ptlang=2052&low_login_enable=0&u1=https%3A%2F%2Fqzs.qzone.qq.com%2Fmqzone%2Findex&from_ui=1&fp=loginerroralert&device=2&aid=549000912&daid=5&pt_3rd_aid=0&ptredirect=1&h=1&g=1&pt_uistyle=9&regmaster=&pt_vcode_v1={$v1}&pt_verifysession_v1={$pt_verifysession}";
        if (!empty($sms_code)) {
            $url .= '&pt_sms_code=' . $sms_code;
            $cookie .= 'pt_sms_ticket=' . $sms_ticket . '; pt_sms=' . $sms_code . ';';
        }
        $referrer = 'https://xui.ptlogin2.qq.com/cgi-bin/xlogin?proxy_url=https%3A//qzs.qq.com/qzone/v6/portal/proxy.html&daid=5&&hide_title_bar=1&low_login=0&qlogin_auto_login=0&no_verifyimg=1&link_target=blank&appid=549000912&style=22&target=self&s_url=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&pt_no_auth=0';
        $ret = $this->get_curl_proxy($url, 0, $referrer, $cookie, 1);
//        dump($ret);die();
        if (preg_match("/ptuiCB\('(.*?)'\)/", $ret['body'], $arr)) {
            $r = explode("','", str_replace("', '", "','", $arr[1]));
//            dump($r);die();
            if ($r[0] == 0) {
                preg_match('/skey=@(.{9});/', $ret['header'], $skey);
                preg_match('/superkey=(.*?);/', $ret['header'], $superkey);
                $data = $this->get_curl_proxy($r[2], 0, 0, 0, 1);
                if ($data) {
                    preg_match("/p_skey=(.*?);/", $data['header'], $matchs);
                    $pskey = $matchs[1];
                    preg_match("/Location: (.*?)\r\n/iU", $data['header'], $matchs);
                }
                if ($skey[1] && $pskey) {
                    return resultArray(0, '登录成功', ['uin' => $uin, 'skey' => '@' . $skey[1], 'pskey' => $pskey, 'superkey' => $superkey[1]]);
                } else {
                    if (!$pskey)
                        return resultArray(-3, '登录成功，获取P_skey失败！' . $r[2]);
                    elseif (!$skey[1])
                        return resultArray(-3, '登录成功，获取Skey失败！');
                }
            } elseif ($r[0] == 4) {
                return resultArray(4, '验证码错误');
            } elseif ($r[0] == 3) {
                return resultArray(3, '密码错误');
            } elseif ($r[0] == 19) {
                return resultArray(19, '您的帐号暂时无法登录，请到 http://aq.qq.com/007 恢复正常使用', ['uin' => $uin]);
            } elseif ($r[0] == 10009) {
                preg_match('/pt_sms_ticket=(.*?);/', $ret['header'], $sms_ticket);
                preg_match('/ptdrvs=(.*?);/', $ret['header'], $ptdrvs_new);
                $cookie = str_replace($ptdrvs, $ptdrvs_new[1], $cookie);
                return resultArray(10009, '需要手机验证', ['sms_ticket' => $sms_ticket[1], 'cookie' => $cookie, 'phone' => $r[4]]);
            } elseif ($r[0] == 10010) {
                return resultArray(10010, $r[4]);
            } else {
                return resultArray(-6, $r[4]);
            }
        } else {
            return resultArray(-2, $ret);
        }
    }

    /**
     * 发送验证码
     * @param $uin
     * @param $sms_ticket
     * @param $cookie
     * @return array
     */
    public function sms_code_send($uin, $sms_ticket, $cookie)
    {
//        return resultArray(1, '短信发送成功');

        if (empty($uin)) return resultArray(-1, 'QQ不能为空');
        if (empty($sms_ticket)) return resultArray(-1, 'sms_ticket不能为空');
        $cookie .= '; pt_sms_ticket=' . $sms_ticket;
        $url = "https://ssl.ptlogin2.qq.com/send_sms_code?bkn=&uin={$uin}&aid=549000912&pt_sms_ticket={$sms_ticket}";
        $data = $this->get_curl_proxy($url, 0, 'https://ui.ptlogin2.qq.com/web/verify/iframe?uin=' . $uin . '&appid=549000912', $cookie);
        if (preg_match("/ptui_sendSMS_CB\('(.*?)'\)/", $data, $arr)) {
            $r = explode("', '", $arr[1]);
            if ($r[0] == '10012') {
                return resultArray(1, '短信发送成功');
            } else {
                if (!empty($r[1])) {
                    return resultArray(0, $r[1]);
                } else {
                    return resultArray(0, '短信发送失败');
                }
            }
        } else {
            return resultArray(0, $data);
        }

    }

    /**
     * 获取登录二维码
     */
    public function getQrCode()
    {
        $url = 'https://ssl.ptlogin2.qq.com/ptqrshow?appid=549000912&e=2&l=M&s=3&d=72&v=4&t=0.5409099' . time() . '&daid=5&pt_3rd_aid=0';
        $arr = $this->get_curl_proxy($url, 0, 0, 0, 1);
//        dump($arr);die();
        if (preg_match('/qrsig=(.*?);/', $arr['header'], $match)) {
            $qrsig = $match[1];
        }
        $image = base64_encode($arr['body']);
        $url = "https://xui.ptlogin2.qq.com/cgi-bin/xlogin?proxy_url=https%3A//qzs.qq.com/qzone/v6/portal/proxy.html&daid=5&&hide_title_bar=1&low_login=0&qlogin_auto_login=1&no_verifyimg=1&link_target=blank&appid=549000912&style=22&target=self&s_url=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&pt_qr_app=%E6%89%8B%E6%9C%BAQQ%E7%A9%BA%E9%97%B4&pt_qr_link=https%3A//z.qzone.com/download.html&self_regurl=https%3A//qzs.qq.com/qzone/v6/reg/index.html&pt_qr_help_link=https%3A//z.qzone.com/download.html&pt_no_auth=0";
//        $url = 'https://ssl.ptlogin2.qq.com/ptqrshow?appid=549000912&e=2&l=M&s=4&d=72&v=4&t=0.5409099' . time() . '&daid=5';
//        $url = "http://ptlogin2.qq.com/ptqrshow?pt_clientver=5449&pt_src=1&appid=501004106&e=0&l=M&s=5&d=72&v=4&t=0.1254632154789654";
//        $url = 'https://ssl.ptlogin2.qq.com/ptqrshow?appid=549000912&e=2&l=M&s=4&d=72&v=4&t=0.5409099'.time().'&daid=5';
        $arr = $this->get_curl_proxy($url, 0, 0, 0, 1);

        if (preg_match('/pt_login_sig=(.*?);/', $arr['header'], $match)) {
            $login_sig = $match[1];
        }
        if (isset($login_sig, $qrsig)) {
            return resultArray(1, '二维码获取成功', ['qrsig' => $qrsig, 'login_sig' => $login_sig, 'image' => $image]);
        } else {
            return resultArray(0, '二维码获取失败');
        }
    }

    /**
     * 获取登录状态
     * @param $qrsig
     * @param $loginsig
     * @return array
     */
    public function getQrLogin($qrsig, $loginsig)
    {

        if (empty($qrsig)) return resultArray(-1, 'qrsig不能为空');
        $url = "https://ssl.ptlogin2.qq.com/ptqrlogin?u1=https%3A%2F%2Fqzs.qzone.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&ptqrtoken=" . $this->getqrtoken($qrsig) . "&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=0-0-" . time() . "&js_ver=20032614&js_type=1&login_sig={$loginsig}&pt_uistyle=40&aid=549000912&daid=5&";
        $cookie = "qrsig={$qrsig}; ";
        $ret = $this->get_curl_proxy($url, 0, $url, $cookie, 1);
        if (preg_match("/ptuiCB\('(.*?)'\)/", $ret['body'], $arr)) {
            $str = str_replace("', '", "','", $arr[1]);
            $r = explode("','", $str);

            $code = $r[0];
            $message = $r[4];
            if ($code == 0) {
                $url = $r[2];
                preg_match('/uin=o(\d+);/', $ret['header'], $uin);
                preg_match('/[1-9][0-9]{4,}/',$uin[1],$uin);

                preg_match('/skey=@(.{9});/', $ret['header'], $skey);
                preg_match('/superkey=(.*?);/', $ret['header'], $superkey);
                $data = $this->get_curl_proxy($url, 0, 0, 0, 1);
                preg_match("/p_skey=(.*?);/", $data['header'], $pskey);
                if (isset($uin[0], $skey[1], $pskey[1], $superkey[1])) {
                    return resultArray(1, '登录成功', ['uin' => $uin[0], 'skey' => '@' . $skey[1], 'pskey' => $pskey[1], 'superkey' => $superkey[1]]);
                } else {
                    return resultArray(0, '登录成功，获取相关信息失败！', $url);
                }
            } elseif ($code == 65) {
                return resultArray(-1, '二维码已失效');
            } elseif ($code == 66) {
                return resultArray(2, '二维码未失效');
            } elseif ($code == 67) {
                return resultArray(3, '正在验证二维码');
            } elseif ($code == 68) {
                return resultArray(4, '本次登录已被拒绝');
            } elseif ($code == 10009) {
                return resultArray(4, '需要手机验证码才能登录，此次登录失败');
            } else {
                return resultArray(4, $message, ['code' => $code]);
            }
        } else {
            return resultArray(4, '获取登录结果失败', $ret);
        }
    }

    /**
     * Tencent.Javascript pt.hash33
     * @param $qrsig
     * @return int
     */
    private function getqrtoken($qrsig)
    {
        $len = strlen($qrsig);
        $hash = 0;
        for ($i = 0; $i < $len; $i++) {
            $hash += (($hash << 5) & 2147483647) + ord($qrsig[$i]) & 2147483647;
            $hash &= 2147483647;
        }
        return $hash & 2147483647;
    }

    /**
     * Javascript Math.random()
     */

    private function random($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    /**
     * 第一次获取图片
     * @param $uin
     * @param $imageid
     * @param $cap_cd
     * @param $sess
     * @param $sid
     * @param int $img_index
     * @return string
     */
    private function getvcpic($uin, $imageid, $cap_cd, $sess, $sid, $img_index = 0)
    {
        $url = "https://t.captcha.qq.com/hycdn?index={$img_index}&image={$imageid}?aid=549000912&captype=&curenv=inner&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=&color=&showtype=embed&fb=1&theme=&lang=2052&ua=" . base64_encode($this->ua) . "&enableDarkMode=0&grayscale=1&subsid=3&sess={$sess}&fwidth=0&sid={$sid}&forcestyle=undefined&wxLang=&tcScale=1&noBorder=noborder&uid={$uin}&cap_cd={$cap_cd}&rnd=" . rand(100000, 999999) . "&TCapIframeLoadTime=23&prehandleLoadTime=192&createIframeStart=" . time() . "953&rand=" . rand(10000000, 99999999) . "&websig=&vsig=&img_index={$img_index}";
        return $url;
    }

    /**
     * 第二次获取图片
     * @param $uin
     * @param $cap_cd
     * @param $sess
     * @param $sid
     * @param $img_index
     * @return string
     */
    public function getvcpic2($uin, $cap_cd, $sess, $sid, $img_index)
    {
        $url = "https://t.captcha.qq.com/cap_union_new_getcapbysig?aid=549000912&captype=&curenv=inner&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=&color=&showtype=embed&fb=1&theme=&lang=2052&ua=" . base64_encode($this->ua) . "&enableDarkMode=0&grayscale=1&subsid=9&sess={$sess}&fwidth=0&sid={$sid}&forcestyle=undefined&wxLang=&tcScale=1&noBorder=noborder&uid={$uin}&cap_cd={$cap_cd}&rnd=" . rand(100000, 999999) . "&TCapIframeLoadTime=54&prehandleLoadTime=119&createIframeStart=" . time() . "001&rand=" . rand(10000000, 99999999) . "&websig=&vsig=&img_index={$img_index}";
        return $url;
    }


    /**
     * 自动识别验证
     * @param $imgAurl
     * @param $imgBurl
     * @param $imgCurl
     * @param $YYY
     * @return float|int
     */
    public function captcha($imgAurl, $imgBurl, $imgCurl = NULL, $YYY = NULL)
    {

        $imgA = imagecreatefromstring($this->get_curl_proxy($imgAurl, 0, $this->referrer));
        $imgB = imagecreatefromstring($this->get_curl_proxy($imgBurl, 0, $this->referrer));

        $imgWidth = imagesx($imgA);
        $imgHeight = imagesy($imgA);

        $t = 0;
        $r = 0;
        for ($y = 20; $y < $imgHeight - 20; $y++) {
            for ($x = 20; $x < $imgWidth - 20; $x++) {
                $rgbA = imagecolorat($imgA, $x, $y);
                $rgbB = imagecolorat($imgB, $x, $y);
                if (abs($rgbA - $rgbB) > 1800000) {
                    $t++;
                    $r += $x;
                }
            }
        }
        $x = round($r / $t) - 70;

//        putRuntimeCache("temp", '1.jpg', $this->get_curl_proxy($imgAurl, 0, $this->referrer));
//        putRuntimeCache("temp", '2.jpg', $this->get_curl_proxy($imgBurl, 0, $this->referrer));
//        putRuntimeCache("temp", '3.jpg', $this->get_curl_proxy($imgCurl, 0, $this->referrer));
//        $imgC = imagecreatefromstring($this->get_curl_proxy($imgCurl, 0, $this->referrer));
//        $width = imagesx($imgC);
//        $height = imagesy($imgC);
//
//        imagecopymerge($imgB, $imgC, $x, $YYY, 0, 0, $width, $height, 70);
//        imagejpeg($imgB,root_path() . "/runtime/temp/4.jpg");
        return $x;
    }


    public function getcollect($width, $sid)
    {
        $tokenid = (string)$this->random(2067831491, 5632894513);
        $slideValue = $this->generate_slideValue($width);
        return $this->tdcData($tokenid, $slideValue, $sid);
    }

    private function tdcData($tokenid, $slideValue, $sid)
    {
        $url = "https://t.captcha.qq.com/tdc.js?app_data={$sid}&t=" . time();
        $script = urlencode(base64_encode(get_curl($url)));
        $slideValue = urlencode($slideValue);
        $post = "script={$script}&tokenid={$tokenid}&slideValue={$slideValue}";
        $data = get_curl('http://collect.qqzzz.net/', $post, 0, 0, 0, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36');
        return json_decode($data, true);
    }

    private function generate_slideValue($width)
    {
        $sx = rand(700, 730);
        $sy = rand(295, 300);
        $this->trace_x = $sx;
        $this->trace_y = $sy;
        $ex = $sx + intval(($width - 55) / 2);
        $stime = rand(100, 300);
        $res = '[' . $sx . ',' . $sy . ',' . $stime . '],';
        $randy = array(0, 0, 0, 0, 0, 0, 1, 1, 1, 2, 3, -1, -1, -1, -2);
        while ($sx < $ex) {
            $x = rand(3, 9);
            $sx += $x;
            $y = $randy[array_rand($randy)];
            $time = rand(9, 18);
            $stime += $time;
            $res .= '[' . $x . ',' . $y . ',' . $time . '],';
        }
        $res .= '[0,0,' . rand(10, 25) . ']';
        return $res;
    }

    public function get_curl_proxy($url, $post = 0, $referer = 0, $cookie = 0, $header = 0, $ua = 0, $nobaody = 0)
    {
        $data = array('url' => $url, 'post' => $post, 'referer' => $referer, 'cookie' => $cookie, 'header' => $header, 'ua' => $ua, 'nobaody' => $nobaody);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $this->loginApi);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
//        $ret = '';
        curl_close($ch);
        if (false == $ret){
            header('Content-type: application/json; charset=utf-8');
            exit(json_encode(resultArray(0,'获取API结果失败',$data)));
        }
//        dump($ret);
        try {
            $ret = unserialize($ret);
        } catch (Exception $e){
//            $e->getMessage();
            header('Content-type: application/json; charset=utf-8');
            exit(json_encode(resultArray(0,'当前API节点异常',$data)));
        }
        return $ret;
    }
}
