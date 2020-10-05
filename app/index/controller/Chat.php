<?php
declare (strict_types = 1);

namespace app\index\controller;

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
        return autoTemplate();
    }

    /**
     * 提交聊天室
     * @return string|\think\response\Redirect
     */
    public function submit(){
        return autoTemplate();
    }
}
