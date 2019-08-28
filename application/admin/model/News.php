<?php

namespace app\admin\model;

use think\Model;

class News extends Model
{

    public static function getIsTopList()
    {
        return [
            '0' => '否',
            '1' => '是'
        ];
    }

    public static function getDeclareList()
    {
        return [
            '1' => '编译声明',
            '2' => '转载声明',
            '3' => '原创声明'
        ];
    }

    public function getDeclareTextAttr($value, $data)
    {
        $list = self::getDeclareList();
        return !empty($list[$data['declare_id']]) ? $list[$data['declare_id']] : '-';
    }

    public function getAddTimeTextAttr($value, $data)
    {
        return !empty($data['add_time']) ? date('Y-m-d H:i:s', $data['add_time']) : '-';
    }

    public function getTopEndDateTextAttr($value, $data)
    {
        return !empty($data['top_end_date']) ? date('Y-m-d H:i:s', $data['top_end_date']) : '-';
    }

    public function category()
    {
        return $this->belongsTo('LiveCategory', 'category_id');
    }

    public function kaolaAdmin()
    {
        return $this->belongsTo('KaolaAdmin', 'add_uid');
    }

    public function source()
    {
        return $this->belongsTo('NewsSource', 'source_id');
    }

    public function layout()
    {
        return $this->belongsTo('NewsLayout', 'layout_id');
    }

    public function type()
    {
        return $this->belongsTo('NewsType', 'type_id', 'id', '', 'left')->setEagerlyType(0);
    }
}
