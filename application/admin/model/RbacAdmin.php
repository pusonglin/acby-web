<?php
namespace app\admin\model;

use think\Db;
use think\facade\Cache;
use think\facade\Request;
use think\Model;

class RbacAdmin extends Model
{

    //用户登录
    public static function doLogin()
    {
        check_no_refer();
        $post = Request::post();
        if($post){
            verify_check($post['yzm'],'admin_login_yzm');
            $map = array(
                'username' => $post['user']
            );
            $cur=Db::name('admin')
                ->where($map)
                ->field('id,username,password,headimg,realname,status,phone')
                ->find();
            if(!empty($cur)){
                if($cur['status']==1){
                    if($cur['password'] == md5($post['pwd'])){
                        //生成token
                        /*$str = $cur['id'].'_'.rand(100,999).'_'.time();
                        $cur['token'] = md5($str);*/
                        $isExist=Db::name('admin_token')->where('user_id','=',$cur['id'])->value('user_id');
                        $fn = $isExist?'update':'insert';
                        $loginData = [
                            'user_id' => $cur['id'],
                            'login_time' => time(),
                            'login_ip' => Request::ip()
                        ];
                        Db::name('admin_token')->$fn($loginData);
                        session('admin_login',$cur);
                        session('admin_login_yzm',null);
                        save_logs('登录后台管理系统');
                        echo 1;
                    }else{
                        echo 2; //密码不匹配，请重新输入。
                    }
                }else if($cur['status']==2){
                    echo 3; //该账户已经被禁止使用，请联系客服。
                }else{
                    echo 4; //该账户已经被冻结，请联系客服。
                }
            }else{
                echo 5; //用户名不存在。
            }
        }
    }

    //个人设置->个人资料
    public static function selfInfo()
    {
        global $loginId;
        $cur = Db::view('admin a','id,username,headimg,sex,realname,phone,email,remark,create_time')
            ->view('admin_role b','role_id','a.id = b.user_id','left')
            ->view('role c','name role_name','c.id = b.role_id','left')
            ->view('admin_token d','login_time,login_ip','d.user_id = a.id','left')
            ->where(['a.id'=>$loginId])
            ->find();
        return $cur;
    }

    //个人设置->保存个人资料
    public static function updateInfo()
    {
        $post = Request::post();
        if($post){
            global $loginId;
            $data = $post;
            $data['id'] = $loginId;
            $res = Db::name('admin')->update($data);
            $log = '修改个人信息';
            if($res!==false){
                //处理头像垃圾图片
                global $uptxt;
                $upImgs = up_read_txt($uptxt);
                if(!empty($upImgs)){
                    unlink($uptxt);
                    $oldHeadImg = session('admin_login.headimg');
                    $oldHeadImg = strpos($oldHeadImg,'/static/common/')===false?$oldHeadImg:'';
                    $allimgs = $upImgs;
                    if($oldHeadImg){
                        $allimgs[] = $oldHeadImg;
                    }
                    $useArr[0] = $data['headimg'];
                    $diff = array_diff($allimgs,$useArr);
                    if(!empty($diff)){
                        up_del_nouse($diff);
                    }
                    session('admin_login.headimg',$data['headimg']);
                }
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo E_WLFM;
            }
        }
    }

    //个人设置->修改密码
    public static function savePwd()
    {
        $post = Request::post();
        if($post){
            if($post['newPwd']!=$post['reNewPwd']){
                echo '新密码与确认密码不一致！';
                exit;
            }
            global $loginId;
            $oldPwd = get_field('admin',array('id'=>$loginId),'password');
            if($oldPwd==md5($post['oldPwd'])){
                $data = array(
                    'id' => $loginId,
                    'password' => md5($_POST['newPwd'])
                );
                $res = Db::name('member')->update($data);
                $log = '修改登录密码！';
                if($res!==false){
                    save_logs($log);
                    echo 1;
                }else{
                    save_logs($log,2);
                    echo '密码修改失败！';
                }
            }else{
                echo '原始密码有误！';
            }
        }
    }

    //首页统计
    public static function homeReport(){
        $datas = array();
        //组织数量
        $map = array(
            'status' => 1,
        );
        $cur['party_num'] = rand(8,20);
        //支部数量
        $cur['group_num'] = rand(22,68);
        //用户总数

        $cur['member_num'] = rand(40,88);
        //党员总数

        $cur['party_member_num'] = rand(10,34);
        $datas['cur'] = $cur;
        //文库发布
        $date = array();
        $party_news_num = array();
        $news_num = array();
        //组织入驻
        $party_num = array();
        //用户注册
        $register_num = array();
        //用户登录
        $login_num = array();
        for($i=6;$i>=0;$i--){
            $start = strtotime(date('Ymd',strtotime('-'.$i.' day')));

            $date[] = date('m月d日',$start);
            //共享文库
            $party_news_num[] = rand(40,120);

            $news_num[] = rand(10,35);
            //组织入驻

            $party_num[] = rand(44,77);
            //用户注册

            $register_num[] = rand(22,11);
            //用户登录

            $login_num[] = rand(5,34);
        }
        $data = array(
            'date' => json_encode($date),
            'party_news_num' => json_encode($party_news_num),
            'news_num' => json_encode($news_num),
            'party_num' => json_encode($party_num),
            'register_num' => json_encode($register_num),
            'login_num' => json_encode($login_num),
        );
        $datas['data'] = $data;


        $party_join = array();

        for($i=1;$i<=4;$i++){
            switch ($i){
                case 1:
                    $name = 'PHP';
                    $map['rank'] = 1;
                    break;
                case 2:
                    $name = 'JAVA';
                    $map['rank'] = 2;
                    break;
                case 3:
                    $name = 'C++';
                    $map['rank'] = 3;
                    break;
                case 4:
                    $name = 'Ruby';
                    $map['rank'] = 4;
                    break;
                default:return false;
            }
            $party_join[] = array(
                'name' => $name,
                'value' => rand(23,67)
            );
        }
        $datas['doc'] = $party_join;
        echo json_encode($datas);
    }

    //------------------------
    // 用户管理
    //------------------------
    //用户列表
    public static function index()
    {
        $post = Request::post();
        $map = [
            ['a.status','in','1,2']
        ];
        map_format($map);
        $list = Db::view('admin a','id,username,phone,status')
            ->view('admin_role b','role_id','b.user_id = a.id','left')
            ->view('role c','name role_name','c.id = b.role_id','left')
            ->where($map)
            ->order('a.id')
            ->page($post['page'],$post['limit'])
            ->select();
        if(empty($list)){
            ajax_return();
        }

        if($post['page']==1){
            $count = Db::view('admin a','id')->where($map)->count('a.id');
            cache(request()->controller().'_'.request()->action().'_count',$count);
        }else{
            $count = cache(request()->controller().'_'.request()->action().'_count');
            if(!($count>0)){
                $count = Db::view('admin a','id')->where($map)->count('a.id');
            }
        }
        ajax_return($list,$count);
    }

    //党员导入
    public static function import()
    {
        ignore_user_abort(true); // 忽略客户端断开
        set_time_limit(600);       // 设置执行不超时

        //文件上传
        $path = './uploads/file/'.date('Ymd');
        if(!is_dir($path)){
            mkdir($path, 0777, true);
        }

        $file = request()->file('file');

        //移动到框架应用根目录/uploads/ 目录下
        $info = $file->validate(['ext'=>'xls,xlsx'])->rule('uniqid')->move($path);
        if(!$info){
            //上传失败获取错误信息
            ajax_return(null,0,$file->getError().'！');
        }

        //成功上传后 获取上传信息
        global $uptxt;
        $excel_file_path = trim($path,'.').'/'.$info->getSaveName();
        up_write_txt($uptxt,$excel_file_path."\n");


        //excel文件的地址
        $excel_file_path = '.'.$excel_file_path;

        //分析文件获取后缀判断是2007版本还是2003
        $extend = pathinfo($excel_file_path);
        $extend = strtolower($extend["extension"]);

        //判断xlsx版本，如果是xlsx的就是2007版本的，否则就是2003
        if ($extend=="xlsx") {
            $PHPReader = new \PHPExcel_Reader_Excel2007();
            $PHPExcel = $PHPReader->load($excel_file_path);
        }else{
            $PHPReader = new \PHPExcel_Reader_Excel5();
            $PHPExcel = $PHPReader->load($excel_file_path);
        }
        //取得表格
        $sheet = $PHPExcel->getSheet(0);
        //取得总行数
        $highestRow = $sheet->getHighestRow();
        //取得总列数
        $highestColumn = $sheet->getHighestColumn();
        //数据处理

        $num = 0;
        $role=Db::name('role')->where('status','=',1)->field('id,name')->select();
        for($j=3;$j<=$highestRow;$j++){
            $temp = array();
            for($k='B';$k<= $highestColumn;$k++){
                $value = $PHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                $key = null;
                $val = "";
                switch ($k){
                    case 'B':
                        $key = 'his_id';
                        $val = $value;
                        break;
                    case 'D':
                        $key = 'username';
                        $val = $value;
                        break;
                    case 'E':
                        $key = 'role';
                        $val = $value;
                        break;
                    case 'F':
                        $key = 'position';
                        $val = $value;
                        break;
                    case 'G':
                        $key = 'valid_low';
                        $val = $value;
                        break;
                    case 'H':
                        $key = 'valid_high';
                        $val = $value;
                        break;
                    case 'I':
                        $key = 'telecom';
                        $val = $value;
                        break;
                    case 'J':
                        $key = 'name';
                        $val = $value;
                        break;
                    case 'K':
                        $key = 'id_card';
                        $val = $value;
                        break;
                    case 'L':
                        $key = 'sex';
                        $val = $value=='男'?1:2;
                        break;
                    case 'M':
                        $key = 'birth_time';
                        $val = $value;
                        break;
                    case 'N':
                        $key = 'birth_addr';
                        $val = $value;
                        break;
                    case 'O':
                        $key = 'dep_id';
                        $val = $value;
                        break;
                    case 'P':
                        $key = 'dep_name';
                        $val = $value;
                        break;
                    case 'Q':
                        $key = 'role_status';
                        $val = 'active';
                        break;
                }
                //读取单元格值
                if($key){
                    $temp[$key] = $val;
                }
            }
            if($temp['username'] && ($temp['id_card'] || $temp['telecom'])){
                $map1=[
                    ['id_card','=',$temp['id_card']],
                    ['status','in','1,2']
                ];
                $map2=[
                    ['telecom','=',$temp['telecom']],
                    ['status','in','1,2']
                ];
                if($temp['id_card'] && $temp['telecom']){
                    $isExist = Db::name('admin')->whereOr([$map1,$map2])->value('id');
                }else if($temp['id_card']){
                    $isExist = get_field('admin',$map1);
                }else{
                    $isExist = get_field('admin',$map2);
                }
                if(!$isExist){
                    $data = [
                        'password' => md5('111'),
                        'username' => $temp['username'],
                        'name' => $temp['name'],
                        'id_card' => $temp['id_card'],
                        'headimg' => '/static/common/img/sex'.$temp['sex'].'.png',
                        'telecom' => $temp['telecom'],
                        'sex' => $temp['sex'],
                        'his_id'=>$temp['his_id'],
                        'role'=>$temp['role'],
                        'position'=>$temp['position'],
                        'valid_low'=>$temp['valid_low'],
                        'valid_high'=>$temp['valid_high'],
                        'birth_time'=>$temp['birth_time'],
                        'birth_addr'=>$temp['birth_addr'],
                        'dep_id'=>$temp['dep_id'],
                        'dep_name'=>$temp['dep_name'],
                        'role_status'=>$temp['role_status']
                    ];
                    Db::startTrans();
                    $res = Db::name('admin')->insertGetId($data);
                    $role_id=0;
                    foreach ($role as $val){
                        if($val['name']==$temp['role']){
                            $role_id+=$val['id'];
                        }
                    }
                    if($role_id==0){
                        $arr=array(
                            'name'=>$temp['role']
                        );
                        $r=Db::name('role')->insertGetId($arr);
                        $role_id=$r;
                        array_merge($role,array('id'=>$r,'name'=>$temp['role']));
                    }
                    $brr=array(
                       'user_id'=>$res,
                       'role_id'=>$role_id
                    );
                    Db::name('admin_role')->insert($brr);
                    if($res){
                        $num++;
                        Db::commit();
                    }else{
                        Db::rollback();
                    }
                }else{
                    $info=Db::name('admin')->where('id','=',$isExist)->field('role,position,dep_id,dep_name')->find();
                    $key=array('role','position','dep_id','dep_name');
                    foreach ($key as $v){
                        if(!strstr($info[$v],$temp[$v])){
                            $info[$v].='/'.$temp[$v];
                        }
                    }
                    Db::name('admin')->where('id','=',$isExist)->update($info);
                }
            }
        }

        //删除文件
//        global $uptxt;
//        global $oldtxt;
//        $data = array(
//            'uptxt' => $uptxt,
//            'oldtxt' => $oldtxt,
//            'id' => 0,
//            'text' => '',
//            'check_tab' => null
//        );
        //fsockopen_request(HOME_URL.'/fsockopen/upSaveDel',$data);

        if($num>0){
            $log = '批量导入党员数据'.$num.'条！';
            save_logs($log,1);
            ajax_return(null,0,'ok');
        }else{
            ajax_return(null,0,'成功导入0条！');
        }
    }

    //批量导出
    public static function export()
    {
        $post = Request::post();
        if($post){
            check_no_refer();
            $fileName = 'member_'.time().'_'.mt_rand(1000,9999);
            Cache::set($fileName,$post['id']);
            echo $fileName;
        }else{
            //模板文件
            $excel_file_path = "./uploads/template/memberImportTemplate.xlsx";
            //检查文件路径
            if(!file_exists($excel_file_path)){
                exit('模板不存在！');
            }

            //分析文件获取后缀判断是2007版本还是2003
            $extend = pathinfo($excel_file_path);
            $extend = strtolower($extend["extension"]);

            //判断xlsx版本，如果是xlsx的就是2007版本的，否则就是2003
            if ($extend=="xlsx") {
                $PHPReader = new \PHPExcel_Reader_Excel2007();
                $PHPExcel = $PHPReader->load($excel_file_path);
            }else{
                $PHPReader = new \PHPExcel_Reader_Excel5();
                $PHPExcel = $PHPReader->load($excel_file_path);
            }

            //查询数据
            $fileName = Request::get('id');
            //$ids = Cache::get($fileName);
            $map = [
                ['id','in',$fileName]
            ];
            $list = Db::name('admin')
                ->where($map)
                ->field('id,his_id,username,role,position,valid_low,valid_high,telecom,name,id_card,sex,birth_time,birth_addr,dep_id,dep_name,role_status')
                ->select();
            if(empty($list)){
                exit('非法请求！');
            }

            foreach($list as $k=>$v){
                $j = $k+3;
                $PHPExcel->getSheet(0)
                    ->setCellValue('A'.$j, $k+1)
                    ->setCellValue('B'.$j, $v['his_id'])
                    ->setCellValue('C'.$j, str_cut($v['his_id'],1))
                    ->setCellValue('D'.$j, $v['username'])
                    ->setCellValue('E'.$j, $v['role'])
                    ->setCellValue('F'.$j, $v['position'])
                    ->setCellValue('G'.$j, $v['valid_low'])
                    ->setCellValue('H'.$j, $v['valid_high'])
                    ->setCellValue('I'.$j, $v['telecom'])
                    ->setCellValue('J'.$j, $v['name'])
                    ->setCellValue('K'.$j, $v['id_card'])
                    ->setCellValue('L'.$j,$v['sex']==1?'男':'女')
                    ->setCellValue('M'.$j, $v['birth_time'])
                    ->setCellValue('N'.$j, $v['birth_addr'])
                    ->setCellValue('O'.$j, $v['dep_id'])
                    ->setCellValue('P'.$j, $v['dep_name'])
                    ->setCellValue('Q'.$j, $v['role_status']);
            }
            //导出属性设置
            $outputFileName = "党员信息表_".date('Ymd').".xls";
            $objWriter = new \PHPExcel_Writer_Excel5($PHPExcel);
            ob_end_clean();//清除缓冲区,避免乱码
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download,charset=utf-8");
            header('Content-Disposition:inline;filename="'.$outputFileName);
            header("Content-Transfer-Encoding: binary");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            $objWriter->save('php://output');
            ob_clean();//关键
            flush();//关键
            Cache::set($fileName,null);
            exit;
        }
    }



    //新增or编辑
    public static function doUser()
    {
        $id = Request::get('id');
        if($id>0){
            $map = [
                ['a.id','=',$id]
            ];
            $cur = Db::view('admin a','id,username,sex,realname,phone,status')
                ->view('admin_role b','role_id','b.user_id = a.id','left')
                ->where($map)
                ->find();
            $title = '编辑用户';
        }else{
            $cur = [
                'id' => 0,
                'username' => '',
                'sex' => 1,
                'realname' => '',
                'phone' => '',
                'status' => 1,
                'role_id' => '',
            ];
            $title = '添加用户';
        }
        return [
            'cur' => $cur,
            'title' => $title
        ];
    }

    //用户信息保存
    public static function doneUser()
    {
        check_no_refer();
        Db::transaction(function(){
            $data = Request::post();
            if(!empty($data)){
                $roleId = get_data_unset($data,'role_id');
                global $loginId;
                if($data['id']==0){
                    $isExist = get_field('admin',array('username'=>$data['username']));
                    if($isExist){
                        exit('该用户已经存在！');
                    }
                    $data['password'] = md5($data['password']);
                    $data['headimg'] = '/static/common/img/sex'.$data['sex'].'.png';
                    $fn = 'insertGetId';
                    $error = '添加用户失败！';
                    $log = '添加';
                }else{
                    $fn = 'update';
                    $error = '编辑用户失败！';
                    $log = '编辑';
                }
                $res = Db::name('admin')->$fn($data);
                $id = $data['id']>0?$data['id']:$res;
                $log .= '用户（'.'ID='.$id.'）';

                //用户角色保存
                if($res!==false){
                    if($data['id']==0){
                        $role_data = [
                            'user_id' => $res,
                            'role_id' => $roleId
                        ];
                        $res = Db::name('admin_role')->insert($role_data);
                    }else{
                        $map = [
                            ['user_id','=',$data['id']]
                        ];
                        $res = set_field_value('admin_role',$map,'role_id','=',$roleId);
                    }
                }

                if($res!==false){
                    if($data['id']==$loginId && $data['realname']!=session('admin_login.realname')){
                        session('admin_login.realname',$data['realname']);
                    }
                    Base::delRbacCache();
                    save_logs($log);
                    echo 1;
                }else{
                    save_logs($log,2);
                    echo $error;
                }
            }
        });
    }

    //删除用户
    public static function delUser()
    {
        check_no_refer();
        $ids = Request::post('id');
        if($ids){
            $map = [
                ['id','in',$ids]
            ];
            $res = set_field_value('admin',$map,'status','=',3);
            $log = '删除用户（ID为：'.$ids.'）';
            if($res!==false){
                save_logs($log);
                echo 1;
            }else{
                save_logs($log,2);
                echo '删除失败！';
            }
        }
    }

    /******************************************用户列表*****************************************/
    public static function member()
    {
        $post = Request::post();
        $map = [
            ['a.status','in','1,2']
        ];
        map_format($map);
        $list = Db::view('member a','id,username,phone,status')
            ->view('member_info b','sex','b.user_id = a.id','left')
            ->view('member_token c','login_ip','c.user_id = a.id','left')
            ->view('sys_district d','name province','b.province_id=d.id','left')
            ->where($map)
            ->order('a.id')
            ->page($post['page'],$post['limit'])
            ->select();
        if(empty($list)){
            ajax_return();
        }

        if($post['page']==1){
            $count = Db::view('member a','id')->where($map)->count('a.id');
            cache(request()->controller().'_'.request()->action().'_count',$count);
        }else{
            $count = cache(request()->controller().'_'.request()->action().'_count');
            if(!($count>0)){
                $count = Db::view('member a','id')->where($map)->count('a.id');
            }
        }
        ajax_return($list,$count);
    }

    public static function memberDetail(){
        $id = Request::get('id');
        if($id>0){
            $map = [
                ['a.id','=',$id]
            ];
            $cur = Db::view('member a','username,headimg,phone,status')
                ->view('member_info b','sex,birth_info','b.user_id = a.id','left')
                ->view('member_token c','login_ip','c.user_id = a.id','left')
                ->view('sys_district d','name province','b.province_id=d.id','left')
                ->where($map)
                ->find();
            if(empty($cur)){
                jump_error('内容不存在~');
            }
            return $cur;
        }else{
            jump_error('内容不存在~');
        }
    }
}