<?php
namespace app\admin\model;

use think\Db;
use think\facade\Config;
use think\facade\Request;
use think\Model;

class Tool extends Model
{
    //数据清理
    public static function doCleanTab()
    {
        $tab = Request::post('tab');
        if($tab){
            try{
                $res = Db::execute("truncate table ".Config::get('database.prefix').$tab);
                if($res==0){
                    if($tab=='admin'){
                        Db::execute("truncate table ".Config::get('database.prefix').'admin_role');
                        $data = [
                            [
                                'username' => 'lvguozhong',
                                'telecom' => '18111622258',
                                'name' => '吕国忠',
                                'password' => md5('666666'),
                                'headimg' => '/static/common/img/sex1.png'
                            ],
                            [
                                'username' => 'admin',
                                'telecom' => '13800138000',
                                'name' => '管理员',
                                'password' => md5('666666'),
                                'headimg' => '/static/common/img/sex1.png'
                            ]
                        ];
                        Db::name('admin')->insertAll($data);
                        $data = [
                            [
                                'user_id' => 1,
                                'role_id' => 1
                            ],
                            [
                                'user_id' => 2,
                                'role_id' => 2
                            ]
                        ];
                        Db::name('admin_role')->insertAll($data);
                    }
                    $log = '清空数据表'.$tab;
                    save_logs($log);
                    echo 1;
                }else{
                    echo '清空失败！';
                }
            } catch (\Exception $e) {
                echo $e;
                //echo '数据表不存在！';
            }
        }
    }
}