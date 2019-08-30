<?php

namespace app\admin\validate;

use think\Validate;

class Recruit extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'mem_id|会员id' => 'require',
        'company_name|公司名称' => 'require',
        'title|标题' => 'require',
        'city_id|所属城市' => 'require',
        'district_id|所属区域' => 'require',
        'email|邮箱' => 'require',
        'contact_person|联系人' => 'require',
        'nature|工作性质' => 'require',
        'address|工作地址' => 'require',
        'introduction|招聘简介' => 'require',
        'content|招聘内容' => 'require'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [];
}
