<?php
declare (strict_types=1);

namespace app\model;

use think\facade\Session;
use think\Model;

class Sign extends Model
{
    protected $pk = 'uid';

    /**
     * @param $type string 中奖类型
     * @param $value string 中奖天数
     */
    public function createSign($type, $value)
    {
        $this->create([
            'uid' => session::get('user.uid'),
            'day' => $this->getUidSignDay(),
            'type' => $type,
            'value' => $value,
            'sign_time' => time(),
        ]);
    }

    /**
     * 判断用户今天是否签到
     */
    public function isTodaySign()
    {
        $res = $this->where('uid', '=', session::get('user.uid'))
            ->where('sign_time', '>=', getTodayTimestamp())
            ->find();
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取用户连续签到天数
     */
    public function getUidSignDay()
    {
        $res = $this->where('uid', '=', session::get('user.uid'))
            ->where('sign_time', '>=', getYesterdayTimestamp())
            ->order('sign_time', 'desc')
            ->find();
        if ($res) {
            return $res['day'] + 1;
        } else {
            return 1;
        }
    }

    /**
     * 获取用户签到次数
     */
    public function getUidSignCount()
    {
        return $this->where('uid', '=', session::get('user.uid'))->count();
    }

    /**
     * 获取用户中奖天数
     */
    public function getUidDayCount()
    {
        $ret = $this->where('uid', '=', session::get('user.uid'))
            ->where('type','=',1)
            ->select();
        $nub = 0;
        if ($ret) {
            $arr = $ret->toArray();
            foreach ($arr as $value) {
                $nub += $value['value'];
            }
            return $nub;
        } else {
            return 0;
        }
    }

    public function User()
    {
        return $this->hasOne(User::class, 'uid');
    }

    /**
     * 获取签到日志
     * @param string $listRows 每页数量
     * @param $isMyLog
     */
    public function getSignLog($listRows,$isMyLog)
    {
        $sql = $this->hasWhere('User')
//            ->where('value', '<>', '0')
            ->tableField('nickname,qq', 'User')
            ->order('sign_time', 'desc');
        if ($isMyLog) {
            $sql = $sql->where('user.uid', '=', session::get('user.uid'));
        }
        return $sql->paginate($listRows);
    }
}
