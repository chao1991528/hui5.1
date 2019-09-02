<?php

namespace app\admin\controller;

use app\admin\model\KaolaAdmin;
use app\admin\model\Recruit as RecruitModel;

class Recruit extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $categories = $where = [];
        $params = input('get.');
        if (!empty($params['title'])) {
            $where[] = ['title', 'like', '%' . $params['title'] . '%'];
        }
        if (!empty($params['city_id'])) {
            $where[] = ['city_id', '=', $params['city_id']];
            $categories = model('RecruitIndustry')->where(['is_valid' => 1, 'is_delete' => 0, 'city_id' => $params['city_id']])->field('id,name')->select();
        }
        if (!empty($params['industry_id'])) {
            $where[] = ['industry_id', '=', $params['industry_id']];
        }
        if (isset($params['is_top']) && $params['is_top'] != -1) {
            $where[] = ['is_top', '=', $params['is_top']];
        }
        $list = RecruitModel::with([
            'member' => function ($query) {
                $query->field('id,nick_name');
            },
            'city' => function ($query) {
                $query->field('id,name_zh');
            },
            'industry' => function ($query) {
                $query->field('id,name');
            },
            'kaolaAdmin' => function ($query) {
                $query->field('id,user_number');
            }
        ])->where('is_delete', 0)->where($where)->order('id desc')->paginate(10);
        $this->assign([
            'citys' => model('City')->where('is_valid', 1)->field('id,name')->select(),
            'categories' => $categories,
            'isTop' => RecruitModel::getIsTopList(),
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
            $result = $this->validate($data, 'app\admin\validate\Recruit');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return json(['info' => '修改失败:' . $result, 'status' => 'n']);
            }
            $recruit = RecruitModel::where(['id' => $id, 'is_delete' => 0])->find();
            if (!$recruit) {
                return json(['info' => '修改失败:招聘信息不存在！', 'status' => 'n']);
            }
            $data['nature'] = !empty($data['nature']) ?  ',' . implode(',', $data['nature']) . ',' : '';
            $data['top_end_date'] = !empty($data['top_end_date']) ? strtotime($data['top_end_date']) : '';
            $imageArr = explode(',', $data['images']);
            $image200 = $image750 = '';
            foreach ($imageArr as $image) {
                $position = strrpos($image, '.');
                $image200 = $image200 ? $image200 . ',' . substr_replace($image, '_thumb_200', $position, 0) : substr_replace($image, '_thumb_200', $position, 0);
                $image750 = $image750 ? $image750 . ',' . substr_replace($image, '_thumb_750', $position, 0) : substr_replace($image, '_thumb_750', $position, 0);
            }
            $data['image_thumbs_200'] = $image200;
            $data['image_thumbs_750'] = $image750;
            $recruit->allowField(true)->save($data, ['id' => $id]);
            return json(['info' => '修改成功!', 'status' => 'y']);
        } else {
            $info = RecruitModel::where(['id' => $id, 'is_delete' => 0])->find();
            if (!$info) {
                $this->error('招聘信息不存在！');
            }
            $citys = model('City')->where('is_valid', 1)->field('id,name')->select();
            $districts = model('District')->where(['is_valid' => 1, 'city_id' => $info->city_id])->field('id,name')->select();
            $industries = model('RecruitIndustry')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,name')->select();
            $natures = RecruitModel::getNatureList();
            $genders = RecruitModel::getGenderList();
            $visas = RecruitModel::getVisaList();
            $educations = RecruitModel::getEducationList();
            $experiences = RecruitModel::getExperienceList();
            $isTop = RecruitModel::getIsTopList();
            $info->nature = trim($info->nature, ',');
            $info->imageList = explode(',', $info->images);
            $klAdmins = KaolaAdmin::where('is_valid', 1)->field('id,user_number')->select();
            return $this->fetch('', [
                'info' => $info,
                'citys' => $citys,
                'districts' => $districts,
                'industries' => $industries,
                'natures' => $natures,
                'genders' => $genders,
                'visas' => $visas,
                'educations' => $educations,
                'experiences' => $experiences,
                'isTop' => $isTop,
                'klAdmins' => $klAdmins,
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
        $recruit = RecruitModel::where(['is_delete' => 0, 'id' => $id])->find();
        if (!$recruit) {
            return json(['info' => '删除失败:二手信息不存在，无法删除！', 'status' => 'n']);
        }
        $recruit->is_delete = 1;
        $recruit->delete_time = time();
        $recruit->save();
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
        $recruit = db('recruit')->where('id', $id)->field('id,source_url', true)->find();
        if ($recruit) {
            if ($recruit['is_uploaded']) {
                return json(['info' => '上传失败:已经上传过了，请勿重复上传！', 'status' => 'n']);
            } else {
                unset($recruit['is_uploaded']);
            }
            $recruit['update_time'] = time();
        } else {
            return json(['info' => '上传失败:二手信息不存在', 'status' => 'n']);
        }
        $productRecruitModel = model('ProductRecruit');
        $productRecruitModel->save($recruit);
        db('recruit')->where('id', $id)->update(['is_uploaded' => 1]);
        return json(['info' => '上传成功！', 'status' => 'y']);
    }
}
