<?php
/**
 * Created by PhpStorm.
 * User: zejun.lei@foxmail.com
 * Date: 2019.02.18
 * Time: 16:02
 */

namespace app\home\controller;


use app\home\model\Document;

class Index extends Common
{
    public function index(){
        if(is_ajax()){
            $data=Document::getPushData();
            echo json_encode($data);
        }else{
            $data=Document::getHomePageData();
            $this->assign('data',$data);
            return $this->fetch();
        }
    }

    //生成设备二维码
    public function createQRCode(){
        $str=HOME_URL;
        $data = base64_encode($str);
        create_qrcode($data);
    }
}