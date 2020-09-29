<?php
declare (strict_types=1);

namespace app\listener;

use think\Container;
use think\swoole\Websocket;

class RoomLeave
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
        $this->websocket->leave($event['room']);

        $fd = $this->websocket->getSender();
        $this->websocket->emit("LeaveCallback", ['fd' => $fd, 'message' => "leave for {$event['room']} is success!"]);
        $this->websocket->to($event['room'])->emit("SysChatCallback", ["message" => "fd{$fd}离开了本房间"]);
    }
}
