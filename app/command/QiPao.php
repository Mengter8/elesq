<?php
declare (strict_types=1);

namespace app\command;

use app\model\Qq;
use app\model\Task;
use qq\sign;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class QiPao extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('qipao')
            ->setDescription('百变气泡');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出

        $task = new Task();

        /**
         * 百变气泡
         */
        $res = $task->getAllTask('qipao');
        foreach ($res as $v) {
            $do = new sign($v->uin, $v['skey'], $v['pskey'],$v['superkey']);
//            for ($i=0;$i <= 100;$i++) {
//                $do->qipao();
//            }
            $dataset = $v['dataset'];
            $mode = !empty($dataset['mode']) ? $dataset['mode'] : 0;
            $do->qipao($mode);
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin, 'jiasu');
            }
        }
        $output->writeln("百变气泡_" . date("Y-m-d H:i:s"));
    }
}
