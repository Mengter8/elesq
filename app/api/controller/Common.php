<?php
declare (strict_types=1);

namespace app\api\controller;

use app\model\Qq;
use think\Exception;
use think\exception\ValidateException;
use think\facade\Request;

class Common
{
    /**
     * 获取QQ昵称
     */
    public function getNickName()
    {
        $uin = Request::post('uin');
        //自动验证
        try {
            validate(\app\validate\Qq::class)->check([
                'qq' => $uin,
            ]);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return resultJson(0, $e->getError());
        }

        $nickName = get_qqnick($uin);

        if (!empty($nickName)) {
            return resultJson(1, '获取成功!', $nickName);
        } else {
            return resultJson(0, '获取失败!');
        }
    }

    /**
     * 获取p值
     */
    public function getPCode(){
        $uin = Request::post('uin');
        $pwd = Request::post('pwd');
        $vcode = Request::post('vcode');
        if (!isset($uin,$pwd,$vcode))
        {
            return resultJson(0, 'error');
        }
        $uin = escapeshellcmd($uin);
        $pwd = escapeshellcmd($pwd);
        $vcode = escapeshellcmd($vcode);

        $p = exec("/www/server/nvm/versions/node/v12.16.1/bin/node /www/wwwroot/server/server.js {$uin} {$pwd} {$vcode}",$output,$return_var);
        if ($return_var == 0){
            return resultJson(1, '获取成功！', $p);
        } else {
            return resultJson(0, '获取失败!');
        }
    }

    /**
     * 短网址 t.cn url.cn
     */
    public function getShortUrl()
    {
        $url = Request::post('url');
        $type = Request::post('type','t.cn');
        if ($type == 't.cn'){
            $ret = get_curl("https://api.bejson.com/Bejson/Api/ShortUrl/getShortUrl","url={$url}");
            if ($ret && $json = json_decode($ret, true)){
                if (array_key_exists('code', $json) && $json['code'] == 200) {
                    return resultJson(1, '生成成功！',['long_url'=>$url,'short_url'=>$json['data']['short_url'],'type'=>$type]);
                }
            }
        } elseif ($type == 'url.cn'){
            $ret = get_curl("https://vip.video.qq.com/fcgi-bin/comm_cgi?name=short_url&need_short_url=1&url=https://c.pc.qq.com/middleb.html?pfurl={$url}");
            if ($ret && $json = json_decode($ret, true)) {

                if (array_key_exists('ret', $json) && $json['ret'] == 0) {
                    return resultJson(1, '生成成功！',['long_url'=>$url,'short_url'=>$json['short_url'],'type'=>$type]);
                }
            }
        } else {
            return resultJson(0,'$type error');
        }
        return resultJson(0, '生成失败！');

    }
    public function qrLogin()
    {
        $image = request::post('image');
        $image = str_replace(' ', '+', $image);
        $base64_path = putRuntimeCache("temp", time().".png", base64_decode($image));
        $image_path = root_path() . 'extend' . DIRECTORY_SEPARATOR . 'qr.jpg';

        ///新建画布
        $newSize = 660;
        $thumb = imagecreatetruecolor($newSize, $newSize);

        try {
            $qrImage = imagecreatefrompng($base64_path);
        } catch (Exception $e){
            return resultJson(0,'error',$e->getMessage());
        }

        $width = imagesx($qrImage);
        $height = imagesy($qrImage);
        imagecopyresized($thumb, $qrImage, 0, 0, 0, 0, $newSize, $newSize, $width, $height);
        //合成图片
        $width = imagesx($thumb);
        $height = imagesy($thumb);
        $image_1 = imagecreatefromjpeg($image_path);
        imagecopymerge($image_1, $thumb, 210, 730, 0, 0, $width, $height, 100);

        //删除临时文件
        unlink($base64_path);

        // 输出合成图片
        //return response()->contentType("image/jpg");
        $put_path = putRuntimeCache("temp", time().".png", base64_decode($image));
        imagejpeg($image_1,$put_path);
        $cache = file_get_contents($put_path);

        $res = (new \app\model\Qq)->getByUin('1543797310');
        $do = new \qq\qzone($res['uin'],$res['skey'],$res['pskey']);

        //上传
        $url = "https://mobile.qzone.qq.com/up/cgi-bin/upload/cgi_upload_pic_v2?g_tk={$do->gtk2}&qzonetoken={$do->qzoneToken}";
        $cache = urlencode(base64_encode($cache));

        $post = "picture={$cache}&base64=1&hd_height=32&hd_width=32&hd_quality=96&output_type=json&preupload=1&charset=utf-8&output_charset=utf-8&logintype=sid&Exif_CameraMaker=&Exif_CameraModel=&Exif_Time=&uin=1543797310";
        //其中hd_quality参数 普通图片90 高清图片96
        $ret = get_curl($url,$post,0,$do->cookie);
        $json = jsonp_decode($ret,true);
//        dump($json);
        if (array_key_exists('ret', $json) && $json['ret'] == -100){
            (new \app\model\Qq())->setStatus(1543797310,0);
            return resultJson(0,'快捷扫码获取失败',$json['msg']);
        }
        //保存
//        $url ="https://mobile.qzone.qq.com/up/cgi-bin/upload/cgi_upload_pic_v2?g_tk={$do->gtk2}&qzonetoken={$do->qzoneToken}";
        $post = "output_type=json&preupload=2&md5={$json['filemd5']}&filelen=1666%7C1666&batchid=1591764723010000&currnum=0&uploadNum=2&uploadtime=1591764723&uploadtype=2&upload_hd=1&albumtype=7&big_style=1&op_src=15001&charset=utf-8&output_charset=utf-8&uin=1543797310&mobile_dc=actiontype%253D2%2526subactiontype%253D1%2526reserves%253D1%2526page_type%253D2%2526app_id%253D7003&albumid=V10gRnPm4b5HNc&desc=&platformid=52&platformsubid=11";
        $jsonp = get_curl($url,$post,0,$do->cookie);

        $json = jsonp_decode($jsonp,true);
        return resultJson(1,'获取成功',$json['picinfo']['url']);
    }
    /**
     * 获取业务图标HTML
     */
    public function getPayInfoHtml()
    {
        $uin = Request::param('uin');
        $res = (new Qq)->getByUin('1543797310');
        $cookie = 'uin=o0' . $res['uin'] . '; skey=' . $res['skey'] . '; p_skey=' . $res['pskey'] . ';';
        //备用https://mc.vip.qq.com/privilegelist/index
        $ret = get_curl("https://mc.vip.qq.com/privilegelist/other?friend={$uin}", 0, 'https://vip.qq.com/', $cookie);
        preg_match('/<!--wnsdiff-friendServices-->[\s\S](.*)<!--wnsdiff-friendServices-end-->/', $ret, $match);

        //$ret = get_curl('https://cgi.vip.qq.com/profile/userInfo?data=nick_name&fUin='.Request::param('qq').'&g_tk='. $gtk,0, 'https://h5.vip.qq.com/p/mc/privilegelist/index', $cookie);
        //$json = json_decode($ret,true);
        if ($match) {
            //return json(['code'=>1,'data'=>['prv'=>$match[0],'nick_name'=>$json['info'][1]['nick_name'],'uin'=>$json['info'][1]['uin']]]);
            return json(['code' => 1, 'data' => $match[0]]);
        } else {
            return json(['code' => 0, 'msg' => '获取失败']);
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
        foreach ($arr['service'] as $vo) {
            if ($vo['start_time'] > $vo['end_time'] || $vo['end_time'] > date("Y-m-d H:i:s")) {
                dump($vo['service_name']);
            }
        }
        dump($arr);
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
}