<?php
/**
 * 写日志文件到txt文件
 * @param $log
 * @param string $logName
 */
function write_log_txt($log){
    $logDir = './logs';
    if(!is_dir($logDir)){
        mkdir($logDir, 0777, true);
    }
    $logtxt = $logDir.'/log.txt';
    $log = is_array($log)?var_export($log,true):$log;
    $ofile = fopen($logtxt,'a');
    $start = "\n========start=======\n".date('Y-m-d H:i:s')."\n";
    $end = "\n========end========\n";
    fwrite($ofile,$start.$log.$end);
    fclose($ofile);
}

/**构建url地址请求
 * @param string $url 请求地址
 * @param null $data 请求参数
 * @param bool $json 是否以json字符传递并返回
 * @param bool $isXML 是否返回XML
 * @return array|mixed 返回值
 */
function https_request($url, $data = NULL, $json = false,$isXML=false){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)) {
        if($json && is_array($data)){
            $data = json_encode($data);
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        if($json){ //发送JSON数据
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($data)
                )
            );
        }
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($curl);
    $errorno = curl_errno($curl);
    if ($errorno) {
        return array('errorno' => false, 'errmsg' => $errorno);
    }
    curl_close($curl);
    if($isXML){
        $xmlObj = simplexml_load_string($res,'SimpleXMLElement',LIBXML_NOCDATA);
        $xmlStr = json_encode($xmlObj);
        $res = json_decode($xmlStr,true);
    }
    if($json){
        return json_decode($res, true);
    }else{
        return $res;
    }
}

//打印数组(调试使用)
function my_print($data,$isdump=false){
    echo '<meta charset="utf-8"><pre>';
    if(!$isdump){
        print_r($data);
    }else{
        var_dump($data);
    }
    exit;
}