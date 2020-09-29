<?php
declare (strict_types=1);

namespace app\listener;

use think\Container;
use think\swoole\Websocket;

class RoomJoin
{
    public $websocket = null;

    public function __construct(Container $container)
    {
        $this->websocket = $container->make(Websocket::class);
    }

    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        $this->websocket->join($event['room']);

        $fd = $this->websocket->getSender();
        $this->websocket->emit("JoinCallback", ['fd' => $fd, 'message' => "join to {$event['room']} is success!"]);
        $this->websocket->to($event['room'])->emit("SysChatCallback", ["message" => "fd {$fd} 加入了本房间"]);
//        $this->websocket->to($event['room'])->emit("SysChatCallback", ["message" => $this->websocket->getTo()]);

    }
}
