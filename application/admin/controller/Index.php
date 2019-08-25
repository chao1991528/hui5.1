<?php

namespace app\admin\controller;

class Index extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return $this->fetch();
    }

    public function welcome()
    {
        return '<p class="f-20 text-success">欢迎使用后台管理系统</p>';
    }

    public function logout()
    {
        $res = (new \app\admin\service\Admin())->logout();
        return ($res === true) ? $this->redirect('login/index') : $this->error($res);
    }
}
