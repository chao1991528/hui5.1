<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Base extends Controller
{
    protected function initialize(){
        if(!is_login()){
            $this->redirect(url('login/index'));
        }
    }
}    
