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
            $where[] = ['news_title', 'like', '%' . $params['news_title'] . '%'];
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
            'categories' => model('NewsCategory')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,category_name')->select(),
            'sources' => model('NewsSource')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,source_name')->select(),
            'isTop' => NewsModel::getIsTopList(),
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
            if ($data['type_id'] == 1) {
                //图片新闻
                unset($data['publish_time']);
                $data['news_image'] = $data['news_picture'];
                $data['news_picture'] = '';
                $image_list = explode(',', $data['news_image']);
                if ($data['layout_id'] == 1 || $data['layout_id'] == 3) {
                    $data['news_picture'] = $image_list[0];
                } else {
                    $data['news_picture'] = implode(',', array_slice($image_list, 0, 3));
                }
            }
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

    public function add()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $result = $this->validate($data, 'app\admin\validate\News');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return json(['info' => '添加失败:' . $result, 'status' => 'n']);
            }
            $news = new NewsModel();
            $data['top_end_date'] = !empty($data['top_end_date']) ? strtotime($data['top_end_date']) : '';
            $data['publish_time'] = !empty($data['publish_time']) ? strtotime($data['publish_time']) : '';
            $data['remark'] = !empty($data['remark']) ? $data['remark'] : '';
            $data['add_time'] = time();
            $data['delete_time'] = 0;
            $data['origin_publish_time'] = '';
            if ($data['type_id'] == 1) {
                //图片新闻
                unset($data['publish_time']);
                $data['news_image'] = $data['news_picture'];
                $data['news_picture'] = '';
                $image_list = explode(',', $data['news_image']);
                if ($data['layout_id'] == 1 || $data['layout_id'] == 3) {
                    $data['news_picture'] = $image_list[0];
                } else {
                    $data['news_picture'] = implode(',', array_slice($image_list, 0, 3));
                }
            } else {
                $data['news_image'] = '';
            }
            $news->allowField(true)->save($data);
            return json(['info' => '添加成功!', 'status' => 'y']);
        } else {
            $info = new NewsModel([
                'news_title' => '',
                'type_id' => '',
                'category_id' => '',
                'layout_id' => '',
                'source_id' => '',
                'title_tag_id' => '',
                'news_tag_id' => '',
                'is_publish' => '',
                'is_recommend' => 0,
                'is_hot' => 0,
                'is_publish' => 0,
                'declare_id' => '',
                'is_top' => 0,
                'is_applet' => 0,
                'news_url' => '',
                'search_key' => '',
                'remark' => '',
                'news_picture' => '',
                'publish_time_text' => '',
                'images' => '',
                'content' => '',
                'add_uid' => ''
            ]);
            $categories = model('NewsCategory')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,category_name')->select();
            $sources = model('NewsSource')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,source_name')->select();
            $types = model('NewsType')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,type_name')->select();
            $layouts = model('NewsLayout')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,layout_name')->select();
            $tags = db('news_tag')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,tag_name')->select();
            $isTop = NewsModel::getIsTopList();
            $declares = NewsModel::getDeclareList();
            $klAdmins = KaolaAdmin::where('is_valid', 1)->field('id,user_number')->select();
            return $this->fetch('news/edit', [
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

    /**
     * 上传到正式服务器
     */
    public function uploadToProduct($id)
    {
        if(empty($id)){
            $this->error('id不能为空！');
        }
        $field = 'category_id,source_id,title_tag_id,news_tag_id,layout_id,type_id,news_title,news_picture,content,news_url,search_key,remark,declare_id,is_valid,is_recommend,is_hot,top_end_date,add_time,add_uid,is_applet,is_publish,publish_time,is_uploaded';
        $news = db('news')->where('id', $id)->field($field)->where('id', $id)->find();
        if (!empty($news)) {
            $news['news_image'] = !empty($news['news_image']) ? $news['news_image'] : '';
            if($news['is_uploaded']){
                return json(['info' => '上传失败:已经上传过了，请勿重复上传！', 'status' => 'n']);
            }else{
                unset($news['is_uploaded']);
            }
        } else {
            return json(['info' => '上传失败:新闻不存在！', 'status' => 'n']);
        }
        $productNewsModel = model('ProductNews');
        $productNewsModel->save($news);
        db('news')->where('id', $id)->update(['is_uploaded' => 1]);
        return json(['info' => '上传成功！', 'status' => 'y']);
    }
}
