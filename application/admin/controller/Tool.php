<?php
namespace app\admin\controller;

use app\admin\model\Tool as Tools;


class Tool extends Common
{
    protected $except;

    //------------------------
    // 清空数据
    //------------------------
    //清空数据
    public function cleanTab(){
        if(is_ajax()){
            Tools::doCleanTab();
        }else{
            return $this->fetch('cleanTab');
        }
    }

}
