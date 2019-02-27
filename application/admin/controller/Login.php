<?php
namespace app\admin\controller;
use app\admin\model\RbacAdmin;
use think\Controller;

class Login extends Controller
{
    public function index()
    {
        //判断是否已经登录
        $isLogin = session('admin_login');
        if($isLogin){
            return redirect(ADMIN_URL);
        }
        return $this->fetch();
    }

    //后台用户登录验证
    public function doLogin()
    {
        if(is_ajax()){
            RbacAdmin::doLogin();
        }else{
            jump_error();
        }
    }

    //验证码生成
    public function verify()
    {
        return verify_create('admin_login_yzm');
    }
}
