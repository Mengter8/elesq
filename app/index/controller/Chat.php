<?php
declare (strict_types = 1);

namespace app\index\controller;

use think\facade\View;

/**
 * 聊天室基础控制器
 * Class Chat
 * @package app\index\controller
 */
class Chat
{
    /**
     * 聊天室首页
     * @return string|\think\response\Redirect
     */
    public function index(){
        $chat = new \app\model\Chat();
        $ret = $chat->queryAllChat();
        View::assign([
            'list' => $ret
        ]);
        return autoTemplate();
    }
}
