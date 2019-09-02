<?php

namespace app\admin\controller;

use app\admin\model\House as HouseModel;
use app\admin\model\KaolaAdmin;


class House extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $districts = $where = [];
        $params = input('get.');
        if (!empty($params['title'])) {
            $where[] = ['title', 'like', '%' . $params['title'] . '%'];
        }
        if (!empty($params['house_resource_type'])) {
            $where[] = ['house_resource_type', '=', $params['house_resource_type']];
        }
        if (!empty($params['house_rent_type'])) {
            $where[] = ['house_rent_type', '=', $params['house_rent_type']];
        }
        if (!empty($params['house_resource_city_id'])) {
            $where[] = ['house_resource_city_id', '=', $params['house_resource_city_id']];
            $districts = model('District')->where(['is_valid' => 1, 'city_id' => $params['house_resource_city_id']])->field('id,name')->select();
        }
        if (!empty($params['house_resource_districts_id'])) {
            $where[] = ['house_resource_districts_id', '=', $params['house_resource_districts_id']];
        }
        $list = HouseModel::with([
            'member' => function ($query) {
                $query->field('id,nick_name');
            },
            'city' => function ($query) {
                $query->field('id,name_zh');
            }
        ])->where('is_delete', 0)->where($where)->order('id desc')->paginate(10);
        $this->assign([
            'citys' => model('City')->where('is_valid', 1)->field('id,name')->select(),
            'districts' => $districts,
            'resourceTypes' => HouseModel::getResourceTypeList(),
            'rentTypes' => HouseModel::getRentTypeList(),
            'total' => $list->total(),
            'list' => $list
        ]);
        return $this->fetch();
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $result = $this->validate($data, 'app\admin\validate\House');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return json(['info' => '修改失败:' . $result, 'status' => 'n']);
            }
            $house = HouseModel::where(['id' => $id, 'is_delete' => 0])->find();
            if (!$house) {
                return json(['info' => '修改失败:房源信息不存在！', 'status' => 'n']);
            }
            $data['house_tag'] = !empty($data['house_tag']) ? ',' . implode(',', $data['house_tag']) . ',' : '';
            $data['house_config'] = !empty($data['house_config']) ? ',' . implode(',', $data['house_config']) . ',' : '';
            $data['top_end_date'] = !empty($data['top_end_date']) ? strtotime($data['top_end_date']) : '';
            $data['can_reside_time'] = !empty($data['can_reside_time']) ? strtotime($data['can_reside_time']) : 0;
            $imageArr = explode(',', $data['images']);
            $image200 = $image750 = '';
            foreach ($imageArr as $image) {
                $position = strrpos($image, '.');
                $image200 = $image200 ? $image200 . ',' . substr_replace($image, '_thumb_200', $position, 0) : substr_replace($image, '_thumb_200', $position, 0);
                $image750 = $image750 ? $image750 . ',' . substr_replace($image, '_thumb_750', $position, 0) : substr_replace($image, '_thumb_750', $position, 0);
            }
            $data['image_thumbs_200'] = $image200;
            $data['image_thumbs_750'] = $image750;
            $house->allowField(true)->save($data, ['id' => $id]);
            return json(['info' => '修改成功!', 'status' => 'y']);
        } else {
            $info = HouseModel::where(['id' => $id, 'is_delete' => 0])->find();
            if (!$info) {
                $this->error('房源信息不存在！');
            }
            $city_list = model('City')->where('is_valid', 1)->field('id,name,name_zh')->select();
            $district_list = model('District')->where(['is_valid' => 1, 'city_id' => $info->house_resource_city_id])->field('id,name')->select();
            $house_resource_type_list = HouseModel::getResourceTypeList();
            $house_rent_type_list = HouseModel::getRentTypeList();
            $house_room_type_list = HouseModel::getRoomTypeList();
            $have_separate_bathroom_list = HouseModel::getHaveSeparateBathroomList();
            $tenant_gender_list = HouseModel::getTenantGenderList();
            $is_parlor_resident_list = HouseModel::getIsParlorResidentList();
            $can_keep_pat_list = HouseModel::getCanKeepPatList();
            $tags = db('HouseTag')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,tag_name')->select();
            $configs = db('HouseConfig')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,config_name')->select();
            $house_type_list = db('HouseType')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,type_name')->select();
            $info->images = str_replace(['"', '[', ']', ' '], ['', '', '', ''], $info->images);
            $info->imageList = array_slice(explode(',', $info->images), 0 , 9);
            $info->images = implode(',', $info->imageList);
            $info->house_config = str_replace(['"', '[', ']', ' '], ['', '', '', ''], $info->house_config);
            $info->house_tag = str_replace(['"', '[', ']', ' '], ['', '', '', ''], $info->house_tag);
            $info->house_config = trim($info->house_config, ',');
            $info->house_tag = trim($info->house_tag, ',');
            $klAdmins = KaolaAdmin::where('is_valid', 1)->field('id,user_number')->select();
            $coupons = db('HouseCoupon')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,title')->select();
            return $this->fetch('', [
                'info' => $info,
                'city_list' => $city_list,
                'district_list' => $district_list,
                'house_resource_type_list' => $house_resource_type_list,
                'house_rent_type_list' => $house_rent_type_list,
                'house_room_type_list' => $house_room_type_list,
                'have_separate_bathroom_list' => $have_separate_bathroom_list,
                'tenant_gender_list' => $tenant_gender_list,
                'number_list' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                'is_parlor_resident_list' => $is_parlor_resident_list,
                'can_keep_pat_list' => $can_keep_pat_list,
                'tags' => $tags,
                'house_type_list' => $house_type_list,
                'klAdmins' => $klAdmins,
                'coupons' => $coupons,
                'configs' => $configs
            ]);
        }
    }


    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $house = HouseModel::where(['is_delete' => 0, 'id' => $id])->find();
        if (!$house) {
            return json(['info' => '删除失败:房源信息不存在，无法删除！', 'status' => 'n']);
        }
        $house->is_delete = 1;
        $house->delete_time = time();
        $house->save();
        return json(['info' => '删除成功', 'status' => 'y']);
    }

    /**
     * 上传到正式服务器
     */
    public function uploadToProduct($id)
    {
        if (empty($id)) {
            return json(['info' => '上传失败:id不能为空！', 'status' => 'n']);
        }
        $house = db('houses')->where('id', $id)->field('id,house_sn,update_time,delete_time,email_img,source_url', true)->find();
        if ($house) {
            if ($house['is_uploaded']) {
                return json(['info' => '上传失败:已经上传过了，请勿重复上传!', 'status' => 'n']);
            } else {
                unset($house['is_uploaded']);
            }
            $house['house_sn'] = 30 . date('YmdHis') . rand(1000, 9999);
            $house['add_time'] = time();
            $house['source_from'] = 2;
            $house['update_time'] = time();
        } else {
            return json(['info' => '上传失败:生活信息不存在', 'status' => 'n']);
        }
        $productHouseModel = model('ProductHouse');
        $productHouseModel->save($house);
        db('houses')->where('id', $id)->update(['is_uploaded' => 1]);
        return json(['info' => '上传成功！', 'status' => 'y']);
    }
}
