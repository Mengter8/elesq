<?php
declare (strict_types = 1);

namespace app\index\middleware;

use think\facade\View;

class CheckBrowser
{
    /**
     * @var array|mixed|string
     */
    private $ua;

    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
//        $this->ua = Request()->server('HTTP_USER_AGENT');//2020-7-24 修改之前
        $this->ua = Request()->server('HTTP_USER_AGENT','');
        if (preg_match('/QQ\//', $this->ua) || strpos($this->ua, 'MicroMessenger') !== false){
            exit(View::fetch('other/jump'));
        }
        // 继续执行进入到控制器
        return $next($request);
    }
}
