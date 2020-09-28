<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\swoole\websocket\Room;

class WebSocket extends Command
{
    /**
     * @var Swoole\WebSocket\Server
     */
    private $ws;

    protected function configure()
    {
        // 指令配置
        $this->setName('app\command\websocket')
            ->setDescription('the app\command\websocket command');        
    }

    protected function execute(Input $input, Output $output)
    {


        $this->ws = new \think\swoole\Websocket("0.0.0.0", 9502);
        $this->ws->on('open', function ($ws, $request) {
            var_dump($request->fd, $request->get, $request->server);
            $ws->push($request->fd, "hello, welcome\n");
        });
        // 指令输出
    	$output->writeln('app\command\websocket');
    }
}
