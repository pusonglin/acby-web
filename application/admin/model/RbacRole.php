<?php
namespace app\admin\model;

use think\Db;
use think\facade\Request;
use think\Model;

class RbacRole extends Model
{
    //------------------------
    // 角色管理
    //------------------------
    //角色列表
    public static function index()
    {
        $post = Request::post();
        $map =array();
        map_format($map);

        $list = Db::view('role a','id,name,is_super,status,remark')
            ->where($map)
            ->order('a.id')
            ->page($post['page'],$post['limit'])
            ->select();
        if(empty($list)){
            ajax_return();
        }

        if($post['page']==1){
            $count = Db::view('role a','id')->where($map)->count('a.id');
            cache(request()->controller().'_'.request()->action().'_count',$count);
        }else{
            $count = cache(request()->controller().'_'.request()->action().'_count');
            if(!($count>0)){
                $count = Db::view('role a','id')->where($map)->count('a.id');
            }
        }
        ajax_return($list,$count);
    }

    //新增or编辑
    public static function doRole()
    {
        $id = Request::get('id');
        if($id>0){
            $map['id'] = $_GET['id'];
            $cur = get_find('role',$map);
            $title = '编辑角色';
        }else{
            $cur = [
                'id' => 0,
                'name' => '',
                'is_super' => 2,
                'remark' => '',
                'status' => 1,
            ];
            $title = '添加角色';
        }
        return [
            'cur' => $cur,
            'title' => $title
        ];
    }

    //保存数据
    public static function doneRole()
    {
        check_no_refer();
        $post = Request::post();
        if($post){
            $map = [
                ['name','=',$post['name']],
                ['id','notin',$post['id']]
            ];
            $isExist = get_field('role',$map);
            if($isExist){
                exit('角色名称已存在！');
            }
            $data = $post;
            if($data['id']==0){
                $fn = 'insert';
                $error = '添加角色失败！';
                $log = '添加';
            }else{
                $fn = 'update';
                $error = '编辑角色失败！';
                $log = '编辑';
            }
            $res = Db::name("role")->$fn($data);
            $id = $data['id']>0?$data['id']:$res;
            $log .= '角色（ID为：'.$id.'）';
            if($res!==false){
                Base::delRbacCache();
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo $error;
            }
        }
    }

    //删除角色
    public static function delRole()
    {
        check_no_refer();
        Db::transaction(function (){
            $ids = Request::post('id');
            $map = [
                ['id','in',$ids]
            ];
            $res = do_del('role',$map);
            $log = '删除角色（ID为：'.$ids.'）';
            if($res!==false) {
                $maps = [
                    ['role_id','in',$ids]
                ];
                do_del('admin_role',$maps);
                do_del('role_right',$maps);
                Base::delRbacCache();
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo '删除失败！';
            }
        });
    }

    //为角色配置权限
    public static function setRight()
    {
        //获取级联菜单
        $id = Request::get('id');
        $cacheName = 'right_str';
        $str = cache($cacheName);
        if(!$str){
            $list = get_option_list(null,false,array(),'right','id,name,type,url,sortnum,pid','sortnum desc,id');
            if(!empty($list)){
                //获取当前角色的权限
                $currightids = Base::getRoleAllRightIds($id,false);
                $str = '<div class="tree tree-right">';
                $str .= '   <ul>';
                $str .=         self::right_list($list,$currightids);
                $str .= '   </ul>';
                $str .= '</div>';
            }else{
                $str = 'nodata';
            }
            cache_save('right',$cacheName,$str);
        }

        //是否刷新
        global $loginId;
        $cur_role_id = Base::getAdminRoleId($loginId);
        $fresh = $cur_role_id==$id?1:2;
        return [
            'str' => $str,
            'fresh' => $fresh
        ];
    }

    //拼接权限列表
    private static function right_list($list,$currightids)
    {
        $str = '';
        foreach($list as $v){
            if(in_array($v['id'],$currightids)){
                $checked =' checked="checked" ';
                $active = ' check_active';
            }else{
                $checked = '';
                $active = '';
            }
            $str .= '<li>';
            if(isset($v['_child'])){
                $str .= '<span><i class="fa fa-angle-double-down"></i><label><input class="right" value="'.$v["id"].'" type="checkbox" '.$checked.' /><b class="check_list '.$active.'"></b>'.$v["name"].'</label></span>';
                $str .= '<ul>';
                $str .= self::right_list($v['_child'],$currightids);
                $str .= '</ul>';
            }else{
                $str .= '<span><i class="fa fa-leaf"></i><label><input class="right" value="'.$v["id"].'" type="checkbox" '.$checked.'/><b class="check_list '.$active.'"></b>'.$v["name"].'</label></span>';
            }
            $str .= '</li>';
        }
        return $str;
    }

    //执行配置权限
    public static function doSetRight()
    {
        check_no_refer();
        $post = Request::post();
        if($post['right_ids']){
            $data['role_id'] = $post['role_id'];
            $datas = array();
            foreach($post['right_ids'] as $v){
                $data['right_id'] = $v;
                $datas[] = $data;
            }
            do_del('role_right',array('role_id'=>$post['role_id']));
            $res = Db::name('role_right')->insertAll($datas);
        }else{
            $res = do_del('role_right',array('role_id'=>$post['role_id']));
        }
        $log = '为'.$_POST['role_name'].'配置权限！';
        if($res!==false){
            Base::delRbacCache();
            save_logs($log);
            echo 1;
        }else{
            save_logs($log,2);
            echo '权限保存失败！';
        }
    }

}