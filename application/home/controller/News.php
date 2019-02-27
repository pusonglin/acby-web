<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/19
 * Time: 14:59
 */

namespace app\home\controller;


use app\home\model\Document;
use think\Db;
use think\facade\Request;

class News extends Common
{
    //新闻详情
    public function details(){
        $id=Request::param('id');
        $id=base64_decode($id);
        $cur=Document::newsDetail($id);
        //分享参数
        //$share_title = $cur['title'];
        //$share_desc = '爱车保养';
        //$share_img = 'http://'.$_SERVER["SERVER_NAME"].$cur['cover'];
        //$data = $this->share_config($share_title,$share_desc,$share_img,'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
        //$this->assign('data',$data);
        $this->assign('cur',$cur);
        return $this->fetch('details');
    }

    //获取分类
    public function typeList(){
        if(is_ajax()){
            $cur=Document::typeList();
            echo json_encode($cur);
        }else{
            $type_id=Request::param('flag');
            $title=Request::param('title');
            $cur=Document::typeList();
            $count=Db::name('library_news')->where('type','like','%'.$type_id.'%')->count('id');
            $this->assign('title',$title);
            $this->assign('count',$count);
            $this->assign('cur',$cur);
            return $this->fetch('typeList');
        }

    }
}