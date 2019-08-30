<?php

namespace app\admin\controller;

use QL\QueryList;
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

    /**
     * 获取会员昵称
     */
    public function getMemberNickname()
    {
        $mem_id = input('get.mem_id');
        if (!$mem_id) {
            return json(['status' => 'n', 'info' => '参数id不能为空！']);
        }
        return json(['status' => 'y', 'info' => ['nick_name' => model('Member')->where(['id' => $mem_id, 'is_valid' => 1])->value('nick_name')]]);
    }

    /**
     *  根据城市id获取生活分类
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

    /**
     * 根据城市id获取区域
     */
    public function getDistrictByCityId()
    {
        $city_id = input('get.city_id');
        if (!$city_id) {
            return json(['status' => 'n', 'info' => '城市id不能为空！']);
        }
        $districts = model('District')->where(['city_id' => $city_id, 'is_valid' => 1])->field('id,name')->select();
        return json(['status' => 'y', 'info' => ['districts' => $districts]]);
    }

    /**
     * 根据url采集微信新闻
     */
    public function collect_wechat()
    {
        $url = input('url');
        if(empty($url)){
            $this->error('微信采集地址不能为空！');
        }
        $_host = parse_url($url, PHP_URL_HOST);  //获取主机名
        if($_host !== 'mp.weixin.qq.com') {
            $this->error('文章链接来源不属于微信');
        }

        $html = \app\admin\extend\Http::get($url);
        if(empty($html)){
            $this->error('文章内容为空');
        }
        $html = str_replace("<!--headTrap<body></body><head></head><html></html>-->", "", $html);  //去除微信干扰元素!!!否则乱码
        preg_match("/var msg_cdn_url = \".*\"/", $html, $matches);   //获取微信封面图
        $coverImgUrl = $matches[0];
        $coverImgUrl = substr(explode('var msg_cdn_url = "', $coverImgUrl)[1], 0, -1);

        $rules = [  //设置QueryList的解析规则
            'title'   => ['#activity-name', 'text'],  //文章标题
            'content' => ['#js_content', 'html'],  //文章内容
            //'author'  => ['#js_profile_qrcode .profile_nickname','text'],  //公众号
        ];
        $data = QueryList::get($url)->rules($rules)->queryData(function($item){
            //利用回调函数下载文章中的图片并替换图片路径为本地路径
            //使用本例请确保当前目录下有image文件夹，并有写入权限
            $content = QueryList::html($item['content']);
            $content->find('img')->map(function($img){
                $src     = $img->attrs('data-src')[0];
                $imgpath = saveFileFromUrl($src);

                $img->attr('src',$imgpath);
                $img->removeAttr('data-src');
                $img->removeAttr('data-ratio');
                $img->removeAttr('data-w');

            });
            $item['content'] = $content->find('')->html();
            return $item;
        }
        );
        $data[0]['news_picture'] = saveFileFromUrl($coverImgUrl);

        $this->success('成功',null, $data[0]);
    }
}