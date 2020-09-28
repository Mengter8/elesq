<?php
declare (strict_types=1);

namespace app\api\controller;

use qq\qzone;
use think\facade\Request;
use think\facade\View;

class Qlogin
{
    /**
     * base64转图片二维码登录接口
     */
    public function get_qrlogin()
    {
        $image = request::get('image');
        $image = str_replace(' ', '+', $image);
        View::assign([
                'image' => $image
            ]
        );
        return View::fetch('/get_qrlogin');
    }
}
