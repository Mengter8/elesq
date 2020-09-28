<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class Qq extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'qq' => ['require','number','length:5,10'],
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'qq.require'  =>  'QQ不能为空',
        'qq.number'  =>  '请输入正确的QQ',
        'qq.length'  =>  '请输入正确的QQ',
    ];
}
