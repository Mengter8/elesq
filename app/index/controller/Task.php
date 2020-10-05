<?php
declare (strict_types=1);

namespace app\index\controller;

use think\facade\Request;
use think\facade\View;

class Task
{
    /**
     * @var Request
     */

    protected $middleware = ['app\index\middleware\CheckLoginUser','app\index\middleware\CheckSafeChallenge'];


    /**
     * 执行任务pjax
     */
    public function runTask()
    {
        $type = Request::get('type');

        foreach (getTask() as $value) {
            if ($value['id'] == $type) {
                $title = $value['name'];
                break;
            }
        }
        View::assign([
            'title' => $title,
            'type' => $type,
            'uin' => Request::get('uin'),
        ]);
        if (Request::isMobile()) {
            return View::fetch('mobile/task/run_task');
        } else {
            return View::fetch('pc/task/run_task');
        }
    }

    /**
     * 获取任务列表
     */
    public function getTaskList()
    {
        $uin = Request::get('uin');
        $task = (new \app\model\Task())
            ->Field('type')
            ->whereUin($uin)->select()->toArray();
        foreach ($task as $key => $value) {
            $arr[$value['type']] = NULL;
        }
        $arr['auto'] = NULL;
        View::assign([
            'task' => $arr,
            'uin' => $uin,
        ]);
        if (Request::isMobile()) {
            return View::fetch('mobile/task/get_task_list');
        } else {
            return View::fetch('pc/task/get_task_list');
        }
    }

    /**
     * 添加任务
     */
    public function addTask()
    {
        $uin = Request::post('uin');
        $type = Request::post('type');

        $res = (new \app\model\Task)
            ->whereType($type)
            ->whereUin($uin)
            ->find();
        if ($res and $res->ToArray()) {
            return '<script>x.msg(\'请不要重复添加\');</script>';
        }
        \app\model\Task::create([
            'uin' => $uin,
            'type' => $type,
            'dataset' => '',
            'create_time' => time(),
            'last_time' => time(),
            'next_time' => time()
        ]);
        if (Request::isMobile()) {
            return "<script>x.msg('添加成功');qqset_mode_load();$(\".{$type}access\").html(\"已添加\");</script>";
        } else {
            return "<script>x.msg('添加成功');load_qqset_html({$uin},1);$(\".{$type}access\").html(\"已添加\");</script>";
        }

    }

    /**
     * 设置任务 HTML
     */
    public function setTask()
    {
        $uin = request::param('uin');
        $type = Request::param('type');
        $res = (new \app\model\Task)->getTaskData($type, $uin);

        if (!$res) {
            //未添加
            return 'error';
        }
        View::assign([
            'status' => $res['status'],
            'data' => $res['dataset'],
            'uin' => $uin
        ]);

        if (Request::isMobile()) {
            View::config(['view_dir_name' => 'view' . DIRECTORY_SEPARATOR . 'mobile']);
            return View::fetch($type);
        } else {
            View::config(['view_dir_name' => 'view' . DIRECTORY_SEPARATOR . 'pc']);
            return View::fetch($type);
        }
    }

    /**
     * 设置dataset
     */
    public function setTaskData()
    {
        $uin = request::post('uin');
        $type = Request::post('type');
        $status = Request::post('status');
        if ($type == 'zan') {
            $dataset = Request::post(['server', 'mode', 'qqlist']);//服务器
        } elseif ($type == 'qunqd') {
            $dataset = Request::post(['tid', 'site', 'content', 'mode', 'qunlist']);//服务器
        } else {
            $dataset = NULL;
        }
        $ret = (new \app\model\Task)->setTaskData($type, $uin, $dataset, $status);
        if ($ret) {
            if ($dataset == NULL) {
                if ($status != 0) {
                    return "<script>x.msg('功能已开启');$(\"#is{$type} input\").prop(\"checked\", true);$(\"#is{$type} .icon-dian\").removeClass(\"hong\").addClass(\"lv\");</script>";
                } else {
                    return "<script>x.msg('功能已关闭');$(\"#is{$type} input\").prop(\"checked\", false);$(\"#is{$type} .icon-dian\").removeClass(\"lv\").addClass(\"hong\");</script>";
                }
            } else {
                if ($status != 0) {
                    return "<script>x.mclose();x.msg('保存成功');$(\"#is{$type} input\").prop(\"checked\", true);$(\"#is{$type} .icon-dian\").removeClass(\"hong\").addClass(\"lv\");</script>";
                } else {
                    return "<script>x.mclose();x.msg('保存成功');$(\"#is{$type} input\").prop(\"checked\", false);$(\"#is{$type} .icon-dian\").removeClass(\"lv\").addClass(\"hong\");</script>";
                }
            }
        } else {
            return '<script>x.mclose();x.msg(\'保存失败\');</script>';
        }
    }

    /**
     * 删除任务
     * @return string
     */
    public function delTask()
    {
        $uin = Request::post('uin');
        $type = Request::post('type');

        if ((new \app\model\Task())->DeleteTask($uin, $type) == 1) {
            return "<script>x.msg('删除成功');$('#is{$type}').remove();</script>";
        } else {
            return "<script>x.msg('删除失败');$('#is{$type}').remove();</script>";
        }
    }
}
