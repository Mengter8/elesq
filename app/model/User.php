<?php
declare (strict_types=1);

namespace app\model;

use think\facade\Session;
use think\Model;

class User extends Model
{
    protected $pk = 'uid';

    /**
     * 更新上次登录信息
     * @param $ret
     */
    public function updateLastLogin($ret)
    {
        $ret->last_ip = get_client_ip();
        $ret->last_time = time();
        $ret->save();
    }

    public function getLastUser()
    {
        $res = $this->where('qq', '<>', '')->order('reg_time', 'desc')->limit(18)->select();
        return $res;
    }

    /**
     * 增加用户会员时间
     * @param int $uid
     * @param int $day 会员天数
     * @param string $name 事件来源
     */
    public function addUserVipTime($uid, $day, $name)
    {
        $times = $day * 24 * 60 * 60;
        if ($this->isVip($uid)) {
            $this->where('uid', '=', $uid)
                ->inc('vip_end_time', $times)
                ->update();
        } else {
            $this->where('uid', '=', $uid)
                ->update(['vip_end_time' => time() + $times]);
        }
        (new Log())->createLog($uid, $day, $name, 'vip', 1);
    }

    /**
     * 判断是否为VIP
     * @param int $uid
     * @return bool
     */
    public function isVip($uid)
    {
        $res = $this->where('uid', '=', $uid)->find();
        if ($res) {
            if (time() <= $res->vip_end_time) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 增加用户余额
     * @param $uid
     * @param float $money
     * @param string $name 事件名称
     */
    public function addUserMoney($uid, $money,$name)
    {
        $this->where('uid', '=', $uid)
            ->inc('money', (float)$money)
            ->update();
        (new Log())->createLog($uid, $money * 100, $name, 'money', 1);
        $this->updateMyInfo();

    }

    /**
     * 减少用户余额
     * @param $uid
     * @param float $money
     * @param string $name 事件名称
     */
    public function decUserMoney($uid, $money,$name)
    {
        $this->where('uid', '=', $uid)
            ->dec('money', (float)$money)
            ->update();
        (new Log())->createLog($uid, $money * 100, $name, 'money', 0);

    }

    /**
     * 开通代理
     * @param $uid
     * @param int $level 代理等级
     */
    public function addUserAgent($uid, $level)
    {
        $this->where('uid', '=', $uid)
            ->update(['agent' => $level]);
    }

    /**
     * 增加我的用户积分
     * @param int $uid
     * @param int $score 积分数量
     * @param string $name
     */
    public function addUserScore($uid, $score, $name)
    {
        $this->where('uid', '=', $uid)
            ->inc('score', $score)
            ->update();
        (new Log())->createLog($uid, $score, $name, 'score', 1);
        $this->updateMyInfo();
    }

    /**
     * 减少用户积分
     * @param int $uid
     * @param int $score 积分数量
     * @param string $class 类型
     */
    public function decUserScore($uid, $score, $name)
    {
        $this->where('uid', '=', $uid)
            ->dec('score', $score)
            ->update();
        (new Log())->createLog($uid, $score, $name, 'score', 0);
        $this->updateMyInfo();
    }

    /**
     * 获取用户邀请数量
     */
    public function getInviteCount()
    {
        return $this->where('tid', '=', session::get('user.uid'))->count();
    }

    /**
     * 获取邀请用户QQ数量
     */
    public function getInviteQQCount()
    {
        //获取推广ID是自己的
        $ret = $this->where('tid', '=', session::get('user.uid'))->select();
//        dump($this->getLastSql());
        $qq = new Qq();
        //循环统计
        foreach ($ret as $key => $value) {
            if ($key == 0) {
                $qq = $qq->where('uid', '=', $value['uid']);
            } else {
                $qq = $qq->whereOr('uid', '=', $value['uid']);
            }
        }
        if ($ret->count()) {
            return $qq->count();
        } else {
            return 0;
        }

//        dump((new Qq())->getLastSql());
//        dump($count);
    }


    /**
     * 获取邀请日志
     * @param $listRows string 每页数量
     * @param $isMyLog
     * @return \think\Paginator
     */
    public function getInviteLog($listRows, $isMyLog = false)
    {
        $sql = $this->alias('user')
            ->join('user invite', 'invite.tid = user.uid')
            ->field([
                'invite.nickname' => 'inv_nickname',
                'invite.uid',
                'invite.reg_time',
                'user.nickname',
                'user.qq'
            ])
            ->order('reg_time', 'desc');
        if ($isMyLog) {
            $sql = $sql->where('user.uid', '=', session::get('user.uid'));
        }
//        dump($this->getLastSql());
        return $sql->paginate($listRows);


//        $ret= $this->hasWhere('User')
//            ->where('invite.tid','<>','0')
//            ->tableField('nickname,qq', 'User')
//            ->order('reg_time', 'desc')
//            ->paginate($listRows);
    }

    /**
     * 更新用户的SESSION缓存
     */
    public function updateMyInfo()
    {
        $ret = $this->getByUid(session::get('user.uid'));
        if ($ret) {
            session::set('user', $ret->toArray());
        }
    }

    /**
     * 获取积分排行榜
     */
    public function getScoreList()
    {
        return $this->order('score', 'desc')
            ->select();
    }

    /**
     * 查询代理信息
     * @param $uin
     * @return bool|mixed|string[]
     */
    public function getAgentInfo($uin)
    {
        $self = new static();
        $res = $self->where('qq', '=', $uin)->find();
        if ($res) {
            return findAgentLevel($res->agent);
        } else {
            return false;
        }
    }
}
