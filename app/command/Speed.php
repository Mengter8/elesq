<?php
declare (strict_types=1);

namespace app\command;

use app\model\Qq;
use app\model\Task;
use qq\sign as qqsign;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Speed extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('sign')
            ->setDescription('加速类任务签到');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出

        $task = new Task();

        /**
         * 手机六小时加速
         */
        $res = $task->getAllTask('mobileSpeed');
        foreach ($res as $v) {
            $do = new qqsign($v->uin, $v['skey'], $v['pskey']);
            $do->mobileSpeed();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin, 'jiasu');
            }
        }
        $output->writeln("手机六小时加速_" . date("Y-m-d H:i:s"));

        /**
         * 手游加速
         */
        $res = $task->getAllTask('gameSpeed');
        foreach ($res as $v) {
            $do = new qqsign($v->uin, $v['skey'], $v['pskey']);
            $do->gameSpeed();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin, 'jiasu');
            }
        }
        $output->writeln("手游加速_" . date("Y-m-d H:i:s"));

        /**
         * 手机六小时加速
         */
        $res = $task->getAllTask('yunDong');
        foreach ($res as $v) {
            $do = new qqsign($v->uin, $v['skey'], $v['pskey']);
            $do->yunDong();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin, 'jiasu');
            }
        }
        $output->writeln("运动加速_" . date("Y-m-d H:i:s"));
    }
}
