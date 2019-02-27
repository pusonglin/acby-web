<?php
namespace app\admin\model;

use think\facade\Config;
use think\facade\Request;
use think\Model;

class Base extends Model
{

    //------------------------
    // 字母C
    //------------------------
    //校验登录状态
    public static function checkLogin()
    {
        global $admin_login;
        $admin_login = session('admin_login');
        global $loginId;
        $loginId = $admin_login['id']>0?$admin_login['id']:'-1';
        if($loginId<=0){
            if(is_ajax()){
                ajax_return(null,0,'登录已失效，请重新登录！');
            }else{
                $url = ADMIN_URL.'/login.html';
                exit("<script language=\"javascript\">window.open('".$url."','_top');</script>");
            }
        }
    }

    //验证当前登录用户的权限
    public static function checkAccess()
    {
        $passController = Config::get('template.pass_controller');
        $controllerName = request()->controller();
        global $cacheOptions;
        if(array_search($controllerName,$passController)===false){ //存在则返回键名，否则返回false
            global $admin_login;
            //当前角色的所有权限
            $roleAllRightIds = self::getRoleAllRightIds($admin_login['id']);
            //验证控制器权限
            $cacheName = "right_controllerID-".$controllerName;
            $controllerId = cache($cacheName,'',$cacheOptions);
            if(!$controllerId){
                $controllerId = get_field('right',array('url'=>$controllerName,'type'=>2));
                cache_save('right',$cacheName,$controllerId);
            }
            $isExist = in_array($controllerId,$roleAllRightIds);
            if(!$isExist){
                jump_error('您没有操作权限哦~');
            }
            //验证操作方法权限
            $passAction = Config::get('template.pass_action');
            $actionName = request()->action();;
            if(array_search($actionName,$passAction)===false){
                //获取该控制器下的所有权限id
                $cacheName = 'right_controllerID-'.$controllerId.'_childIds';
                $childIds = cache($cacheName,'',$cacheOptions);
                if(empty($childIds)){
                    $childIds = get_field('right',[['controller_id','=',$controllerId]],'id',true);
                    cache_save('right',$cacheName,$childIds);
                }
                //获取当前方法名id
                $cacheName = 'right_controllerID-'.$controllerId.'_action-'.$actionName;
                $actionId = cache($cacheName,'',$cacheOptions);
                if(!$actionId){
                    $map = [
                        ['url','=',$actionName],
                        ['id','in',$childIds]
                    ];
                    $actionId = get_field('right',$map);
                    cache_save('right',$cacheName,$actionId);
                }
                //当前方法是否存在该控制器下
                if(!$actionId){
                    jump_error('权限未添加,请联系客服~');
                }
                //当前方法是否有权限访问
                $isExist = in_array($actionId,$roleAllRightIds);
                if(!$isExist){
                    jump_error('您没有操作权限哟~');
                }
            }
        }
    }

    //------------------------
    // 字母D
    //------------------------
    //删除权限相关cache
    public static function delAuthCache()
    {
        cache_del('auth_role');
        cache_del('auth_right');
        cache_del('auth_role_right');
        cache_del('auth_party_right');
    }

    //删除权限相关cache
    public static function delRbacCache()
    {
        cache_del('role');
        cache_del('right');
        cache_del('admin');
    }


    //------------------------
    // 字母G
    //------------------------
    //获取用户的所有权限id
    public static function getRoleAllRightIds($id,$isUserId=true)
    {
        if($isUserId){
            if(!($id>0)){
                return false;
            }
            $roleid = self::getAdminRoleId($id);
            if(!($roleid>0)){
                return false;
            }
        }else{
            $roleid = $id;
        }
        $cacheName = 'allRightIds_role-'.$roleid;
        cache_save('role',$cacheName,null);
        global $cacheOptions;
        $roleAllRightIds = cache($cacheName,'',$cacheOptions);
        if(empty($roleAllRightIds)){
            $roleAllRightIds = get_field('role_right',[['role_id','=',$roleid]],'right_id',true,'right_id');
            cache_save('role',$cacheName,$roleAllRightIds);
        }
        return $roleAllRightIds;
    }

    //获取某个用户的角色id
    public static function getAdminRoleId($uid)
    {
        if(!($uid>0)){
            return false;
        }
        $cacheName = 'admin-'.$uid.'_roleid';
        global $cacheOptions;
        $roleid = cache($cacheName,'',$cacheOptions);
        if(!$roleid){
            $roleid = get_field('admin_role',array('user_id'=>$uid),'role_id',false,'role_id');
            cache_save('admin',$cacheName,$roleid);
        }
        return $roleid;
    }

    //获取地址列表
    public static function getAddressList()
    {
        $post = Request::post();
        if($post){
            $pid = $post['pid'];
            $list = get_district_childdren($pid);
            if(empty($list)){
                echo json_encode(0);
            }else{
                echo json_encode($list);
            }
        }
    }

    //获取所有角色列表
    public static function getRbacRoleList()
    {
        $cacheName = 'role-select-list';
        $rolelist = cache($cacheName);
        if(empty($rolelist)){
            $rolelist = get_select('role',array('status'=>1),'id,name');
            cache_save('role',$cacheName,$rolelist);
        }
        return $rolelist;
    }
}