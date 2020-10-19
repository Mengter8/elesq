<?php
declare (strict_types=1);

namespace app\model;

use think\Model;

class Server extends Model
{
    protected $pk = 'id';

    public function getId($id)
    {
        $res = $this->where('id', '=', $id)
            ->find();
        if ($res) {
            return $res->toArray();
        } else {
            return false;
        }
    }

    /**
     * 获取所有服务器列表
     */
    public function getServerList()
    {
        return $this->field('id,name')
            ->where('status', '=', '1')
            ->select()
            ->toArray();
    }
}