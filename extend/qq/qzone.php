<?php
/**
 * QQ空间操作类
 */

namespace qq;

class qzone extends login
{

    /**
     * 获取说说列表[CP]
     */
    public function getListCP()
    {
        $url = "https://h5.qzone.qq.com/webapp/json/mqzone_feeds/getActiveFeeds?qzonetoken={$this->qzoneToken}&g_tk={$this->gtk2}";
        $post = 'res_type=0&res_attach=&refresh_type=2&format=json&attach_info=';
        $json = get_curl($url, $post, 1, $this->cookie);
        $arr = json_decode($json, true);
        if ($arr['ret'] === 0 && $arr['code'] === 0) {
            return $arr['data']['vFeeds'];
        } elseif ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = '获取最新说说失败！Skey已过期[CP]';
        } else {
            $this->msg[] = '获取最新说说失败！请联系站长[CP]';
        }
    }


    /**
     * 说说点赞[CP]
     * @param $unikey
     * @param $curkey
     * @param $appid
     * @return string
     */
    public function likeCP($unikey, $curkey, $appid)
    {
        $post = "opuin={$this->uin}&unikey={$unikey}&curkey={$curkey}&appid={$appid}&opr_type=like&format=purejson";
//        print_r($post);
        $url = "https://h5.qzone.qq.com/proxy/domain/w.qzone.qq.com/cgi-bin/likes/internal_dolike_app?qzonetoken={$this->qzoneToken}&g_tk={$this->gtk2}";
//        print_r($url);
        $json = get_curl($url, $post, 1, $this->cookie);
        $arr = json_decode($json, true);
        if (array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            return "点赞成功";
        } elseif ($arr['ret'] == -1) {
            $this->fail == true;
            return "点赞失败[CP]！SKEY已失效";
        } elseif (@array_key_exists('msg', $arr)) {
            return "点赞失败[CP]！{$arr['msg']}[{$arr['ret']}]";
        } else {
            return "点赞失败[CP]！";
        }
    }

    /**
     * 批量点赞
     * @param int $mod 特殊模式 0关闭 1白名单 2黑名单
     * @param array $list 特殊模式 QQ列表[开启有效]
     */
    public function like($mod = 0, $list = [])
    {
//        dump($list);
        $shuos = $this->getListCP();
        $i = 0;
        if ($shuos && $shuos != []) {
            foreach ($shuos as $k) {
                $like = array_key_exists('like', $k) ? $k['like']['isliked'] : 0; //未点赞0 已点赞1
                $appid = $k['comm']['appid'];
                $curkey = urlencode($k['comm']['curlikekey']);
                $unikey = urlencode($k['comm']['orglikekey']);
                $uin = $k['userinfo']['user']['uin']; //说说QQ
                $nickname = $k['userinfo']['user']['nickname']; //说说名称
//            $summary = $k['summary']['summary'];//说说全部内容
//            $summary = $k['operation']['share_info']['title']; //说说摘要内容
                $summary = $k['operation']['share_info']['title'] ? $k['operation']['share_info']['title'] : @$k['summary']['summary']; //这里不@ 空白说说就会报错
                $likeNum = @$k['like']['num'];//说说点赞数量
                $msg = "{$nickname} - {$summary} - {$likeNum}赞";
                if ($like == 0) { //$forbid白名单
                    if ($mod == 1) {
                        if (in_array($k['userinfo']['user']['uin'], $list)) {
                            $this->msg[] = "{$msg} - {$this->likeCP($unikey, $curkey, $appid)}";
                            $i++;
                        } else {
                            $this->msg[] = "{$msg} - 白名单已跳过";
                        }
                    } elseif ($mod == 2) {
                        if (!in_array($k['userinfo']['user']['uin'], $list)) {
                            $this->msg[] = "{$msg} - {$this->likeCP($unikey, $curkey, $appid)}";
                            $i++;
                        } else {
                            $this->msg[] = "{$msg} - 黑名单已跳过";
                        }
                    } else {
                        $this->msg[] = "{$msg} - {$this->likeCP($unikey, $curkey, $appid)}";
                        $i++;
                    }
                } else {
                    $this->msg[] = "{$msg} - 已赞过";
                }
            }
            if ($i == 0) {
                $this->msg[] = "没有要点赞的说说";
            }
        } else {
            $this->msg[] = "好友说说列表为空";
        }

    }


    /**
     * 说说点赞[PC协议]
     * @param $uin
     * @param $curkey
     * @param $unikey
     * @param $from
     * @param $appid
     * @param $typeid
     * @param $abstime
     * @param $fid
     */
    public function likePC($uin, $curkey, $unikey, $from, $appid, $typeid, $abstime, $fid)
    {
        $post = 'qzreferrer=http%3A%2F%2Fuser.qzone.qq.com%2F' . $this->uin . '&opuin=' . $this->uin . '&unikey=' . $unikey . '&curkey=' . $curkey . '&from=' . $from . '&appid=' . $appid . '&typeid=' . $typeid . '&abstime=' . $abstime . '&fid=' . $fid . '&active=0&fupdate=1';
        $url = 'https://user.qzone.qq.com/proxy/domain/w.qzone.qq.com/cgi-bin/likes/internal_dolike_app?qzonetoken=' . $this->getQzoneToken() . '&g_tk=' . $this->gtk2;
        $ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36';
        $get = get_curl($url, $post, 'https://user.qzone.qq.com/' . $this->uin, $this->cookie, 0, $ua);
        preg_match('/callback\((.*?)\)\;/is', $get, $json);
        if ($json = $json[1]) {
            $arr = json_decode($json, true);
            if ($arr['message'] == 'succ' || $arr['msg'] == 'succ') {
                $this->msg[] = '赞 ' . $uin . ' 的说说成功[PC]';
            } elseif ($arr['code'] == -3000) {
                $this->fail = true;
                $this->msg[] = '赞 ' . $uin . ' 的说说失败[PC]！原因:SKEY已失效';
            } elseif (@array_key_exists('message', $arr)) {
                $this->msg[] = '赞 ' . $uin . ' 的说说失败[PC]！原因:' . $arr['message'];
            } else {
                $this->msg[] = '赞 ' . $uin . ' 的说说失败[PC]！原因:' . $json;
            }
        } else {
            $this->msg[] = '获取赞' . $uin . '的说说结果失败[PC]';
        }
    }

    /**
     * 获取说说列表[PC协议]
     * @param array $forbid
     */
    public function getListPc($forbid = array())
    {
        $url = 'https://user.qzone.qq.com/proxy/domain/ic2.qzone.qq.com/cgi-bin/feeds/feeds3_html_more?format=json&begintime=' . time() . '&count=20&uin=' . $this->uin . '&g_tk=' . $this->gtk2;
        $json = get_curl($url, 0, 'https://user.qzone.qq.com/' . $this->uin, $this->cookie);
        $arr = json_decode($json, true);
        if ($arr['code'] == -3000) {
            $this->fail = true;
            $this->msg[] = $this->uin . '获取说说列表失败，原因:SKEY过期！[PC]';
        } elseif (strpos($json, '"code":0')) {
            $this->msg[] = $this->uin . '获取说说列表成功[PC]';
            $json = str_replace(array("\\x22", "\\x3C", "\/"), array('"', '<', '/'), $json);

            $i = 0;
            preg_match_all('/appid:\'(\d+)\',typeid:\'(\d+)\',key:\'([0-9A-Za-z]+)\'.*?,abstime:\'(\d+)\'.*?,uin:\'(\d+)\'.*?,html:\'(.*?)\'/i', $json, $arr);
            if (preg_match_all('/appid:\'(\d+)\',typeid:\'(\d+)\',key:\'([0-9A-Za-z]+)\'.*?,abstime:\'(\d+)\'.*?,uin:\'(\d+)\'.*?,html:\'(.*?)\'/i', $json, $arr)) {
                foreach ($arr[1] as $k => $row) {
                    if (preg_match('/data\-unikey="([0-9A-Za-z\.\-\_\/\:]+)" data\-curkey="([0-9A-Za-z\.\-\_\/\:]+)" data\-clicklog="like" href="javascript\:\;"><i class="fui\-icon icon\-op\-praise"><\/i>/i', $arr[6][$k], $match)) {
                        $appid = $arr[1][$k];
                        $typeid = $arr[2][$k];
                        $fid = $arr[3][$k];
                        $abstime = $arr[4][$k];
                        $touin = $arr[5][$k];
                        $unikey = urlencode($match[1]);
                        $curkey = urlencode($match[2]);
                        if (!in_array($touin, $forbid)){
                            $this->likePC($touin, $curkey, $unikey, 1, $appid, $typeid, $abstime, $fid);
                        }
                        ++$i;
                        if ($this->fail) break;
                    }
                }
            } else {
                $this->msg[] = $this->uin . '没有要赞的说说[PC]';
            }
        } else {
            $this->like();
            echo 1;
        }
    }


    /**
     * 获取自己说说
     */
    public function getMyList($uin = null, $num = 20)
    {
        if (empty($uin)) $uin = $this->uin;
        $url = 'https://mobile.qzone.qq.com/list?qzonetoken=' . $this->qzoneToken . '&g_tk=' . $this->gtk2 . '&res_attach=&format=json&list_type=shuoshuo&action=0&res_uin=' . $this->uin . '&count=' . $num;
        $json = get_curl($url, 0, 1, $this->cookie);
        $arr = json_decode($json, true);
        if (@array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = '获取说说列表成功！';
            return $arr['data']['vFeeds'];
        } else {
            $this->msg[] = '获取最新说说失败！原因:' . $arr['message'];
            return false;
        }
    }

    /**
     * 返回空间人气数量
     * @param $uin
     * @return bool|mixed
     */
    public function get_renqi($uin)
    {
        $url = 'https://h5.qzone.qq.com/proxy/domain/r.qzone.qq.com/cgi-bin/qzone_dynamic_v7.cgi?uin=' . $uin . '&param=848';
        $jsonp = get_curl($url);
        $arr = jsonp_decode($jsonp, true);
//        dump($arr);
        if (array_key_exists('code', $arr) && $arr['code'] == 0) {
            $todaycount = $arr['data']['app_848']['data']['modvisitcount'][0]['todaycount'];//今日访问
            $totalcount = $arr['data']['app_848']['data']['modvisitcount'][0]['totalcount'];//总访问
            return $todaycount;
        }
        //未开通
        return false;
    }

    /**
     * 访问空间[CP]
     * @param int $visit 要访问的QQ
     */
    public function visitCp($visit)
    {
        $url = 'https://h5.qzone.qq.com/webapp/json/friendSetting/reportSpaceVisitor?g_tk=' . $this->gtk2 . '&uin=' . $visit . '&visituin=' . $this->uin;
        $jsonp = get_curl($url, 0, 'https://user.qzone.qq.com/' . $visit, $this->cookie);
        $arr = jsonp_decode($jsonp, true);
//        dump($arr);
        if (@array_key_exists('ret', $arr) && $arr['ret'] == 0) {
            $this->msg[] = $this->uin . ' 访问 ' . $visit . ' 的空间成功[CP]';
        } elseif (@array_key_exists('retCode', $arr) && $arr['retCode'] == 0) {
            $this->msg[] = $this->uin . ' 访问 ' . $visit . ' 的空间失败[CP] 今日已访问';
        } elseif (@array_key_exists('ret', $arr) && $arr['ret'] == -3000) {
            $this->msg[] = $this->uin . ' 访问 ' . $visit . ' 的空间失败[CP] 登录状态已过期';
            $this->fail = true;
        } elseif (array_key_exists('msg', $arr)) {
            $this->msg[] = $this->uin . ' 访问 ' . $visit . ' 的空间失败[CP]！原因:' . $arr['msg'];
        } else {
            $this->msg[] = $this->uin . ' 访问 ' . $visit . ' 的空间失败[CP]！请10秒后再试';
        }
    }


    /**
     * 访问空间[Pc]
     * @param $visit string 要访问的QQ
     */
    public function visitPc($visit)
    {
        $url = "https://user.qzone.qq.com/proxy/domain/g.qzone.qq.com/fcg-bin/cgi_emotion_list.fcg?uin={$visit}&loginUin={$this->uin}&num=3&noflower=1&g_tk={$this->gtk}";
//        $url = "https://g.qzone.qq.com/fcg-bin/cgi_emotion_list.fcg?uin={$visit}&loginUin={$this->uin}&num=3&noflower=1&g_tk={$this->gtk}&qzonetoken={$this->qzoneToken}";

        $jsonp = get_curl($url, 0, 'https://user.qzone.qq.com/' . $visit, $this->cookie);
        $arr = jsonp_decode($jsonp, true);
//        dump($arr);
        if (@array_key_exists('code', $arr) && $arr['code'] == 0) {
            $this->msg[] = $this->uin . ' 访问 ' . $visit . ' 的空间成功[PC]';
        } elseif (@array_key_exists('code', $arr) && $arr['code'] == -3000) {
            $this->msg[] = $this->uin . ' 访问 ' . $visit . ' 的空间失败[PC] 登录状态已过期';
        } elseif (array_key_exists('message', $arr)) {
            $this->msg[] = $this->uin . ' 访问 ' . $visit . ' 的空间失败[PC]！原因:' . $arr['message'];
        } else {
            $this->msg[] = $this->uin . ' 访问 ' . $visit . ' 的空间失败[PC]！请10秒后再试';
        }
    }

    /**
     * 获取说说
     * @param $uin string 要获取的QQ
     */
    public function getShuo($uin)
    {
        $ret = get_curl("https://user.qzone.qq.com/proxy/domain/taotao.qq.com/cgi-bin/emotion_cgi_msglist_v6?uin={$uin}&ftype=0&sort=0&pos=0&num=20&replynum=100&callback=_preloadCallback&code_version=1&format=jsonp&need_private_comment=1&qzonetoken={$this->getQzoneToken()}&g_tk={$this->gtk}", 0, 0, $this->cookie);
        $json = jsonp_decode($ret, true);
        return $json;
    }

    public function updateImage(){
        $url = "https://mobile.qzone.qq.com/up/cgi-bin/upload/cgi_upload_pic_v2?g_tk={$this->gtk2}&qzonetoken={$this->qzoneToken}";
        $post = "picture=iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAGSUlEQVRYR62XWWyUVRTHf3dm2s50SpmKpQi0rIUpS1irUZYHFY3BKBBN1BhIIBIChIgY9UFf1MSohGigiZggEZcHE14UBQQE7AIUECrFQinF1LK0hZZO22ln%2Fcz9trmzlGLivMz33e8u%2F3PO%2F3%2FOuQL1d7TGjzO6AcRSYITxSYCmThLGiz1mvltzralxa9xe3wWO%2FUSp4OnyemtaYnVl5Vo0Pgfc%2F%2BlgHYgCQsv0rIIWEQRv8ET5jsTKysrVaOyy7VQ3GdILSZubW1hWZ%2FhmAdbERp6aVyE4enQ8Tte1%2F83quBkeeZCQAIT5L8ctQNa%2FViY4XlmBYL39UY1lpjhncnFcg0gMYnHjMIcTHA6IacaYfM7OSuGTDm634HhVC1CcoNm9SJdCOGltKAw4GD88jycK8pnm9VCck81wl4vuaIyb4Qh72%2B7ye0c3uJwKCH2vdgmgF%2FDaREq1OhPJpJWRKIRjzC8s4PWSh1hWWIDX6UgSlfVyNRjCX3WRqPSG06WoSAwIjlcHgGEZZZXJ3fLwgYju6venjOO9CWMyHqoOBmNxplQ3cD0QNEJh7%2BsICI5VdwP5NgltjQ8ip4EwOJ3sneNnRWHBkIfLCaG4xqyaS1zu6jO5YBFSSAA1CQCDalpANAb9YT2OP5RP58WUw8%2F19hOMxljgy0sDNRCPM6v6Mo1dQciRHrATnAJgMHdH4wbRcnJYOsLHmuIilj%2FoSzrk7aYbfHKlVWf9suJCvp8xHo%2FCh85IDH%2FVZTp6BswQ2HnQBKCJRAgsEspYh6MQjbN8bJEe6znDctOsO3A7wDM1F0E4DLkFw3xaXsqb40fac0%2FcDfJYzRWQqtGB2flAAjjRjUZ%2Bghhm8pAsj2lsnT6RLSWjBo31ztbbrKttBK%2FHyAHd%2Fbw2rZgvp4%2B113zc3ME751vAnW3MSYQ6IPjtpMkBRf8ysYSjbJs5mc3FRfckWns4ytwTl7neJrcR4HVzcsFUHvElvDW7pom6WwHwZCvZUOeBBUDk2zKU6EIR1k4cy07%2FuPtieWMwxEdNbfRFYqydUMiTDySI%2BGN7gOerr0KWywiRrjK7UgYER04lPCA%2FyjiFo%2ByaXcpzhT72tnVxoTvIwsJ8Xhp5f7KzUEtHlh5rpLmzDzw56WVdQwKoNQCoKohDnsuJSwju9g5AKAruHA49WpZk3VDuebWule8aOyA3WyVeomIiARyWAEwVJFyjsx9NM9gtn4NhRJ6H2kVlzB%2FmGepsNjfc4rP6m%2BDOMopTaoIziCgBnDYAWPJTQZhVbnpBHtkOB%2Bf%2B6cSd56ZqcRnz8sy%2BJQOUNRdu8NVfbeDJMgqQDKv%2BUwudzoOA4NCZFBUoTURfmFcmjeLrmSV6OLa33GHTqSZyc90cXFDKQoXp1hFH7%2FTx%2BOFGyHEZxFOza%2FqzBHDWyAMqQqnVvjD%2BEfk0LPIn2biqvpU9da04fbnsmVPCy6N9akPGyrobfHOpHbw5StxN61PDoIfg17MKB8yJERl%2FqF3kp3x4cvaraOlk4%2Bm%2F9STlyffQscRvl%2BHGvjBlR5qIy0Yky5neuCa1eno4JIA%2FTA8o7VJfiNVTR7NLyWYS2vVQlCUnm2no6tcBzC3Kp3bBRJzm0ocrr3H6Rg94ZcIZ1GpVjgHBwXOmDBPW52a5aFjsp0SSyPzta%2B9hXV0r13tCRkULRvhg9hjeLS3UZ7xwppW9VzuNbKf3glbrrrTn9phNSAngfACNYcYCI%2FYrp0jiGbm8uT%2FMh40d7G6%2BDVIV8gC9FYuxY95Yni3KY9W5mxxvuWuwXhYbaX26uxNKSIALCA6c70UTXtvUUIwlo31sKBlBVWeQL1o66Q1Iq12GpOTG%2BgHgy3bqPWdPTxjcLiNnyNxh0zJDf6m2fIIBwf66VmCMrVHpvqhmJB%2B9h3MacrJLqOk%2B6VlJVukNSbhUjafepjJ22HQI9v%2B5E421tgzlRtLVal9vhUeVqvWc9C2D61PjbrnaCNG3gl8uTAKabA4kT1ArVzqxklq4Idxt76uoIx6fYVB0X%2F16BBXJIO7jepXmEZvdyYRLU4T%2BeQsrJm1LtL4%2FX9yEpm3TK0fa3WCoy6diVeZYqxdYWeHeYsXErcoqE%2FBP9bPAsdG8nnuTk0nKrSjjBSZly%2BQuOwjiICK%2BneWTz1oR%2BRe1qoV6ChQlaQAAAABJRU5ErkJggg%3D%3D&base64=1&hd_height=32&hd_width=32&hd_quality=90&output_type=json&preupload=1&charset=utf-8&output_charset=utf-8&logintype=sid&Exif_CameraMaker=&Exif_CameraModel=&Exif_Time=&uin=1543797310";
        $ret = get_curl($url,0,0,$this->cookie);
    }
}