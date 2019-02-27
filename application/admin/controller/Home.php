<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/19
 * Time: 9:44
 */

namespace app\admin\controller;


use app\admin\model\RbacAdmin;

class Home extends Common
{

    //首页右侧日期显示
    public function index()
    {
        if(is_ajax()){
            RbacAdmin::homeReport();
        }else{
            return $this->fetch();
        }
    }
}