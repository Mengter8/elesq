<?php
declare (strict_types=1);

namespace app\api\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Qq extends Model
{

    public function User()
    {
        return $this->hasOne(\app\model\User::class, 'uid', 'uid');
    }

    public function getMyList($uin)
    {
        $list = $this->hasWhere('User', ['qq' => $uin], 'uin,nickname,status,update_time')
            ->select()->toArray();
        return $list;
    }

    public function Server()
    {
        return $this->hasOne(\app\model\Server::class, 'id', 'sid');

    }

    public function getUinStatus($uin)
    {
        $res = $this->hasWhere('Server', '', 'uin,nickname,status,update_time')
            ->where('uin', '=', $uin)
            ->tableField('name', 'Server')
            ->find();
        if ($res) {
            return $res->toArray();
        } else {
            return false;
        }
    }
}
