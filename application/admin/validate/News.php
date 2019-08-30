<?php

namespace app\admin\validate;

use think\Validate;

class News extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'news_title|标题' => 'require',
	    'source_id|新闻来源' => 'require',
	    'type_id|类型' => 'require',
	    'layout_id|布局' => 'require',
	    'news_picture|图片' => 'require',
	    'content|内容' => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [];
}
