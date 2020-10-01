<?php
declare (strict_types=1);

namespace app\index\controller;

use app\model\Qq;
use app\model\Task;
use app\model\User;
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
    public function phpinfo(){
        phpinfo();
    }

    public function test(){
        //取昵称
        echo get_qqnick('204461275');
        echo get_qqnick('123456');
        //判断所有QQ Auto是否存在 没有会导致报错
        $task = new Task();
//        $task->DeleteTask(NULL,'1543797310');
        $qq = new Qq();
        $allQq = $qq->select()->toArray();

        foreach ($allQq as $value){
            $res = $task->where('uin','=',$value['uin'])->where('type','=','auto')->select()->toArray();
            if (!$res){
                dump("{$value['uin']} auto任务不存在 已创建");
                //创建auto
                $task->createTask($value['uin'], 'auto',array());
            }
        }

        $allTask = $task->where('type','=','auto')->select()->toArray();

        foreach ($allTask as $value){

            $res = $qq->where('uin','=',$value['uin'])->select()->toArray();
            if (!$res){
                dump("{$value['uin']} qq数据库没有该信息 已删除该任务");
                $task->DeleteTask(NULL,$value['uin']);
            }
        }
        dump(count($allQq));
        dump(count($allTask));
        {
            $output = \think\facade\Console::call('QiPao');
            return $output->fetch();
        }
    }
}
