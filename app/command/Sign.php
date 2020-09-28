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

class Sign extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('sign')
            ->setDescription('普通类任务签到');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出

        $task = new Task();

        /**
         * QQ打卡
         */
        $res = $task->getAllTask('checkin');
        foreach ($res as $v) {
            $do = new qqsign($v->uin, $v['skey'], $v['pskey']);
            $do->checkin();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin, 'jiasu');
            }
        }
        $output->writeln("打卡_" . date("Y-m-d H:i:s"));


        /**
         * 游戏签到
         */
        $res = $task->getAllTask('gameSign');
        foreach ($res as $v) {
            $do = new qqsign($v->uin, $v['skey'], $v['pskey']);
            $do->gameSign();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin, 'jiasu');
            }
        }
        $output->writeln("打卡_" . date("Y-m-d H:i:s"));


        /**
         * 图书签到
         */
        $res = $task->getAllTask('book');
        foreach ($res as $v) {
            $do = new qqsign($v->uin, $v['skey'], $v['pskey']);
            $do->book();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin, 'jiasu');
            }
        }
        $output->writeln("打卡_" . date("Y-m-d H:i:s"));
        /**
         * 花藤代养
         */
        $res = $task->getAllTask('flower');
        foreach ($res as $v) {
            $do = new qqsign($v->uin, $v['skey'], $v['pskey']);
            $do->flower();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin, 'jiasu');
            }
        }
        $output->writeln("花藤代养_" . date("Y-m-d H:i:s"));
        /**
         * 图书签到
         */
        $res = $task->getAllTask('weishi');
        foreach ($res as $v) {
            $do = new qqsign($v->uin, $v['skey'], $v['pskey']);
            $do->weishi();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin, 'jiasu');
            }
        }
        $output->writeln("微视签到_" . date("Y-m-d H:i:s"));

    }
}
