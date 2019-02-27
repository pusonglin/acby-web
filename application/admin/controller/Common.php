<?php
namespace app\admin\controller;
use app\admin\model\Base;
use think\Controller;
use think\Db;
use think\facade\Config;
use think\facade\Request;

class Common extends Controller
{
    //初始化
    public function initialize()
    {
        global $cacheOptions;
        $cacheOptions = Config::get('cache.memcache');
        Base::checkLogin();
        Base::checkAccess();
        set_txt_dir();
    }


    //------------------------
    // 字母S
    //------------------------
    //改变某个字段的值
    public function setZdVal()
    {
        $post = Request::post();
        if($post){
            check_no_refer();
            $data = array(
                'id' => $post['id'],
                $post['zdName'] => $post['zdVal']
            );
            if(isset($post['timeZd'])){
                $data[$post['timeZd']] = $post['zdVal']==1?time():0;
            }
            $res = Db::name($post['tb'])->update($data);
            if($res!==false){
                //改变推荐内容
                self::setRecommend();
                //党组织业务配置
                if($post['tb']=='auth_party_action'){
                    self::setPartyAction();
                }
                cache_del($post['tb']);
                echo 1;
            }else{
                echo '操作失败！';
            }
        }
    }

    //推荐到首页和学习版块
    private function setRecommend()
    {
        $post = Request::post();
        if($post['zdName']=='is_home'){
            if($post['zdVal']==1){
                //设为推荐
                $info = get_find($post['tb'],array('id'=>$post['id']),'title,cover,status,create_time');
                $data = array(
                    'pid' => $post['id'],
                    'title' => $info['title'],
                    'status' => $info['status'],
                    'cover' => $info['cover'],
                    'party_id' => 0,
                    'create_time' => $info['create_time']
                );
                $res = Db::name('library_home')->insert($data);
                if($res){
                    $list = get_select('library_home',array('party_id'=>0),'id,pid');
                    if(count($list)>5){
                        $ids = array();
                        $pids = array();
                        foreach ($list as $k=>$v){
                            if($k>4){
                                $ids[] = $v['id'];
                                $pids[] = $v['pid'];
                            }
                        }

                        do_del('library_home',['id'=>$ids]);
                        set_field_value($post['tb'],['id'=>$pids],'is_home','=',2);
                    }
                }
            }else{
                //取消推荐
                $map = array(
                    'pid' => $post['id'],
                    'party_id' => 0,
                );
                Db::name('library_home')->where($map)->delete();
            }
        }else if($post['zdName']=='status'){
            switch ($post['tb']){
                case 'library_news':
                    set_field_value('library_home',array('pid'=>$post['id'],'party_id'=>0),'status','=',$post['zdVal']);
                    break;
                default:return false;
            }
        }else if($post['zdName']=='study_home'){
            switch ($post['tb']){
                case 'library_education':
                    $flag = 1;
                    break;
                case 'exam':
                    $flag = 2;
                    break;
                case 'study':
                    $flag = 3;
                    break;
                default:return false;
            }
            if($post['zdVal']==1){
                //设为推荐
                $info = get_find($post['tb'],array('id'=>$post['id']),'title,cover,status,create_time');
                $data = array(
                    'pid' => $post['id'],
                    'flag' => $flag,
                    'title' => $info['title'],
                    'status' => $info['status'],
                    'cover' => $info['cover'],
                    'party_id' => 0,
                    'create_time' => $info['create_time']
                );
                $res = Db::name('study_home')->insert($data);
                if($res){
                    $list = get_select('study_home',array('flag'=>$flag,'party_id'=>0),'id,pid');
                    if(count($list)>5){
                        $ids = array();
                        $pids = array();
                        foreach ($list as $k=>$v){
                            if($k>4){
                                $ids[] = $v['id'];
                                $pids[] = $v['pid'];
                            }
                        }
                        do_del('study_home',['id'=>$ids]);
                        set_field_value($post['tb'],['id'=>$pids],'study_home','=',2);
                    }
                }
            }else{
                //取消推荐
                $map = array(
                    'pid' => $post['id'],
                    'flag' => $flag,
                    'party_id' => 0,
                );
                Db::name('study_home')->where($map)->delete();
            }
        }
    }

    //开启和关闭党组织业务
    private function setPartyAction()
    {
        $post = Request::post();
        $party_id = get_field('auth_party_action',array('id'=>$post['id']),'party_id');
        if($party_id>0){
            Db::name('auth_party_right')->where(array('party_id'=>$party_id))->delete();
            cache_del('auth_party_right','auth_partyRightIds_party-'.$party_id);
            cache_del('auth_right');
        }
    }


    //------------------------
    // 字母T
    //------------------------
    //提示用户在该页面中的权限
    public function telUserAccess($rightArr){
        //当前控制器id
        $controllerName = request()->controller();
        $cacheName = 'right_controllerID-'.$controllerName;

        global $cacheOptions;
        $controllerId = cache($cacheName,'',$cacheOptions);
        if(!$controllerId){
            $controllerId = get_field('right',array('url'=>$controllerName,'type'=>2));
            cache_save('right',$cacheName,$controllerId);
        }

        //查询当前页面下的所有方法的名称
        global $admin_login;
        $roleId = Base::getAdminRoleId($admin_login['id']);
        $cacheName = 'right_controllerID-'.$controllerId.'_roleID-'.$roleId.'_childIds_url';
        $childUrls =  cache($cacheName,'',$cacheOptions);

        if(empty($childUrls)){
            //当前角色的所有权限
            $roleAllRightIds = Base::getRoleAllRightIds($admin_login['id']);
            $map = [
                ['controller_id','=',$controllerId],
                ['id','in',$roleAllRightIds]
            ];
            $childUrls = get_field('right',$map,'url',true);
            cache_save('right',$cacheName,$childUrls);
        }

        $_right = array();
        foreach ($rightArr as $v){
            $_right[$v] = in_array($v,$childUrls)?1:2;
        }
        $this->assign('_right',$_right);
    }
}
