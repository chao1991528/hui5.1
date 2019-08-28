<?php

namespace app\admin\validate;

use think\Validate;

class Live extends Validate
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
        'category_id|分类' => 'require',
        'contact_person|联系人' => 'require',
        'company_name|公司名称' => 'require',
        'service_area|服务区域' => 'require',
        'images|图片' => 'require',
        'content|内容' => 'require'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [];


}
