<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/19
 * Time: 11:45
 */

namespace app\home\model;


use think\Db;
use think\facade\Request;
use think\Model;

class Document extends Model
{
    //获取首页数据
    public static function getHomePageData(){
        $data=array(
            'essence'=>self::getEssenceData(),
            'push'=>self::getPushData(),
            'hot'=>self::getHotData()
        );
        return $data;
    }

    //获取精选内容
    public static function getEssenceData(){
        $map=[
            ['status','=',1],
            ['is_essence','=',1]
        ];
        $list=Db::name('library_news')
            ->where($map)
            ->field('id,title,summary,cover,type,source')
            ->limit(4)
            ->order('essence_time desc')
            ->select();
        if(!empty($list)){
            foreach ($list as &$v){
                $cover=json_decode($v['cover']);
                $v['cover']=get_thumb_img($cover[0],776,400);
                $ids=array_filter(explode(',',$v['type']));
                $v['type']=self::getTheme($ids);
                $v['href']=HOME_URL.'/News/details?id='.base64_encode($v['id']);
            }
        }
        return $list;
    }

    //获取推荐内容
    public static function getPushData(){
        $post=Request::post();
        $post['page']=isset($post['page'])?$post['page']:1;
        $map=[
            ['status','=',1],
            ['is_push','=',1]
        ];
        $list=Db::name('library_news')
            ->where($map)
            ->field('id,title,summary,cover,type,source,create_time')
            ->page($post['page'],10)
            ->order('create_time desc')
            ->select();
        if(!empty($list)){
            foreach ($list as &$v){
                $cover=json_decode($v['cover']);
                $v['cover']=get_thumb_img($cover[0],776,400);
                $ids=array_filter(explode(',',$v['type']));
                $v['type']=self::getTheme($ids);
                $v['create_time']=time_fortmat($v['create_time']);
                $v['href']=HOME_URL.'/News/details?id='.base64_encode($v['id']);
            }
        }
        $ad=self::getTwoAd($post['page']);
        $data=array(
          'ad'=>$ad,
          'list'=>$list
        );
        return $data;
    }

    //获取热点新闻
    public static function getHotData(){
        $map=[
            ['status','=',1],
            ['is_hot','=',1]
        ];
        $list=Db::name('library_news')
            ->where($map)
            ->field('id,title')
            ->limit(6)
            ->order('create_time desc')
            ->select();
        foreach ($list as &$v){
            $v['href']=HOME_URL.'/News/details?id='.base64_encode($v['id']);
        }
        return $list;
    }

    //获取主题标签
    public static function getTheme($ids){
        $map=[
          ['id','in',$ids],
          ['status','=',1]
        ];
        $list=Db::name('news_type')
            ->where($map)
            ->field('id,name')
            ->select();
        return $list;
    }

    public static function newsDetail($id){
        Db::name('library_news')->where('id','=',$id)->setInc('pv');
        $cur=Db::name('library_news')
            ->where('id','=',$id)
            ->field('id,title,content,pv,cover,type,source,create_time,author_summary')
            ->find();
        $cover=json_decode($cur['cover']);
        $cur['cover']=get_thumb_img($cover[0],776,400);
        $ids=array_filter(explode(',',$cur['type']));
        $cur['type']=self::getTheme($ids);
        $cur['create_time']=date('Y年m月d日',$cur['create_time']);
        $cur['content']=htmlspecialchars_decode($cur['content']);
        return $cur;
    }

    //获取两条广告
    public static function getTwoAd($page){
        $map=[
            ['status','=',1]
        ];
        $list=Db::name('block_page')
            ->where($map)
            ->field('id,cover,title,url as href')
            ->page($page,2)
            ->order('sort desc,create_time desc')
            ->select();
        if(!empty($list)){
            foreach ($list as &$v){
                $cover=json_decode($v['cover']);
                $v['cover']=$cover[0];
            }
        }
        return $list;
    }

    //获取资讯分类
    public static function typeList(){
        $param=Request::param();
        $id=$param['flag'];
        $page=isset($param['page'])?$param['page']:1;
        $map=[
            ['status','=',1],
            ['type','like','%'.$id.'%']
        ];
        $list=Db::name('library_news')
            ->where($map)
            ->field('id,title,summary,cover,type,source,create_time')
            ->page($page,20)
            ->order('create_time desc')
            ->select();
        if(!empty($list)){
            foreach ($list as &$v){
                $cover=json_decode($v['cover']);
                $v['cover']=get_thumb_img($cover[0],776,400);
                $ids=array_filter(explode(',',$v['type']));
                $v['type']=self::getTheme($ids);
                $v['create_time']=time_fortmat($v['create_time']);
                $v['href']=HOME_URL.'/News/details?id='.base64_encode($v['id']);
            }
        }
        return $list;
    }

}