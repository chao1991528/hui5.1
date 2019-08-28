<?php

namespace app\admin\model;

use think\Model;

class Live extends Model
{
    public static function getStatusList()
    {
        return [
            '0' => '下架',
            '1' => '正常',
            '2' => '被举报下架'
        ];
    }

    public static function getIsTopList()
    {
        return [
            '0' => '否',
            '1' => '是'
        ];
    }

    public function getStatusTextAttr($value, $data)
    {
        $status = self::getStatusList();
        return !empty($status[$data['status']]) ? $status[$data['status']] : '-';
    }

    public function getAddTimeTextAttr($value, $data)
    {
        return !empty($data['add_time']) ? date('Y-m-d H:i:s', $data['add_time']) : '-';
    }

    public function getTopEndDateTextAttr($value, $data)
    {
        return !empty($data['top_end_date']) ? date('Y-m-d H:i:s', $data['top_end_date']) : '';
    }

    public function getTopTimeTextAttr($value, $data)
    {
        return !empty($data['update_time']) ? date('Y-m-d H:i:s', $data['update_time']) : '-';
    }

    public function member()
    {
        return $this->belongsTo('Member', 'mem_id');
    }

    public function city()
    {
        return $this->belongsTo('City', 'city_id');
    }

    public function category()
    {
        return $this->belongsTo('LiveCategory', 'category_id');
    }

    public function kaolaAdmin()
    {
        return $this->belongsTo('KaolaAdmin', 'add_uid');
    }
}
