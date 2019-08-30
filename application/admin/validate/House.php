<?php

namespace app\admin\validate;

use think\Validate;

class House extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'title|标题' => 'require',
        'house_rent_type|出租类型' => 'require',
        'mobile|手机号' => 'require',
        'contact_person|联系人' => 'require',
        'house_resource_city_id|城市' => 'require',
        'house_resource_districts_id|所在区域' => 'require',
        'house_resource_address|房源位置' => 'require',
        'house_resource_longitude|房源经度' => 'require',
        'house_resource_latitude|房源纬度' => 'require',
        'mem_id|会员id' => 'require',
        'house_tag|房屋标签' => 'require|checkCount',
        'house_config|房屋配置' => 'require',
        'house_type_id|户型' => 'require',
        'images|图片' => 'require',
        'content|内容' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [];

    // 自定义验证规则
    protected function checkCount($value, $rule, $data = [])
    {
        return count($value) > 4 ? '房屋标签不能超过4个' : true;
    }
}
