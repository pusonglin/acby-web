<?php
namespace app\admin\controller;

use app\admin\model\Base;
use app\admin\model\RbacAdmin;
use app\admin\model\RbacRight;
use app\admin\model\Syssets;
use think\Db;

class Index extends Common
{
    public function index()
    {
        //查询一级栏目列表
        $list = RbacRight::topNav();
        $this->assign('list',$list);

        //当前用户信息
        global $admin_login;
        $this->assign('user',$admin_login);
        return $this->fetch('index');
    }

    //获取功能权限
    public function getRightList()
    {
        if(is_ajax()){
            RbacRight::getRightList();
        }
    }

    //获取地址列表
    public function getAddressList()
    {
        if(is_ajax()){
            Base::getAddressList();
        }
    }

    //获取新闻二级分类
    public function getNewsCateLists()
    {
        if(is_ajax()){
            Syssets::getNewsCateLists();
        }
    }
}
