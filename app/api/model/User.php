<?php
declare (strict_types = 1);

namespace app\api\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class User extends Model
{
    public function getUserInfo($uin){
        $res = $this->where('qq','=',$uin)
            ->field('uid,nickname,money,agent,reg_time,last_time,vip_start_time,vip_end_time')
            ->find()
            ->toArray();
        if ($res['agent'] == 0){
            $res['agent'] = '非代理';
        }else {
            $res['agent'] = findAgentLevel($res['agent'])['name'];
        }
        return $res;
    }
}
