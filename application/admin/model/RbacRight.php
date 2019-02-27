<?php
namespace app\admin\model;

use think\Db;
use think\facade\Request;
use think\Model;

class RbacRight extends Model
{
    //后台顶部导航
    public static function topNav()
    {
        //获取当前登录用户的权限id
        global $admin_login;
        $rightids = Base::getRoleAllRightIds($admin_login['id']);
        //查询一级栏目列表
        $map = [
            ['id','in',$rightids],
            ['pid','=',0]
        ];
        $list = get_select('right',$map,'id,name','sortnum desc,id');

        //查询子集
        foreach ($list as &$v){
            $map = [
                ['pid','=',$v['id']],
                ['isnav','=',1],
                ['type','=',2],
                ['id','in',$rightids],
            ];
            $childId = get_field('right',$map,'id',false,'sortnum desc,id');
            if($childId>0){
                $where=[
                    ['pid','=',$childId],
                    ['isnav','=',1],
                    ['type','=',3],
                    ['id','in',$rightids]
                ];
                $childId2 = get_field('right',$where,'id',false,'sortnum desc,id');
                $v['fresh_id'] = $childId2>0?$childId2:$childId;
            }else{
                $v['fresh_id'] = 0;
            }
        }

        return $list;
    }

    //点击顶部导航获取功能权限
    public static function getRightList()
    {
        $top_pid = Request::post('top_pid');
        if(!($top_pid>0)){
            return false;
        }

        //获取当前登录用户的权限id
        global $admin_login;
        $rightids = Base::getRoleAllRightIds($admin_login['id']);
        if(!empty($rightids)){
            $map = [
                ['pid','=',$top_pid],
                ['top_pid','=',$top_pid],
                ['isnav','=',1],
                ['id','in',$rightids]
            ];
            $fileds = 'id,name,url,sortnum,icon';
            $order = ['sortnum'=>'desc','id'=>'asc'];
            $list = get_select('right',$map,$fileds,$order);

            //查询子集
            foreach ($list as &$v){
                $map[0] = ['pid','=',$v['id']];
                $v['_child'] = get_select('right',$map,$fileds,$order);
            }

            echo json_encode($list);
        }
    }

    //获取所有节点树状结构
    public static function getAllRightList()
    {
        $list = get_option_list(null,false,array(),'right','id,name,type,url,sortnum,pid','sortnum desc,id');
        $lists = array();
        self::formatTreelist($list,$lists);
        return $lists;
    }

    //树状结构处理
    private static function formatTreelist($list,&$lists=array())
    {
        foreach($list as $v){
            $space = '';
            if(isset($v['name'])){
                for($i=1;$i<$v['level'];$i++){
                    $space.='　　';
                }
                if($v['level']>1){
                    $line = $v['islast']=='Y'?'└─':'├─';
                }else{
                    $line = '';
                }
                $v['name'] = $space.$line.$v['name'];
                $lists[] = array(
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'pid' => $v['pid'],
                    'type' => $v['type'],
                    'url' => $v['url'],
                    'sortnum' => $v['sortnum']
                );
            }
            if(isset($v['_child'])){
                self::formatTreelist($v['_child'],$lists);
            }
        }
    }

    //新增or编辑
    public static function doRight()
    {
        $id = Request::get('id');
        if($id>0){
            $cur = get_find('right',array('id'=>$id),'id,url,name,type,pid,isnav,icon');
            $title = '编辑节点';
        }else{
            $cur = [
                'id' => 0,
                'url' => '',
                'name' => '',
                'type' => 2,
                'pid' => '',
                'isnav' => 1,
                'icon' => ''
            ];
            $title = '添加节点';
        }

        //查询权限节点
        $map = [
            ['type','<',4]
        ];
        $list = get_option_list(null,false,$map,'right','id,name,type,url,sortnum,pid','sortnum desc,id');
        $lists = array();
        self::formatTreelist($list,$lists);

        return [
            'cur' => $cur,
            'title' => $title,
            'lists' => $lists
        ];
    }

    //保存节点
    public static function doneRight()
    {
        check_no_refer();
        Db::transaction(function (){
            $data = Request::post();
            //检测节点控制器是否存在名称
            $map = [
                ['type','=',$data['type']],
                ['url','=',$data['url']],
                ['pid','=',$data['pid']],
                ['id','notin',$data['id']],
            ];
            $isExist = get_field('right',$map);
            if($isExist){
                exit('此节点URL已经存在！');
            }

            if($data['type']==1){ //系统
                $data['top_pid'] = 0;
                $data['controller_id'] = 0;
            }else if($data['type']==2){ //模块
                $pidInfo = get_find('right',array('id'=>$data['pid']),'top_pid,controller_id');
                $data['top_pid'] = $pidInfo['top_pid']>0?$pidInfo['top_pid']:$data['pid'];
                $data['controller_id'] = $pidInfo['controller_id']>0?$pidInfo['controller_id']:$data['id'];
            }else{//功能&功能点
                $pidInfo = get_find('right',array('id'=>$data['pid']),'top_pid,controller_id');
                $data['top_pid'] = $pidInfo['top_pid']>0?$pidInfo['top_pid']:$data['pid'];
                $data['controller_id'] = $pidInfo['controller_id']>0?$pidInfo['controller_id']:$data['pid'];
            }

            if($data['id']==0){
                $fn = 'insert';
                $error = '添加节点失败！';
                $log = '添加';
            }else{
                $fn = 'update';
                $error = '节点修改失败！';
                $log = '编辑';
            }
            $log .= '节点（'.$data['name'].'）';
            $res = Db::name('right')->$fn($data);
            if($res!==false){
                if($data['id']==0){
                    //为超级管理自动配置权限
                    $super = get_field('role',array('is_super'=>1),'id',true);
                    if(!empty($super)){
                        $rdata = array();
                        foreach($super as $v){
                            $rdata[] = array(
                                'role_id' => $v,
                                'right_id' => $res
                            );
                        }
                        Db::name('role_right')->insertAll($rdata);
                    }
                }

                if($data['type']==2 && $data['id']==0 && $res>0){
                    set_field_value('right',array('id'=>$res),'controller_id','=',$res);
                }else{
                    set_field_value('right',array('controller_id'=>$data['controller_id']),'top_pid','=',$data['top_pid']);
                }

                if($data['type']==3 && $data['id']>0){
                    set_field_value('right',array('pid'=>$data['id']),'controller_id','=',$data['controller_id']);
                }

                Base::delRbacCache();
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo $error;
            }
        });
    }

    //删除节点
    public static function delRight()
    {
        check_no_refer();
        Db::transaction(function (){
            $ids = Request::post('id');
            $delIds = explode(',',$ids);
            if(!empty($delIds)){
                $map = [
                    ['pid','in',$delIds]
                ];
                $childs = get_field('right',$map,'id',true);
                $diff = array_diff($childs, $delIds);
                if(empty($diff)){
                    $map = [
                        ['id','in',$delIds]
                    ];
                    $res = do_del('right',$map);

                    //删除角色权限
                    if($res!==false){
                        $map = [
                            ['right_id','in',$delIds]
                        ];
                        $res = do_del('role_right',$map);
                    }

                    $log = '删除节点（ID为：'.$ids.'）';
                    if($res!==false){
                        Base::delRbacCache();
                        save_logs($log);
                        echo 1;
                    }else{
                        save_logs($log,2);
                        echo "删除节点失败！";
                    }
                }else{
                    echo "请先删除该节点下的子菜单！";
                }
            }
        });
    }
}