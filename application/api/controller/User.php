<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/21
 * Time: 16:49
 */

namespace app\api\controller;


use app\api\model\Member;

class User extends Common
{
    //用户注册
    public function register(){
        Member::register();
    }

    //用户登录
    public function login(){
        Member::login();
    }

    //第三方登陆
    public function autoLogin(){
        Member::authLogin();
    }

    //重置密码
    public function resetPwd(){
        Member::resetPwd();
    }
}