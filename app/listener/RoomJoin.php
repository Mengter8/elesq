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
        if (isset($event['token'])) {
            $chat = new Chat();
            $user = $chat->getUserInfo($event['token']);
            $this->websocket->emit("SystemCallback", ["message" => "用户 {$user['nickname']} 加入了聊天室"]);
            $this->websocket->broadcast()->emit("SystemCallback", ["message" => "用户 {$user['nickname']} 加入了聊天室"]);
        }
    }
}
