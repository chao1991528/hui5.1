<?php

namespace app\admin\controller;

use app\admin\model\KaolaAdmin;
use app\admin\model\Market as MarketModel;

class Market extends Base
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
            $categories = model('MarketCategory')->where(['is_valid' => 1, 'is_delete' => 0, 'city_id' => $params['city_id']])->field('id,category_name')->select();
        }
        if (!empty($params['category_id'])) {
            $where[] = ['category_id', '=', $params['category_id']];
        }
        if (isset($params['status']) && $params['status'] != -1) {
            $where[] = ['status', '=', $params['status']];
        }
        if (isset($params['is_top']) && $params['is_top'] != -1) {
            $where[] = ['is_top', '=', $params['is_top']];
        }
        $list = MarketModel::with([
            'member' => function ($query) {
                $query->field('id,nick_name');
            },
            'city' => function ($query) {
                $query->field('id,name_zh');
            },
            'category' => function ($query) {
                $query->field('id,category_name');
            },
            'quanzi' => function ($query) {
                $query->field('id,quanzi_name');
            },
            'kaolaAdmin' => function ($query) {
                $query->field('id,user_number');
            }
        ])->where('is_delete', 0)->where($where)->order('id desc')->paginate(10);
        $this->assign([
            'citys' => model('City')->where('is_valid', 1)->field('id,name')->select(),
            'categories' => $categories,
            'status' => MarketModel::getStatusList(),
            'isTop' => MarketModel::getIsTopList(),
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
            $result = $this->validate($data, 'app\admin\validate\Market');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return json(['info' => '修改失败:' . $result, 'status' => 'n']);
            }
            if (empty($data['mobile']) && empty($data['email']) && empty($data['weixin_no'])) {
                return json(['info' => '修改失败:联系方式手机、微信、邮箱最少填写一个！', 'status' => 'n']);
            }
            $market = MarketModel::where(['id' => $id, 'is_delete' => 0])->find();
            if (!$market) {
                return json(['info' => '修改失败:二手资讯不存在！', 'status' => 'n']);
            }
            $data['tag_ids'] = !empty($data['tag_ids']) ? implode(',', $data['tag_ids']) : '';
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
            $market->allowField(true)->save($data, ['id' => $id]);
            return json(['info' => '修改成功!', 'status' => 'y']);
        } else {
            $info = MarketModel::where(['id' => $id, 'is_delete' => 0])->find();
            if (!$info) {
                $this->error('二手资讯不存在！');
            }
            $citys = model('City')->where('is_valid', 1)->field('id,name')->select();
            $districts = model('District')->where(['is_valid' => 1, 'city_id' => $info->city_id])->field('id,name')->select();
            $categories = model('MarketCategory')->where(['is_valid' => 1, 'is_delete' => 0, 'city_id' => $info->city_id])->field('id,category_name')->select();
            $quanzi = model('MarketQuanzi')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,quanzi_name')->select();
            $property = MarketModel::getPropertyList();
            $oldnew = MarketModel::getOldnewList();
            $guarantee = MarketModel::getGuaranteeList();
            $dealArea = MarketModel::getDealAreaList();
            $delivery = MarketModel::getDeliveryList();
            $status = MarketModel::getStatusList();
            $isTop = MarketModel::getIsTopList();
            $tags = model('MarketTag')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,tag_name')->select();
            $info->imageList = explode(',', $info->images);
            $klAdmins = KaolaAdmin::where('is_valid', 1)->field('id,user_number')->select();
            return $this->fetch('market/edit', [
                'info' => $info,
                'citys' => $citys,
                'districts' => $districts,
                'categories' => $categories,
                'quanzi' => $quanzi,
                'property' => $property,
                'oldnew' => $oldnew,
                'guarantee' => $guarantee,
                'dealArea' => $dealArea,
                'delivery' => $delivery,
                'status' => $status,
                'isTop' => $isTop,
                'tags' => $tags,
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
        $market = MarketModel::where(['is_delete' => 0, 'id' => $id])->find();
        if (!$market) {
            return json(['info' => '删除失败:二手信息不存在，无法删除！', 'status' => 'n']);
        }
        $market->is_delete = 1;
        $market->delete_time = time();
        $market->save();
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
        $market = db('market')->where('id', $id)->field('id,source_url', true)->find();
        if ($market) {
            if ($market['is_uploaded']) {
                return json(['info' => '上传失败:已经上传过了，请勿重复上传！', 'status' => 'n']);
            } else {
                unset($market['is_uploaded']);
            }
            $market['update_time'] = time();
        } else {
            return json(['info' => '上传失败:二手信息不存在', 'status' => 'n']);
        }
        $productMarketModel = model('ProductMarket');
        $productMarketModel->save($market);
        db('market')->where('id', $id)->update(['is_uploaded' => 1]);
        return json(['info' => '上传成功！', 'status' => 'y']);
    }
}
