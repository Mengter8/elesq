<?php
namespace qq;
use app\model\Qq;
use think\facade\Request;

class cf extends login
{
    public $arr = [
        '318' => '广东电信一区',
        '327' => '广东电信一区',
        '338' => '广东电信二区',
        '339' => '广东电信四区',
        '319' => '北京网通一区',
        '321' => '北京网通二区',
        '334' => '北京网通三区',
        '335' => '北京网通四区',
        '322' => '辽宁网通一区',
        '323' => '辽宁网通二区',
        '336' => '辽宁网通三区',
        '344' => '江苏电信一区',
        '357' => '江苏电信二区',
        '328' => '湖北电信一区',
        '329' => '湖北电信二区',
        '341' => '湖南电信一区',
        '340' => '湖南电信二区',
        '320' => '上海电信一区',
        '326' => '上海电信二区',
        '324' => '福建电信一区',
        '325' => '浙江电信一区',
        '349' => '浙江电信二区',
        '330' => '陕西电信一区',
        '342' => '南方电信大区',
        '343' => '北方网通大区',
        '348' => '云南电信一区',
        '345' => '河南网通一区',
        '359' => '河南网通二区',
        '346' => '山东网通一区',
        '358' => '山东网通二区',
        '347' => '安徽电信一区',
        '351' => '吉林网通一区',
        '352' => '江西电信一区',
        '353' => '广西电信一区',
        '354' => '山西网通一区',
        '355' => '河北网通一区',
        '333' => '四川电信一区',
        '356' => '四川电信二区',
        '332' => '重庆电信一区',
        '360' => '移动专区',
        '350' => '黑龙江网通一区',
        '361' => '教育网专区'
    ];

    public function getZhanji($Area)
    {
        $url = 'https://apps.game.qq.com//cf/a20131114fhcx_mobile/getUserAct.php?action=getuseract&iUin=' . $this->uin . '&sArea=' . $Area;
        $ret = get_curl($url, 0, 'https://vip.qq.com/', $this->cookie);
        echo($ret);
    }

    public function getCFName()
    {
        $arr = $this->arr;
        $this->res = (new Qq)->getByUin('552492765');
        $this->login($this->res['qq'], $this->res['sid'], $this->res['skey'], $this->res['pskey'], $this->res['superkey']);

        //http://www.360.cn/n/10388.html

        $i = 0;
        foreach ($this->arr as $v => $k) {
            if ($i >= 20) {
                $urlsleep[]['url'] = 'https://apps.game.qq.com/comm-cgi-bin/content_admin/activity_center/query_role.cgi?game=cf&area=' . $v;
            } else {
                $urls[]['url'] = 'https://apps.game.qq.com/comm-cgi-bin/content_admin/activity_center/query_role.cgi?game=cf&area=' . $v;
            }
            $i++;
        }
        $ret = rolling_curl($urls, 0, 'https://apps.game.qq.com', $this->cookie_pc);
        foreach ($ret as $v => $k) {
            if (getSubstr($k, 'szNick_name=', '&')) {
                $k = @getSubstr($k, 'szNick_name=', '&');
                $k = urldecode($k);
                //array_slice($arr,$v,1)
                echo array_slice($arr, $v, 1)[0];
                echo $k . '<BR>';
            }
        }
        //sleep(1);
        usleep(500000);//0.5秒
        $retsleep = rolling_curl($urlsleep, 0, 'https://apps.game.qq.com', $this->cookie_pc);
        foreach ($retsleep as $v => $k) {
            if (getSubstr($k, 'szNick_name=', '&')) {
                $k = @getSubstr($k, 'szNick_name=', '&');
                $k = urldecode($k);
                //array_slice($arr,$v,1);
                echo array_slice($arr, $v, 1)[0];
                echo $k . '<BR>';
            }
        }
    }

    public function test()
    {
        $this->res = (new Qq)->getByUin('1543797310');
        $area = "356";//CF大区
        $this->login($this->res['qq'], $this->res['sid'], $this->res['skey'], $this->res['pskey'], $this->res['superkey']);
        //$cookie = "uin=o1543797310; skey=@MpET74b53;";
        $this->zjfl();


        $url = "http://apps.game.qq.com/cf/a20170210information/getCfcashInfo.php?action=getMycashInfo&rd=0.5209099117427276";
        //装备领取查询 格式[道具名称,道具价格,领取时间]



        $url = "http://comm.aci.game.qq.com/main?game=cf&callback=1557879913021930&sCloudApiName=ams.gameattr.role&sServiceDepartment=group_a&area={$area}";
        $url = "http://apps.game.qq.com/comm-cgi-bin/content_admin/activity_center/query_role.cgi?game=cf&callback=14649452832977635&area={$area}";
        //防止失效去验证两次
        $res = get_curl($url, 0, $url, $this->cookie_pc);
        $res = mb_convert_encoding($res, "UTF-8", "GBK");
        echo $res;
    }

    /**
     * 3.13宅家福利
     * 大区ID取角色名称->
     * 绑定大区
     * 领取礼包
     */
    public function zjfl(){
        //基础信息
        $area = Request::get('area');//大区ID
        $areaName = urlencode(urlencode($this->arr[$area])); //河北网通大区
        $uin = Request::get('uin');
        $skey = Request::get('skey');
        $cookie = "uin=o{$uin}; skey={$skey}";
        $gtk = getGTK($skey);
        //查询角色
        $url = "http://cf.aci.game.qq.com/main?game=cf&area={$area}&callback=158585265684517546&sCloudApiName=ams.gameattr.role&iAmsActivityId=290169&sServiceDepartment=group_f";
        $res = get_curl($url,0,$url,$cookie);
        preg_match('/checkparam:\'(.*?)\',/',$res,$match); //cf|yes|1543797310|355|1543797310*||||58548%2F48*|||1585851856
        preg_match('/\*\|\|\|\|(.*)\*\|\|\|/',$res,$nickName); //角色名
        preg_match('/md5str:\'(.*?)\',/',$res,$md5str); //md5str
        dump($res);
//        exit();
        //绑定大区
        $check = $match[1];
        $check = str_replace('_','%5F',$check);
        $check = urlencode($check);
        $check = urlencode($check);
        $check = str_replace('%252A','*',$check);
        $nickName = urlencode(urlencode(urlencode(urldecode($nickName[1]))));
        $url = "http://cf.ams.game.qq.com/ams/ame/amesvr?ameVersion=0.3&sServiceType=cf&iActivityId=290169&sServiceDepartment=group_f&sSDID=5b6cdc747e2c2f3a4cf95b62cd9e4185&sMiloTag=AMS-MILO-290169-647242-o1543797310-1585852743670-iOFWNc&isXhrPost=true";
        $data = "sServiceType=cf&user_area={$area}&user_roleId={$uin}&user_roleName={$nickName}&user_areaName={$areaName}&user_roleLevel=0&user_checkparam={$check}&user_md5str={$md5str[1]}&user_sex=undefined&user_platId=&user_partition={$area}&iActivityId=290169&iFlowId=647242&g_tk={$gtk}&e_code=0&g_code=0&eas_url=http%3A%2F%2Fcf.qq.com%2Fcp%2Fa20200228cfmainbp%2F&eas_refer=http%3A%2F%2Fcf.qq.com%2Fcp%2Fa20200228cfmainbp%2F%3Freqid%3Dff6f7808-edda-403a-8b13-d1028f36ce18%26version%3D22&xhr=1&sServiceDepartment=group_f&xhrPostKey=xhr_158585768476481";
        dump(get_curl($url, $data, $url, $cookie));
        //提交领取
        $url = "https://cf.ams.game.qq.com/ams/ame/amesvr?ameVersion=0.3&sServiceType=cf&iActivityId=290169&sServiceDepartment=group_f&sSDID=5b6cdc747e2c2f3a4cf95b62cd9e4185&sMiloTag=AMS-MILO-290169-647278-o0017341718-1584149678772-8keJeV&isXhrPost=true";
        $postId = "647279&dmid=5·648090&dmid=1·647277·647278&dmid=1·647278&dmid=2·647278&dmid=3·647278&dmid=4·647294&dmid=1·647278&dmid=6·647278&dmid=7·647278&dmid=8·647278&dmid=9·647295&dmid=2·647278&dmid=11·647278&dmid=12·647278&dmid=13·647278&dmid=14·647278&dmid=15·647278&dmid=16·647278&dmid=17·647278&dmid=18·647278&dmid=19·647278&dmid=20·647278&dmid=21·647278&dmid=22·647278&dmid=23·647278&dmid=24·647278&dmid=25·647278&dmid=26·647278&dmid=27·647278&dmid=28·647278&dmid=29·647278&dmid=30·647278&dmid=31·647278&dmid=32·647278&dmid=33·647278&dmid=34·647278&dmid=35·647278&dmid=36·647278&dmid=37·647278&dmid=38·647278&dmid=39·647260&sEncrypt=befoppwcve·647291·647252·647253·647254·647255·647256·647257·647271·647246&dmid=1·647246&dmid=2·647246&dmid=3·647246&dmid=4·647246&dmid=5·647246&dmid=6·647246&dmid=7·647246&dmid=8·647247·647261·647262·647263·647264·647265·647266·647267·647268";
        $arr = explode("·",$postId);
        foreach ($arr as $iFlowId){
            $data = "gameId=&sArea=&iSex=&sRoleId=&iGender=&xhr=1&sServiceType=cf&objCustomMsg=&areaname=&roleid=&rolelevel=&rolename=&areaid=&iActivityId=290169&iFlowId={$iFlowId}&g_tk={$gtk}&e_code=0&g_code=0&eas_url=http%3A%2F%2Fcf.qq.com%2Fcp%2Fa20200228cfmainbp%2F&eas_refer=http%3A%2F%2Fnoreferrer%2F%3Freqid%3D237820b3-fc53-45b2-8b0a-3cf8d4eab7ec%26version%3D22&sServiceDepartment=group_f&xhrPostKey=xhr_158414967877238";
            $res = get_curl($url, $data, $url, $cookie);
            $json = json_decode($res,true);
            dump($json);
        }
    }

    /**
     * 3月21日15:30-准点在线
     */
    public function zszx(){
        $url = "https://cf.ams.game.qq.com/ams/ame/amesvr?ameVersion=0.3&sServiceType=cf&iActivityId=292379&sServiceDepartment=group_f&sSDID=7474e2a73b721daabd35ec59df8df43d&sMiloTag=AMS-MILO-292379-650219-o0017341718-1584751768144-MkCFDu&isXhrPost=true";
        $data = "sServiceType=cf&user_area=318&user_roleId=17341718&user_roleName=%2525E4%2525B8%2525BFFly%25255E%2525E6%2525AD%2525BB%2525E7%2525A5%25259E&user_areaName=%25E5%25B9%25BF%25E4%25B8%259C%25E7%2594%25B5%25E4%25BF%25A1%25E4%25B8%2580%25E5%258C%25BA&user_roleLevel=12&user_checkparam=cf%257Cyes%257C17341718%257C318%257C17341718*%257C%257C%257C%257C%2525E4%2525B8%2525BFFly%25255E%2525E6%2525AD%2525BB%2525E7%2525A5%25259E*%257C%257C%257C1584751764&user_md5str=13E0C05C07A8E140E4CD5FB1F203C124&user_sex=undefined&user_platId=&user_partition=318&iActivityId=292379&iFlowId=650219&g_tk=1903828754&e_code=0&g_code=0&eas_url=http%3A%2F%2Fcf.qq.com%2Fcp%2Fa20200309punctuality%2F&eas_refer=http%3A%2F%2Fcf.qq.com%2Fcp%2Fa20200309punctuality%2F%3Freqid%3Dcaf3a808-612b-4f08-9249-704f982f9b36%26version%3D22&xhr=1&sServiceDepartment=group_f&xhrPostKey=xhr_158475176814488";

        $url = "https://cf.ams.game.qq.com/ams/ame/amesvr?ameVersion=0.3&sServiceType=cf&iActivityId=292379&sServiceDepartment=group_f&sSDID=7474e2a73b721daabd35ec59df8df43d&sMiloTag=AMS-MILO-292379-650237-o0017341718-1584751981312-SB70DI&isXhrPost=true";
        $data = "gameId=&sArea=&iSex=&sRoleId=&iGender=&xhr=1&sServiceType=cf&objCustomMsg=&areaname=&roleid=&rolelevel=&rolename=&areaid=&iActivityId=292379&iFlowId=650237&g_tk=1903828754&e_code=0&g_code=0&eas_url=http%3A%2F%2Fcf.qq.com%2Fcp%2Fa20200309punctuality%2F&eas_refer=http%3A%2F%2Fcf.qq.com%2Fcp%2Fa20200309punctuality%2F%3Freqid%3D4ef9aca6-cd46-426c-892f-01cc76a1092b%26version%3D22&sServiceDepartment=group_f&xhrPostKey=xhr_158475198131362";
        $postId = "650242&sEncrypt=cfanlkbxag·650236&sEncrypt=cfanlkbxag·650227&dmid=111·652041&dmid=3·652041&dmid=4·652041&dmid=1·652041&dmid=2·652041&dmid=5·652041&dmid=6·652041&dmid=7·652041&dmid=8·652041&dmid=9·652041&dmid=10·652084&dmid=1·652086&dmid=1·650262&dmid=1·652087&dmid=1·650275&dmid=1·650250&dmid=1·650570&dmid=6·650570&dmid=7·650570&dmid=8·650570&dmid=1·650570&dmid=2·650570&dmid=3·650570&dmid=4·650570&dmid=5·650570&dmid=9·650570&dmid=10·650227&dmid=111·650597&dmid=1·650626&dmid=1·650250·650267·650275·650276·650278·650277·650262·651191&dmid=2·650238&dmid=1·650238&dmid=2·650238&dmid=3·650238&dmid=4·650627&dmid=111·650229·650230·650231·650232·650233·650234·650263·650223&dmid=1·650223&dmid=2·650223&dmid=3·650223&dmid=4·650223&dmid=5·650223&dmid=6·650223&dmid=7·650223&dmid=8650224·650258·650260·650261&dmid=2·650261&dmid=3·650261&dmid=5650243·650244·650245·650246·650247·650248·650249";
    }

    /**
     * 腾讯游戏道聚城
     */
    public function djc(){
        $url = "http://apps.game.qq.com/ams/ame/ame.php?ameVersion=0.3&sServiceType=dj&iActivityId=11117&sServiceDepartment=djc&set_info=djc";
        $data = "iActivityId=96939&iFlowId=96910&g_tk={$this->gtk}&e_code=0&g_code=0&eas_url=http%253A%252F%252Fdaoju.qq.com%252Fmall%252Fjudou2.0%252Fcf.shtml&eas_refer=&sServiceDepartment=djc&sServiceType=dj";
        $res = get_curl($url, $data, 0, $this->cookie_pc);
        echo $res;
        $data = "iActivityId=11117&iFlowId=96910&g_tk={$this->gtk}&e_code=0&g_code=0&eas_url=http%253A%252F%252Fdaoju.qq.com%252Fmall%252Fjudou2.0%252Fcf.shtml&eas_refer=&sServiceDepartment=djc&sServiceType=dj";
        $res = get_curl($url, $data, 0, $this->cookie_pc);
        echo $res;
        //腾讯游戏道聚城
    }

    /**
     * 封号查询
     */
    public function fenghao()
    {
        /**
         * $gameId说明
         * 3 = CF
         * 4 = QQ飞车
         * 23 = LOL
         */
        $gameId = 3;

        $this->res = (new Qq)->getByUin('1543797310');
        $this->login($this->res['qq'], $this->res['sid'], $this->res['skey'], $this->res['pskey'], $this->res['superkey']);

        $url = "https://gamesafe.qq.com/api/proxy/punish_query?need_appeal=2&query_type=3&limit=10";
        $urlAll = "https://gamesafe.qq.com/api/proxy/punish_query?need_appeal=2&query_type=3&limit=10&end_date=1568750484";
        //查询所有
        $url = "http://gamesafe.qq.com/json.php?mod=Interface&act=getAllPunishData&game_id={$gameId}";

        $res = get_curl($url, 0, $url, $this->cookie_pc);
        //去除BOM
        if (0 === strpos(bin2hex($res), 'efbbbf')) {
            $res = substr($res, 3);
        }
        $json = json_decode($res, true);
        dump($json);
    }
}