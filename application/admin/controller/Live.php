<?php

namespace app\admin\controller;

use app\admin\model\KaolaAdmin;
use app\admin\model\Live as LiveModel;

class Live extends Base
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
            $categories = model('LiveCategory')->where(['is_valid' => 1, 'is_delete' => 0, 'city_id' => $params['city_id']])->field('id,category_name')->select();
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
        $list = LiveModel::with([
            'member' => function ($query) {
                $query->field('id,nick_name');
            },
            'city' => function ($query) {
                $query->field('id,name_zh');
            },
            'category' => function ($query) {
                $query->field('id,category_name');
            },
            'kaolaAdmin' => function ($query) {
                $query->field('id,user_number');
            }
        ])->where('is_delete', 0)->where($where)->order('id desc')->paginate(10);
        $this->assign([
            'citys' => model('City')->where('is_valid', 1)->field('id,name')->select(),
            'categories' => $categories,
            'status' => LiveModel::getStatusList(),
            'isTop' => LiveModel::getIsTopList(),
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
            $result = $this->validate($data, 'app\admin\validate\Live');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return json(['info' => '修改失败:' . $result, 'status' => 'n']);
            }
            if (empty($data['mobile']) && empty($data['email']) && empty($data['weixin_no'])) {
                return json(['info' => '修改失败:联系方式手机、微信、邮箱最少填写一个', 'status' => 'n']);
            }
            $market = LiveModel::where(['id' => $id, 'is_delete' => 0])->find();
            if (!$market) {
                return json(['info' => '修改失败:生活信息不存在！', 'status' => 'n']);
            }
            $data['tag_ids'] = !empty($data['tag_ids']) ? implode(',', $data['tag_ids']) : '';
            $data['top_end_date'] = !empty($data['top_end_date']) ? strtotime($data['top_end_date']) : '';
            $market->allowField(true)->save($data, ['id' => $id]);
            return json(['info' => '修改成功!', 'status' => 'y']);
        } else {
            $info = LiveModel::where(['id' => $id, 'is_delete' => 0])->find();
            if (!$info) {
                $this->error('生活信息不存在！');
            }
            $citys = model('City')->where('is_valid', 1)->field('id,name')->select();
            $categories = model('LiveCategory')->where(['is_valid' => 1, 'is_delete' => 0, 'city_id' => $info->city_id])->field('id,category_name')->select();
            $status = LiveModel::getStatusList();
            $isTop = LiveModel::getIsTopList();
            $klAdmins = KaolaAdmin::where('is_valid', 1)->field('id,user_number')->select();
            $tags = model('LiveTag')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,tag_name')->select();
            $info->imageList = explode(',', $info->images);
            return $this->fetch('live/edit', [
                'info' => $info,
                'citys' => $citys,
                'categories' => $categories,
                'status' => $status,
                'isTop' => $isTop,
                'klAdmins' => $klAdmins,
                'tags' => $tags
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
        $live = LiveModel::where(['is_delete' => 0, 'id' => $id])->find();
        if (!$live) {
            $this->error('生活信息不存在，无法删除！');
        }
        $live->is_delete = 1;
        $live->delete_time = time();
        $live->save();
        return json(['info' => '删除成功', 'status' => 'y']);
    }

    /**
     * 上传数据到正式站
     */
    public function uploadToProduct($id)
    {
        if(empty($id)){
            $this->error('id不能为空！');
        }
        $live = db('live')->where('id', $id)->field('id,source_url,email_image', true)->where('id', $id)->find();
        if (!empty($live)) {
            if($live['is_uploaded']){
                return json(['info' => '上传失败:已经上传过了，请勿重复上传！', 'status' => 'n']);
            }else{
                unset($live['is_uploaded']);
            }
        } else {
            return json(['info' => '上传失败:生活信息不存在', 'status' => 'n']);
        }
        $productLiveModel = model('ProductLive');
        $productLiveModel->save($live);
        db('live')->where('id', $id)->update(['is_uploaded' => 1]);
        return json(['info' => '上传成功！', 'status' => 'y']);
    }
}
