<?php
namespace app\admin\controller;

use app\admin\model\BlockPage;
use think\Db;
use think\facade\Request;

class Block extends Common
{
    //------------------------
    // 广告页面管理
    //------------------------
    //广告页面列表
    public function page()
    {
        if(is_ajax()){
            BlockPage::pageList();
        }else{
            $this->telUserAccess(array('doPage','delPage','viewPage','pubPage'));
            return $this->fetch('page');
        }
    }

    //添加&编辑广告页面
    public function doPage()
    {
        $data = BlockPage::doPage();
        $this->assign('cur',$data['cur']);
        $this->assign('title',$data['title']);
        $this->display('doPage');
    }

    //保存广告页面信息
    public function donePage()
    {
        BlockPage::donePage();

    }

    //删除广告页面
    public function delPage()
    {
        BlockPage::delPage();
    }

    //发布广告页面
    public function pubPage(){
        $blockIds = BlockPage::pubPage();
        if(empty($blockIds)){
            echo 'error';
        }else{
            self::buildHtmlfile($blockIds);
            echo 1;
        }
    }



    //预览模板页面
    public function viewPage()
    {
        $page_id = Request::get('page_id');
        $blockids = get_field('block',array('page_id'=>$page_id),'id',true);
        if(!empty($blockids)){
            self::buildHtmlfile($blockids,true);
        }
        $url = get_field('block_page',array('id'=>$page_id),'url');
        if(preg_match('/(http:\/\/)|(https:\/\/)/i', $url)){
            exit("<script language=\"javascript\">window.open('".$url."?act=temp','_blank');</script>");
        }else{
            jump_error('页面已失联~');
        }
    }

    //创建静态页面
    private function buildHtmlfile($blockIds,$isTemp=false)
    {
        $map = [
            ['block_id','in',$blockIds],
            ['status','=',1]
        ];
        $list = get_select('block_data',$map,'block_id,content');
        if(!empty($list)){
            foreach ($list as $v){
                if(file_exists('../template/'.$v['block_id'].'.'.config('url_html_suffix'))){
                    $data = json_decode($v['content'],true);
                    $this->assign('data',$data);
                    $content  = $this->fetch('../../../template/'.$v['block_id']);
                    $htmlpath = dirname($_SERVER['SCRIPT_FILENAME']).'/html/';
                    $htmlpath .= $isTemp?'temp/':'';
                    $htmlfile = $htmlpath . $v['block_id'] . '.'.config('url_html_suffix');
                    $File = new \think\template\driver\File();
                    $File->write($htmlfile, $content);
                }
            }
        }
    }

    //------------------------
    // 广告模块管理
    //------------------------
    //模块列表
    public function block()
    {
        if(is_ajax()){
            BlockPage::blockList();
        }else{
            $page_title = get_field('block_page',array('id'=>Request::get('page_id')),'title');
            $this->assign('page_title',$page_title);

            $this->telUserAccess(array('doBlock','delBlock'));
            return $this->fetch('block');
        }
    }

    //添加&编辑页面模块
    public function doBlock()
    {
        $data = BlockPage::doBlock();
        //my_print($data);
        $this->assign('cur',$data['cur']);
        $this->assign('title',$data['title']);
        return $this->fetch('doBlock');
    }

    //保存模块信息
    public function doneBlock()
    {
        BlockPage::doneBlock();
    }

    //删除模块
    public function delBlock()
    {
        BlockPage::delBlock();
    }

    //模板数据写入
    public function data()
    {
        if(is_ajax()){
            BlockPage::saveData();
        }else{
            $cur = BlockPage::blockData();
            $this->assign('cur',$cur);
            return $this->fetch('data');
        }
    }

}
