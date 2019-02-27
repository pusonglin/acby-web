<?php
/**
 * Created by PhpStorm.
 * User: zejun.lei@foxmail.com
 * Date: 2019.02.18
 * Time: 16:02
 */

namespace app\home\controller;


use \think\facade\Cache;
use think\Controller;
use think\facade\Config;
use think\facade\Request;

class Common extends Controller
{

    //初始化
    public function initialize() {
        //self::_isLogin();//验证登录
    }

    //验证登录方法
    public function _isLogin(){
        global $home_login;
        $home_login = session('home_login');
        global $loginId;
        $loginId = $home_login['id']>0?$home_login['id']:'-1';
        if($loginId<=0){
            if(is_ajax()){
                ajax_return(null,0,'token_expire');
            }else{
                $url = HOME_URL.'/login.html';
                exit("<script language=\"javascript\">window.open('".$url."','_top');</script>");
            }
        }
    }

    //分享
    public function share_config($title="爱车保养",$summary,$img,$url){
        $agent = $_SERVER["HTTP_USER_AGENT"];
        $explorer = 'other';
        if(strpos($agent,"MicroMessenger")){
            $explorer = 'wx';
        }else if(strpos($agent,"MQQBrowser")){
            $explorer = 'qq';
        }else if(strpos($agent,"UCBrowser")){
            $explorer = 'uc';
        }
        $wx_config=$this->get_wx_jsConfig();
        //分享参数
        $share_config = array( 'title' => $title, 'desc' => $summary, 'img' => $img, 'url' => $url );
        $cur['share_config'] = $share_config;
        $cur['wx_config'] = $wx_config;
        $cur['explorer'] = $explorer;
        return $cur;
    }


    //js-sdk测试
    public function get_wx_jsConfig(){
        $url='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
        $noncestr = 'pandapia_yymmdd';
        $timestamp = time();
        $ticket = self::get_js_ticket();
        $appid = Config::get('auth.wx_config.appid');
        $string = 'jsapi_ticket='.$ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
        $signature = sha1($string);
        $wx_config = array( 'noncestr'=>$noncestr, 'timestamp'=>$timestamp, 'signature'=>$signature, 'ticket'=>$ticket, 'appid'=>$appid );
        return $wx_config;
    }

    //获取js配置中的ticket
    private function get_js_ticket(){
        $cacheName = 'wx_js_ticket';
        $ticket = Cache::get($cacheName);
        if(!$ticket){
            $wxConfig = get_WX_config();
            $url = sprintf("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$wxConfig['access_token']."&type=jsapi",$wxConfig['token']);
            $res = https_request($url);
            $res = json_decode($res, true);
            if($res['errcode']==0){
                $ticket = $res['ticket'];
                cache_save('wx_config',$cacheName,$ticket);
            }
        }
        return $ticket;
    }
}