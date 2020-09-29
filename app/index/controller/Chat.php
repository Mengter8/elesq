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
    public function index(){
        return autoTemplate();
    }
}
