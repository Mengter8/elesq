<?php
declare (strict_types=1);

namespace app\index\controller;

use app\model\User;
use app\model\Qq;
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
            $server = isset($value['dataset']['server']) ? $value['dataset']['server'] : 0;
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
            if ($TRes = $Task->getTaskData('zan', $uin)) {
                $server = $TRes['dataset']['server'];
            } else {
                $server = 0;
                $TRes['status'] = 0;
                $TRes['create_time'] = 0;
            }
            View::assign([
                'task_status' => $TRes['status'],
                'create_time' => $TRes['create_time'],
                'server' => $server,
            ]);
        } else {
            $res['nickname'] = getQqNickname($uin);
            $res['status'] = NULL;
            $res['update_time'] = NULL;
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

    public function agent_query()
    {
        return autoTemplate();
    }

    public function agent_ajax()
    {
        $uin = input('uin');
        $user = new User();
        $res = $user->getAgentInfo($uin);
        if ($res) {
            return resultJson(1, 'y', $res);

        } else {
            return resultJson(0, 'No', $res);
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

    public function agent()
    {
        return autoTemplate();
    }
}
