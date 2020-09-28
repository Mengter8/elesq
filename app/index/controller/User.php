<?php
declare (strict_types=1);

namespace app\index\controller;

use app\model\Log;
use app\model\Qq;
use app\model\Task;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;
use think\exception\ValidateException;

class User
{
    /**
     * 登录
     */
    public function index()
    {
        if (empty(Session::get('user'))) {
            // 跳转到登录页面
            exit(View::fetch('other/notLogin'));
        }
        cookie('domin_url', '/user/index');

        (new \app\model\User())->updateMyInfo();
        if (!\think\facade\Request::isMobile()) {
            $siteCount['user'] = \app\Model\User::count();
            $siteCount['today'] = \app\model\User::where('reg_time', '>=', strtotime(date("Y-m-d 00:00:00")))->count();
            $siteCount['uin'] = Qq::count();
            $siteCount['task'] = Task::count();
            View::assign([
                'siteCount' => $siteCount,
                'lastZan' => (new task())->getLastZan(),//最新秒赞QQ
                'scoreList' => (new \app\model\User())->getScoreList(),
            ]);
        }
        return autoTemplate();
    }

    public function login()
    {
        if (Session::has('user')) {
            return redirect((string)url('/user/index'));
        } else {
            $this->qq_login();
//            return View::fetch();
        }
    }

    public function bindQq()
    {
        return autoTemplate();
    }

    public function update()
    {
        $nickname = Request::post('nickname');
        $uin = Request::post('uin');
        try {
            validate(\app\validate\Qq::class)->check([
                'qq' => $uin,
            ]);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return json(['code' => 0, 'message' => $e->getError()]);
        }

        $User = new \app\model\User();

        if ($ret = $User->getByQq($uin)) {
            if ($ret->uid != session::get('user.uid')) {
                return resultJson(0, '该QQ已被其他用户绑定');
            }
        }

        $ret = $User->getByUid(session::get('user.uid'));
        $ret->nickname = $nickname;
        $ret->qq = $uin;
        $ret->save();
        session::set('user', $ret->toArray());
        return json(['code' => 1]);
    }

    /**
     * 跳转QQ登录
     */
    public function qq_login()
    {
        $oauth = new \Connect\Oauth();
        $oauth->qq_login();
    }

    /**
     * 登录回调
     */
    public function qq_callback()
    {
        $user = new \app\model\User();
        $Oauth = new \Connect\Oauth();

        $accessToken = $Oauth->qq_callback();
        $openid = $Oauth->get_openid();

        $ret = $user->getByOpenid($openid);
        if ($ret) {
            // 登录
            session::set('user', $ret->toArray());
            $user->updateLastLogin($ret); //更新最后一次登录
            return redirect((string)url('/user/index'));
        } else {
            $ret = $user->create([
                'tid' => session::get('tid', 0),
                'qq' => '',
                'nickname' => '',
                'money' => 0.00,
                'score' => 0,
                'agent' => 0,
                'openid' => $openid,
                'reg_time' => time(),
                'last_ip' => get_client_ip(),
                'last_time' => time(),
                'vip_start_time' => time(),
                'vip_end_time' => time() + (7 * 24 * 60 * 60),
            ]);
            if ($ret->uid) {
                //增加积分
                $user->addUserScore(session::get('tid', 0), 30, '推广好友');
                session::set('user', $ret->toArray());
                return redirect((string)url('/user/index'));
            } else {
                return json(['code' => 0, 'msg' => '注册失败']);
            }
        }
    }

    /**
     * 注销
     */
    public function logout()
    {
        session::delete('user');
        //电脑模拟的话x.ajax 可能UA不一样 如果手机不行就isAjax
        if (Request::isMobile()) {
            return "<script>x.msg('退出成功');setTimeout(function(){x.url('/','');}, 1000);</script>";
        } else {
            return redirect((string)url('/index/index'));
        }
    }

    /**
     * 邀请
     */
    public function invite()
    {
        $tid = Request::get('tid/d');
        $user = new Qq();
        if ($tid) {
            if ($user->getByUid($tid)) {
                session::set('tid', $tid);
            }
        }
        return redirect((string)url('/index/index'));
    }

    /**
     * 积分兑换
     */
    public function score()
    {
        return autoTemplate();
    }

    /**
     * 我的积分记录
     */
    public function scoreLog()
    {
        return autoTemplate();
    }

    /**
     * 积分兑换日志 HTML
     */
    public function scoreLogHtml()
    {
        $log = new log();
        $type = Request::get('type');
        $page = Request::get('page');

        $listRows = 12;
        $list = $log->getScoreLog($listRows, $type);
        $isLoadingSuccess = ceil($list->total() / $listRows) <= $page;

        View::assign([
            'list' => $list,
            'count' => $list->total(),
            'isLoadingSuccess' => $isLoadingSuccess
        ]);
        return autoTemplate();
    }

    /**
     * 我的会员中心
     */
    public function vip()
    {
        $diff = data_Diff(session::get('user.vip_start_time'), session::get('user.vip_end_time'));
        if ($diff < 0) {
            $coler = 'lan';
            $title = '已过期';
        } elseif ($diff >= 1 && $diff < 30) {
            $coler = 'lan';
            $title = '体验VIP';
        } elseif ($diff < 90) {
            $coler = 'lan';
            $title = '包月VIP';
        } elseif ($diff < 365) {
            $coler = 'zi';
            $title = '包季VIP';
        } elseif ($diff >= 365) {
            $coler = 'huang';
            $title = '年费VIP';
        }
        View::assign([
            'coler' => $coler,
            'title' => $title,
            'diff' => $diff
        ]);
        return autoTemplate();
    }

    public function vipLog()
    {
        return autoTemplate();
    }

    public function vipLogHtml()
    {
        $log = new log();
        $page = Request::get('page');

        $listRows = 12;
        $list = $log->getVipLog($listRows);
        $isLoadingSuccess = ceil($list->total() / $listRows) <= $page;

        View::assign([
            'list' => $list,
            'count' => $list->total(),
            'isLoadingSuccess' => $isLoadingSuccess
        ]);
        return autoTemplate();
    }

    public function money()
    {
        return autoTemplate();
    }

    public function moneyLog()
    {
        return autoTemplate();
    }

    public function moneyLogHtml()
    {
        $log = new log();
        $type = Request::get('mod');

        $page = Request::get('page');

        $listRows = 12;
        $list = $log->getMoneyLog($listRows, $type);
        $isLoadingSuccess = ceil($list->total() / $listRows) <= $page;

        View::assign([
            'list' => $list,
            'count' => $list->total(),
            'isLoadingSuccess' => $isLoadingSuccess
        ]);
        return autoTemplate();
    }
}
