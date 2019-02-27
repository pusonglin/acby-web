<?php
namespace app\admin\controller;

use app\admin\model\RbacAdmin;

class User extends Common
{
    //修改个人资料
    public function info()
    {
        $cur = RbacAdmin::selfInfo();
        $this->assign('cur',$cur);
        return $this->fetch();
    }

    //执行修改个人资料
    public function updateInfo()
    {
        if(is_ajax()){
            RbacAdmin::updateInfo();
        }else{
            jump_error();
        }
    }

    //修改登录密码
    public function pwd()
    {
        if(is_ajax()){
            RbacAdmin::savePwd();
        }else{
            return $this->fetch();
        }
    }

    //注销登录
    public function logout()
    {
        save_logs('退出后台管理系统');
        session('admin_login',null);
        return redirect(ADMIN_URL);
    }
}
