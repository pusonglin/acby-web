<?php
namespace app\home\model;

use think\Db;
use think\facade\Cache;
use think\facade\Request;
use think\facade\Session;
use think\Model;


class Member extends Model
{
    protected $_link;
    protected $pageNum;
    protected $post;


    //用户注册
    public static function register () {
        $post = Request::post();
        if (!$post['phone']||!$post['pwd']) {
            return_msg('参数错误',2);
        }
        $check =Db::name('member')->where('phone','=',$post['phone'])->value('id');
        if ($check) {
            return_msg('该手机号码已注册',2);
        }
        if($post['code']==Session::get('phoneCode')){
            $str = md5 ($post['phone']);
            $nickname = self::getNickName ($str);
            $data = array ('nickname' => $nickname, 'headimg' => '/Public/static/img/sex1.png', 'pwd' => md5 ($post['pwd']),
                           'phone' => $post['phone']);
            Db::startTrans();
            $flag = false;
            $re = Db::name('member')->insertGetIdGetId($data);
            if ($re) {
                $info_data = array ('user_id' => $re, 'from' => 'phone', 'create_time' => time ());
                $res =Db::name('member_info')->insertGetId($info_data);
                if ($res) {
                    $flag = true;
                }
            }
            if ($flag) {
                Db::commit();
                return_msg('ok',1);
            } else {
                Db::rollback();
                return_msg(E_WLFM,2);
            }
        }else{
            return_msg('验证码错误',2);
        }
    }

    //分配随机用户昵称
    public static function getNickName ($md5Str) {
        $num = mt_rand (10, 99);
        $start = mt_rand (0, 30);
        $nickName = '用户' . $num . substr ($md5Str, $start, 2);
        $isExist=Db::name('member')->where('nickname','=',$nickName)->value('id');
        if ($isExist) {
            return self::getNickName ($md5Str);
        } else {
            return $nickName;
        }
    }

    //用户登录
    public static function login () {
        $post = Request::post();
        if (!$post['phone']||!$post['pwd']) {
            return_msg('账号或密码错误了哦！', 2);
        }
        $map = [['phone','=',$post['phone']]];
        $cur = get_find ('member', $map);
        if (empty($cur)) {
            return_msg('用户不存在',2);
        }
        if ($cur['pwd'] != md5 ($post['pwd'])) {
            return_msg('密码不正确',2);
        }
        switch ($cur['status']) {
            case 2:
                return_msg('账号被禁用，请联系客服！',2);
                break;
            case 1:
                $info = array ('id' => $cur['id'], 'party_id' => $cur['party_id'],'group_id' => $cur['group_id'],
                               'jpush_reg_id' => 0, 'device_type' => 4,
                               'nickname' => $cur['nickname']);
                Session::set('loginId', $cur['id']);
                Session::set('member_login', $cur);
                self::getUserToken ($info);
                break;
            default:
                return_msg('未知错误',2);
        }
    }

    //获取登录返回信息
    public static function getUserToken ($info) {
        $uid = $info['id'];
        $old_token =Db::name('member_token')->where('user_id','=',$uid)->field('user_id,token')->find();
        $now = time ();
        if ($info['device_type'] == 3) {
            if ($old_token['token']) {
                $tokenInfo = Cache::get($old_token['token']);
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
        $data = array ('user_id' => $uid, 'token' => $token, 'jpush_reg_id' => $info['jpush_reg_id'],
                       'device_type' => $info['device_type'], 'expire_time' => $now + 604800, 'login_time' => $now,
                       'login_ip' =>Request::ip());
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
            return_msg(E_WLFM,2);
        }
        //缓存token
        $tokenInfo = array ('id' => $uid, 'party_id' => $info['party_id'] ? $info['party_id'] : -1,'group_id' => $info['group_id'] ? $info['group_id'] : -1,
                            'nickname' => $info['nickname'],'expire_time' => $data['expire_time']);
        Cache::set($token, $tokenInfo, 604800);
        //返回数据
        return_msg('ok',1);
    }

    //第三方登录昵称检测
    private function checkNickName ($nickName) {
        //检测昵称是否为中、英文和数字
        //$nickName = check_str($nickName)?$nickName:self::getNickName(md5(time()));
        //检测昵称是否有敏感词
        //$nickName = check_member_sensitive($nickName)?$nickName:self::getNickName(md5(time()));
        //判断昵称是否为空
        $nickName = $nickName ? $nickName : self::getNickName (md5 (time ()));
        $count = get_count ('member', ['nickname','=',$nickName]);
        if ($count > 0) {
            $nickName .= mt_rand (1000, 9999);
        }
        return $nickName;
    }

    //重置密码
    public static function resetPwd () {
        $post =Request::post();
        if (!$post['pwd']||!$post['phone']) {
            res_api ('参数错误');
        }
        $field = array ('phone', 'pwd');
        check_sign ($field);
        $pwd = md5 ($post['pwd']);
        $res =Db::name('member')->where (['phone','=',$post['phone']])->setField ('pwd', $pwd);
        if ($res !== false) {
            res_api ('ok');
        } else {
            res_api ('修改失败');
        }
    }


    //获取用户基本信息
    public static function index ($loginId) {
        $info = Db::view('member a','id,nickname,realname,headimg,phone,party_id,group_id,identity')
            ->view('member_info b','sex,birth_info','a.id=b.user_id','left')
            ->view('member_token c','is_push','a.id=c.user_id','left')
            ->where ('a.id','=',$loginId)
            ->find ();
        if(!empty($info)){
            switch ($info['identity']) {
                case 1:
                    $name = '群众';
                    break;
                case 2:
                    $name = '入党积极分子';
                    break;
                case 3:
                    $name = '党员发展对象';
                    break;
                case 4:
                    $name = '预备党员';
                    break;
                case 5:
                    $name = '正式党员';
                    break;
            }
            $info['identity'] = $name;
        }
        return $info;
    }

    //获取用户绑定信息
    public static function getBindingList () {
        global $loginId;
        $field = 'id,wx_unionid,wb_openid,qq_openid';
        $info =Db::name('member')->where ('id','=',$loginId)->field ($field)->find ();
        if (empty($info)) {
            res_api ('用户信息错误');
        }
        $list = array (array ('flag' => 'wx', 'is_binding' => 2), array ('flag' => 'wb', 'is_binding' => 2),
                       array ('flag' => 'qq', 'is_binding' => 2));
        foreach ($list as &$v) {
            if ($info['wx_unionid']&&$v['flag'] == 'wx') {
                $v['is_binding'] = 1;
            }
            if ($info['wb_openid']&&$v['flag'] == 'wb') {
                $v['is_binding'] = 1;
            }
            if ($info['qq_openid']&&$v['flag'] == 'qq') {
                $v['is_binding'] = 1;
            }
        }
        res_api ('ok', $list);
    }

    //修改用户基本信息
    public static function changeMemberMsg () {
        global $loginId;
        $post =Request::post();
        $value = $post['value'];
        if (!$value) {
            res_api ('参数错误');
        }
        $db = 'member';
        $where =[['id','=',$loginId]];
        switch ($post['flag']) {
            case 1:
                $field = 'headimg';
                break;
            case 2:
                $field = 'nickname';
                break;
            case 3:
                $field = 'pwd';
                $value = md5 ($value);
                break;
            case 4:
                $field = 'birth_info';
                $db = 'member_info';
                $where =[['user_id','=',$loginId]];
                break;
            case 5:
                $field = 'sex';
                $db = 'member_info';
                $where =[['user_id','=',$loginId]];
                break;
            default:
                res_api ('参数错误');
                break;
        }
        $member =Db::name($db);
        $res = $member->where ($where)->setField ($field, $value);
        if ($res !== false) {
            res_api ('ok');
        } else {
            res_api (E_WLFM);
        }
    }

    //我的消息
    public static function msg () {
        $page = get_page ('page');
        global $loginId;
        $list =Db::name('member_message')->field ('id,title,summary,is_read,create_time')->where ('to_uid','=',$loginId)->page ($page, 20)->order ('id desc')->select ();
        $ids = array ();
        foreach ($list as &$v) {
            $v['create_time'] = date ('Y-m-d', $v['create_time']);
            $ids[] = $v['id'];
        }
        if (!empty($ids)) {
            set_field_value ('member_message', [['id','in', $ids],['is_read','=',2]], 'is_read', '=', 1);
        }
        res_api ('ok', $list);
    }

    //设置推送通知
    public static function pushSetting () {
        $post =Request::post();
        if (!in_array ($post['is_push'], array (1, 2))) {
            return_msg('2','参数错误');
        }
        global $loginId;
        $cur =Db::name('member_token')->where ('user_id','=',$loginId)->find ();
        if (empty($cur)) {
            return_msg('2','数据异常');
        }
        $res =Db::name('member_token')->where ('user_id','=',$loginId)->setField ('is_push', $post['is_push']);
        if ($res !== false) {
            return_msg('1','ok');
        } else {
            return_msg('2',E_WLFM);
        }
    }

    //退出登录
    public static function logout () {
        global $loginId;
        $data =array('token'=>null,'expire_time'=>0);
        $res =Db::name('member_token')->where('user_id','=',$loginId)->update($data);
        if ($res !== false) {
            Session::clear(null);
            return 1;
        } else {
            return 2;
        }
    }

    //分配随机登录名
    private function getUserName($md5Str){
        $num = mt_rand(10,99);
        $start = mt_rand(0,28);
        $userName = 'xjs_'.$num.substr($md5Str,$start,4);
        $isExist = get_field('member',[['username','=',$userName]]);
        if($isExist){
            return self::getUserName($md5Str);
        }else{
            return $userName;
        }
    }

    //第三方登录
    public static function authLogin () {
        $post = Request::post();
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
        $info =Db::name('member')->where ($where)->field ('id,status,nickname,headimg,party_id')->find ();
        if (empty($info)) {
            Db::startTrans();
            $flag = false;
            $time = time ();
            $headimg = $post['headimg'] ? $post['headimg'] : '/Public/static/img/sex1.jpg';
            $nickname = self::checkNickName ($post['nickname']);
            $data = array ('nickname' => $nickname, 'headimg' => $headimg, 'identity' => 1, 'status' => 1,
                           $field => $value);
            $uid =Db::name('member')->insertGetId($data);
            if ($uid) {
                $info_data = array ('user_id' => $uid, 'sex' => $post['sex'], 'from' => $from,
                                    $from => $post['nickname'], 'create_time' => $time);
                $res =Db::name('member_info')->insertGetId($info_data);
                if ($res) {
                    $flag = true;
                }
            }
            if ($flag) {
                Db::commit ();
                $party_id = 0;
            } else {
                Db::rollback ();
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
            $party_id = $info['party_id'];
        }
        if ($uid > 0) {
            $msg = array ('id' => $uid, 'party_id' => $party_id, 'jpush_reg_id' => $post['jpush_reg_id'],
                          'device_type' => $post['device_type'], 'nickname' => $nickname, 'headimg' => $headimg);
            self::getUserToken ($msg);
        } else {
            res_api (E_WLFM);
        }
    }

    //第三方授权登录
    public static function oAuthLogin($from,$userInfo,$backType='json'){
        $openId = $userInfo['openid'];
        $map[] =[$from.'_openid','=',$openId] ;
        $res = get_find('member',$map,'id,nickname,status,headimg');
        if($res['status']==2){
            if($backType=='json'){
                $back = array(
                    'flag' => 0,
                    'msg' => '您的账号已经被冻结，请联系客服！'
                );
                echo json_encode($back);
            }else{
                echo '抱歉,您的账号已经被冻结，请联系客服！';
            }
            exit;
        }else if($res['status']==3){
            if($backType=='json'){
                $back = array(
                    'flag' => 0,
                    'msg' => '您的账号已被停用，请联系客服！'
                );
                echo json_encode($back);
            }else{
                echo '您的账号已被停用，请联系客服！';
            }
            exit;
        }
        if(empty($res)){
            $time = time();
            $nickname = self::checkNickName($userInfo['nickname']);
            $headimg  =$userInfo['headimgurl']?$userInfo['headimgurl']:'/Public/static/img/default_head.png';
            $data = array(
                'nickname' => $nickname,
                'headimg' => $headimg,
                $from.'_openid' => $openId,
                'sex' =>  $userInfo['sex']==1?1:2,
                $from => $userInfo['nickname'],
                'from' => $from,
                'create_time' => $time,
            );
            $uid = Db::name('member')->insertGetId($data);
        }else{
            $uid = $res['id'];
            $headimg = $res['headimg'];
            $nickname = $res['nickname'];
        }
        if($uid>0){
            $cur = array(
                'has_token' => 2,
                'id' => $uid,
                'nickname' => $nickname,
                'headimg' => $headimg,
                'token' => ''
            );
            if($from=='wb'&&$userInfo['wb_token']&&$uid>0){
                Db::name('member')->where('id','=',$uid)->setField('wb_token',$userInfo['wb_token']);
            }
            self::getUserToken($cur);
        }else{
            if($backType=='json'){
                echo json_encode(array('flag'=>0,'msg'=>E_WLFM));
            }else{
                echo E_WLFM;
            }
        }
    }

    //第三方授权绑定账号
    public static function oAuthBound($from,$userInfo){
        $openId = $userInfo['openid'];
        $map = [
            [$from.'_openid','=',$openId]
        ];
        $isExist = get_find('member',$map,'id');
        if($isExist){
            $logout_url = "https://api.weibo.com/oauth2/revokeoauth2?access_token=".$userInfo['wb_token'];
            https_request($logout_url);
            $back = array(
                'flag' => 0,
                'msg' => '此账号已经绑定其他账号'
            );
        }else{
            global $loginId;
            if($loginId>0){
                $data = array(
                    'id' => $loginId,
                    $from => $userInfo['nickname'],
                    $from.'_openid' => $openId,
                );
                $res = Db::name('member')->insertGetId($data);
                if($res!==false){
                    $back = array(
                        'flag'=>1,
                        'msg'=>'账号绑定成功'
                    );
                }else{
                    $back = array(
                        'flag'=>0,
                        'msg'=>E_WLFM
                    );
                }
            }else{
                $back = array(
                    'flag' => 0,
                    'msg' => '登录已失效，请重新登录'
                );
            }
        }
        return $back;
    }

}