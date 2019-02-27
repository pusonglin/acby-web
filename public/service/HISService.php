<?php

define('SOAP_MODEL', 2);// 1:wsdl模式  2:no-wsdl模式
include_once './lib/common.php';
include_once './lib/SoapDiscovery.php';
$soap = new SoapDiscovery('HISService','HISService');

//创建 WSDL 服务
//如果是客户端调用会访问两次此处，第一次是get用来生成wsdl文件，第二次是post用来调用webservice服务
//如果是链接访问第一次会生成wsdl并且在页面输出wsdl文件内容，第二次访问就直接输出wsdl文件内容
if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'){
    @set_time_limit(3000);
    @ini_set('memory_limit', '-1');
    $options['cache_wsdl'] = WSDL_CACHE_NONE;

    if (SOAP_MODEL == 1) {
        $wsdl = 'HISService.wsdl';
        // WSDL模式不用传 uri 参数但传了也不会有问题
    } else {
        $options['uri'] = 'http://'.$_SERVER['SERVER_NAME'];
        $wsdl = null;
    }
    try {
        $server = new SoapServer($wsdl, $options);
        $server->setClass('HISService');
        $server->handle();
    }catch (SoapFault $fault){
        echo 'Error Message: ' . $fault->getMessage();
    }
}else{
    // 查看 WSDL xml，删除以下程序就相当于 non-WSDL 模式
    header('Content-type: text/xml');
    if (isset($_SERVER['QUERY_STRING']) && (strcasecmp($_SERVER['QUERY_STRING'], 'wsdl') == 0 || strpos($_SERVER['QUERY_STRING'], 'wsdl') !== false)) {
        echo $soap->getWSDL();
    } else {
        echo file_get_contents('HISService.wsdl');
    }
}

class HISService
{
    private $api_url = 'http://192.168.31.31/api/Hispush';

    //接口鉴权
    public function __construct()
    {
        $xmlstr = file_get_contents('php://input');
        write_log_txt($xmlstr);
        $xmlstr = preg_replace('/\sxmlns="(.*?)"/', ' _xmlns="${1}"', $xmlstr);
        $xmlstr = preg_replace('/<(\/)?(\w+):(\w+)/', '<${1}${2}_${3}', $xmlstr);
        $xmlstr = preg_replace('/(\w+):(\w+)="(.*?)"/', '${1}_${2}="${3}"', $xmlstr);
        $xmlobj = simplexml_load_string($xmlstr);
        $res = json_decode(json_encode($xmlobj),true);
        $token = false;

        //HIS系统请求
        if(!$token && isset($res['soapenv_Header']['urn_token'])){
            $token = $res['soapenv_Header']['urn_token'];
        }

        //php测试请求
        if(!$token && isset($res['env_Header']['ns2_token'])){
            $token = $res['env_Header']['ns2_token'];
        }

        if($token != md5('HISService_Auth_1')){
            throw new SoapFault('HISService', 'Token expires!');
        }
    }

    //HIS系统请求接口
    public function HISServiceAPI($action,$message)
    {
        write_log_txt($action);
        write_log_txt($message);
        //接口数据处理
        $res = https_request($this->api_url,['action'=>$action,'message'=>$message]);
        write_log_txt($res);
        return $res;
    }
}