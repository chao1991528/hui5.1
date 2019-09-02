<?php

namespace app\api\controller;

use think\facade\Log;
use think\Controller;

/**
 * 公共接口
 */
class Common extends Controller
{

    /**
     * 上传文件
     * @ApiMethod (POST)
     * @param File $file 文件流
     */
    public function upload()
    {
        $thumb = input('post.thumb', 1);
        $needWater = input('param.type', 0); //0需要水印，1不需要
        Log::info('--------begin-------------type:' . $needWater);

        $files = $this->request->file('');
        // halt(count($files));
        if (empty($files)) {
            $this->error('请选择文件！');
        }
        $data = [];
        $imageDomain = config('app.image_domain');
        foreach ($files as $file) {
            $uploadDir = $dir = './uploads/';
            $info = $file->move($uploadDir);
            if ($info) {
                $file = $dir . $info->getSaveName();
                $imagewidth = $imageheight = 0;
                $imgInfo = getimagesize($info->getPathname());
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
                $image = \think\Image::open($file);
                if ($imagewidth < 900) {
                    $waterImage = 'water_logo_70.png';
                } else if ($imagewidth >= 900 && $imagewidth < 2000) {
                    $waterImage = 'water_logo_120.png';
                } else {
                    $waterImage = 'water_logo_150.png';
                }
                if ($needWater == 0) {
                    //需要水印
                    $image->water('./static/images/' . $waterImage, \think\Image::WATER_SOUTHEAST, 50)->save($file);
                }
                if ($thumb) {
                    $thumb200Height = $imageheight > $imagewidth ? (200 * $imageheight / $imagewidth) : 200;
                    $thumb750Height = $imageheight > $imagewidth ? (750 * $imageheight / $imagewidth) : 750;
                    $thumb_200_name = str_replace('.', '_thumb_200.', $info->getSaveName());
                    $image->thumb(200, $thumb200Height)->save($dir . $thumb_200_name);
                    $image = \think\Image::open($file);
                    $thumb_750_name = str_replace('.', '_thumb_750.', $info->getSaveName());
                    $image->thumb(750, $thumb750Height)->save($dir . $thumb_750_name);
                }
                $data['url'][] = $imageDomain . $file;
            } else {
                // 上传失败获取错误信息
                $this->error($file->getError());
            }
        }
        Log::info('--------end-------------return:' . json_encode($data));
        return json(['msg' => '上传成功!', 'data' => $data]);
    }

}
