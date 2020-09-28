<?php
declare (strict_types=1);

namespace app\index\controller;

use app\model\Mpz;
use qq\qzone;
use think\facade\Request;
use think\facade\View;
use app\model\Qq;

/**
 * 小工具
 */
class Tools
{
    protected $middleware = ['app\index\middleware\CheckLoginUser', 'app\index\middleware\CheckSafeChallenge'];

    public function mpz()
    {
        $uin = Request::get('uin');
        $res = (new Qq)->findMyUin($uin);
        if ($res) {
            View::assign([
                'nickname' => $res['nickname'],
                'uin' => $res['uin'],
                'status' => $res['status'],
            ]);
        }
        dump(cookie('mpz_auto'));
        return autoTemplate();
    }

    /**
     * 获取所有点赞列表
     */
    public function mpzListHtml()
    {
        $uin = Request::get('uin');
        $list = (new Qq)->getZanList($uin, 'mpz');
        if (count($list) == 1) {
            cookie('mpz_auto', null);
        }
        View::assign([
            'uin' => $uin,
            'list' => $list,
        ]);
        return autoTemplate();
    }

    /**
     * 名片点赞
     */
    public function LikeMpz()
    {
        $uin = Request::post('uin'); //被赞的
        $qq = Request::post('qq'); //数据库要赞
        $res = (new Qq)->getByUin($uin);
        if (!$res) {
            cookie('mpz_auto', NULL);
            return json(['code' => 0, 'message' => '请选择QQ后使用']);
        }
        if ($res['status'] == 0) {
            cookie('mpz_auto', NULL);
            return json(['code' => 0, 'message' => '当前选择QQ状态已失效，请更新后使用']);
        }

        if (!$res = (new Qq)->getByUin($qq)) {
            return json(['code' => 0, 'message' => '没有这个QQ']);
        }
        $do = new \qq\mpz($qq, $res['skey'], $res['pskey']);
        $msg = $do->mpz_like($uin);
        if ($do->fail == true) {
            (new Qq())->setStatus($qq);
        }
        if ($msg['code'] == 5 || $msg['code'] == 4) {
            (new Mpz())->signLog($uin, $qq, 'mpz');
        }
        return json($msg);
    }

    /**
     * 说说列表
     */
    public function shuoList()
    {
        $uin = Request::get('uin');
        $res = (new Qq)->getByUin($uin);
        $arr = (new qzone($res->uin, $res->skey, $res->pskey))->getMyList($uin);
        View::assign([
            'list' => $arr,
        ]);
        return autoTemplate();
    }

    /**
     * 说说互赞
     */
    public function shuo()
    {
        $uin = Request::get('uin');
        $tid = Request::get('tid');
        $appid = Request::get('appid');
        $content = Request::get('content');
        $createTime = Request::get('createTime');
        $source_name = Request::get('source_name');
        $res = (new Qq)->findMyUin($uin);
        $list = (new Qq)->where('status', '=', 1)
            ->limit(12)
            ->select()
            ->toArray();
        if ($res) {
            View::assign([
                'nickname' => $res['nickname'],
                'uin' => $res['uin'],
                'status' => $res['status'],
                'content' => $content,
                'createTime' => $createTime,
                'source_name' => $source_name,
                'list' => $list,
                'tid' => $tid,
                'appid' => $appid
            ]);
        }
        return autoTemplate();
    }

    /**
     * 获取所有点赞列表
     */
    public function shuoListHtml()
    {
        $uin = Request::get('uin');
        $tid = Request::get('tid');
        $list = (new Qq)->getZanList($uin, $tid);

        if (count($list) == 1) {
            cookie('mpz_auto', null);
        }
        View::assign([
            'uin' => $uin,
            'list' => $list,
            'tid' => $tid
        ]);
        return autoTemplate();
    }

    /**
     * 说说点赞
     */
    public function likeShuo()
    {
        $tid = Request::post('tid');
        $appid = Request::get('appid');
        $uin = Request::post('uin'); //被赞的
        $qq = Request::post('qq'); //数据库要赞
        $res = (new Qq)->getByUin($uin);
        if (!$res) {
            cookie('mpz_auto', NULL);
            return json(['code' => 0, 'message' => '请选择QQ后使用']);
        }
        if ($res['status'] == 0) {
            cookie('mpz_auto', NULL);
            return json(['code' => 0, 'message' => '当前选择QQ状态已失效，请更新后使用']);
        }

        if (!$res = (new Qq)->getByUin($qq)) {
            return json(['code' => 0, 'message' => '没有这个QQ']);
        }
        $do = new \qq\qzone($qq, $res['skey'], $res['pskey']);
        $key = "http://user.qzone.qq.com/{$uin}/mood/{$tid}";
//        echo $key;
        $msg = $do->likeCP($key, $key, $appid);
        if ($do->fail == true) {
            (new Qq())->setStatus($qq);
        } else {
            (new Mpz())->signLog($uin, $qq, $tid);
        }

        return json(['code' => 1, 'message' => $msg]);
    }
}
