<?php
declare (strict_types=1);

namespace app\model;

use qq\login;
use think\Model;

/**
 * @mixin think\Model
 */
class Qq extends Model
{
    protected $pk = 'uin';

    /**
     * 获取点赞列表
     */
    public function getZanList($uin, $type)
    {
        $notlist = (new \app\model\Mpz)->getByDataset($uin, $type);
        $list = $this->where('status', '=', 1)
            ->whereNotIn('uin', $notlist . $uin)
            ->limit(12)
            ->select()
            ->toArray();
        return $list;
    }

    /**
     * 获取自己QQ列表
     * @param int $mod
     * @param null $search
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMyUin($mod = 0, $search = NULL)
    {
        $uid = session('user.uid');
        if ($mod == 1) {
            $status = 1;
        } elseif ($mod == 2) {
            $status = 0;
        }
        $sql = $this->where('uid', '=', $uid);
        if (isset($status)) {
            $sql = $sql->where('status', '=', $status);
        }
        if (!is_null($search)) {
            $sql = $sql->where('uin', 'like', "%{$search}%");
        }
        return $sql->select()
            ->toArray();
    }

    /**
     * 是否是自己的QQ
     */
    public function findMyUin($uin)
    {
        $res = $this->where('uin', '=', $uin)->where('uid','=',session('user.uid'))->find();
        if ($res) {
            return $res;
        } else {
            return false;
        }

    }

    /**
     * 设置QQ状态 默认失效
     * @param int $uin 用户QQ
     * @param int $status 状态 0失效 1正常
     * @return Qq
     */
    public function setStatus($uin, $status = 0)
    {
        return $this->where('uin', '=', $uin)->update(['status' => $status]);
//        Qq::update(['status' => 0], ['uin' => $qq['uin']]);
    }

    /**
     * @param $uid
     * @param $sid
     * @param $uin
     * @param $pwd
     * @param $skey
     * @param $pskey
     * @param $superkey
     * @return \think\response\Json
     */
    public function add($uid,$sid,$uin,$pwd,$skey,$pskey,$superkey)
    {
        //验证CK有效性
        $login = new login($uin,$skey,$pskey,$superkey);
        if ($login->checkLogin() == false){
            return resultJson(0, '登录信息验证失败');
        }
        if (!$sid){
            return resultJson(0, '未设置保存节点');
        }
        $task = new Task();
        $res = $this->getByUin($uin);
        if (!$uid) {
            //不存在UID 则使用数据库UID
            $uid = isset($res['uid']) ?  $res['uid']: '';
        }
        if (!$pwd) {
            //不存在密码 则使用数据库密码
            $pwd = isset($res['pwd']) ?  $res['pwd']: '';
        }
        $data = [
            'uid' => $uid,
            'sid' => $sid,
            'uin' => $uin,
            'pwd' => $pwd,
            'nickname' => getQqNickname($uin),
            'skey' => $skey,
            'pskey' => $pskey,
            'superkey' =>$superkey,
            'status' => 1,
            'fail' => 0,
        ];
        if ($res) {
            if ($res['fail'] == 1) {
                //如果[自动更新]状态失效 连续秒赞重新计算
                $data['update_time'] = time();
            }
            $this->update($data, ['uin' => $uin]);
            return resultJson(1, '更新成功');
        } else {
            $data['update_time'] = time();
            $this->create($data);
            //创建自动更新任务
            $task->createTask($uin, 'auto',array());
            return resultJson(1, '添加成功');
        }
    }

    /**
     * 删除
     * @param $id
     */
    public function del($id)
    {
        $this->where('id', '=',$id)->delete();
    }

    public function getByUin($uin)
    {
        return $this->where('uin', '=', $uin)
            ->find();
    }

    public function Server()
    {
        return $this->hasOne(Server::class, 'id','sid');
    }

    /**
     * 查询自己QQ所在的服务器草
     */
    public function queryUinForServer($uin)
    {
        $res = $this->hasWhere('Server')
            ->where('uin','=',$uin)
            ->where('uid','=',session('user.uid'))
            ->tableField('name,api', 'Server')
            ->find();
        if ($res){
            return $res->toArray();
        } else {
            return false;
        }
    }
}
