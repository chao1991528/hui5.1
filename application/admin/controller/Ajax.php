<?php

namespace app\admin\controller;

use think\Controller;

class Ajax extends Controller
{
    protected function initialize()
    {
        if (!is_login()) {
            echo json_encode(['status' => 'n', 'info' => '请先登录！']);
            die;
        }
    }

    public function upload()
    {
        $file = request()->file('file');
        $params = input('get.');
        // 移动到/uploads/模块 目录下
        $dir = './uploads/' . $params['model'] . '/';
        $info = $file->move($dir);
        if ($info) {
            $imageDomain = config('app.image_domain');
            $file = $dir . $info->getSaveName();
            if ($params['water']) {
                $image = \think\Image::open($file);
                $image->water('./static/images/water_logo.png', \think\Image::WATER_SOUTHEAST)->save($file);
            }
            if ($params['thumb']) {
                // 按照原图的比例生成缩略图并保存
                if (!empty($params['thumb_width1']) && !empty($params['thumb_height1'])) {
                    $image = \think\Image::open($file);
                    $thumb_file1 = $dir . str_replace('.', "_thumb_{$params['thumb_width1']}.", $info->getSaveName());
                    $image->thumb($params['thumb_width1'], $params['thumb_height1'])->save($thumb_file1);
                }
                if (!empty($params['thumb_width2']) && !empty($params['thumb_height2'])) {
                    $image = \think\Image::open($file);
                    $thumb_file2 = $dir . str_replace('.', "_thumb_{$params['thumb_width2']}.", $info->getSaveName());
                    $image->thumb($params['thumb_width2'], $params['thumb_height2'])->save($thumb_file2);
                }
            }
            return json(['code' => 1, 'info' => '文件上传成功!', 'filename' => $imageDomain . '/uploads/' . $params['model'] . '/' . $info->getSaveName()]);
        } else {
            // 上传失败获取错误信息
            return json(['code' => 0, 'info' => '文件上传失败：' . $file->getError()]);
        }
    }

    /**
     * 二手切换城市
     */
    public function marketChangeCity()
    {
        $data = [];
        $data['city_id'] = input('post.city_id');
        if (!$data['city_id']) {
            return json(['status' => 'n', 'msg' => '城市id为空']);
        }
        $categories = model('MarketCategory')->where("city_id = {$data['city_id']} and is_valid=1 and is_delete=0")->field('id,category_name')->select();
        $districts = model('District')->where("city_id = {$data['city_id']} and is_valid=1")->field('id,name')->order('is_hot desc,id desc')->select();
        $quanzi = model('MarketQuanzi')->where("city_id = {$data['city_id']} and is_valid=1 and is_delete=0")->field('id,quanzi_name')->select();
        $_return_data = ['info' => ['categories' => $categories, 'districts' => $districts, 'quanzi' => $quanzi], 'status' => 'y'];

        return json($_return_data);
    }

    public function getMemberNickname()
    {
        $mem_id = input('get.mem_id');
        if (!$mem_id) {
            return json(['status' => 'n', 'info' => '参数id不能为空！']);
        }
        return json(['status' => 'y', 'info' => ['nick_name' => model('Member')->where(['id' => $mem_id, 'is_valid' => 1])->value('nick_name')]]);
    }

    /**
     *
     */
    public function getLiveCategoryByCityId()
    {
        $city_id = input('get.city_id');
        if (!$city_id) {
            return json(['status' => 'n', 'info' => '城市id不能为空！']);
        }
        $categories = model('LiveCategory')->where(['city_id' => $city_id, 'is_valid' => 1])->field('id,category_name')->select();
        return json(['status' => 'y', 'info' => ['categories' => $categories]]);
    }
}
