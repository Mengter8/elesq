<?php
declare (strict_types=1);

namespace app\index\middleware;

use app\model\Qq;
use think\facade\Request;

class CheckSafeChallenge
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
        // 判断是否自己的QQ
        $this->uin = Request::param('uin');
        if ($this->uin) {
            $this->res = (new Qq())->findMyUin($this->uin);
            if (!$this->res) {
                //不是自己的QQ
                exit(':-) SafeChallenge');
            }
        }

        //判断task任务是否存在
        $this->type = Request::param('type');
        if ($this->type) {
            if (!findTask($this->type)) {
                abort(401, '请勿恶意操作');
            }
        }
        // 继续执行进入到控制器
        return $next($request);
    }
}
