<?php
declare (strict_types=1);

namespace app\index\middleware;

use think\facade\Request;
use think\facade\Session;
use think\facade\View;

class CheckLoginUser
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        // 判断是否登录
        if (empty(Session::get('user'))) {
            // 跳转到登录页面
            if (Request::isMobile()){
                exit(View::fetch('other/notLogin'));
            }else {
                exit(View::fetch('other/notLoginPc'));
            }
        }
        // 继续执行进入到控制器
        return $next($request);
    }
}
