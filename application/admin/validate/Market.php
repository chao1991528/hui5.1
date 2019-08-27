<?php

namespace app\admin\validate;

use think\Validate;

class Market extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'title|标题' => 'require',
        'mem_id|会员id' => 'require',
        'city_id|城市' => 'require',
        'district_id|区域' => 'require',
        'content|内容' => 'require',
        'contact_person|联系人' => 'require'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [];


}
