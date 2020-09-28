<?php
declare (strict_types=1);

namespace app\model;

use think\facade\Session;
use think\Model;

/**
 * @mixin think\Model
 */
class log extends Model
{
    protected $pk = 'uid';

    /**
     * @param int $uid 用户ID
     * @param int $score 变更数量
     * @param string $name 中文简介
     * @param string $class 日志分类
     * @param int $type 1增加/0减少
     */
    public function createLog($uid,$score,$name, $class, $type)
    {
        $this->create([
            'uid' => $uid,
            'class' => $class,
            'type' => $type,
            'score' => $score,
            'name'=>$name,
            'create_time' => time(),
        ]);
    }

    /**
     * 获取积分日志
     * @param string $listRows vip/score
     * @param int $type 增加或减少
     */
    public function getScoreLog($listRows,$type){
         $sql = $this->where('class','=','score');
         if ($type == 1){
             $sql = $sql->where('type','=',1);
         } elseif($type ==2){
             $sql = $sql->where('type','=',0);
         }

         return $sql->order('create_time','desc')->paginate($listRows);
    }
    public function User()
    {
        return $this->hasOne(User::class, 'uid');
    }

    /**
     * 获取兑换记录
     * @param string $listRows 每页数量
     * @param bool $isMyLog 是否是自己的日志
     */
    public function getRedeemLog($listRows,$isMyLog)
    {
        $sql = $this->hasWhere('User')
            ->where('class','=','redeem')
            ->tableField('nickname,qq', 'User')
            ->order('create_time', 'desc');
        if ($isMyLog) {
            $sql = $sql->where('User.uid', '=', session::get('user.uid'));
        }
        return $sql->paginate($listRows);
    }

    /**
     * 获取VIP日志
     * @param $listRows
     */
    public function getVipLog($listRows){
        return $this->where('class','=','vip')
            ->where('uid', '=', session::get('user.uid'))
            ->order('create_time', 'desc')
            ->paginate($listRows);
    }

    public function getMoneyLog($listRows,$type){
        $sql = $this->where('class','=','money');
        if ($type == 1){
            $sql = $sql->where('type','=',1);
        } elseif($type ==2){
            $sql = $sql->where('type','=',0);
        }

        return $sql->where('uid', '=', session::get('user.uid'))
            ->order('create_time', 'desc')
            ->paginate($listRows);
    }
}
