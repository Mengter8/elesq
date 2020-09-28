<?php
declare (strict_types=1);

namespace app\command;

use app\model\Qq;
use app\model\Task;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class VipSign extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('vipsign')
            ->setDescription('VIP类任务签到');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $task = new Task();
        $res = $task->getAllTask('vip');
        foreach ($res as $v) {
            $do = new \qq\vipSign($v->uin, $v->skey, $v->pskey);
            $do->vip();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin,0);
            } else {
                (new Task())->setTaskTime($v->uin,'vip');
            }
            $output->writeln("会员签到_" . date("Y-m-d H:i:s"));
        }
        /**
         * 大会员签到
         */
        $res = $task->getAllTask('bigVip');
        foreach ($res as $v) {
            $do = new \qq\vipSign($v->uin, $v->skey, $v->pskey);
            $do->bigVip();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin, 'hz');
            }
            $output->writeln("黄钻签到_" . date("Y-m-d H:i:s"));
        }
        /**
         * 蓝钻签到
         */
        $res = $task->getAllTask('gameVip');
        foreach ($res as $v) {
            $do = new \qq\vipSign($v->uin, $v->skey, $v->pskey);
            $do->gameVip();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin,'lz');
            }
            $output->writeln("蓝钻签到_" . date("Y-m-d H:i:s"));
        }
        /**
         * 黄钻签到
         */
        $res = $task->getAllTask('qzoneVip');
        foreach ($res as $v) {
            $do = new \qq\vipSign($v->uin, $v->skey, $v->pskey);
            $do->qzoneVip();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin,'lz');
            }
            $output->writeln("黄钻签到_" . date("Y-m-d H:i:s"));
        }
        /**
         * 腾讯视频签到
         */
        $res = $task->getAllTask('videoVip');
        foreach ($res as $v) {
            $do = new \qq\vipSign($v->uin, $v->skey, $v->pskey);
            $do->videoVip();
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin,'lz');
            }
            $output->writeln("腾讯视频会员签到_" . date("Y-m-d H:i:s"));
        }
    }
}
