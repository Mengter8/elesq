<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;


class Mpz extends Model
{
    /**
     * @param int $uin 被赞的QQ
     * @param int $qq 数据库QQ
     * @param $type
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function signLog($uin,$qq,$type){
        $today = date('Y-m-d');
        $ret = $this->where('uin','=', $uin)
            ->where('type','=',$type)
            ->where('time','=', $today)
            ->find();
        if ($ret) {
            \think\facade\Db::query("UPDATE `ele_mpz` set `dataset` = concat(`dataset`,'{$qq},') WHERE `uin` = {$uin} AND `time` = '{$today}' AND `type` = '{$type}'");
        } else {
            //没有就创建
            $this->create([
                'uin'=>$uin,
                'type'=>$type,
                'dataset'=>$qq . ',',
                'time'=>$today
            ]);
        }
    }

    /**
     * 查询用户的dataset
     * @param $uin
     * @param $type
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getByDataset($uin,$type){
        $ret =  $this->where('uin','=',$uin)
            ->where('type','=',$type)
            ->where('time','=',date('Y-m-d'))
            ->find();
        if ($ret) {
            return $ret->dataset;
        } else {
            return false;
        }
    }
    public function BeTest(){
        $this->where('time','<', date('Y-m-d'))->delete();
    }
}
