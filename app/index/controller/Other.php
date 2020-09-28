<?php
declare (strict_types=1);

namespace app\index\controller;

use app\model\Log;
use app\model\User;
use app\model\Qq;
use app\model\Sign;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;

class Other
{
    public function index()
    {
        cookie('domin_url', '/other/index');
        return autoTemplate();
    }

    /**
     * 秒赞墙
     */
    public function wall()
    {
        $task = new \app\model\Task();
        $res = $task->getZanWall();
//        dump($res);
        $vipList = [];
        $jisuList = [];
        $freeList = [];
        foreach ($res as $key => $value) {
            $server = isset(unserialize($value['dataset'])['server']) ? unserialize($value['dataset'])['server'] : 0;
            if ($server == 2) {
                $jisuList[] = $res[$key];
            } elseif ($server == 1) {
                $vipList[] = $res[$key];
            } elseif ($server == 0) {
                $freeList[] = $res[$key];
            }
        }
        View::assign([
            'vipList' => $vipList,
            'jisuList' => $jisuList,
            'freeList' => $freeList,
        ]);
        return autoTemplate();
    }

    /**
     * 秒赞认证
     */
    public function prove()
    {
        $uin = Request::param('uin');
        $Qq = new Qq();
        $Task = new \app\model\Task();
        $res = $Qq->getByUin($uin);
        if ($res) {
            $TRes = $Task->getTaskData('zan', $uin);
            $server = $TRes['dataset']['server'];
            View::assign([
                'task_status' => $TRes['status'],
                'create_time' => $TRes['create_time'],
                'server' => $server,
            ]);
        }
        View::assign([
            'uin' => $uin,
            'nickname' => $res['nickname'],
            'uin_status' => $res['status'],
            'update_time' => $res['update_time'],
        ]);
        return autoTemplate();
    }

    /**
     * 秒赞认证查询
     */
    public function prove_query()
    {
        return autoTemplate();
    }

    public function agent_query(){
        return autoTemplate();
    }
    public function agent_ajax(){
        $uin = input('uin');
        $user = new User();
        $res = $user->getAgentInfo($uin);
        if ($res){
            return resultJson(1,'y',$res);

        } else {
            return resultJson(0,'No',$res);
        }
    }

    /**
     * 好友邀请
     */
    public function invite()
    {
        $user = new User();
        $user->updateMyInfo();
        View::assign([
            'inviteCount' => $user->getInviteCount(),
            'inviteQQCount' => $user->getInviteQQCount(),
            'score' => session::get('user.score'),
        ]);
        return autoTemplate();
    }

    /**
     * 好友邀请
     */
    public function inviteLog()
    {
        $isMyLog = Request::get('isMyLog', false);
        View::assign([
            'isMyLog' => $isMyLog
        ]);
        return autoTemplate();
    }

    public function inviteLogHtml()
    {
        $user = new User();
        $page = Request::get('page');
        $isMyLog = Request::get('isMyLog', false);
        //每页数量
        $listRows = 12;
        $list = $user->getInviteLog($listRows, $isMyLog);
        $isLoadingSuccess = ceil($list->total() / $listRows) <= $page;

        View::assign([
            'list' => $list,
            'count' => $list->total(),
            'isLoadingSuccess' => $isLoadingSuccess
        ]);
        return autoTemplate();
    }

    /**
     * 每日签到
     */
    public function sign()
    {
        (new User())->updateMyInfo();
        $sign = new Sign();
        View::assign([
            'signDay' => $sign->getUidSignCount(),
            'vipDay' => $sign->getUidDayCount(),
            'score' => session::get('user.score'),
            'isSignSuccess' => $sign->isTodaySign()
        ]);
        return autoTemplate();
    }

    public function signAjax()
    {
        $sign = new Sign();
//        判断是否登录
        if (!Session::has('user')) {
            return resultJson(0, '请先进行登录');
        }
//        判断今天是否签到
        if ($sign->isTodaySign()) {
            return resultJson(0, '今天已经签过了');
        }
        $prize_arr = getPrizeList();
        foreach ($prize_arr as $key => $value) {
            $arr[] = $value['prize'];
        }
        $rid = get_rand($arr);

        if ($rid !== false) {
            //中奖结果
            $res = $prize_arr[$rid];
            //记录签到
            $sign->createSign($res['type'], $res['value']);
            if ($res['type'] == 1) {
                // 加会员
                (new User())->addUserVipTime(session::get('user.uid'), $res['value'], '签到活动');
                $message = "恭喜您中奖 <fort class=\"hong\">{$res['value']}</fort> 天VIP会员，运气爆棚！";
            }
            if ($res['type'] == 2) {
                //加积分
                (new User())->addUserScore(session::get('user.uid'), $res['value'], '签到活动');
                $message = "恭喜您中奖 <fort class=\"hong\">{$res['value']}</fort> 个积分，运气爆棚！";
            }
        } else {
            $sign->createSign(0, 0);
            $message = "很遗憾，您没有中奖";
        }
        return resultJson(1, $message, ['signDay' => $sign->getUidSignCount(), 'vipDay' => $sign->getUidDayCount(), 'score'=>session::get('user.score')]);
    }

    /**
     * 所有签到日志
     */
    public function signLog()
    {
        (new User())->updateMyInfo();
        $isMyLog = Request::get('isMyLog', false);
        View::assign([
            'isMyLog' => $isMyLog
        ]);
        return autoTemplate();
    }

    public function signLogHtml()
    {
        $Sign = new Sign();
        $page = Request::get('page');
        $isMyLog = Request::get('isMyLog', false);

        $listRows = 12;
        $list = $Sign->getSignLog($listRows, $isMyLog);
        $isLoadingSuccess = ceil($list->total() / $listRows) <= $page;

        View::assign([
            'list' => $list,
            'count' => $list->total(),
            'isLoadingSuccess' => $isLoadingSuccess
        ]);
        return autoTemplate();
    }

    /**
     * 积分兑换
     */
    public function redeem()
    {
        $list = getRedeemList();
        View::assign([
            'list' => $list,
            'score' =>session('user.score'),
        ]);
        return autoTemplate();
    }

    /**
     * 积分兑换日志
     */
    public function redeemLog()
    {
        $isMyLog = Request::get('isMyLog');
        View::assign([
            'isMyLog' => $isMyLog,
        ]);
        return autoTemplate();
    }

    /**
     * 积分兑换请求
     */
    public function redeemAjax()
    {
        $id = Request::post('id');
        if (!Session::has('user')) {
            return resultJson(0, '请先进行登录');
        }
        $user = new User();
        $list = getRedeemList();
        if (isset($list[$id])) {
            $score = $list[$id]['score'];
            $day = $list[$id]['day'];
            $name = $list[$id]['name'];
            $res = $user->getByUid(session::get('user.uid'))->toArray();
            if ($res['score'] > $score) {
                //兑换会员
                $user->addUserVipTime(session::get('user.uid'), $day, '积分兑换');
                (new log())->createLog(session::get('user.uid'), $score, $name,'redeem', 1);
                $user->decUserScore(session::get('user.uid'), $score, '积分兑换');
                return resultJson(1, "消耗了{$score}点积分兑换{$name}成功!",['score'=>session::get('user.score')]);
            } else {
                return resultJson(0, "您的积分不足，请攒够了再来吧!");

            }
        } else {
            return resultJson(0, 'ERROR: ID');
        }
    }

    /**
     * 积分兑换日志 HTML
     */
    public function redeemLogHtml()
    {
        $log = new log();
        $page = Request::get('page');
        $isMyLog = Request::get('isMyLog');
        $listRows = 12;
        $list = $log->getRedeemLog($listRows, $isMyLog);
        $isLoadingSuccess = ceil($list->total() / $listRows) <= $page;

        View::assign([
            'list' => $list,
            'count' => $list->total(),
            'isLoadingSuccess' => $isLoadingSuccess
        ]);
        return autoTemplate();
    }
    public function agent(){
        return autoTemplate();
    }
}
