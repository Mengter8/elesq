<?php
declare (strict_types=1);

namespace app\listener;


use app\model\Chat;
use app\model\User;
use think\Container;
use think\swoole\Websocket;

class RoomChat
{
    public $websocket = null;

    /**
     * 注入容器管理类，从容器中取出Websocket类，或者也可以直接注入Websocket类，
     */
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
        if (isset($event['content'], $event['token'])) {
            $fd = $this->websocket->getSender();
            $chat = new Chat();
            $user = $chat->getUserInfo($event['token']);
            $ret = $chat->createChat($user['uid'], $event['content']);
            $content = htmlspecialchars($event['content']);//html编码防止xss

            //发送消息
            //如果没有加入Room 只返回消息 自己可见
            //如果加入Room 则群发加入的Room 消息
            $this->websocket->emit("ChatCallback", ['fd' => $fd, 'message' => "{$content}",'token'=>$event['token'],'user'=>$user]);

            //进行对指定Room 进行群发
            $this->websocket->to('chat')->emit("SysChatCallback", ['fd' => $fd, "message" => "{$content}", 'time' => date('m-d H:i', $ret->time), "user" => $user]);
            /*
            //指定客户端发送(FD)
            $this->websocket->setSender(1)->emit("callback", ['getdata' => $event['content']]);

            //关闭指定客户端连接，参数为fd，默认为当前链接
            $this->websocket->close();
            */
        }
    }
}