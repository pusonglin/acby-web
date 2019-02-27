<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/21
 * Time: 13:52
 */

namespace app\home\controller;


use app\home\model\Member;
use think\facade\Config;
use think\facade\Request;
use think\facade\Session;

class User extends Common
{
    //登录
    public function login(){
        if(Request::instance()->isPost()){
            Member::login();
        }else{
            $backurl = isset($_SERVER['HTTP_REFERER']);
            $this->assign('backUrl',$backurl);
            $state = md5('dj_'.time());
            Session::set('login_auth_state',$state);
            //三方登录地址
            //微信
            $wxConfig = Config::get('auth.wx_config');
            $redirect_uri = urlencode($wxConfig['redirect_uri']);
            $oAuth['wxUrl'] =  'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$wxConfig['app_id'].'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state='.$state.'&connect_redirect=1#wechat_redirect';

            //微博
            $wbConfig = Config::get("wb_config");
            $oAuth['wbUrl'] = "https://api.weibo.com/oauth2/authorize?client_id=".$wbConfig['app_id']."&response_type=code&redirect_uri=".$wbConfig['redirect_uri'];

            //QQ
            $qqConfig = Config::get('auth.qq_config');
            $oAuth['qqUrl'] = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=".$qqConfig['app_id']."&redirect_uri=".$qqConfig['redirect_uri']."&state=".$state."&scope=get_user_info&display=mobile";
            $this->assign('oAuth',$oAuth);
            Session::set('login_to_url',$backurl);

            return $this->fetch('login');
        }
    }

    //注册
    public function register(){
        if(Request::instance()->isPost()){
            $cur=Member::register();
            echo json_encode($cur);
        }else{
            return $this->fetch('register');
        }
    }

    //密码登陆
    public function passwordLogin(){
        return $this->fetch('passwordLogin');
    }

    //忘记密码
    public function findPwd(){
        return $this->fetch('findPwd');
    }

    //忘记密码 第二步
    public function findPwd2(){
        return $this->fetch('findPwd2');
    }
}