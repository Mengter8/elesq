<?php
declare (strict_types=1);

namespace app\listener;


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
        //获取当前客户端fd
        $fd = $this->websocket->getSender();
        //获取当前room?
        $room = $this->websocket->getTo();
        //发送消息
        //如果没有加入Room 只返回消息 自己可见
        //如果加入Room 则群发加入的Room 消息
        $this->websocket->emit("ChatCallback", ['fd' => $fd,  'message' => "收到消息：{$event['content']}"]);
//
//        //进行对指定Room 进行群发
        $this->websocket->to('chat')->emit("SysChatCallback", [  "message" => "fd {$fd} 发送了消息：{$event['content']}"]);
//
//        //指定客户端发送(FD)
//        $this->websocket->setSender(1)->emit("callback", ['getdata' => $event['content']]);
//
//        //关闭指定客户端连接，参数为fd，默认为当前链接
//        $this->websocket->close();
    }
}