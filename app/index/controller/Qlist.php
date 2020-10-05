<?php
declare (strict_types=1);

namespace app\index\controller;

use app\model\Qq;
use app\model\Task;
use think\facade\Request;
use think\facade\View;

class Qlist
{
    protected $request;

    protected $middleware = ['app\index\middleware\CheckLoginUser','app\index\middleware\CheckSafeChallenge'];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function index()
    {
        cookie('domin_url','/qlist/index');
        return autoTemplate();
    }

    public function listHtml()
    {
        $mod = Request::get('mod');
        $search = Request::get('search');
        $list = (new Qq)->getMyUin($mod,$search);
        if (Request::isMobile()){
            foreach ($list as $item=>$value){
                $ret = (new Task())->findTypeUin('auto',$value['uin']);
                $list[$item]['last_time']=$ret['last_time'];
            }
        } else {
            foreach ($list as $item=>$value){
                $ret = (new Task())->findTypeUin('zan',$value['uin']);
                $list[$item]['zan_status']=$ret['status'];
                $list[$item]['last_time']=$ret['last_time'];
            }
        }
        View::assign([
            'Qlist' => $list
        ]);
        return autoTemplate();
    }

    /**
     * 删除QQ
     */
    public function del()
    {
        $uin = Request::param('uin');
        $ret = Qq::where('uin', '=', $uin)->delete();
        $ret = Task::where('uin', '=', $uin)->delete();
        if ($ret != 0) {
            return "<script>x.mclose();x.msg('删除成功');$('#uin{$uin}').remove();</script>";
        } else {
            return json(["ret" => 0, "msg" => "删除失败！"]);
        }
    }

    /**
     * 功能设置
     */
    public function qset()
    {
        View::assign([
            'Qlist' => (new Qq)->getMyUin(),
        ]);
        return autoTemplate();
    }

    public function setHtml()
    {
        $uin = Request::param('uin');
        $res = (new Qq())->getByUin($uin);

        $task = Task::whereUin($uin)->select();
        View::assign([
            'uin' => $res['uin'],
            'pwd' => $res['pwd'],
            'nickname' => $res['nickname'],
            'status' => $res['status'],
            'skey' => $res['skey'],
            'pskey' => $res['pskey'],
            'task' => $task,
        ]);
        return autoTemplate();
    }

    public function setPwd(){
        $uin = Request::post('uin');
        $pwd = Request::post('pwd');
        $res = (new Qq)->getByUin($uin);
        if (!$res) return 'error';
        if (Request::isPost()) {
            $res->pwd = $pwd;
            $res->save();
            return "<script>x.mclose();x.msg('补充成功');</script>";
        }
    }
    /**
     * 补充密码
     * @return string
     */
    public function setPwdHtml()
    {
        $uin = Request::get('uin');
        $res = (new Qq)->getByUin($uin);

        View::assign([
            'uin' => $res['uin'],
            'nickname' => $res['nickname'],
        ]);
        return autoTemplate();
    }
}
