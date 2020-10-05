<?php
// 这是系统自动生成的公共文件
use think\facade\Request;
use think\facade\Cookie;
use think\facade\View;

function autoTemplate()
{
    if (Request::isMobile()) {
        View::config(['view_dir_name' => 'view' . DIRECTORY_SEPARATOR . 'mobile']);

        if (Request::controller() == 'Index') {
            if (isset($_SERVER['HTTP_REFERER']) && parse_url($_SERVER['HTTP_REFERER'])['path'] == 'index/index') {
//                dump(parse_url($_SERVER['HTTP_REFERER']));
                //&& parse_url($_SERVER['HTTP_REFERER'])['path'] == 'index/index'
                Cookie::set('domin_url', '/index/index');
            }
        }


        if (!Request::isAjax()) {
            if (Request::controller() == 'Index') {
                View::layout('layout');
            } else {
                return redirect('/index/index');
            }
        }
    } else {
        View::config(['view_dir_name' => 'view' . DIRECTORY_SEPARATOR . 'pc']);
        if (!Request::isAjax()) {
            if (Request::controller() != 'Index') {
                View::layout('layout');
            }
        }
    }

    return View::fetch();

}

function resultMsg($title, $content, $icon = 'check lv')
{
    View::assign([
        'title' => $title,
        'content' => $content,
        'icon' => $icon
    ]);
    if (request()->isMobile()) {
        if (!request()->isAjax()) {
            View::layout('mobile/layout');
            return View::fetch('other/msg');
        } else {
            Cookie::set('domin_url', '/shop/return_url.html?money=0.1&name=UID%3A1+-+钻石代理&out_trade_no=20200621082026276&pid=104888&trade_no=2020062108202736159&trade_status=TRADE_SUCCESS&type=alipay&sign=28ad1dc87861756e8851ef34cd9958ce&sign_type=MD5');
        }

    } else {
        View::layout('pc/layout');
        return View::fetch('other/msgPc');
    }
}

/**
 * 获取服务器名称
 * @param $id
 * @return string|null
 */
function getServerName($id)
{
    if ($id == 2) {
        return '急速服务器';
    } elseif ($id == 1) {
        return 'VIP服务器';
    } elseif ($id == 0) {
        return '免费服务器';
    } else {
        return NULL;
    }
}