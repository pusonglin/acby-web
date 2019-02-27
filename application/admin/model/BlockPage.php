<?php
namespace app\admin\model;

use think\Db;
use think\facade\Cache;
use think\facade\Request;
use think\Model;

class BlockPage extends Model
{
    //广告页面列表
    public static function pageList()
    {
        $post = Request::post();
        $map =array();
        map_format($map);
        $list = Db::view('block_page a','id,title,url,pub_time')
            ->view('admin b','realname','b.id = a.act_uid','left')
            ->where($map)
            ->order('a.id desc')
            ->page($post['page'],$post['limit'])
            ->select();
        if(empty($list)){
            ajax_return();
        }
        if($post['page']==1){
            $count = Db::view('block_page a','id')->where($map)->count('a.id');
            Cache::set(request()->controller().'_'.request()->action().'_count',$count);
        }else{
            $count = Cache::get(request()->controller().'_'.request()->action().'_count');
            if(!($count>0)){
                $count = Db::view('block_page a','id')->where($map)->count('a.id');
            }
        }
        ajax_return($list,$count);
    }

    //新增or编辑广告页面
    public static function doPage()
    {
        $id = Request::get('id');
        if($id>0){
            $cur = get_find('block_page',array('id'=>$id),'id,title,url');

            $title = '编辑页面';
        }else{
            $cur = [
                'id' => 0,
                'title' => '',
                'url' => ''
            ];
            $title = '添加页面';
        }
        return [
            'cur' => $cur,
            'title' => $title
        ];
    }

    //保存广告页面信息
    public static function donePage()
    {
        $post = Request::post();
        if($post){
            global $loginId;
            $data = $post;
            if($data['id']==0){
                $fn = 'insert';
                $log = '添加';
                $data['create_time'] = time();
                $data['create_uid'] = $loginId;
            }else{
                $fn = 'update';
                $log = '编辑';
            }
            $log .= '广告页面（'.$post['title'].'）';
            $res = Db::name('block_page')->$fn($data);
            if($res!==false){
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo E_WLFM;
            }
        }
    }

    //删除广告页面
    public static function delPage()
    {
        $ids = Request::post('id');
        $delIds = explode(',',$ids);
        if($delIds){
            $map = [
                ['page_id','in',$delIds]
            ];
            $isExist = get_field('block',$map);
            if($isExist){
                echo "请先删除该页面中的广告位！";
                exit;
            }

            $map = [
                ['id','in',$delIds]
            ];
            $res = do_del('block_page',$map);
            $log = '删除广告页面（ID为：'.$ids.'）';
            if($res!==false){
                save_logs($log);
                Base::delAuthCache();
                echo 1;
            }else{
                save_logs($log,2);
                echo "删除功能失败！";
            }
        }
    }

    //发布广告页面
    public static function pubPage()
    {
        return Db::transaction(function (){
            $id = Request::post('id');
            if($id>0){
                $blockids = get_field('block',array('page_id'=>$id),'id',true);
                if(empty($blockids)){
                    echo '此页面下没有可发布的广告位！';
                    exit;
                }
                $map = [
                    ['status','=',2],
                    ['block_id','in',$blockids]
                ];
                $res = set_field_value('block_data',$map,'status','=',1);
                if($res!==false) {
                    global $loginId;
                    $data = [
                        'id' => $id,
                        'pub_time' => time(),
                        'act_uid' => $loginId
                    ];
                    Db::name('block_page')->update($data);

                    //删除垃圾数据及文件
                    $useImgs = array();
                    $allImgs = array();
                    $delids = array();
                    $map = [
                        ['block_id','in',$blockids]
                    ];
                    $list = get_select('block_data',$map,'id,content,block_id','block_id desc,id desc');
                    $block_id = 0;
                    foreach ($list as $v){
                        if($block_id==$v['block_id']){
                            $delids[] = $v['id'];
                        }
                        $content = json_decode($v['content'],true);
                        foreach ($content as $v1) {
                            foreach ($v1 as $v2) {
                                if ($v2['isupload'] == 1 && $v2['val']) {
                                    $allImgs[] = $v2['val'];
                                    if ($block_id!=$v['block_id']) {
                                        $useImgs[] = $v2['val'];
                                    }
                                }
                            }
                        }
                        $block_id = $v['block_id'];
                    }
                    $allImgs = array_unique($allImgs);
                    $useImgs = array_unique($useImgs);
                    $diff = array_diff($allImgs, $useImgs);
                    if (!empty($delids)) {//删除垃圾数据
                        $map = [
                          ['id','in',$delids]
                        ];
                        do_del('block_data',$map);
                    }
                    if (!empty($diff)) {//删除垃圾文件
                        up_del_nouse($diff);
                    }
                    return $blockids;
                }
            }
        });
    }

    //模块列表
    public static function blockList()
    {
        $post = Request::post();
        $map =[
            ['a.status','=',1]
        ];
        map_format($map);
        $list=Db::name('block_page')
            ->alias('a')
            ->join('admin b','b.id=a.create_uid','left')
            ->join('admin c','c.id=a.act_uid','left')
            ->field('a.id,a.sort,a.url,a.title,a.status,a.create_time,b.realname as create_uid,a.pub_time,c.realname as act_uid')
            ->where($map)
            ->order('a.id desc')
            ->page($post['page'],$post['limit'])
            ->select();
        if(empty($list)){
            ajax_return();
        }
        foreach ($list as &$v){
            $v['create_time'] = time_fortmat($v['create_time'],'i','.');
            $v['pub_time'] = date('Y-m-d H:i',$v['pub_time']);
        }

        if($post['page']==1){
            $count = Db::view('block_page a','id')->where($map)->count();
            Cache::set(request()->controller().'_'.request()->action().'_count',$count);
        }else{
            $count = Cache::get(request()->controller().'_'.request()->action().'_count');
            if(!($count>0)){
                $count = Db::view('block_page a','id')->where($map)->count();
            }
        }
        ajax_return($list,$count);
    }

    //添加&编辑页面模块
    public static function doBlock()
    {
        $id = Request::get('id');
        if($id>0){
            $cur = get_find('block_page',['id'=>$id],'*');
            $cur['create_time'] = date('Y-m-d H:i:s',$cur['create_time']);
            if($cur['pub_time']!=''){

                $cur['pub_time'] = date('Y-m-d H:i:s',$cur['pub_time']);
            }
            $cur['cover_json']=$cur['cover'];
            $cur['cover']=json_decode($cur['cover']);
            $title = '编辑模块';
        }else{
            $cur = [
                'id' => 0,
                'title' => '',
                'url' => '',
                'cover' => '',
                'cover_json'=>'',
                'status' => 2,
                'create_time' => date('Y-m-d H:i:s'),
                'pub_time' => '',
                'sort'=>0
            ];
            $title = '添加模块';
        }
        return [
            'cur' => $cur,
            'title' => $title
        ];
    }

    //保存模块信息
    public static function doneBlock()
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
            if($data['pub_time']!=''){
                if($data['id']==0){
                    $data['act_uid'] = $loginId;
                }else{
                    $pu_time=Db::name('block_page')->where('id','=',$data['id'])->value('pub_time');
                    if($data['pub_time']!=$pu_time){
                        $data['act_uid'] = $loginId;
                    }
                }
            }
            if($data['id']==0){
                unset($data['id']);
            }
            $res = Db::name('block_page')->$fn($data);
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
    public static function delBlock()
    {
        Db::transaction(function (){
            $id = Request::post('id');
            if($id){
                $map = [
                    ['id','in',$id]
                ];
                $res = do_del('block_page',$map);
                $log = '删除广告位（ID为：'.$id.'）';
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

    //模板信息查询
    public static function blockData()
    {
        $id = Request::get('id');
        $cur = Db::view('block a','id,page_id,title,profiles,count,conditions')
            ->view('block_page b','title page_title','b.id = a.page_id','left')
            ->where(['a.id'=>$id])
            ->find();
        if(empty($cur)){
            return false;
        }
        $content = get_field('block_data',['block_id'=>$id],'content',false,'id desc');
        $conditions = json_decode($cur['conditions'],true);
        if(isset($content)){
            $content = json_decode($content,true);
            foreach ($conditions as $k=>&$v){
                foreach ($v as $k1=>&$v1){
                    $v1['val'] = $content[$k][$k1]['val'];
                }
            }
        }
        $cur['conditions'] = $conditions;
        return $cur;
    }

    //模板数据写入
    public static function saveData()
    {
        Db::transaction(function (){
            $post = Request::post();
            if($post){
                global $loginId;
                $profiles = $post['profiles'];
                $count = $post['count'];
                $zdsarr = explode(',',$profiles);
                $temps = array();
                $imgstr = '';
                for($i=0;$i<$count;$i++){
                    $temp = array();
                    foreach($zdsarr as $v){
                        $temp[$v]['val'] = $post[$v][$i];
                        $temp[$v]['isupload'] =  preg_match("/_upload/i",$v)?1:0;
                        $imgstr .= preg_match("/_upload/i",$v)?'<img src="'.$_POST[$v][$i].'" />':'';
                    }
                    $temps[] = $temp;
                }
                $data = array(
                    'block_id' => $_POST['block_id'],
                    'content' => json_encode($temps),
                    'status'  => 2,
                    'create_time' => time(),
                    'create_uid'  => $loginId
                );
                $res =  Db::name('block_data')->insert($data);
                if($res!==false){
                    $data1 = array(
                        'id' => $_POST['block_id'],
                        'update_time' => time(),
                        'update_uid' => $loginId
                    );
                    $res = Db::name('block')->update($data1);
                }
                if($res!==false){
                    up_save_del(0,$imgstr);
                    echo 1;
                }else{
                    echo E_WLFM;
                }
            }
        });
    }
}