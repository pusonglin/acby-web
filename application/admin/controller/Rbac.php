<?php
namespace app\admin\controller;

use app\admin\model\Base;
use app\admin\model\RbacAdmin;
use app\admin\model\RbacRight;
use app\admin\model\RbacRole;

class Rbac extends Common
{
    //------------------------
    // 角色管理
    //------------------------
    //角色列表
    public function role()
    {
        if(is_ajax()){
            RbacRole::index();
        }else{
            $this->telUserAccess(array('doRole','delRole','setRight'));
            return $this->fetch('role');
        }
    }

    //添加&编辑角色【用户组】
    public function doRole()
    {
        $data = RbacRole::doRole();
        $this->assign('cur',$data['cur']);
        $this->assign('title',$data['title']);
        return $this->fetch('doRole');
    }

    //添加&编辑角色保存
    public function doneRole(){
        RbacRole::doneRole();
    }

    //执行删除角色
    public function delRole(){
        RbacRole::delRole();
    }

    //为角色配置权限
    public function setRight(){
        $data = RbacRole::setRight();
        $this->assign('str',$data['str']);
        $this->assign('fresh',$data['fresh']);
        return $this->fetch('setRight');
    }

    //执行配置权限
    public function doSetRight()
    {
        RbacRole::doSetRight();
    }


    //------------------------
    // 用户管理
    //------------------------
    //用户列表
    public function user()
    {
        if(is_ajax()){
            RbacAdmin::index();
        }else{
            $this->telUserAccess(array('doUser','delUser','import','export'));
            return $this->fetch('user');
        }
    }
    //批量导入
    public function import()
    {
        RbacAdmin::import();
    }

    //批量导出
    public function export()
    {
        RbacAdmin::export();
    }



    //添加&编辑用户
    public function doUser()
    {
        //查询当前数据信息
        $data = RbacAdmin::doUser();
        $this->assign('cur',$data['cur']);
        $this->assign('title',$data['title']);

        //获取角色列表
        $roleList = Base::getRbacRoleList();
        $this->assign('role_list',$roleList);

        return $this->fetch('doUser');
    }

    //添加&编辑用户保存
    public function doneUser()
    {
        RbacAdmin::doneUser();
    }

    //执行删除用户
    public function delUser()
    {
        RbacAdmin::delUser();
    }


    //------------------------
    // 节点管理
    //------------------------
    //节点列表
    public function right(){
        if(is_ajax()){
            $lists = RbacRight::getAllRightList();
            ajax_return($lists);
        }else{
            $this->telUserAccess(array('doRight','delRight'));
            return $this->fetch('right');
        }
    }

    //添加&编辑节点
    public function doRight()
    {
        //当前数据信息
        $data = RbacRight::doRight();
        $this->assign('lists',$data['lists']);
        $this->assign('title',$data['title']);
        $this->assign('cur',$data['cur']);

        return $this->fetch('doRight');
    }

    //添加&编辑节点保存
    public function doneRight()
    {
        RbacRight::doneRight();
    }

    //执行删除节点
    public function delRight()
    {
        RbacRight::delRight();
    }


    //用户列表
    public function member(){
        if(is_ajax()){
            $lists = RbacAdmin::member();
            ajax_return($lists);
        }else{
            $this->telUserAccess(array('detail'));
            return $this->fetch('member');
        }
    }

    //添加&编辑用户
    public function memberDetail()
    {
        //查看用户详情
        $data = RbacAdmin::memberDetail();
        $this->assign('cur',$data);
        return $this->fetch('memberDetail');
    }

}
