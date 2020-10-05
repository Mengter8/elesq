<?php
declare (strict_types=1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Chat extends Model
{

    public function User()
    {
        return $this->hasOne(User::class, 'uid', 'uid');
    }

    /**
     * 查询所有聊天内容
     */
    public function queryAllChat()
    {
        $ret = $this->hasWhere('User')
            ->tableField('*', 'User')
            ->select();
        if ($ret) {
            return $ret->toArray();
        } else {
            return false;
        }
    }

    /**
     * 聊天室加入内容
     * @param $uid
     * @param $content
     */
    public function createChat($uid, $content)
    {
        return $this->create([
            'uid' => $uid,
            'content' => $content,
            'time' => time(),
        ]);
    }

    /**
     * 查询用户信息
     * @param string $token
     * @return false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserInfo($token = '')
    {
        $user = new User();
        $ret = $user->where('openid', '=', $token)->find();

        if ($ret) {
            $vipInfo = getVipLevel($ret['vip_start_time'], $ret['vip_end_time'], $ret['agent']);
            $info['level'] = $vipInfo['title'];
            $info['color'] = $vipInfo['chat_color'];

            $info['uid'] = $ret['uid'];
            $info['nickname'] = $ret['nickname'];
            $info['qq'] = $ret['qq'];
            return $info;
        } else {
            return false;
        }
    }
}
