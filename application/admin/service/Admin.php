<?php

namespace app\admin\service;

use app\admin\model\Admin as AdminModel;

class Admin{

	public function checkLogin($username, $password)
	{
		$admin = AdminModel::where('username', $username)->field('password,status,salt,username,avatar,nickname')->find();
		if(!$admin){
			return '账号不存在！';
		}
		if ($admin->password != $this->encryptPassword($password, $admin->salt)) {
            return '密码错误！';
        }
        if($admin->status == AdminModel::STATUS_FORBBID){
        	return '账号被禁用！';
        }
        unset($admin->password, $admin->salt);
        session('admin', $admin);
        return true;
	}

	/**
	* 给原始密码加密
	*/
	public function encryptPassword($password, $salt){
		return md5(md5($password) . $salt);
	}

	/**
	* 退出
	*/
	public function logout()
	{
		session('admin', null);
		return true;
	}
}