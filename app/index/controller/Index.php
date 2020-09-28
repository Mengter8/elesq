<?php
declare (strict_types=1);

namespace app\index\controller;

use app\model\Qq;
use app\model\Server;
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
        if (!\think\facade\Request::isMobile()) {
            $lastUser = (new User)->getLastUser();
            View::assign([
                'lastUser' => $lastUser,
            ]);

        }
        View::assign([
            'siteCount' => $siteCount,
        ]);
        return autoTemplate();
    }
    public function phpinfo(){
        phpinfo();
    }

    public function test(){

        echo get_qqnick('204461275');
                echo get_qqnick('123456');

        die();
        $task = new Task();
        $res = $task->where('type','=','auto')->select()->toArray();
        dump($res);
        {
            $output = \think\facade\Console::call('QiPao');
            return $output->fetch();
        }
    }
}
