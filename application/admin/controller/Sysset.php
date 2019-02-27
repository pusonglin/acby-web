<?php
namespace app\admin\controller;


use app\admin\model\Syssets;

class Sysset extends Common
{
    //------------------------
    // 行政区域
    //------------------------
    //省市区列表
    public function district()
    {
        $list = get_district_list();
        $this->assign('list',$list);
        //$this->telUserAccess(array('doDistrict','delDistrict'));
        return $this->fetch('district');
    }


    //------------------------
    // 常规栏目
    //------------------------
    //栏目列表
    public function category()
    {
        //列表数据
        $list = get_option_list();
        $this->assign('list',$list);

        $this->telUserAccess(array('doCategory','delCategory'));
        return $this->fetch('category');
    }

    //添加&编辑栏目
    public function doCategory()
    {
        //列表数据
        $list = get_option_list();
        $this->assign('optionList',$list);

        //当前数据
        $data = Syssets::doCategory();
        $this->assign('cur',$data['cur']);
        $this->assign('title',$data['title']);
        return $this->fetch('doCategory');
    }

    //保存栏目
    public function doneCategory()
    {
        Syssets::doneCategory();
    }

    //删除栏目
    public function delCategory()
    {
        Syssets::delCategory();
    }



    //------------------------
    // 日志管理
    //------------------------
    //操作日志管理
    public function logs()
    {
        if(is_ajax()){
            Syssets::logs();
        }else{
            $this->telUserAccess(array('delLogs'));
            return $this->fetch('logs');
        }
    }

    //删除操作日志
    public function delLogs()
    {
        Syssets::delLogs();
    }


    //------------------------
    // APP版本管理
    //------------------------
    //版本列表
    public function version()
    {
        if(is_ajax()){
            Syssets::version();
        }else{
            $this->telUserAccess(array('doVersion','delVersion'));
            return $this->fetch('version');
        }
    }

    //添加&编辑
    public function doVersion()
    {
        $data = Syssets::doVersion();
        $this->assign('cur',$data['cur']);
        $this->assign('title',$data['title']);
        return $this->fetch('doVersion');
    }

    //保存模板
    public function doneVersion()
    {
        Syssets::doneVersion();
    }

    //删除模板
    public function delVersion()
    {
       Syssets::delVersion();
    }


    //------------------------
    // 缓存管理
    //------------------------
    //数据缓存列表
    public function cache()
    {
        if(is_ajax()){
            Syssets::cacheList();
        }else{
            $this->telUserAccess(array('delCache','delAllCache'));
            return $this->fetch('cache');
        }
    }

    //删除选中的缓存
    public function delCache()
    {
        Syssets::delCache(1);
    }

    //删除全部缓存
    public function delAllCache(){
        Syssets::delCache(2);
    }


    //------------------------
    // 特定文章
    //------------------------
    //文章列表
    public function block(){
        if(is_ajax()){
            Syssets::block();
        }else{
            $this->telUserAccess(array('doBlock','delBlock'));
            return $this->fetch('block');
        }
    }

    //添加&编辑
    public function doBlock()
    {
        $data = Syssets::doBlock();
        $this->assign('cur',$data['cur']);
        $this->assign('title',$data['title']);
        return $this->fetch('doBlock');
    }

    //保存模板
    public function doneBlock()
    {
        Syssets::doneBlock();
    }

    //删除模板
    public function delBlock()
    {
        Syssets::delBlock();
    }

    //------------------------
    // 敏感词库
    //------------------------
    //词库列表
    public function sensitive()
    {
        if(is_ajax()){
            Syssets::sensitive();
        }else{
            $this->telUserAccess(array('doSensitive','delSensitive'));
            return $this->fetch('sensitive');
        }
    }

    //添加敏感词
    public function doSensitive()
    {
        if(is_ajax()){
            Syssets::doneSensitive();
        }else{
            return $this->fetch('doSensitive');
        }
    }

    //删除敏感词
    public function delSensitive()
    {
        Syssets::delSensitive();
    }


    //------------------------
    // 搜索管理
    //------------------------
    //搜索列表
    public function search()
    {
        if(is_ajax()){
            Syssets::search();
        }else{
            $this->telUserAccess(array('delSearch'));
            return $this->fetch('search');
        }
    }

    //删除搜索关键词
    public function delSearch()
    {
        Syssets::delSearch();
    }

}
