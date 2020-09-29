<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        //自动更新
        'update'=>'app\command\Update',
        //说说赞
        'zan' => 'app\command\Zan',
        //普通签到
        'sign' => 'app\command\Sign',
        //会员类签到
        'vipSign' => 'app\command\VipSign',
        //加速类任务
        'Speed' => 'app\command\Speed',
        //百变气泡
        'QiPao' => 'app\command\QiPao',
        //群签到
        'Qunqd'=> 'app\command\Qunqd',
        //ck fuzz
        'WebSocket' => 'app\command\WebSocket',

    ],
];
