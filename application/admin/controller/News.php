<?php

namespace app\admin\controller;

use app\admin\model\News as NewsModel;
use app\admin\model\KaolaAdmin;

class News extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = [];
        $params = input('get.');
        if (!empty($params['news_title'])) {
            $where[] = ['news_title', 'like', '%' . $params['title'] . '%'];
        }
        if (!empty($params['city_id'])) {
            $where[] = ['city_id', '=', $params['city_id']];

        }
        if (!empty($params['category_id'])) {
            $where[] = ['category_id', '=', $params['category_id']];
        }
        if (!empty($params['source_id'])) {
            $where[] = ['source_id', '=', $params['source_id']];
        }
        if (isset($params['is_top']) && $params['is_top'] != -1) {
            $where[] = ['is_top', '=', $params['is_top']];
        }
        $list = NewsModel::with([
            'category' => function ($query) {
                $query->field('id,category_name');
            },
            'kaolaAdmin' => function ($query) {
                $query->field('id,user_number');
            }
        ])->where('is_delete', 0)->where($where)->order('id desc')->paginate(10);
        $this->assign([
            'citys' => model('City')->where('is_valid', 1)->field('id,name')->select(),
            'categories' => model('NewsCategory')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,category_name')->select(),
            'sources' => model('NewsSource')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,source_name')->select(),
            'isTop' => NewsModel::getIsTopList(),
            'total' => $list->total(),
            'list' => $list
        ]);
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
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
            $result = $this->validate($data, 'app\admin\validate\News');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return json(['info' => '修改失败:' . $result, 'status' => 'n']);
            }
            $news = NewsModel::where(['id' => $id, 'is_delete' => 0])->find();
            if (!$news) {
                return json(['info' => '修改失败:新闻不存在！', 'status' => 'n']);
            }
            $data['top_end_date'] = !empty($data['top_end_date']) ? strtotime($data['top_end_date']) : '';
            $news->allowField(true)->save($data, ['id' => $id]);
            return json(['info' => '修改成功!', 'status' => 'y']);
        } else {
            $info = NewsModel::where(['id' => $id, 'is_delete' => 0])->find();
            if (!$info) {
                $this->error('新闻不存在！');
            }
            $categories = model('NewsCategory')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,category_name')->select();
            $sources = model('NewsSource')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,source_name')->select();
            $types = model('NewsType')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,type_name')->select();
            $layouts = model('NewsLayout')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,layout_name')->select();
            $tags = db('news_tag')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,tag_name')->select();
            $isTop = NewsModel::getIsTopList();
            $declares = NewsModel::getDeclareList();
            $klAdmins = KaolaAdmin::where('is_valid', 1)->field('id,user_number')->select();
            $info->news_picture = str_replace(['"', '[', ']', ' '], ['', '', '', ''], $info->news_picture);
            $info->news_image = str_replace(['"', '[', ']', ' '], ['', '', '', ''], $info->news_image);
            // $info->content = html_entity_decode($info->content);
            if (!empty($info->news_picture)) {
                $picture_arr = explode(',', $info->news_picture);
                foreach ($picture_arr as $key => $value) {
                    if ($value == 'http://pool.kaolanews.com/upload') {
                        unset($picture_arr[$key]);
                    }
                }
                $info->images = $picture_arr;
                $info->news_picture = empty($picture_arr) ? '' : implode(',', $picture_arr);
            }
            return $this->fetch('', [
                'info' => $info,
                'types' => $types,
                'categories' => $categories,
                'sources' => $sources,
                'layouts' => $layouts,
                'tags' => $tags,
                'declares' => $declares,
                'isTop' => $isTop,
                'klAdmins' => $klAdmins
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
        $news = NewsModel::where(['is_delete' => 0, 'id' => $id])->find();
        if (!$news) {
            $this->error('生活信息不存在，无法删除！');
        }
        $news->is_delete = 1;
        $news->delete_time = time();
        $news->save();
        return json(['info' => '删除成功', 'status' => 'y']);
    }
}
