<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/21
 * Time: 16:49
 */

namespace app\api\model;


use think\Db;
use think\facade\Cache;
use think\facade\Request;
use think\Model;

class Member extends Model
{
    //用户注册
    public static function register () {
        $post = Request::param();
        check_sign (array ('phone', 'pwd'));
        if (!$post['phone']||!$post['pwd']) {
            res_api ('参数错误');
        }
        $check =Db::name('member')->where ('phone','=',$post['phone'])->value('id');
        if ($check) {
            res_api ('该手机号码已注册');
        }
        $str = md5 ($post['phone']);
        $nickname = self::getNickName ($str);
        $data = array ('nickname' => $nickname, 'headimg' => '/static/common/img/sex1.png', 'pwd' => md5 ($post['pwd']),
            'phone' => $post['phone']);
        Db::startTrans();
        $flag = false;
        $re =Db::name('member')->insertGetId($data);
        if ($re) {
            $time= time ();
            $info_data = array ('user_id' => $re, 'from' => 'phone', 'create_time' =>$time);
            $res =Db::name('member_info')->insertGetId($info_data);
            if ($res) {
                $flag = true;
            }
        }
        if ($flag) {
            Db::commit();
            res_api ('ok');
        } else {
            Db::rollback();
            res_api (E_WLFM);
        }
    }

    //分配随机用户昵称
    public static function getNickName ($md5Str) {
        $num = mt_rand (10, 99);
        $start = mt_rand (0, 30);
        $nickName = '用户' . $num . substr ($md5Str, $start, 2);
        $isExist = get_field ('member', [['nickname','=',$nickName]]);
        if ($isExist) {
            return self::getNickName ($md5Str);
        } else {
            return $nickName;
        }
    }

    //用户登录
    public static function login () {
        check_sign (array ('phone', 'pwd', 'jpush_reg_id', 'device_type'));
        $post = Request::param();
        if (!$post['phone']||!$post['pwd']||!in_array ($post['device_type'], array (1, 2, 3))) {
            res_api ('参数错误');
        }
        $map = [['phone','=',$post['phone']]];
        $cur = get_find ('member', $map, 'id,pwd,status,realname,nickname');

        if (empty($cur)) {
            res_api ('用户不存在');
        }
        if ($cur['pwd'] != md5 ($post['pwd'])) {
            res_api ('密码不正确');
        }
        switch ($cur['status']) {
            case 2:
                res_api ('账号被禁用，请联系客服！');
                break;
            case 1:
                $info = array (
                    'id' => $cur['id'],
                    'jpush_reg_id' => $post['jpush_reg_id'],
                    'device_type' => $post['device_type'],
                    'nickname' => $cur['nickname'],
                    'realname' => $cur['realname']
                );
                self::getUserToken ($info);
                break;
            default:
                res_api ('未知错误');
        }
    }

    //获取登录返回信息
    public static function getUserToken ($info) {
        $uid = $info['id'];
        $old_token = get_find ('member_token',[['user_id','=',$uid]], 'user_id,token', 'user_id');
        $now = time ();
        if ($info['device_type'] == 3) {
            if ($old_token['token']) {
                $tokenInfo =Cache::get($old_token['token']);
                if ($tokenInfo['expire_time'] < $now) {
                    $token = $old_token['token'];
                } else {
                    $token = md5 ('token_' . $uid . '_' . $now);
                }
            } else {
                $token = md5 ('token_' . $uid . '_' . $now);
            }
        } else {
            $token = md5 ('token_' . $uid . '_' . $now);
        }
        $info['name'] = $info['realname']!=''?$info['realname']:$info['nickname'];
        $data = array (
            'user_id' => $uid,
            'token' => $token,
            'jpush_reg_id' => $info['jpush_reg_id'],
            'device_type' => $info['device_type'],
            'expire_time' => $now + 604800,
            'login_time' => $now,
            'login_ip' =>Request::ip()
        );
        if (empty($old_token)) {
            //首次登录
            $res =Db::name('member_token')->insertGetId($data);
        } else {
            //非首次登录
            if ($info['jpush_reg_id']) {
                set_field_value ('member_token', [['jpush_reg_id','=',$info['jpush_reg_id']]], 'jpush_reg_id', '=', 0);
                cache_del ('member_token', 'jpush_reg_id_' . $uid);
            }
            Cache::set($old_token['token'], null);
            $res =Db::name('member_token')->update($data);
        }
        if ($res === false) {
            res_api (E_WLFM);
        }
        //缓存token
        $tokenInfo = array (
            'id' => $uid,
            'nickname' => $info['nickname'],
            'expire_time' => $data['expire_time']
        );
        Cache::set($token, $tokenInfo, 604800);

        //返回数据
        $back = array ('id' => $uid, 'token' => $token,'user_info'=>$info);
        res_api ('ok', $back);
    }

    //第三方登录
    public static function authLogin () {
        $post = Request::param();
        check_sign (array ('from', 'nickname', 'sex', 'headimg', 'openid', 'unionid', 'jpush_reg_id', 'device_type'));
        if (!in_array ($post['from'], array ('wx', 'wb', 'qq'))||!in_array ($post['device_type'], array (1, 2, 3))) {
            res_api ('参数错误');
        }
        $from = $post['from'];
        if ($from == 'wx') {
            $field = 'wx_unionid';
            $value = $post['unionid'];
        } else {
            $field = $from . '_openid';
            $value = $post['openid'];
        }
        if (!$value) {
            res_api ('授权登陆参数错误');
        }
        $where =[[$field,'=',$value]];
        $info =Db::name('member')->where ($where)->field ('id,status,nickname,headimg')->find ();
        if (empty($info)) {
            Db::startTrans();
            $flag = false;
            $time = time ();
            $headimg = $post['headimg'] ? $post['headimg'] : '/static/common/img/sex1.jpg';
            $nickname = self::checkNickName ($post['nickname']);
            $data = array ('nickname' => $nickname, 'headimg' => $headimg, 'identity' => 1, 'status' => 1,
                $field => $value);
            $uid =Db::name('member')->insertGetId($data);
            if ($uid) {
                $info_data = array ('user_id' => $uid, 'sex' => $post['sex'], 'from' => $from, 'create_time' => $time);
                $res =Db::name('member_info')->insert($info_data);
                if ($res) {
                    $flag = true;
                }
            }
            if ($flag) {
                Db::commit();
            } else {
                Db::rollback();
                res_api ('授权登陆失败');
            }
        } else {
            switch ($info['status']) {
                case 2:
                    res_api ('账号被冻结，请联系客服！');
                    break;
                case 3:
                    res_api ('账号被禁用，请联系客服！');
                    break;
            }
            $uid = $info['id'];
            $headimg = $info['headimg'];
            $nickname = $info['nickname'];
        }
        if ($uid > 0) {
            $msg = array ('id' => $uid, 'realname'=>$nickname,'jpush_reg_id' => $post['jpush_reg_id'],
                'device_type' => $post['device_type'], 'nickname' => $nickname, 'headimg' => $headimg);
            self::getUserToken ($msg);
        } else {
            res_api (E_WLFM);
        }
    }

    //第三方登录昵称检测
    public static function checkNickName ($nickName) {
        //检测昵称是否为中、英文和数字
        //$nickName = check_str($nickName)?$nickName:self::getNickName(md5(time()));
        //检测昵称是否有敏感词
        //$nickName = check_member_sensitive($nickName)?$nickName:self::getNickName(md5(time()));
        //判断昵称是否为空
        $nickName = $nickName ? $nickName : self::getNickName (md5 (time ()));
        $count = get_count ('member', [['nickname','=',$nickName]]);
        if ($count > 0) {
            $nickName .= mt_rand (1000, 9999);
        }
        return $nickName;
    }

    //重置密码
    public static function resetPwd () {
        $post = Request::param();
        if (!$post['pwd']||!$post['phone']) {
            res_api ('参数错误');
        }
        $field = array ('phone', 'pwd');
        check_sign ($field);
        $pwd = md5 ($post['pwd']);
        $res =Db::name('member')->where ('phone','=',$post['phone'])->setField ('pwd', $pwd);
        if ($res !== false) {
            res_api ('ok');
        } else {
            res_api ('修改失败');
        }
    }

    //获取用户基本信息
    public static function index () {
        check_token();
        global $loginId;
        $info =Db::view('member a','id,nickname,realname,headimg,phone')
            ->view('member_info b','birth_info,sex','a.id=b.user_id','left')
            ->view('member_token c','is_push','a.id=c.user_id','left')
            ->where ('a.id','=',$loginId)
            ->find ();
        if (empty($info)) {
            res_api ('用户信息错误');
        }
        res_api ('ok', $info);
    }

    //我的收藏
    public static function myCollect(){
        check_token();
        $post=Request::post();
        $page=isset($post['page'])?$post['page']:1;
        global $loginId;
        $map=[
            ['uid','=',$loginId],
        ];
        $list=Db::name('collection')
            ->alias('a')
            ->join('library_news b','a.pid=b.id','left')
            ->where($map)
            ->field('a.create_time,b.title')
            ->page($page,20)
            ->order('a.create_time desc')
            ->select();
        if(!empty($list)){
            foreach ($list as &$v){
                $v['create_time']=date('Y-m-d',$v['create_time']);
            }
        }
        res_api('ok',$list);
    }

    //我的浏览记录
    public function glanceOver(){
        check_token();
        $post=Request::post();
        $page=isset($post['page'])?$post['page']:1;
        global $loginId;
        $map=[
            ['uid','=',$loginId],
        ];
        $list=Db::name('member_glance')
            ->alias('a')
            ->join('library_news b','a.pid=b.id','left')
            ->where($map)
            ->field('a.create_time,b.title')
            ->page($page,20)
            ->order('a.create_time desc')
            ->select();
        if(!empty($list)){
            foreach ($list as &$v){
                $v['create_time']=date('Y-m-d',$v['create_time']);
            }
        }
        res_api('ok',$list);
    }

}