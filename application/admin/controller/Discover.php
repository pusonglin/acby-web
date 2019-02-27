<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/22
 * Time: 12:43
 */

namespace app\admin\controller;
use \app\admin\model\Document as Documents;

class Discover extends Common
{
    //发现列表
    public function index(){
        if(is_ajax()){
            Documents::discovery();
        }else{
            $this->assign('page_title','发现列表');
            $this->telUserAccess(array('doDiscovery','delDiscovery','doneDiscovery'));
        return $this->fetch('index');
        }
    }

    public function doDiscovery()
    {
        $data = Documents::doDiscovery();
        $this->assign('cur',$data['cur']);
        $this->assign('title',$data['title']);

        return $this->fetch('doDiscovery');
    }

    //保存活动
    public function doneDiscovery()
    {
        Documents::doneDiscovery();
    }

    //删除活动
    public function delDiscovery()
    {
        Documents::delDiscovery();
    }

}