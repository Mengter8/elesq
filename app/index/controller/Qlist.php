<?php
declare (strict_types=1);

namespace app\index\controller;

use app\model\Qq;
use app\model\Task;
use think\facade\Request;
use think\facade\View;

class Qlist
{
    private $res;
    private $uin;
    protected $request;

    protected $middleware = ['app\index\middleware\CheckLoginUser'];

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->initialize();
    }

    protected function initialize()
    {
        $this->uin = Request::param('uin');
        if ($this->uin) {
            $this->res = (new Qq())->findMyUin($this->uin);
            if (!$this->res) {
                abort(401, '请勿恶意操作');
            }
        }
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
        $ret = Qq::where('uin', '=', $this->uin)->delete();
        $ret = Task::where('uin', '=', $this->uin)->delete();
        if ($ret != 0) {
            return "<script>x.mclose();x.msg('删除成功');$('#uin{$this->uin}').remove();</script>";
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
        $task = Task::whereUin($this->uin)->select();
        View::assign([
            'uin' => $this->res['uin'],
            'pwd' => $this->res['pwd'],
            'nickname' => $this->res['nickname'],
            'status' => $this->res['status'],
            'skey' => $this->res['skey'],
            'pskey' => $this->res['pskey'],
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
