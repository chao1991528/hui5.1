<?php
use think\facade\Env;

function is_login(){
	return session('admin') ? true : false;
}

if (!function_exists('saveFileFromUrl')) {

    /**
     * 根据图片地址保存图片
     *
     * @param string $url
     * @return string
     */
    function saveFileFromUrl($url, $save_to='')
    {
        $uploadRootDir = Env::get('root_path') . 'public';
        $uploadDir = '/uploads/weixin/' . date('Ymd') . "/";
        $dir = $uploadRootDir . $uploadDir;
        if(!is_dir($dir)){
            mkdir($dir);
        }
        $file_name = md5(time()) . rand(10000, 99999) . '.jpg';
        // halt($url);
        file_put_contents($dir . $file_name, file_get_contents($url));
        return config('image_domain') . $uploadDir . $file_name;
    }
}