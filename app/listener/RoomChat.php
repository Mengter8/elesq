<?php
declare (strict_types = 1);

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
        //回复客户端消息
        //$event 为从客户端接收的数据
        $this->websocket->emit("callback", ['fd' => 12, 'data' => $event['asd']]);
        //增加如下内容，表示进行对指定room进行群发
        $this->websocket->to('chat')->emit("callback", ['guangbo' => 1, 'getdata' => $event['asd']]);

        //指定客户端发送，假设已知某一客户端连接fd为1，则发送如何
        $this->websocket->setSender(28)->emit("callback", ['guangbo' => 1, 'getdata' => $event['asd']]);
        //获取当前客户端fd
//        $this->websocket->getSender();
        //关闭指定客户端连接，参数为fd，默认为当前链接
        $this->websocket->emit("callback", ['fd' => 12, 'getdata' => $event['asd']]);

//        $this->websocket->close();
    }
}