<?php
namespace app\admin\controller;


use app\admin\model\Document as Documents;
use think\facade\Request;

class Document extends Common
{

    public function index(){
        if(is_ajax()){
            Documents::lists();
        }else{
            $cateList = get_cate_child_list('party_news');
            $this->assign('cateList',$cateList);
            $this->telUserAccess(array('doParty','delParty'));
            return $this->fetch('party');
        }
    }

    //添加&编辑
    public function doParty()
    {
        //当前数据信息
        $data =Documents::doNews();
        $this->assign('title',$data['title']);

        //一级分类列表
        $firstList = get_cate_child_list('party_news');
        $this->assign('firstList',$firstList);

        //二级分类列表
        $pid = $data['cur']['first_cate_id']?$data['cur']['first_cate_id']:$firstList[0]['id'];
        $secondList = get_cate_child_list($pid,false);
        $this->assign('secondList',$secondList);

        //获取分类
        $this->assign('typeList',$data['cur']['typeList']);

        if(!($data['cur']['id']>0)){
            $data['cur']['first_cate_id'] = $firstList[0]['id'];
            $data['cur']['second_cate_id'] = $secondList[0]['id'];
        }
        $this->assign('cur',$data['cur']);
        return $this->fetch('doParty');
    }


    //添加&编辑保存
    public function doneParty()
    {
        Documents::doneNews();
    }

    //删除文章
    public function delParty()
    {
        Documents::delNews();
    }


    public function theme()
    {
        if(is_ajax()){
            Documents::theme();
        }else{
            $this->telUserAccess(array('doTheme','delTheme'));
            return $this->fetch('theme');
        }
    }

    //新增&编辑主题
    public function doTheme()
    {
        $data = Documents::doTheme();
        $this->assign('cur',$data['cur']);
        $this->assign('title',$data['title']);

        return $this->fetch('doTheme');
    }

    //保存活动
    public function doneTheme()
    {
        Documents::doneTheme();
    }

    //删除活动
    public function delTheme()
    {
        Documents::delTheme();
    }

    //Ajax获取二级分类
    public function getSecondList(){
        $pid=Request::post('pid');
        $secondList = get_cate_child_list($pid,false);
        echo json_encode($secondList);
    }


}
