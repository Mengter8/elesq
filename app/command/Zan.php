<?php
declare (strict_types=1);

namespace app\command;

use app\model\Qq;
use app\model\Task;
use qq\qzone;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Zan extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('zan')
            ->setDescription('空间类任务');
    }

    protected function execute(Input $input, Output $output)
    {
        $task = new Task();
        $res = $task->getAllTask('zan');

        foreach ($res as $v) {
//            dump($v);

            $do = new qzone($v->uin, $v->skey, $v->pskey);
            $do->like();
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin,0);
            } else {
                (new Task())->setTaskTime($v->uin,'zan');
            }
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            $output->writeln("运行成功:" . date("Y-m-d H:i:s"));
        }
    }

    /***
     * 多线程异步提交
     */
//    public function zanbak()
//    {
//        $res = Qq::getByStatus(1)->select();
//        foreach ($res as $v) {
//            $urls[]['url'] = Request::domain() . url('/ajax/zan/qq/' . $v['qq'] . '/server/teue');
//            echo $v['qq'] . ' OK!<br>';
//        }
//        print_r(rolling_curl($urls));
//    }
}
