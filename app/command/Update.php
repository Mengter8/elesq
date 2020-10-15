<?php
declare (strict_types=1);

namespace app\command;

use app\model\Qq;
use app\model\Server;
use app\model\Task;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\filesystem\driver\Local;

class Update extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('update')
            ->setDescription('自动更新');
    }

    protected function execute(Input $input, Output $output)
    {
        $server = new Server();
        $task = new Task();
        $res = $task->getAutoUpdateTask();
//        dump($res->toArray());die();
        foreach ($res as $key => $value) {
//            dump($value);
            $uin = $value['uin'];
            $sid = $value['sid'];
            $Qlogin = new \qq\Qlogin($server->getId($sid)['api']);
            $checkvc = $Qlogin->checkvc($uin);
            if ($checkvc['code'] == 1) {
                //可直接登录
                $vcode = $checkvc['data']['vcode'];
                $pt_verifysession = $checkvc['data']['pt_verifysession'];
                $cookie = $checkvc['data']['cookie'];

            } elseif ($checkvc['code'] == 2) {
                //开始循环 滑块验证
                $cap_cd = $checkvc['data']['cap_cd'];
                $cookie = $checkvc['data']['cookie'];

                for ($i = 1; $i <= 5; $i++) {
                    if (!isset($sess, $sid)) {
                        $getvc = $Qlogin->getvc($uin, $cap_cd);
                        $sid = $getvc['data']['sid'];
                        $sess = $getvc['data']['sess'];
                        $collectname = $getvc['data']['collectname'];
                    } else {
                        $getvc = $Qlogin->getvc($uin, $cap_cd, $sess, $sid);
                        $sess = $getvc['data']['sess'];
                    }
//                    dump($getvc);
                    if ($getvc['code'] == 0) {
                        $output->writeln($uin . ' GetVC Error!');
                    }
                    $dovc = $Qlogin->dovc($uin, $getvc['data']['ans'], $cap_cd, $sess, $sid, $collectname);
                    if ($dovc['code'] == 0) {
                        //验证通过
                        $vcode = $dovc['data']['vcode'];
                        $pt_verifysession = $dovc['data']['pt_verifysession'];
                        $output->writeln("{$uin} 验证通过滑块");
                        break;
                    } else {
                        $output->writeln( "{$uin} {$dovc['message']}");
                    }
                }
            }

            if (isset($vcode, $pt_verifysession, $cookie)) {
                $pwd = strtoupper(md5($value['pwd']));
                if (\think\facade\Request::env('APP_DEBUG') == true) {
                    $res = get_curl("https://api.elesq.cn/common/getPCode.html", "uin={$uin}&pwd={$pwd}&vcode={$vcode}");
//                    dump($vcode);
                    $json = json_decode($res, true);
                    $p = $json['data'];
                } else {
                    $cmd_uin = escapeshellcmd($uin);
                    $cmd_pwd = escapeshellcmd($pwd);
                    $cmd_vcode = escapeshellcmd($vcode);
                    $p = exec("/www/server/nvm/versions/node/v12.18.3/bin/node /www/wwwroot/server/server.js {$cmd_uin} {$cmd_pwd} {$cmd_vcode}");
                }
                if ($p == 'error' || $p == '') exit($uin . ' getp failed!');
                $qqlogin = $Qlogin->qqlogin($uin, $p, $vcode, $pt_verifysession, $cookie);
                if ($qqlogin['code'] == 0) {
                    (new Qq())->add('',$sid,$uin,'',$qqlogin['data']['skey'],$qqlogin['data']['pskey'],$qqlogin['data']['superkey']);
                    $output->writeln($uin . ' 更新成功');
                    (new Task())->setTaskTime($uin, 'auto');
                } elseif($qqlogin['code'] == -6){
                    $output->writeln($uin . ' ' . $qqlogin['message']);
                }else {
                    Qq::where(['uin' => $uin])->inc('fail')->update();
                    $output->writeln($uin . ' ' . $qqlogin['message']);
                }
            } else {
                Qq::where(['uin' => $uin])->inc('fail')->update();
                $output->writeln("{$uin} 更新失败");
            }

            unset($vcode, $pt_verifysession);
            unset($sess, $sid);
//            break;
        }
        $output->writeln("运行成功:" . date("Y-m-d H:i:s"));
    }
}
