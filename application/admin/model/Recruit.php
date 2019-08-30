<?php

namespace app\admin\model;

use think\Model;

class Recruit extends Model
{
    public static function getIsTopList()
    {
        return [
            '0' => '否',
            '1' => '是'
        ];
    }

    public static function getNatureList()
    {
        return [
            '0' => '不限',
            '1' => '全职',
            '2' => '兼职',
            '3' => '实习',
            '4' => '合同工',
            '5' => 'Casual'
        ];
    }

    public static function getGenderList()
    {
        return [
            '0' => '不限',
            '1' => '限男性',
            '2' => '限女性'
        ];
    }

    public static function getExperienceList()
    {
        return [
            '1' => '需要经验',
            '2' => '经验不限'
        ];
    }

    public static function getVisaList()
    {
        return [
            '1' => '学生签证',
            '2' => '工作签证',
            '3' => '永居签证',
            '4' => '澳洲国籍',
            '5' => '打工度假签证',
            '6' => '其他签证'
        ];
    }

    public static function getEducationList()
    {
        return [
            '0' => '不限',
            '1' => 'PHD',
            '2' => 'Master',
            '3' => 'Bachelor',
            '4' => 'Diploma',
            '5' => 'High school',
            '6' => 'Foundation'
        ];
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

    public function industry()
    {
        return $this->belongsTo('RecruitIndustry', 'industry_id');
    }

    public function kaolaAdmin()
    {
        return $this->belongsTo('KaolaAdmin', 'add_uid');
    }
}
