<?php
declare (strict_types=1);

namespace app\command;

use app\model\Qq;
use app\model\Task;
use qq\qzone;
use qq\sign;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Qunqd extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('qunqd')
            ->setDescription('群签到任务');
    }

    protected function execute(Input $input, Output $output)
    {
        $task = new Task();
        $res = $task->getAllTask('qunqd');
        foreach ($res as $v) {
            $do = new sign($v->uin, $v->skey, $v->pskey);
            $dataset = unserialize($v->dataset);
            $data = qunFindId($dataset['tid']);
            $content = !empty($dataset['content']) ? $dataset['content'] : $data['text'];
            $category_id = isset($data['category_id']) ? $data['category_id'] : '';
            $pic_id = isset($data['pic_id']) ? $data['pic_id'] : '';
            $template_id = isset($data['template_id']) ? $data['template_id'] : '';

            $mode = !empty($dataset['mode']) ? $dataset['mode'] : 0;
            $list = !empty($dataset['qunlist']) ? explode(',', str_replace(['，', '.'], ',', $dataset['qunlist'])) : [];

            $do->qunqd($content, $dataset['site'], $category_id, $pic_id, $template_id, $mode, $list);
            foreach ($do->msg as $result) {
                $output->writeln("{$v->uin}_{$result}");
            }
            if ($do->fail == true) {
                (new Qq())->setStatus($v->uin, 0);
            } else {
                (new Task())->setTaskTime($v->uin,'qunqd');
            }
        }
        $output->writeln("群签到_" . date("Y-m-d H:i:s"));
    }
}
