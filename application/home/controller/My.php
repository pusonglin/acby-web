<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/25
 * Time: 10:42
 */

namespace app\home\controller;


class My extends Common
{
    //我的主页
    public function index(){
       return $this->fetch('index');
    }

    //修改密码
    public function changePwd(){
        return $this->fetch('changePwd');
    }

    //
    public function pwdConfirm(){
        return $this->fetch('pwdConfirm');
    }
}