<?php
/**
 * Created by PhpStorm.
 * User: zejun.lei@foxmail.com
 * Date: 2019.02.18
 * Time: 11:31
 */

namespace app\admin\model;


use think\Db;
use think\facade\Cache;
use think\facade\Request;
use think\Model;

class Document extends Model
{
    //新闻列表
    public static function lists(){
        $post=Request::post();
        global $admin_login;
        $map=[
                ['a.status','<',3],
                ['a.user_id','=',$admin_login['id']]
            ];
        map_format($map);

        $list=Db::view('library_news a','id,first_cate_id,title,pv,zan_num,collect_num,cover,status,is_push,is_essence,is_hot,create_time,user_id')->view('admin b','realname','b.id = a.user_id','left')->view('sys_category c','name category','c.id = a.first_cate_id','left')->where($map)->order('a.id desc')->page($post['page'],$post['limit'])->select();
        if(empty($list)){
            ajax_return();
        }
        foreach($list as &$v){
            $v['create_time']=date('Y.m.d',$v['create_time']);
            $v['has_cover']=$v['cover']?1:2;
            $cover=json_decode($v['cover']);
            if(count($cover)>0){
                $v['cover']=get_thumb_img($cover[0],135,90);
            }else{
                $v['cover']=get_thumb_img('/static/admin/images/nopic.jpg',135,90);
            }
        }

        $cacheName=request()->controller().'_'.request()->action().'_count';
        if($post['page']==1){
            $count=Db::view('library_news a','id')->where($map)->count('a.id');
            Cache::set($cacheName,$count);
        }else{
            $count=Cache::get($cacheName);
            if(!($count>0)){
                $count=Db::view('library_news a','id')->where($map)->count('a.id');
            }
        }

        ajax_return($list,$count);
    }

    //新增&编辑
    public static function doNews(){
        $id=Request::get('id');
        global $admin_login;
        $typeList=get_select('news_type',array('user_id'=>$admin_login['id']),'id,name');
        if($id>0){
            $field='id,title,second_cate_id,summary,first_cate_id,type,cover,content,create_time,attachment,status,source';
            $cur=get_find('library_news',['id'=>$id],$field);
            $cur['attachment_json']=$cur['attachment'];
            $cur['cover_json']=$cur['cover'];
            $cur['attachment']=json_decode($cur['attachment'],true);
            $cur['cover']=json_decode($cur['cover'],true);
            $cur['content']=htmlspecialchars_decode($cur['content']);
            up_put_old_txt(serialize($cur['cover']).$cur['content'].serialize($cur['attachment']));
            $cur['create_time']=date('Y-m-d H:i:s',$cur['create_time']);
            $type_id=explode(',',$cur['type']);
            foreach($typeList as &$v){
                if(in_array($v['id'],$type_id)){
                    $v['isCheck']=1;
                }else{
                    $v['isCheck']=2;
                }
            }
            $cur['typeList']=$typeList;
            $title='编辑内容';
        }else{
            foreach($typeList as $key=>&$v){
                if($key==0){
                    $v['isCheck']=1;
                }else{
                    $v['isCheck']=2;
                }
            }
            $cur=['id'=>0,'title'=>'','first_cate_id'=>'','second_cate_id'=>'','cover'=>'','type'=>'','summary'=>'','cover_json'=>'','content'=>'','attachment'=>'','attachment_json'=>'','status'=>1,'source'=>'','create_time'=>date('Y-m-d H:i:s'),'typeList'=>$typeList];
            $title='添加内容';
        }
        return ['cur'=>$cur,'title'=>$title];
    }

    //保存内容
    public static function doneNews(){
        check_no_refer();
        $data=Request::post();
        unset($data['editorValue']);
        if($data){
            $data['create_time']=strtotime($data['create_time']);
            $data['attachment']=json_decode($data['attachment'],true);
            if(!empty($data['attachment'])){
                foreach($data['attachment'] as &$v){
                    if($v['size']==0){
                        $v['size']=get_file_size($v['url']);
                        $v['type']=get_file_type($v['url']);
                    }
                }
            }
            $data['cover'] = json_decode($data['cover'],true);
            //$data['cover']=explode(',',$data['cover']);
            //$data['cover']=json_encode($data['cover']);
            $data['cover'] = empty($data['cover'])?'':json_encode($data['cover']);
            $data['attachment']=empty($data['attachment'])?'':json_encode($data['attachment']);
            $data['content']=htmlspecialchars($data['content']);
            if($data['id']==0){
                global $admin_login;
                $fn='insertGetId';
                $data['user_id']=$admin_login['id'];
                $log='新增';
            }else{
                $fn='update';
                $log='编辑';
            }
            $log.='新闻资讯（'.$data['title'].'）';
            $res=Db::name('library_news')->$fn($data);
            if($res!==false){
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo E_WLFM;
            }
        }
    }


    //删除文章
    public static function delNews(){
        check_no_refer();
        Db::transaction(
            function(){
                $ids=Request::post('id');
                if($ids){
                    $map=[['id','in',$ids],];
                    $pids=get_field('library_news',$map,'pid',true);
                    unset($map[1]);
                    unset($map[2]);
                    $data=array('status'=>3,'is_banner'=>2,'banner_time'=>0,'is_home'=>2);
                    $res=Db::name('library_news')->where($map)->update($data);
                    if($res!==false){
                        $map=[['pid','in',$ids]];
                        $res=do_del('library_home',$map);
                    }
                    if($res!==false&&!empty($pids)){
                        $map=[['pid','in',$pids],['party_id','=',0]];
                        $res=do_del('library_reprint',$map);
                    }
                    $log='删除文库文章（ID为：'.$ids.'）';
                    if($res!==false){
                        save_logs($log);
                        echo 1;
                    }else{
                        save_logs($log,2);
                        echo "删除失败！";
                    }
                }
            });
    }


    //主题列表
    public static function theme(){
        $post=Request::post();
        if($post){
            global $admin_login;
            $map=[['status','=',1],['user_id','=',$admin_login['id']]];
            map_format($map);
            $list=Db::view('news_type','id,name,create_time')->where($map)->order('id desc')->page($post['page'],$post['limit'])->select();

            if(empty($list)){
                ajax_return();
            }

            foreach($list as &$v){
                $v['create_time']=date('Y-m-d H:i',$v['create_time']);
            }

            $cacheName=request()->controller().'_'.request()->action().'_count';
            if($post['page']==1){
                $count=Db::view('news_type','id')->where($map)->count('id');
                Cache::set($cacheName,$count);
            }else{
                $count=Cache::get($cacheName);
                if(!($count>0)){
                    $count=Db::view('news_type','id')->where($map)->count('id');
                }
            }
            ajax_return($list,$count);
        }
    }

    //新增&编辑主题
    public static function doTheme()
    {
        $id = Request::get('id');
        if($id>0){
            $cur = get_find('news_type',array('id'=>$id),'id,name,status');
            $title = '编辑主题';
        }else{
            $cur = [
                'id' => 0,
                'name' => '',
                'status' => 2
            ];
            $title = '添加主题';
        }

        return [
            'cur' => $cur,
            'title' => $title
        ];
    }


    //保存主题
    public static function doneTheme()
    {
        check_no_refer();
        $data = Request::post();
        if(!empty($data)){
            global $admin_login;
            if($data['id']==0){
                $fn = 'insertGetId';
                unset($data['id']);
                $data['user_id'] = $admin_login['id'];
                $data['create_time'] = time();
                $log = '新增';
            }else{
                $fn = 'update';
                $log = '编辑';
            }
            $log .= '主题（'.$data['name'].'）';
            $res = Db::name('news_type')->$fn($data);
            if($res!==false){
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo E_WLFM;
            }
        }
    }


    //删除主题
    public static function delTheme()
    {
        check_no_refer();
        $ids = Request::post('id');
        if($ids){
            $map = [
                ['id','in',$ids]
            ];
            $res = set_field_value('news_type',$map,'status','=',3);
            $log = '删除活动（ID为：'.$ids.'）';
            if($res!==false){
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo "删除失败！";
            }
        }
    }

    /*********************************发现*********************************/
    public static function discovery(){
        $post=Request::post();
        $map =[
            ['a.status','=',1]
        ];
        map_format($map);
        $list=Db::name('discover_news')
            ->alias('a')
            ->join('admin b','b.id=a.create_uid','left')
            ->field('a.id,a.sort,a.url,a.title,a.status,a.create_time,b.realname as create_uid')
            ->where($map)
            ->order('a.id desc')
            ->page($post['page'],$post['limit'])
            ->select();
        if(empty($list)){
            ajax_return();
        }
        foreach ($list as &$v){
            $v['create_time'] = time_fortmat($v['create_time'],'i','.');
        }

        if($post['page']==1){
            $count = Db::view('discover_news a','id')->where($map)->count();
            Cache::set(request()->controller().'_'.request()->action().'_count',$count);
        }else{
            $count = Cache::get(request()->controller().'_'.request()->action().'_count');
            if(!($count>0)){
                $count = Db::view('discover_news a','id')->where($map)->count();
            }
        }
        ajax_return($list,$count);
    }

    //添加&编辑页面模块
    public static function doDiscovery()
    {
        $id = Request::get('id');
        if($id>0){
            $cur = get_find('discover_news',['id'=>$id],'*');
            $cur['create_time'] = date('Y-m-d H:i:s',$cur['create_time']);
            $cur['cover_json']=$cur['cover'];
            $cur['cover']=json_decode($cur['cover']);
            $title = '编辑发现';
        }else{
            $cur = [
                'id' => 0,
                'title' => '',
                'url' => '',
                'cover' => '',
                'cover_json'=>'',
                'status' => 2,
                'create_time' => date('Y-m-d H:i:s',time()),
                'sort'=>0
            ];
            $title = '添加发现';
        }
        return [
            'cur' => $cur,
            'title' => $title
        ];
    }

    //保存模块信息
    public static function doneDiscovery()
    {
        $post = Request::post();
        if($post){
            $data = $post;
            unset($data['editorValue']);
            $data['cover'] = json_decode($data['cover'],true);
            $data['cover'] = empty($data['cover'])?'':json_encode($data['cover']);
            global $loginId;
            if($data['id']==0){
                $fn = 'insertGetId';
                $error = '模块添加失败！';
                $log = '添加';
                $data['create_time'] = time();
                $data['create_uid'] = $loginId;
            }else{
                $fn = 'update';
                $error = '模块编辑失败！';
                $log = '编辑';
            }
            if($data['id']==0){
                unset($data['id']);
            }
            $res = Db::name('discover_news')->$fn($data);
            if($res!==false){
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo $error;
            }
        }
    }

    //删除模块
    public static function delDiscovery()
    {
        Db::transaction(function (){
            $id = Request::post('id');
            if($id){
                $map = [
                    ['id','in',$id]
                ];
                $res = do_del('discover_news',$map);
                $log = '删除发现（ID为：'.$id.'）';
                if($res!==false){
                    save_logs($log);
                    echo 1;
                }else{
                    save_logs($log,2);
                    echo E_WLFM;
                }
            }
        });
    }
}