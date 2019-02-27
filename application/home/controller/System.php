<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/20
 * Time: 16:41
 */

namespace app\home\controller;


use think\Db;
use think\facade\Request;

class System extends Common
{
    public function systemArticle(){
        $key=Request::param('jum_key');
        $data=Db::name('sys_article')->where('code','=',$key)->field('title,content')->find();
        $this->assign('data',$data);
        return $this->fetch('systemArticle');
    }
}