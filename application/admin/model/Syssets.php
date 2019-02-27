<?php
namespace app\admin\model;

use think\Db;
use think\facade\Config;
use think\facade\Request;
use think\Model;

class Syssets extends Model
{
    //------------------------
    // 常规栏目
    //------------------------
    //新增&编辑
    public static function doCategory()
    {
        $id = Request::get('id');
        if($id>0){
            $cur = get_find('sys_category',array('id'=>$_GET['id']),'id,pid,name,code,status,summary');
            $title = '编辑栏目';
        }else{
            $pid = Request::get('pid');
            $cur = [
                'id' => 0,
                'pid' => $pid,
                'name' => '',
                'code' => '',
                'staus' => 1,
                'summary' => '',
            ];
            $title = '添加栏目';
        }
        return [
            'cur' => $cur,
            'title' => $title
        ];
    }

    //保存栏目
    public static function doneCategory()
    {
        check_no_refer();
        $data = Request::post();
        if($data){
            if($data['code']){
                $map = [
                    ['code','=',$data['code']],
                    ['status','=',1],
                    ['id','notin',$data['id']],
                ];
                $isExist = get_field('sys_category',$map);
                if($isExist){
                    exit('栏目编号已存在！');
                }
            }
            if($data['id']==0){
                $fn = 'insert';
                $error = '栏目添加失败！';
                $log = '添加';
            }else{
                $fn = 'update';
                $error = '栏目编辑失败！';
                $log = '编辑';
            }
            $log .= '栏目（'.$data['name'].'）';
            $res = Db::name('sys_category')->$fn($data);
            if($res!==false){
                cache_del('sys_category');
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo $error;
            }
        }
    }

    //删除栏目
    public static function delCategory()
    {
        check_no_refer();
        $ids = Request::post('id');
        $delIds = explode(',',$ids);
        if(!empty($delIds)){
            $map = [
                ['pid','in',$delIds]
            ];
            $childs = get_field('sys_category',$map,'id',true);
            $diff = array_diff($childs, $delIds);
            if(empty($diff)){
                $map = [
                    ['id','in',$delIds]
                ];
                $res = set_field_value('sys_category',$map,'status','=',2);

                $log = '删除栏目（ID为：'.$ids.'）';
                if($res!==false){
                    cache_del('sys_category');
                    save_logs($log);
                    echo 1;
                }else{
                    save_logs($log,2);
                    echo "删除节点失败！";
                }
            }else{
                echo "请先删除该栏目下的子栏目！";
            }
        }
    }

    //获取新闻二级分类
    public static function getNewsCateLists()
    {
        $post = Request::post();
        if($post['pid']){
            $list = get_select('sys_category',array('pid'=>$post['pid']),'id,name','sortnum desc,id');
        }else{
            $list = array();
        }
        echo json_encode($list);
    }

    //------------------------
    // 日志管理
    //------------------------
    //日志列表
    public static function logs()
    {
        $post = Request::post();
        if($post){
            $map = [
                ['a.status','<',3]
            ];
            map_format($map);

            $list = Db::view('sys_logs a','id,content,page,create_time,status')
                ->view('admin b','username','b.id = a.user_id','left')
                ->where($map)
                ->order('a.id desc')
                ->page($post['page'],$post['limit'])
                ->select();
            if(empty($list)){
                ajax_return();
            }
            foreach ($list as &$v){
                $v['create_time'] = date('Y.m.d H:i',$v['create_time']);
            }

            if($post['page']==1){
                $count = Db::view('sys_logs a','id')
                    ->view('admin b','username','b.id = a.user_id','left')
                    ->where($map)->count('a.id');
                cache(request()->controller().'_'.request()->action().'_count',$count);
            }else{
                $count = cache(request()->controller().'_'.request()->action().'_count');
                if(!($count>0)){
                    $count = Db::view('sys_logs a','id')
                        ->view('admin b','username','b.id = a.user_id','left')
                        ->where($map)->count('a.id');
                }
            }
            ajax_return($list,$count);
        }
    }

    //删除日志
    public static function delLogs()
    {
        check_no_refer();
        $ids = Request::post('id');
        if($ids){
            $map = [
                ['id','in',$ids]
            ];
            $res = set_field_value('sys_logs',$map,'status','=',3);
            $log = '删除日志（ID为：'.$ids.'）';
            if($res!==false){
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo "删除失败！";
            }
        }
    }

    //------------------------
    // APP版本管理
    //------------------------
    //版本列表
    public static function version()
    {
        $post = Request::post();
        if($post){
            $map =array();
            map_format($map);
            $list = Db::view('sys_app_version a','id,version,remark,is_must,type,create_time')
                ->where($map)
                ->order('a.id desc')
                ->page($post['page'],$post['limit'])
                ->select();
            if(empty($list)){
                ajax_return();
            }
            foreach ($list as &$v){
                $v['create_time'] = date('Y.m.d H:i',$v['create_time']);
            }
            if($post['page']==1){
                $count = Db::view('sys_app_version a','id')->where($map)->count('a.id');
                cache(request()->controller().'_'.request()->action().'_count',$count);
            }else{
                $count = cache(request()->controller().'_'.request()->action().'_count');
                if(!($count>0)){
                    $count = Db::view('sys_app_version a','id')->where($map)->count('a.id');
                }
            }
            ajax_return($list,$count);
        }
    }

    //新增&编辑
    public static function doVersion()
    {
        $id = Request::get('id');
        if($id>0){
            $cur = get_find('sys_app_version',array('id'=>$_GET['id']),'id,url,version,remark,is_must,type');
            $title = '编辑版本';
        }else{
            $cur = [
                'id' => 0,
                'url' => '',
                'version' => '',
                'remark' => '',
                'is_must' => 2,
                'type' => 1,
            ];
            $title = '添加版本';
        }
        return [
            'cur' => $cur,
            'title' => $title
        ];
    }

    //保存版本
    public static function doneVersion()
    {
        check_no_refer();
        $data = Request::post();
        if($data){
            $map = [
                ['type','=',$data['type']],
                ['version','=',$data['version']],
                ['id','notin',$data['id']],
            ];
            $isExist = get_field('sys_app_version',$map);
            if($isExist){
                exit('此版本号已存在！');
            }
            if($data['id']==0){
                $data['create_time'] = time();
                $fn = 'insert';
                $error = '版本号添加失败!';
                $log = '新增';
            }else{
                $fn = 'update';
                $error = '版本号编辑失败!';
                $log = '编辑';
            }
            $log .= '版本号（'.$data['version'].'）';
            $res = Db::name('sys_app_version')->$fn($data);
            if($res!==false){
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo $error;
            }
        }
    }

    //删除版本号
    public static function delVersion()
    {
        check_no_refer();
        $ids = Request::post('id');
        if($ids){
            $map = [
                ['id','in',$ids]
            ];
            $res = do_del('sys_app_version',$map);
            $log = '删除版本号（ID为：'.$ids.'）';
            if($res!==false){
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo "删除失败！";
            }
        }
    }

    //------------------------
    // 缓存管理
    //------------------------
    //数据缓存列表
    public static function cacheList()
    {
        $post = Request::post();
        if($post){
            $map = [
                ['a.status','=',1]
            ];
            map_format($map);
            $list = Db::view('sys_cache a','id,tab,cache_name,update_time')
                ->where($map)
                ->order('a.id desc')
                ->page($post['page'],$post['limit'])
                ->select();
            if(empty($list)){
                ajax_return();
            }
            if($post['page']==1){
                $count = Db::view('sys_cache a','id')->where($map)->count('a.id');
                cache(request()->controller().'_'.request()->action().'_count',$count);
            }else{
                $count = cache(request()->controller().'_'.request()->action().'_count');
                if(!($count>0)){
                    $count = Db::view('sys_cache a','id')->where($map)->count('a.id');
                }
            }
            ajax_return($list,$count);
        }
    }

    //删除缓存
    public static function delCache($type)
    {
        check_no_refer();
        if($type==1){
            $ids = Request::post('id');
            if(!$ids){
                exit('参数错误');
            }
            $map = [
                ['id','in',$ids]
            ];
        }else{
            $map = [
                ['status','=',1]
            ];
            $ids = '全部！';
        }
        $list = get_select('sys_cache',$map,'cache_name');
        $options = Config::get('cache.memcache');
        foreach($list as $v){
            cache($v['cache_name'],null,$options);
        }
        $res = set_field_value('sys_cache',$map,'status','=',2);
        $log = '删除缓存（ID为：'.$ids.'）';
        if($res!==false){
            save_logs($log);
            echo 1;
        }else{
            save_logs($log,2);
            echo "删除失败！";
        }
    }

    //------------------------
    // 特定文章
    //------------------------
    //文章列表
    public static function block()
    {
        $post = Request::post();
        if($post){
            $map = [];
            map_format($map);
            $list = Db::view('sys_article a','id,title,code')
                ->where($map)
                ->order('a.id desc')
                ->page($post['page'],$post['limit'])
                ->select();
            if(empty($list)){
                ajax_return();
            }
            if($post['page']==1){
                $count = Db::view('sys_article a','id')->where($map)->count('a.id');
                cache(request()->controller().'_'.request()->action().'_count',$count);
            }else{
                $count = cache(request()->controller().'_'.request()->action().'_count');
                if(!($count>0)){
                    $count = Db::view('sys_article a','id')->where($map)->count('a.id');
                }
            }
            ajax_return($list,$count);
        }
    }

    //添加&编辑
    public static function doBlock()
    {
        $id = Request::get('id');
        if($id>0){
            $cur = get_find('sys_article',array('id'=>$id),'id,title,code,content');
            up_put_old_txt($cur['content']);
            $title = '编辑模板';
        }else{
            $cur = [
                'id' => 0,
                'title' => '',
                'code' => '',
                'content' => '',
            ];
            $title = '发布模板';
        }
        return [
            'cur' => $cur,
            'title' => $title
        ];
    }

    //保存模板
    public static function doneBlock()
    {
        check_no_refer();
        $data = Request::post();
        if($data['id']==0){
            $isExist = get_field('sys_article',array('code'=>$data['code']));
            if($isExist){
                exit('code已经存在！');
            }
            $fn = 'insert';
            $error = '模板内容添加失败!';
            $log = '新增';
        }else{
            $fn = 'update';
            $error = '模板内容编辑失败!';
            $log = '编辑';
        }
        $log .= '特殊模板（'.$data['title'].'）';
        $res = Db::name('sys_article')->$fn($data);
        if($res!==false){
            //删除垃圾文件
            global $uptxt;
            global $oldtxt;
            $data = array(
                'uptxt' => $uptxt,
                'oldtxt' => $oldtxt,
                'id' => $data['id'],
                'text' => $data['content']
            );
            fsockopen_request(HOME_URL.'/fsockopen/upSaveDel',$data);
            cache_del('sys_article');
            save_logs($log);
            echo 1;
        }else{
            save_logs($log,2);
            echo $error;
        }
    }

    //删除模板
    public static function delBlock()
    {
        check_no_refer();
        $ids = Request::post('id');
        if($ids){
            $map = [
                ['id','in',$ids]
            ];
            $list = get_field('sys_article',$map,'content',true);
            $text = '';
            foreach ($list as $v){
                $text .= $v;
            }
            up_put_old_txt($text);
            $res = do_del('sys_article',$map);
            $log = '删除模板（ID为：'.$ids.'）';
            if($res!==false){
                unlink_file_after_del();
                cache_del('sys_article');
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo "删除失败！";
            }
        }
    }

    //------------------------
    // 敏感词库
    //------------------------
    //词库列表
    public static function sensitive()
    {
        $post = Request::post();
        if($post){
            $map = [];
            map_format($map);
            $list = Db::view('sys_sensitive a','id,keyword')
                ->where($map)
                ->order('a.id desc')
                ->page($post['page'],$post['limit'])
                ->select();
            if(empty($list)){
                ajax_return();
            }
            if($post['page']==1){
                $count = Db::view('sys_sensitive a','id')->where($map)->count('a.id');
                cache(request()->controller().'_'.request()->action().'_count',$count);
            }else{
                $count = cache(request()->controller().'_'.request()->action().'_count');
                if(!($count>0)){
                    $count = Db::view('sys_sensitive a','id')->where($map)->count('a.id');
                }
            }
            ajax_return($list,$count);
        }
    }

    //添加敏感词
    public static function doneSensitive()
    {
        check_no_refer();
        $keyword = Request::post('keyword');
        $data = explode(',',$keyword);
        $data = array_unique(array_filter($data));
        $old_sensitive = get_sensitive();
        if(empty($old_sensitive)){
            $diff = $data;
        }else{
            $diff = array_diff($data,$old_sensitive);
        }
        if(!empty($diff)){
            $log = '添加敏感词';
            $temp = array();
            foreach ($diff as $v){
                $temp[] = array('keyword'=>$v);
            }
            $res = Db::name('sys_sensitive')->insertAll($temp);
            if($res!==false){
                $new_sensitive = array_merge($diff,$old_sensitive);
                cache('sys_sensitive',$new_sensitive);
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo E_WLFM;
            }
        }else{
            echo '您输入的敏感词已经存在！';
        }
    }

    //删除敏感词
    public static function delSensitive()
    {
        check_no_refer();
        $ids = Request::post('id');
        if($ids){
            $map = [
                ['id','in',$ids]
            ];
            $del_sensitive = get_field('sys_sensitive',$map,'keyword',true);
            $res = Db::name('sys_sensitive')->where($map)->delete();
            $log = '删除敏感词（ID为：'.$_POST['id'].'）';
            if($res!==false){
                $old_sensitive = get_sensitive();
                if(!empty($old_sensitive)){
                    $new_sensitive = array_diff($old_sensitive,$del_sensitive);
                    cache('sys_sensitive',$new_sensitive);
                }
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo "删除失败！";
            }
        }
    }

    //------------------------
    // 搜索管理
    //------------------------
    //搜索列表
    public static function search()
    {
        $post = Request::post();
        if($post){
            $map = [];
            map_format($map);
            $list = Db::view('sys_search a','id,keyword,status,count')
                ->where($map)
                ->order('a.id desc')
                ->page($post['page'],$post['limit'])
                ->select();
            if(empty($list)){
                ajax_return();
            }
            if($post['page']==1){
                $count = Db::view('sys_search a','id')->where($map)->count('a.id');
                cache(request()->controller().'_'.request()->action().'_count',$count);
            }else{
                $count = cache(request()->controller().'_'.request()->action().'_count');
                if(!($count>0)){
                    $count = Db::view('sys_search a','id')->where($map)->count('a.id');
                }
            }
            ajax_return($list,$count);
        }
    }

    //删除搜索
    public static function delSearch()
    {
        check_no_refer();
        $ids = Request::post('id');
        if($ids){
            $map = [
                ['id','in',$ids]
            ];
            $res = do_del('sys_search',$map);
            $log = '删除搜索关键词（ID为：'.$ids.'）';
            if($res!==false){
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo "删除失败！";
            }
        }
    }
}