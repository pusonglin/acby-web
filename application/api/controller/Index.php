<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/21
 * Time: 16:02
 */

namespace app\api\controller;


use think\Db;
use think\facade\Request;

class Index extends Common
{

    //获取首页轮播
    public function getHomeSwiper(){
        check_sign(array('token'));
        $post=Request::post();
        $first_cate=isset($post['first_cate'])?$post['first_cate']:0;
        $map=[
            ['status','=',1],
            ['is_essence','=',1]
        ];
        if($first_cate){
            $map[]=['first_cate_id','=',$first_cate];
        }
        $list=Db::name('library_news')
            ->where($map)
            ->field('id,title,summary,cover,type,source')
            ->limit(5)
            ->order('essence_time desc')
            ->select();
        if(!empty($list)){
            foreach ($list as &$v){
                $cover=json_decode($v['cover']);
                $v['cover']=get_thumb_img($cover[0],776,400);
                $ids=array_filter(explode(',',$v['type']));
                $v['type']=self::getTheme($ids);
            }
        }
        res_api('ok',$list);
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

    //获取新闻列表
    public function getNewsList(){
        check_sign(array('page','token'));
        $post=Request::post();
        $post['page']=isset($post['page'])?$post['page']:1;
        $first_cate=isset($post['first_cate'])?$post['first_cate']:0;
        $keyword=isset($post['keyword'])?$post['keyword']:0;
        $map=[
            ['status','=',1],
            ['is_push','=',1]
        ];
        if($first_cate){
            $map[]=['first_cate_id','=',$first_cate];
        }
        if($keyword){
            $map[]=['title','like','%'.$keyword.'%'];
        }
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
            }
        }
        $ad=self::getTwoAd($post['page']);
        $data=array(
            'ad'=>$ad,
            'list'=>$list
        );
        res_api('ok',$data);
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

    //获取资讯详情
    public function getNewsDetail(){
        check_sign(array('id','token'));
        global $loginId;
        $info= Db::name('member')->where('id','=',$loginId)->field('id,nickname')->find();
        $id=Request::post('id');
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
        $cur['is_zan'] =2;
        $cur['is_collection'] =2;
        if(!empty($info)){
            $time=time();
            $data=array(
                'pid'=>$id,
                'uid'=>$cur['id'],
                'flag'=>1,
                'create_time'=>$time
            );
            $map=[
                ['pid','=',$id],
                ['uid','=',$cur['id']],
                ['flag','=',1]
            ];
            $isExist=Db::name('member_glance')->where($map)->value('id');
            if($isExist){
                Db::name('member_glance')->where($map)->setField('create_time',$time);
            }else{
                Db::name('member_glance')->insert($data);
            }
            $collect_id=Db::name('collection')->where($map)->value('id');
            if($collect_id){
                $cur['is_collection'] =1;
            }
            $zan_id=Db::name('library_news_zan')->where($map)->value('id');
            if($zan_id){
                $cur['is_zan'] =1;
            }

        }
        res_api('ok',$cur);
    }
}