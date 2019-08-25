<?php
namespace app\admin\controller;

use think\Controller;

class Login extends Controller
{
    public function index()
    {
    	if($this->request->isAjax()){
    		$data = input('post.');
    		$result = $this->validate(
    			$data, 
    			[
    				'username|用户名' => 'require', 
    				'password|密码' => 'require', 
    				'code|验证码' => 'require|captcha'
    			]
    		);
	        if (true !== $result) {
	            // 验证失败
	            return json(['status' => 'n', 'msg' => $result]);
	        }
	        $res = (new \app\admin\service\Admin())->checkLogin($data['username'], $data['password']);
	        return ($res === true) ? json(['status' => 'y', 'msg' => '登录成功！', 'url' => $this->request->domain() . url('index/index')]) : json(['status' => 'n', 'msg' => $res]);
    	}
    	if(is_login()){
    		$this->redirect(url('index/index'));
    	}
        return $this->fetch();
    }

}
