<?php
declare (strict_types=1);

namespace app\index\controller;

use app\model\Qq;
use qq\sign;
use qq\qzone;
use qq\vipSign;
use think\facade\Request;

class Debug
{
//    protected $middleware = ['app\api\middleware\CheckLoginUser'];
    /**
     * @var array|mixed|null
     */
    private $uin;
    /**
     * @var array|bool
     */
    private $res;

    public function __construct()
    {
        $this->initialize();
    }
    protected function initialize()
    {
        $this->uin = Request::post('uin');
        if (Request::has('uin')) {
            $this->res = (new Qq())->findMyUin($this->uin);
            if (!$this->res) {
                abort(401, '请勿恶意操作');
            }
        } else {
            abort(403, '请勿恶意操作');
        }
    }
    /**
     * 说说赞
     */
    public function zan()
    {
        $do = new qzone($this->uin, $this->res['skey'], $this->res['pskey'],$this->res['superkey']);
        $res = (new \app\model\Task())->findTypeUin('zan',$this->uin);
        $dataset = $res['dataset'];
        $mode = !empty($dataset['mode']) ? $dataset['mode'] : 0;
        $list = !empty($dataset['qqlist']) ? explode(',', str_replace(['，', '.'], ',', $dataset['qqlist'])) : [];
        $do->like($mode,$list);
        foreach ($do->msg as $v) {
            echo "{$v}<br>";
        }
        (new \app\model\Task())->setTaskTime($this->uin,'zan');

        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
        return "<b>运行成功:" . date("Y-m-d H:i:s") . "</b>";
    }
    /**
     * 群签到
     */
    public function qunqd()
    {

        $do = new sign($this->uin, $this->res['skey'], $this->res['pskey']);
        $res = (new \app\model\Task())->findTypeUin('qunqd',$this->uin);
        if (!$res || $res['next_time'] >= time() ){
            echo '今日群签到已完成！';
        } else {
            $dataset = $res['dataset'];
            $data = qunFindId($dataset['tid']);
            $content = !empty($dataset['content']) ? $dataset['content'] : $data['text'];
            $category_id = isset($data['category_id']) ? $data['category_id'] : '';
            $pic_id = isset($data['pic_id']) ? $data['pic_id'] : '';
            $template_id = isset($data['template_id'])  ? $data['template_id'] : '';

            $mode = !empty($dataset['mode']) ? $dataset['mode'] : 0;
            $list = !empty($dataset['qunlist']) ? explode(',', str_replace(['，', '.'], ',', $dataset['qunlist'])) : [];

            $do->qunqd($content,$dataset['site'],$category_id,$pic_id,$template_id,$mode,$list);
            foreach ($do->msg as $v) {
                echo "{$v}<br>";
            }
            (new \app\model\Task())->setTaskTime($this->uin,'qunqd');
        }
    }
    /**
     * vip签到
     */
    public function vip()
    {
        $do = new vipSign($this->uin, $this->res['skey'], $this->res['pskey']);
        $do->vip();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }

    /**
     * 大会员签到
     */
    public function bigVip()
    {
        $do = new vipSign($this->uin, $this->res['skey'], $this->res['pskey']);
        $do->bigVip();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }
    /**
     * 黄钻签到
     */
    public function qzoneVip()
    {
        $do = new vipSign($this->uin, $this->res['skey'], $this->res['pskey']);

        $do->qzoneVip();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }

    /**
     * qq运动
     */
    public function qqyundong()
    {
        $uin = Request::get('uin');
        $res = (new Qq)->getByUin($uin);
        $skey = $res['skey'];
        $pskey = $res['pskey'];
        $superkey = $res['superkey'];
        $cookie = "uin=o{$uin}; skey={$skey}; pskey={$pskey};superuin=o{$uin}; superkey={$superkey};supertoken=;";
        $steps = Request::get('steps');
        $url = 'https://ssl.ptlogin2.qq.com/pt_open_login?openlogin_data=appid%3D716027609%26daid%3D381%26pt_skey_valid%3D1%26style%3D35%26s_url%3Dhttp%253A%252F%252Fconnect.qq.com%26refer_cgi%3Dm_authorize%26ucheck%3D1%26fall_to_wv%3D1%26status_os%3D9.3.2%26redirect_uri%3Dauth%253A%252F%252Fwww.qq.com%26client_id%3D1101326786%26response_type%3Dtoken%26scope%3Dall%26sdkp%3Di%26sdkv%3D2.9%26state%3Dtest%26status_machine%3DiPhone8%252C1%26switch%3D1%26pt_flex%3D1&auth_token=0&pt_vcode_v1=0&pt_verifysession_v1=&verifycode=&u=' . $uin . '&pt_randsalt=0&ptlang=2052&low_login_enable=0&u1=http%3A%2F%2Fconnect.qq.com&from_ui=1&fp=loginerroralert&device=2&aid=716027609&daid=381&pt_3rd_aid=1101774620&ptredirect=1&h=1&g=1&pt_uistyle=35&regmaster=&';

        $res = get_curl($url, 0, 1, $cookie);
        $openid = getSubstr($res, 'openid=', '&');
        $access_token = getSubstr($res, 'access_token=', '&');
        $ret = get_curl("http://api.xiaok1.cn/yundong/wxqq.php", "type=qq&openid={$openid}&access_token={$access_token}&steps={$steps}");
        //$ret = get_curl("http://step.mtyqx.cn/api/sport.php", "openid={$openid}&access_token={$access_token}&appid=110132678&steps={$steps}");

        return json([
            'code' => 1,
            'data' => $res,
            'return' => [['openid' => $openid, 'access_token' => $access_token], $ret],
            'message' => '修改成功，请查看',
        ]);
    }

    /**
     * 蓝钻VIP月礼包
     */
    public function gameVip()
    {
        $do = new vipSign($this->uin, $this->res['skey'], $this->res['pskey']);

        $do->gameVip();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }
    /**
     * 蓝钻VIP月礼包
     */
    public function videoVip()
    {
        $do = new vipSign($this->uin, $this->res['skey'], $this->res['pskey']);

        $do->videoVip();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }

    /**
     * QQ打卡
     */
    public function checkin()
    {
        $do = new sign($this->uin, $this->res['skey'], $this->res['pskey']);
        $do->checkin();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }
    /**
     * 花藤
     */
    public function flower()
    {
        $do = new sign($this->uin, $this->res['skey'], $this->res['pskey']);
        $do->flower();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }
    /**
     * 花藤
     */
    public function weiShi()
    {
        $do = new sign($this->uin, $this->res['skey'], $this->res['pskey']);
        $do->weiShi();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }

    /**
     * 手机加速六小时
     */
    public function mobileSpeed()
    {
        $do = new sign($this->uin, $this->res['skey'], $this->res['pskey']);
        $do->mobileSpeed();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }

    /**
     * 手游加速0.2
     */
    public function gameSpeed()
    {
        $do = new sign($this->uin, $this->res['skey'], $this->res['pskey']);
        $do->gameSpeed();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }

    /**
     * 运动加速
     */
    public function yunDong()
    {
        $do = new sign($this->uin, $this->res['skey'], $this->res['pskey']);
        $do->yunDong();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }
    public function gameSign()
    {
        $do = new sign($this->uin, $this->res['skey'], $this->res['pskey']);
        $do->gameSign();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }
    public function book()
    {
        $do = new sign($this->uin, $this->res['skey'], $this->res['pskey']);
        $do->book();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }
    public function qiPao()
    {
        $do = new sign($this->uin, $this->res['skey'], $this->res['pskey'], $this->res['superkey']);
        $do->qipao();
        foreach ($do->msg as $v) {
            echo $v . "<br>";
        }
        if ($do->fail == true) {
            Qq::update(['status' => 0], ['uin' => $this->uin]);
        }
    }
    /**
     * qq空间访问
     */
    public function visitqzone()
    {
        $uin = Request::get('uin');
        $res = (new Qq)->getByUin($uin);
        $do = new qzone($uin, $res['skey'], $res['pskey']);
        $renqi = $do->get_renqi($uin);
        if (!$renqi) {
            return "请开启空间访问权限";
        }
        $res = Qq::whereStatus(1)->select();
        echo "当前{$uin}人气为{$renqi}<br>";
        foreach ($res as $qq) {
            $do = new qzone($qq['uin'], $qq['skey'], $qq['pskey']);
            $do->visitCp($uin);
            foreach ($do->msg as $v) {
                echo $v . "<br>";
            }
            if ($do->fail == true) {
                Qq::update(['status' => 0], ['uin' => $qq['uin']]);
            }
        }
    }

}
