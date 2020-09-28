<?php
declare (strict_types = 1);

namespace app\command;

use qq\sign;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Request;

class Fuzz extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('fuzz')
            ->addArgument('uin', Argument::OPTIONAL, "your qq")
            ->setDescription('the fuzz command');        
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $uin = trim($input->getArgument('uin'));

        $file = file_get_contents(getcwd().'/extend/ck.txt');
        $cks = explode("\r\n",$file);
//        dump($cks);
        foreach ($cks as $v){
            $res = explode(",",$v);
//            dump($res);
            $do = new sign($res[0],$res[1],$res[2]);
//            $post = "uin={$res[0]}&skey={$res[1]}&qq={$uin}&count=10";
//            $res = get_curl("http://www.nb.huzanbao.cn:88/api.php?act=getmpz",$post);
//            $output->writeln($res);
            $do->mpz_like($uin);
            foreach ($do->msg as $msg){
                $output->writeln($msg);
            }
            sleep(1);
        }
    }
}
