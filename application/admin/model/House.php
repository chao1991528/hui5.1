<?php

namespace app\admin\model;

use think\Model;

class House extends Model
{
    protected $table = 'kl_houses';

    public static function getResourceTypeList()
    {
        return [
            '1' => '个人',
            '2' => '中介'
        ];
    }

    public function getResourceTypeTextAttr($value, $data)
    {
        $list = self::getResourceTypeList();
        return !empty($list[$data['house_resource_type']]) ? $list[$data['house_resource_type']] : '-';
    }

    public static function getRentTypeList()
    {
        return [
            '1' => '合租',
            '2' => '整租'
        ];
    }

    public function getRentTypeTextAttr($value, $data)
    {
        $list = self::getRentTypeList();
        return !empty($list[$data['house_rent_type']]) ? $list[$data['house_rent_type']] : '-';
    }

    public static function getRoomTypeList()
    {
        return [
            '1' => '主卧',
            '2' => '次卧',
            '3' => '客厅',
            '4' => '其他'
        ];
    }

    public function getRoomTypeTextAttr($value, $data)
    {
        $list = self::getRoomTypeList();
        return !empty($list[$data['house_room_type']]) ? $list[$data['house_room_type']] : '-';
    }

    public static function getIsParlorResidentList()
    {
        return [
            '0' => '没有',
            '1' => '已有',
            '2' => '未选择'
        ];
    }

    public function getIsParlorResidentTextAttr($value, $data)
    {
        $list = self::getIsParlorResidentList();
        return !empty($list[$data['is_parlor_resident']]) ? $list[$data['is_parlor_resident']] : '-';
    }

    public static function getCanKeepPatList()
    {
        return [
            '0' => '否',
            '1' => '是'
        ];
    }

    public function getCanKeepPatTextAttr($value, $data)
    {
        $list = self::getCanKeepPatList();
        return !empty($list[$data['can_keep_pat']]) ? $list[$data['can_keep_pat']] : '-';
    }

    public static function getHaveSeparateBathroomList()
    {
        return [
            '0' => '否',
            '1' => '是'
        ];
    }

    public function getHaveSeparateBathroomTextAttr($value, $data)
    {
        $list = self::getHaveSeparateBathroomList();
        return !empty($list[$data['have_separate_bathroom']]) ? $list[$data['have_separate_bathroom']] : '-';
    }

    public static function getTenantGenderList()
    {
        return [
            1 => '不限',
            2 => '限男性',
            3 => '限女性'
        ];
    }

    public function getTenantGenderTextAttr($value, $data)
    {
        $list = self::getTenantGenderList();
        return !empty($list[$data['tenant_gender']]) ? $list[$data['tenant_gender']] : '-';
    }

    public function getAddTimeTextAttr($value, $data)
    {
        return !empty($data['add_time']) ? date('Y-m-d H:i:s', $data['add_time']) : '-';
    }

    public function getCanResideTimeTextAttr($value, $data)
    {
        return !empty($data['can_reside_time']) ? date('Y-m-d H:i:s', $data['can_reside_time']) : '';
    }

    public function getTopEndDateTextAttr($value, $data)
    {
        return !empty($data['top_end_date']) ? date('Y-m-d H:i:s', $data['top_end_date']) : '';
    }

    public function member()
    {
        return $this->belongsTo('Member', 'mem_id');
    }

    public function city()
    {
        return $this->belongsTo('City', 'house_resource_city_id');
    }

    public function kaolaAdmin()
    {
        return $this->belongsTo('KaolaAdmin', 'add_uid');
    }
}
