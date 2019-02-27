<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/21
 * Time: 16:50
 */

namespace app\api\controller;


use app\api\model\Member;

class My extends Common
{
    //获取用户基本信息
    public function index(){
        Member::index();
    }

    //我的浏览记录
    public function glanceOver(){
        Member::glanceOver();
    }

    //我的收藏
    public function myCollect(){
        Member::myCollect();
    }
}