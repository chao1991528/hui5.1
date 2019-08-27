<?php

namespace app\admin\model;

use think\Model;

class Market extends Model
{
    public static function getStatusList()
    {
        return [
            '0' => '下架',
            '1' => '正常',
            '2' => '被举报下架'
        ];
    }

    public static function getPropertyList()
    {
        return [
            '1' => '个人',
            '2' => '商家'
        ];
    }

    public static function getOldnewList()
    {
        return [
            '1' => '全新',
            '2' => '二手'
        ];
    }

    public static function getGuaranteeList()
    {
        return [
            '1' => '有效',
            '2' => '过保'
        ];
    }

    public static function getDealAreaList()
    {
        return [
            '1' => '同城',
            '2' => '全澳'
        ];
    }

    public static function getDeliveryList()
    {
        return [
            '1' => '不限',
            '2' => '自提',
            '3' => '面交',
            '4' => '送货'
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
        return $this->belongsTo('MarketCategory', 'category_id');
    }

    public function quanzi()
    {
        return $this->belongsTo('MarketQuanzi', 'quanzi_id');
    }

    public function kaolaAdmin()
    {
        return $this->belongsTo('KaolaAdmin', 'add_uid');
    }
}
