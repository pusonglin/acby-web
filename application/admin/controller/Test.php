<?php
namespace app\admin\controller;

use formatXML\formatXML;
use think\Controller;
use think\Db;
use think\Paginator;

class Test extends Controller
{
    //日志查看
    public function log()
    {
        $data = read_log_txt();
        my_print($data);
    }


}
