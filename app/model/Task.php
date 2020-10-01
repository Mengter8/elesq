<?php
declare (strict_types=1);

namespace app\model;

use think\Model;

class Task extends Model
{
    protected $pk = 'uin';

    protected $type = [
        'dataset' => 'json',
    ];


//    public function getCreateTimeAttr($time)
//    {
//        return $time;//返回create_time原始数据，不进行时间戳转换。
//    }
    public function hasQq()
    {
        return $this->hasOne(Qq::class, 'uin');
    }

    /**
     * 查询 qq状态正常 且指定任务
     * @param $type
     */
    public function getAllTask($type)
    {
        $this->type = $type;
        $list = $this->hasWhere('hasQq', function ($query) {
            $query->where('status', '=', '1');
            if (findTask($this->type)['vip'] == true) {
//                if ((new User())->isVip())
//                $query->where('vip_end_time', '<=', time());
            }
        }, 'uin,dataset,last_time,next_time')
            ->where('Task.type', '=', $type)
            ->where('Task.status', '=', 1)
            ->where('Task.next_time', '<=', time())
            ->tableField('skey,pskey,superkey,status', 'Qq')
            ->select();
//        dump($this->getLastSql());
        return $list;
    }

    /**
     * 查询自动更新任务
     */
    public function getAutoUpdateTask()
    {
        $list = $this->hasWhere('hasQq', ['status' => 0], 'uin,dataset,last_time,next_time')
            ->where('Task.type', '=', 'auto')
            ->where('Task.status', '=', 1)
            ->where('Task.next_time', '<=', time())
            ->where('Qq.fail', '=', 0)//---fail三次不查
            ->tableField('pwd,sid,skey,pskey,status', 'Qq')
            ->select();
//        dump($list->toArray());
        return $list;
    }

    /**
     * 获取任务数据
     */
    public function getTaskData($type, $uin)
    {
        $ret = $this->where('type', '=', $type)
            ->where('uin', '=', $uin)
            ->find();
        if (empty($ret)) {
            return false;
        } else {
            return $ret->toArray();
        }
    }

    /**
     * 保存任务数据
     */
    public function setTaskData($type, $uin, $dataset, $status)
    {
        dump($type);
        dump($uin);
        dump(json_encode($dataset));
        $ret = $this->where('type', '=', $type)
            ->where('uin', '=', $uin)
            ->update([
                'dataset' => json_encode($dataset),
                'status' => $status
            ]);

        return $ret;
    }

    /**
     * 设置最后一次执行时间 && 下次执行时间
     * @param $uin
     * @param $type
     * @return Task|string
     */
    public function setTaskTime($uin, $type)
    {
        foreach (getTask() as $value) {
            if ($value['id'] == $type) {
                if (isset($value['second'])) {
                    $sec = $value['second'];
                } elseif (isset($value['minute'])) {
                    $sec = $value['minute'] * 60;
                } elseif (isset($value['hours'])) {
                    if ($value['hours'] == 24) {
                        $sec = strtotime(date("Y-m-d 00:00:00", strtotime("+1 day"))) - time();
                    } else {
                        $sec = $value['hours'] * 60 * 60;
                    }
                }
            }
        }
        if (isset($sec)) {
            return $this->where('uin', '=', $uin)
                ->where('type', '=', $type)
                ->update([
                    'last_time' => time(),
                    'next_time' => time() + $sec
                ]);
        } else {
            return '没有这个任务';
        }
    }

    /**
     * 获取最新秒赞QQ
     */
    public function getLastZan()
    {
        $res = $this->where('type', '=', 'zan')
            ->where('status', '=', 1)
            ->order('last_time', 'desc')
            ->limit(12)
            ->select();
        return $res;
    }

    /**
     * 查询 qq状态正常 且指定任务
     */
    public function getZanWall()
    {
        $list = $this->hasWhere('hasQq', ['status' => 1], 'uin,dataset,create_time,last_time')
            ->where('Task.type', '=', 'zan')
            ->where('Task.status', '=', 1)
            ->tableField('nickname,update_time', 'Qq')
            ->select()
            ->toArray();
        return $list;
    }

    public function findTypeUin($type, $uin)
    {
        $ret = $this->where('type', '=', $type)
            ->where('uin', '=', $uin)
            ->find();
        if ($ret) {
            return $ret->toArray();
        } else {
            return false;
        }
    }

    /**
     * 删除任务
     * @param string|null $type 删除的type字段 可为空
     * @param int $uin 删除的qq
     */
    public function DeleteTask($type, $uin)
    {
        $ret = $this->where('uin', '=', $uin);
        if ($type){
            $ret->where('type', '=', $type);
        }
        $ret->delete();
    }

    /**
     * 创建任务
     * @param int $uin 任务QQ
     * @param string $type 任务类型
     * @param array $dataset 数据集
     */
    public function createTask($uin, $type,$dataset = array())
    {
        $this->create([
            'uin' => $uin,
            'type' => $type,
            'status' => 1,
            'dataset' => $dataset,
            'last_time' => time(),
            'next_time' => time(),
        ]);
    }
}
