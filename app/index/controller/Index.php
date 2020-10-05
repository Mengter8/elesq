<?php
declare (strict_types=1);

namespace app\index\controller;

use app\model\Qq;
use app\model\Task;
use app\model\User;
use qq\sign;
use think\facade\View;

class Index
{
    /**
     * 首页
     */
    public function index()
    {
//        dump(\think\facade\session::all());
        $siteCount['user'] = User::count();
        $siteCount['today'] = User::where('reg_time', '>=', strtotime(date("Y-m-d 00:00:00")))->count();
        $siteCount['uin'] = Qq::count();
        $siteCount['task'] = Task::count();
        if (Request()->isMobile()) {
            $lastUser = (new Task())->getZanWall();
        } else {
            $lastUser = (new User)->getLastUser();
        }
        View::assign([
            'lastUser' => $lastUser,
            'siteCount' => $siteCount,
        ]);
        return autoTemplate();
    }

    public function phpinfo()
    {
        phpinfo();
    }

    public function qipao()
    {
        $qq = new Qq();
//        $res = $qq->getByUin('1543797310')->toArray();
        $res = $qq->getByUin('466645214')->toArray();
        dump($res);

        $sign = new sign($res['uin'], $res['skey'], $res['pskey'], $res['superkey']);

        $sign->qipao();
        //可能是feeType
        dump($sign->msg);

    }

    public function test()
    {
        //取昵称
        echo get_qqnick('204461275');
        echo get_qqnick('123456');
        //判断所有QQ Auto是否存在 没有会导致报错
        $task = new Task();
//        $task->DeleteTask(NULL,'1543797310');
        $qq = new Qq();
        $allQq = $qq->select()->toArray();

        foreach ($allQq as $value) {
            $res = $task->where('uin', '=', $value['uin'])->where('type', '=', 'auto')->select()->toArray();
            if (!$res) {
                dump("{$value['uin']} auto任务不存在 已创建");
                $task->createTask($value['uin'], 'auto', array());
            }
            $res = $task->where('uin', '=', $value['uin'])->where('type', '=', 'zan')->select()->toArray();
            if (!$res) {
                dump("{$value['uin']} zan任务不存在 已创建");
                $task->createTask($value['uin'], 'zan', array("server" => 0, "mode" => 0, "qqlist" => ""));

            }
        }

        $allTask = $task->select()->toArray();

        foreach ($allTask as $value) {
            $res = $qq->where('uin', '=', $value['uin'])->select()->toArray();
            if (!$res) {
                dump("{$value['uin']} qq数据库没有该信息 已删除该任务");
                $task->DeleteTask($value['uin'], NULL);
            }
        }

        {
            $output = \think\facade\Console::call('QiPao');
            return $output->fetch();
        }
    }
}
