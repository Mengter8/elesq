<?php
declare (strict_types=1);

namespace app\index\middleware;

use think\facade\Db;
use think\facade\Request;
use think\facade\View;
use think\facade\Config;

class LoadConfig
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
        $res = Db::name('config')->select();

        $config = array();
        foreach ($res as $k => $v) {
            $key = $res[$k]['key'];
            $value = $res[$k]['value'];

            $config = array_merge($config, array($key => $value));
        }
        config::set($config, 'conf');
        config::set([
            'host' => Request::host()
        ], 'conf');
        // 继续执行进入到控制器
        return $next($request);
    }
}
