<?php
declare (strict_types=1);

namespace app\listener;

use app\model\Chat;
use app\model\User;
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
        if (isset($event['token'],$event['room'])) {
            $this->websocket->join($event['room']);
            $chat = new Chat();
            $user = $chat->getUserInfo($event['token']);
            $fd = $this->websocket->getSender();
            $this->websocket->emit("JoinCallback", ['fd' => $fd, 'message' => "join to {$event['room']} is success!", 'data' => $user]);
            $this->websocket->to($event['room'])->emit("SystemCallback", ['fd' => $fd, "message" => "用户 {$user['nickname']} 加入了本房间"]);
        }
    }
}
