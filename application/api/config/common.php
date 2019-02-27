<?php
use think\facade\Request;
use think\facade\Session;
use think\facade\Cache;
use think\Db;

/*******************************************************字母C***********************************************************/
/**
 * 验证token
 * @return bool
 */
function check_token(){
    //开发测试
    $isDev = Request::param('dev');
    $uid = Request::param('uid');
    if($isDev==1 && $uid>0){
        $tokenInfo = get_find('member',[['id','=',$uid]],'id,party_id,nickname,group_id,is_master,role_id,manage_group_id');
        if($tokenInfo['role_id']>0){
            $tokenInfo['is_super'] = get_field('auth_role',[['id','=',$tokenInfo['role_id']]],'is_super');
        }

        global $loginId;
        $loginId = $tokenInfo['id'];
        global $member_login;
        $member_login = $tokenInfo;

        //上传目录
        Session::set('uploadDir','web');
        Session::set('web_uploadUid',$member_login['id']);
        set_txt_dir();
        return true;
    }

    //token是否为空
    $token = Request::param('token');
    if(!$token){
        res_api('token_expire');
    }

    $tokenInfo = Cache::get($token);
    if(empty($tokenInfo)){
        res_api('token_expire');
    }

    //token是否有效
    $expire_time = $tokenInfo['expire_time'];
    $now = time();
    if($expire_time<$now){
        Cache::set($token,null);
        res_api('token_expire');
    }else if(($expire_time-$now) < 518400){
        $data = array(
            'user_id' => $tokenInfo['id'],
            'expire_time' => $now+604800,
            'login_time' => $now,
            'login_ip' => Request::ip(0,true)
        );
        $res = Db::name('member_token')->update($data);

        if($res!==false){
//            $party_id = $tokenInfo['party_id'];
//            $tokenInfo = array(
//                'expire_time' => $data['expire_time'],
//                'party_id' => $party_id,
//                'g_id' => $party_id,
//                'id' => $tokenInfo['id']
//            );
            Cache::set($token,$tokenInfo,604800);
        }
    }

    global $loginId;
    $loginId = $tokenInfo['id'];
    global $member_login;
    $member_login = $tokenInfo;

    //上传目录
    Session::set('uploadDir','web');
    Session::set('web_uploadUid',$member_login['id']);
    set_txt_dir();
}

//验证签名
function check_sign($zdArr=array()){
    //开发测试
    $isDev = Request::param('dev');

    if($isDev==1){
        return true;
    }

    //获取的签名
    $signStr = Request::param('signature');

    //待签名数组
    $signArr = array(
        'timetamp' => Request::param('timetamp'),
    );

    //拼接签名数组
    foreach ($zdArr as $v){
        $signArr[$v] = Request::param($v);
    }
    //write_log_txt($signArr);
    //获得真实签名
    $true_sign = get_sign($signArr);
    if($true_sign!=$signStr){
        res_api('签名错误！');
    }

    //是否有token
    $token = Request::param('token');
    if ($token){
        check_token();
    }
}



/*******************************************************字母G***********************************************************/
//获取签名
function get_sign($signArr){
    $signArr['noncestr'] = 'ScTengSenDj_v1apiStrQAzXSWEDCvfrTyuijhg'; //签名随机字符串
    ksort($signArr);
    $str = '';
    foreach ($signArr as $k=>$v){
        if(strlen($v)>0){
            $str .= $k.'='.$v.'&';
        }
    }
    $true_sign = sha1(trim($str,'&'));
    return $true_sign;
}

//获取最后访问的时间
function get_last_view_time($viewId,$tb,$zdName){
    global $loginId;
    $last_time = get_field($tb,[['user_id','=',$loginId,$zdName,'=',$viewId]],'last_time');
    $start_day = time()- 24*3600*7; //仅查询7天之内的更新数
    $start = $last_time>$start_day?$last_time:$start_day;
    return $start;
}


/*****************************************************字母S*************************************************************/
//保存最后一次访问的时间
function save_view_time($viewId,$tb,$zdName){
    global $loginId;
    if($loginId>0){
        $map = [
            ['user_id','=',$loginId],
            [$zdName,'=',$viewId]
        ];
        $isExist = get_field($tb,$map);
        if($isExist){
            set_field_value($tb,$map,'last_time','=',time());
        }else{
            $data = array(
                'user_id' => $loginId,
                $zdName => $viewId,
                'last_time' => time()
            );
            \think\Db::name($tb)->insert($data);
        }
    }
}


/*****************************************************字母T*************************************************************/
//处理帖子列表图片
function thumb_posts_imgs($imgs,$flag=1){
    $imgs = unserialize($imgs);
    $count = count($imgs);
    $back = array();
    if($count==0){
        return in_array($flag,array('1,3,4'))?"":$back;
    }
    if($flag==1){ //帖子封面
        $back = get_thumb_img($imgs[0],660,460);
    }else if($flag==2){ //帖子大图
        foreach ($imgs as $k=>&$v){
            $v = get_thumb_img($v,750,0,1);
        }
        $back = $imgs;
    }else if($flag==3){ //搜索帖子封面
        $back = get_thumb_img($imgs[0],112,112);
    }else if($flag==4){ //我的收藏封面
        if($count>1){
            $back = get_thumb_img($imgs[0],128,128);
        }else{
            $back = get_thumb_img($imgs[0],380,240);
        }
    }
    return $back;
}

    /*
     * 判断是  超级管理员  还是   支部管理员
     * */
    function adminCub($member_login){
        $where = [['id','=',$member_login['id']],['status','=',1]];
        $right=get_find('member',$where,'is_master,position_id,role_id,party_id');
        if($right['is_master']==2){
            $res=get_field('auth_party_right',[['party_id','=',$member_login['party_id']]],'right_ids');
            $right_arr=explode(',',$res);
            if(in_array(1003,$right_arr)){
                $right=1;
            }else{
                $right=2;
            }
        }else{
            $right=1;
        }
//        $role_id=get_field('member',array('id'=>$member_login['id']),'role_id');
//        if($role_id==0){
//            $right=0;
//        }else{
//            $right=get_field('auth_role',array('id'=>$role_id),'is_super');
//        }
        return $right;
    }

//时间处理
function time_fortmat_api($time,$s='i',$f='-',$showRightNow=false,$hasYear=true){
    switch($s){
        case 'd':
            $str1 = $hasYear?'Y'.$f.'m'.$f.'d':'m'.$f.'d';
            $str2 = null;
            break;
        case 'i':
            $str1 = $hasYear?'Y'.$f.'m'.$f.'d H:i':'m'.$f.'d H:i';
            $str2 = 'H:i';
            break;
        case 'y':
            $str1 = $hasYear?'y'.$f.'m'.$f.'d H:i':'m'.$f.'d H:i';
            $str2 = 'H:i';
            break;
        default :
            $str1 = 'Y'.$f.'m'.$f.'d H:i:s';
            $str2 = 'H:i:s';
    }
    if($s!='d'){
        if($showRightNow){
            $yesterday = time()-24*3600;
            if($time<10){
                $time = '——';
            }else if($time<=$yesterday){
                $time = date($str1,$time);
            }else if(time()-$time>3600){
                $hour = intval((time()-$time)/3600);
                $time = $hour.'小时前';
            }else if(time()-$time>60){
                $minute = intval((time()-$time)/60);
                $time = $minute.'分钟前';
            }else {
                $minute = intval((time()-$time)/10);
                $time = $minute > 0?$minute.'0秒钟前':'刚刚';
            }
        }else{
            $today = strtotime(date('Y-m-d'));
            $yesterday = strtotime(date('Y-m-d',time()-24*3600));
            if($time<10){
                $time = '——';
            }else if($time<$yesterday){
                $time = date($str1,$time);
            }else if($time<$today){
                $time = '昨天 ';
            }else{
                $time = '今天 '.date($str2,$time);
            }
        }
    }else{
        $time = $time>100?date($str1,$time):'——';
    }
    return $time;
}


function substr_cut($user_name,$head,$foot){
    $strlen     = mb_strlen($user_name, 'utf-8');
    $firstStr     = mb_substr($user_name, 0, $head, 'utf-8');
    $lastStr     = mb_substr($user_name, -$foot, $foot, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - ($head+$foot)) . $lastStr;
}

?>