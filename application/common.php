<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

//------------------------
// 应用公共方法
//------------------------

use think\Container;
use think\Db;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Debug;
use think\facade\Env;
use think\facade\Hook;
use think\facade\Lang;
use think\facade\Log;
use think\facade\Request;
use think\facade\Route;
use think\facade\Session;
use think\facade\Url;
use think\Response;
use think\route\RuleItem;
use think\captcha\Captcha;

//------------------------
// 字母A
//------------------------
/**
 * ajax返回数据
 * @param null $data @返回值
 * @param int $count @结果集数量
 * @param string $msg @提示信息
 * @param int $code @错误码
 */
function ajax_return($data = null, $count = 0, $msg = "", $code = 0)
{
    $response = array(
        'code' => $code ? $code : 0,
        'msg' => $msg ? $msg : "",
        'count' => $count > 0 ? $count : 0,
        'data' => empty($data) ? null : $data
    );
    echo json_encode($response);
    exit;
}

/**
 * 删除数组中的一个元素
 * @param $arr @原数组
 * @param $var @要去掉的元素
 */
function array_remove_value(&$arr, $var)
{
    foreach ($arr as $key => $value) {
        if (is_array($value)) {
            array_remove_value($arr[$key], $var);
        } else {
            $value = trim($value);
            if ($value == $var) {
                unset($arr[$key]);
            } else {
                $arr[$key] = $value;
            }
        }
    }
}

/**
 * 支付宝公钥签名
 * @param $sign_str
 * @param $true_sign
 * @return bool
 */
function alipay_public_sign($sign_str, $true_sign)
{
    $pukeyPath = '/lib/RSA/alipay_public_key.pem';
    $pukey = file_get_contents($pukeyPath);
    $publickey = openssl_get_publickey($pukey);
    $verify = (bool)openssl_verify($sign_str, base64_decode($true_sign), $publickey);;;
    //释放资源
    openssl_free_key($publickey);
    return $verify;
}


//------------------------
// 字母C
//------------------------
/**
 * 生成36位uuid
 * @param string $prefix
 * @return string
 */
function create_uuid($prefix = "")
{    //可以指定前缀
    $str = md5(time() . '_' . uniqid(mt_rand(), true));
    $uuid = strtoupper(substr($str, 0, 8)) . '-';
    $uuid .= strtoupper(substr($str, 8, 4)) . '-';
    $uuid .= strtoupper(substr($str, 12, 4)) . '-';
    $uuid .= strtoupper(substr($str, 16, 4)) . '-';
    $uuid .= strtoupper(substr($str, 20, 12));
    return $prefix . $uuid;
}

/**
 * 展示下拉选择菜单createOptionLists
 * @param $list
 * @param string $cur
 * @param bool $justNoChildAble
 * @param string $zd
 * @return string
 */
function create_select_list($list, $cur = "", $justNoChildAble = false, $zd = 'name')
{
    $str = '';
    foreach ($list as $v) {
        $space = '';
        if (isset($v[$zd])) {
            for ($i = 1; $i < $v['level']; $i++) {
                $space .= '　　';
            }
            if ($v['level'] > 0) {
                $line = $v['islast'] == 'Y' ? '└─' : '├─';
            } else {
                $line = '';
            }
            $sec = $cur == $v['id'] ? 'selected="selected"' : '';
            $disable = $justNoChildAble && isset($v['_child']) ? ' disabled' : '';
            $str .= '<option value="' . $v['id'] . '" class="' . $v['islast'] . '" ' . $sec . $disable . '>' . $space . $line . $v[$zd] . '</option>';
            if (isset($v['_child'])) {
                $str .= create_select_list($v['_child'], $cur, $justNoChildAble, $zd);
            }
        }
    }
    return $str;
}


/**
 * 检测是否为一个月最后一天
 * @return bool
 */
function check_is_last_day()
{
    $time = time();
    //一个月中的第几天，不带前导零（1 到 31）
    $cur_day = date('j', $time);
    //给定月份中包含的天数
    $has_day = date('t', $time);
    if ($cur_day != $has_day) {
        return false;
    } else {
        return true;
    }
}

/**
 * 检测内容是否含有敏感词并回复
 * @param string $str 要检测的内容
 * @param string $name 名称
 * @param bool $is_app 是否为app
 */
function check_content_sensitive($str, $name = '标题', $is_app = false)
{
    $res = check_sensitive($str);
    if ($res && $res != 'ok') {
        $error = $name . '含有敏感词【' . $res . '】,请修改后再提交！';
        if ($is_app) {
            res_api($error);
        } else {
            echo json_encode(
                array(
                    'flag' => 0,
                    'error' => $error
                )
            );
            exit;
        }
    }
}

/**
 * 检测是否被禁言
 * @return string
 */
function check_say_able($is_app = false)
{
    global $loginId;
    if ($loginId > 0) {
        $say_time = get_field('member_info', array('user_id' => $loginId), 'say_time', false, 'user_id');
        if ($say_time > time()) {
            if ($is_app) {
                res_api('您已被官方禁言,\n可在' . date('Y-m-d H:i', $say_time) . '后发言！\n请到系统消息中查看详情。');
            } else {
                echo json_encode(
                    array(
                        'flag' => 0,
                        'error' => '您已被官方禁言,<br/>可在' . date('Y-m-d H:i', $say_time) . '后发言！<br/>请到系统消息中查看详情。'
                    )
                );
                exit;
            }
        }
    }
}

/**
 * 生成web校验的token
 * @param $tokenName
 */
function create_web_token($tokenName)
{
    $token = md5(time() . '_' . mt_rand(10000, 9999));
    session($tokenName, $token, 600);
    return $token;
}

/**
 * 校验web的token
 * @param $tokenName
 * @param $getToken
 * @param $clean
 */
function check_web_token($tokenName, $getToken, $clean = false, $json = false, $msg = null)
{
    $token = session($tokenName);
    if ($clean) { //立即清除
        session($tokenName, null);
    }
    if (!$token || $token != $getToken) {
        $msg = $msg ? $msg : '页面已过期，请刷新重试！';
        if ($json) {
            $back = array(
                'flag' => 0,
                'error' => $msg
            );
            echo json_encode($back);
        } else {
            echo $msg;
        }
        exit;
    }
}

/**
 * 检测字符串是否为中文、英文、数字
 * @param $str
 * @return bool
 */
function check_str($str)
{
    $reg = '/^[\x{4e00}-\x{9fa5}\w]*$/u';
    return preg_match($reg, $str) ? true : false;
}

//取消超过半小时未支付的订单
function cancel_ticket_order()
{
    $time = time() - 1800;
    $map = array(
        'create_time' => array('elt', $time),
        'status' => 0
    );
    $ids = array();
    $list = get_select('order_ticket', $map, 'id,ticket_id,buy_num');
    foreach ($list as $v) {
        $res = set_field_value('zoo_ticket', array('id' => $v['ticket_id']), 'count', '+', $v['buy_num']);
        if ($res !== false) {
            $ids[] = $v['id'];
        }
    }
    if (!empty($ids)) {
        set_field_value('order_ticket', array('id' => array('in', $ids)), 'status', '=', '-1');
    }
}

//校验是否为模拟登录
function check_no_refer()
{
    if (!$_SERVER['HTTP_REFERER']) {
        exit;
    }
}

//敏感词检测
function check_sensitive($str, $replace = false)
{
    $sys_sensitive = cache('sys_sensitive');
    if (empty($sys_sensitive)) {
        $sys_sensitive = get_field('sys_sensitive', array(), 'keyword', true);
        cache('sys_sensitive', $sys_sensitive);
    }
    if (!empty($sys_sensitive)) {
        if ($replace) {
            $sys_sensitive = array_combine($sys_sensitive, array_fill(0, count($sys_sensitive), '*'));
            $backStr = strtr($str, $sys_sensitive);
        } else {
            $sys_sensitive = "/" . implode("|", $sys_sensitive) . "/i";
            if (preg_match($sys_sensitive, $str, $matches)) {
                $backStr = $matches[0];
            } else {
                $backStr = 'ok';
            }
        }
    } else {
        $backStr = 'ok';
    }
    return $backStr;
}

//存入缓存cache
function cache_save($tb, $cacheName, $cacheData)
{
    $options = Config::get('cache.memcache');
    cache($cacheName, $cacheData, $options);
    if (strpos($_SERVER['HTTP_HOST'], 'admin') !== false) {
        //运营端
        $user = session('admin_login');
    } else if (strpos($_SERVER['HTTP_HOST'], 'manage') !== false) {
        //管理端
        $user = session('manage_login');
    } else {
        $user = array(
            'party_id' => null
        );
    }
    $party_id = isset($user['party_id']) && $user['party_id'] > 0 ? $user['party_id'] : 0;
    $map = array(
        'tab' => $tb,
        'cache_name' => $cacheName,
        'party_id' => $party_id
    );
    $isExist = get_field('sys_cache', $map);
    $fn = $isExist ? 'update' : 'insert';
    $data = array(
        'id' => $isExist,
        'status' => 1,
        'party_id' => $party_id,
        'update_time' => date('Y-m-d H:i:s')
    );
    $data = array_merge($data, $map);
    Db::name('sys_cache')->$fn($data);
}

//删除缓存cache
function cache_del($tb, $cacheName = null)
{
    $map = array(
        'status' => 1,
        'tab' => $tb
    );
    $options = Config::get('cache.memcache');
    if ($cacheName) {
        $map['cache_name'] = $cacheName;
        cache($cacheName, null, $options);
    } else {
        $list = get_select('sys_cache', $map, 'cache_name');
        foreach ($list as $v) {
            cache($v['cache_name'], null, $options);
        }
    }
    Db::name('sys_cache')->where($map)->setField('status', 2);
}


//获取无重复随机数
function create_rand_num($min, $max, $option = array('type' => 1, 'nums' => array()))
{
    $rand = mt_rand($min, $max);
    if ($option['type'] == 1) { //获取nums之外的随机数
        if (in_array($rand, $option['nums'])) {
            create_rand_num($min, $max, $option);
        } else {
            return $rand;
        }
    } else if ($option['type'] == 2 && $option['tb'] && $option['field']) { //生成某表中某个字段随机数
        $isExist = get_field($option['tb'], array($option['field'] => $rand), $option['field'], false, $option['field']);
        if ($isExist) {
            create_rand_num($min, $max, $option);
        } else {
            return $rand;
        }
    } else {
        return '参数错误！';
    }
}


/**
 * 生成条形码
 * @param $param @条形码参数值
 * @param $name @条形码图片名称
 * @param $dir @条形码文件保存路径
 */
function create_barcode($param, $name, $dir)
{
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    require_once(EXTEND_PATH . '/barcode/BCGFontFile.php');
    require_once(EXTEND_PATH . '/barcode/BCGColor.php');
    require_once(EXTEND_PATH . '/barcode/BCGDrawing.php');
    require_once(EXTEND_PATH . '/barcode/BCGcode128.barcode.php');
    $font = new \BCGFontFile(__DIR__ . '/../vendor/topthink/think-captcha/assets/ttfs/1.ttf', 18);
    $color_black = new \BCGColor(0, 0, 0);
    $color_white = new \BCGColor(255, 255, 255);
    $drawException = null;
    $code = '';
    try {
        $code = new \BCGcode128();
        $code->setScale(2); // Resolution
        $code->setThickness(150); // Thickness
        $code->setForegroundColor($color_black); // Color of bars
        $code->setBackgroundColor($color_white); // Color of spaces
        $code->setFont($font); // Font (or 0)
        $code->parse($param); // Text
    } catch (\Exception $exception) {
        $drawException = $exception;
    }
    $drawing = new \BCGDrawing($dir . '/' . $name . '.png', $color_white);
    if ($drawException) {
        $drawing->drawException($drawException);
    } else {
        $drawing->setBarcode($code);
        $drawing->draw();
    }
    header('Content-Type: image/png');
    $drawing->finish(\BCGDrawing::IMG_FORMAT_PNG);
}


/**
 * 生成二维码
 * @param $param @二维码参数值
 * @param string $logo @二维码中间logo
 * @param null $name @二维码文件名称
 * @param null $dir @二维码文件保存路径
 */
function create_qrcode($param, $logo = './static/common/img/logo.png', $name = null, $dir = null)
{
    require_once(EXTEND_PATH . '/phpqrcode/phpqrcode.php');
    $errorCorrectionLevel = 'H';//纠错级别：L、M、Q、H
    $matrixPointSize = 10;//二维码点的大小：1到10

    if ($name && $dir) { //需要保存文件资源
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $path = $dir . '/' . $name . '.png';
    } else {
        //不需要保存文件资源
        $path = false;
    }
    QRcode::png($param, false, $errorCorrectionLevel, $matrixPointSize, 2, false);//不带Logo二维码的文件名
    if ($logo && file_exists($logo)) {
        if ($path) {
            $code = file_get_contents($path);
        } else {
            $path = null;
            $code = ob_get_clean();
        }
        $code = imagecreatefromstring($code);
        $logo = imagecreatefrompng($logo);
        $QR_width = imagesx($code);//二维码图片宽度
        $QR_height = imagesy($code);//二维码图片高度
        $logo_width = imagesx($logo);//logo图片宽度
        $logo_height = imagesy($logo);//logo图片高度
        $logo_qr_width = $QR_width / 4;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        //重新组合图片并调整大小
        imagecopyresampled($code, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        header("Content-type: image/png");
        imagepng($code, $path);
    }
}


//------------------------
// 字母D
//------------------------

/**
 * 删除内容
 * @param string $tb 要删除的内容所属表
 * @param array $where 删除条件
 * @return mixed
 */
function do_del($tb, $where)
{
    return Db::name($tb)->where($where)->delete();
}

/**删除文件并判断父目录是否为空，如果为空则删除
 * @param string $path 目录名称
 */
function del_file_and_dir($path)
{
    if (is_file($path)) {
        unlink($path);
        $arr = explode('/', $path);
        $newArr = array_slice($arr, 0, -1);
        $newPath = join('/', $newArr);
        del_file_and_dir($newPath);
    } else {
        if (is_empty_dir($path)) {
            rmdir($path);
            $arr = explode('/', $path);
            $newArr = array_slice($arr, 0, -1);
            $newPath = join('/', $newArr);
            del_file_and_dir($newPath);
        }
    }
}

/**
 * 删除目录及目录下的文件
 * @param string $dirName 目录名称
 */
function del_dir_and_file($dirName)
{
    if (is_dir($dirName) && $handle = opendir($dirName)) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..") {
                if (is_dir($dirName . '/' . $item)) {
                    del_dir_and_file($dirName . '/' . $item);
                } else {
                    unlink($dirName . '/' . $item);
                }
            }
        }
        closedir($handle);
        //删除目录
        del_file_and_dir($dirName);
    }
}

//------------------------
// 字母F
//------------------------
/**
 * 格式化url地址的参数
 * @param $url
 * @param $key
 * @param $value
 */
function format_url_param(&$url, $key, $value)
{
    if (!$key || !$value) {
        return false;
    }
    if (strpos($url, '?') === false) {
        $url = $url . '?' . $key . '=' . $value;
    } else {
        if (strpos($url, $key . '=') === false) {
            $url = $url . '&' . $key . '=' . $value;
        } else {
            $url = trim(preg_replace('/' . $key . '=[^&]*?&/', $key . '=' . $value . '&', $url . '&'), '&');
        }
    }
}

/**
 * 刷新tokenInfo
 * @param $token
 * @param $zd_name
 * @param $val
 */
function fresh_token_val($token, $zd_name, $val)
{
    if (empty($zd_name) || !$zd_name) {
        return;
    }
    $tokenInfo = S($token);
    if (is_array($zd_name)) {
        foreach ($zd_name as $k => $v) {
            $tokenInfo[$v] = $val[$k];
        }
    } else {
        $tokenInfo[$zd_name] = $val;
    }
    $time = $tokenInfo['expire_time'] - time();
    if ($time > 0) {
        S($token, $tokenInfo, $time);
    }
}

/**
 * 图片翻转
 * @param string $filename 文件路径
 * @param string $src 保存路径
 * @param int $degrees 旋转角度
 * @return bool
 */
function flip($filename, $src, $degrees = 90)
{
    //读取图片
    $data = @getimagesize($filename);
    if ($data == false) return false;
    //读取旧图片
    $src_f = "";
    switch ($data[2]) {
        case 1:
            $src_f = imagecreatefromgif($filename);
            break;
        case 2:
            $src_f = imagecreatefromjpeg($filename);
            break;
        case 3:
            $src_f = imagecreatefrompng($filename);
            break;
    }
    if ($src_f == "") return false;
    $rotate = @imagerotate($src_f, $degrees, 0);
    if (!imagejpeg($rotate, $src, 100)) return false;
    @imagedestroy($rotate);
    return true;
}


/**
 * PHP异步请求
 * @param $url ：请求地址
 * @param array $param ：参数
 */
function fsockopen_request($url, $param = array())
{
    ignore_user_abort(true); // 忽略客户端断开
    set_time_limit(600);       // 设置执行不超时
    $urlinfo = parse_url($url);
    $host = $urlinfo['host'];
    $path = $urlinfo['path'];
    $query = isset($param) ? http_build_query($param) : '';

    $port = 80;
    $errno = 0;
    $errstr = '';
    $timeout = 10;

    $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
    stream_set_blocking($fp, 0); //开启非阻塞模式

    //stream_set_timeout($fp, 3); //设置超时时间（s）

    $out = "POST " . $path . " HTTP/1.1\r\n";
    $out .= "host:" . $host . "\r\n";
    $out .= "content-length:" . strlen($query) . "\r\n";
    $out .= "content-type:application/x-www-form-urlencoded\r\n";
    $out .= "connection:close\r\n\r\n";
    $out .= $query;
    fputs($fp, $out);
    fclose($fp);
}

//------------------------
// 字母G
//------------------------
/**
 * 获取当前登录用户的group_id
 * @param $loginInfo
 * @param bool $map_field
 * @param bool $just_self
 * @return array|int
 */
function get_group_id($loginInfo, $map_field = null, $just_self = false)
{
    if ($map_field) {
        if (isset($loginInfo['is_super']) && $loginInfo['is_super'] == 1) {
            $groupId = [$map_field, '>=', 0];
        } else {
            //只查看当前管辖的支部及下级的内容（如党员）
            $childIds = get_all_child($loginInfo['manage_group_id'], 'pid', 'party_group');
            if (!$just_self) {
                //查看当前管辖的支部及下级的内容和上级发布的内容（如文章）
                $pids = get_all_parents($loginInfo['manage_group_id'], 'party_group', 'id,pid');
                foreach ($pids as $v) {
                    $childIds[] = $v['id'];
                }
                $childIds[] = 0;
            }
            $childIds = array_unique($childIds);
            if (empty($childIds)) {
                $groupId = [$map_field, '=', -1];
            } else {
                $groupId = [$map_field, 'in', $childIds];
            }
        }
    } else {
        //发布信息
        $groupId = $loginInfo['is_super'] == 1 ? 0 : $loginInfo['manage_group_id'];
    }
    return $groupId;
}

/**
 * 读取模板文件
 * @param $blockId
 * @param bool $require_once
 */
function get_html($blockId, $require_once = false)
{
    $htmlpath = dirname($_SERVER['SCRIPT_FILENAME']) . '/html/';
    $htmlpath .= Request::get('act') == 'temp' ? 'temp/' : '';
    $htmlpath .= $blockId . '.' . config('url_html_suffix');
    if (file_exists($htmlpath)) {
        if ($require_once) {
            require_once $htmlpath;
        } else {
            require $htmlpath;
        }
    }
}

/**
 * 获取图片尺寸
 * @param $src
 * @return string
 */
function get_img_size($src)
{
    $src = trim($src, '.');
    if (!$src) {
        return "960x960";
    }
    $path = '.' . $src;
    if (!file_exists($path)) {
        return "960x960";
    }
    try {
        $image = \think\Image::open($path);
        $w = $image->width();
        $h = $image->height();
        return $w . 'x' . $h;
    } catch (\Exception $e) {
        //
        return '960x960';
    }
}

/**
 * 查询分类的子集
 * @param $value @分类标识或父id
 * @param bool $isCode 是否为分类标识code
 * @return mixed 查询结果
 */
function get_cate_child_list($value, $isCode = true)
{
    if ($isCode) {
        $pid = get_field('sys_category', array('code' => $value));
    } else {
        $pid = $value;
    }
    $list = get_select('sys_category', array('pid' => $pid), 'id,name', 'sortnum desc,id');
    return $list;
}

/**
 * 查询党组织的所有权限id
 * @param $party_id
 * @param int $role_id
 * @return array|mixed
 */
function get_party_right_ids($party_id, $role_id = 0)
{
    $cacheName2 = 'auth_partyRightIds_party-' . $party_id;
    global $cacheOptions;
    $partyRightIds = cache($cacheName2, '', $cacheOptions);
    if (empty($partyRightIds)) {
        $partyRightIds = get_field('auth_party_right', array('party_id' => $party_id), 'right_ids');
        if (!$partyRightIds) {
            //查询当前党组织已经开通的权限
            $map = [
                ['party_id', '=', $party_id],
                ['status', '=', 1],
            ];
            $actionIds = Db::name('auth_party_action')
                ->where($map)
                ->where('`is_free` = 1 OR (`is_free` = 2 AND `expire_time` > ' . time() . ')')
                ->column('id');
            if (!empty($actionIds)) {
                $map = [
                    'action_id' => $actionIds
                ];
                $partyRightIds = get_field('auth_action_right', $map, 'right_id', true, 'right_id');
            }
            if (!empty($partyRightIds)) {
                //保存党组织权限id
                $partyRightIds = array_values(array_unique($partyRightIds));
                $data = array(
                    'party_id' => $party_id,
                    'right_ids' => json_encode($partyRightIds)
                );
                Db::name('auth_party_right')->insert($data);
                //保存超级管理员的权限
                $superRoleId = get_field('auth_role', array('party_id' => $party_id, 'is_super' => 1));
                $datas = array();
                foreach ($partyRightIds as $v) {
                    $datas[] = array(
                        'role_id' => $superRoleId,
                        'right_id' => $v
                    );
                }
                do_del('auth_role_right', array('role_id' => $superRoleId));
                Db::name('auth_role_right')->insertAll($datas);
                if ($role_id == $superRoleId) {
                    $roleAllRightIds = $partyRightIds;
                    $cacheName = 'auth_allRightIds_role-' . $role_id;
                    cache_save('auth_role_right', $cacheName, $roleAllRightIds);
                }
                cache_save('auth_party_right', $cacheName2, $partyRightIds);
            }
        } else {
            $partyRightIds = json_decode($partyRightIds);
        }
    }
    return $partyRightIds;
}

/**
 * 获取文件后缀
 * @param $url @文件地址
 */
function get_file_type($url)
{
    $path = trim($url, '.');
    $arr = explode('.', $path);
    return $arr[1];
}

/**
 * 获取文件大小
 * @param $url @文件地址
 */
function get_file_size($url)
{
    $path = trim($url, '.');
    $size = filesize('.' . $path);
    if ($size > 10486) {
        $size = sprintf("%.2f", $size / 1048576) . 'M';
    } else if ($size > 1024) {
        $size = intval($size / 1024) . 'KB';
    } else {
        $size = $size . 'B';
    }
    return $size;
}

/**
 * 根据省市区街道的id获取区域信息
 * @param $cur
 * @return array|int
 */
function get_district_info($cur)
{
    $list = get_select('sys_district', [['id', 'in', [$cur['province_id'], $cur['city_id'], $cur['county_id'], $cur['town_id']]]], 'id,name');
    $prov = "";
    $city = "";
    $county = "";
    $town = "";
    foreach ($list as $v) {
        if ($v['id'] == $cur['province_id']) {
            $prov = $v['name'];
        } else if ($v['id'] == $cur['city_id']) {
            $city = $v['name'];
        } else if ($v['id'] == $cur['county_id']) {
            $county = $v['name'];
        } else if ($v['id'] == $cur['town_id']) {
            $town = $v['name'];
        }
    }
    if ($cur['town_id'] > 0) {
        $district_id = $cur['town_id'];
    } else if ($cur['county_id'] > 0) {
        $district_id = $cur['county_id'];
    } else if ($cur['city_id'] > 0) {
        $district_id = $cur['city_id'];
    } else if ($cur['province_id'] > 0) {
        $district_id = $cur['province_id'];
    } else {
        $district_id = 0;
    }
    return array(
        'district' => $prov . ' ' . $city . ' ' . $county . ' ' . $town,
        'district_id' => $district_id
    );

}

/**
 * 获取模糊查询条件
 * @param $keyword 关键词
 * @param bool $isJson 数据库是否为json数据
 * @return array
 */
function get_search_like($keyword, $isJson = false)
{
    if ($isJson) {
        $keyword = str_replace('"', '', json_encode($keyword));
        $keyword = str_replace("\\", '_', $keyword);
    }
    return array('like', array('%' . $keyword, '%' . $keyword . '%', $keyword . '%'), 'or');
}


/**
 * 通过改变排序获取随机熊猫
 * @return mixed
 */
function get_panda_order()
{
    $order = array(
        0 => 'id',
        1 => 'id desc',
        2 => 'name',
        3 => 'name desc',
        4 => 'name_en',
        5 => 'name_en desc',
        6 => 'geneal',
        7 => 'geneal desc',
        8 => 'zoo_id',
        9 => 'zoo_id desc',
        10 => 'sex',
        11 => 'sex desc',
        12 => 'birthyear',
        13 => 'birthyear desc',
        14 => 'rank',
        15 => 'rank desc',
        16 => 'monther_id',
        17 => 'monther_id desc',
        18 => 'fans',
        19 => 'fans desc'
    );
    $num = mt_rand(0, 19);
    return $order[$num];
}

/**
 * 查询所有的园区列表
 * @return mixed
 */
function get_zoo_list()
{
    $cacheName = 'zoo_list';
    $list = S($cacheName);
    if (empty($list)) {
        $list = get_select('zoo', array('status' => 1), 'id,name,gnote,buy_ticket', 'sortnum desc,id');
        foreach ($list as &$v) {
            $gnote = get_data_unset($v, 'gnote');
            if (strpos($gnote, ',') !== false) {
                $temp = explode(',', $gnote);
                $v['lng'] = $temp[0];
                $v['lat'] = $temp[1];
            } else {
                $v['lng'] = '';
                $v['lat'] = '';
            }
        }
        cache_save('zoo', $cacheName, $list);
    }
    return $list;
}

/**
 * 查询地址列表,所有子菜单
 * @param int $pid 起始父id
 * @param int $self_in 是否包含自己
 * @return mixed 结果列表
 */
function get_district_list($pid = 0, $self_in = 1)
{
    $cacheName = 'sys_district_list_' . $pid . '_' . $self_in;
    $list = cache($cacheName);
    if (empty($list)) {
        $map = array('status' => 1);
        if ($pid > 0) {
            $ids = get_all_child($pid, 'pid', 'sys_district');
            if (!$self_in) {
                array_shift($ids);
            }
            if (!empty($ids)) {
                $map['id'] = array('in', $ids);
            }
        } else {
            if (!$self_in) {
                $map['pid'] = array('notin', array($pid));
            }
        }
        $list = get_select('sys_district', $map, 'id,pid,name,sortnum', 'adcode');
        $list = list_to_tree($list, 'id', 'pid', '_child', $pid);
        cache_save('sys_district', $cacheName, $list);
    }
    return $list;
}

/**
 * 获取当前pid下的一级子菜单
 * @param int $pid 父id
 * @return mixed
 */
function get_district_childdren($pid)
{
    global $cacheOptions;
    $cacheName = 'district_children_' . $pid;
    $list = cache($cacheName, '', $cacheOptions);
    if (empty($list)) {
        if ($pid == 'all') {
            $map = array('status' => 1);
        } else {
            $map = array('status' => 1, 'pid' => $pid);
        }
        $list = get_select('sys_district', $map, 'id,name', 'adcode');
        cache_save('sys_district', $cacheName, $list);
    }
    return $list;
}

/**
 * @return int 当前查询的页码数
 */
function get_page($name = 'p')
{
    if ($name == 'p') { //WEB
        $page = Request::get('p');
        $post = Request::post();
        $page = empty($post) ? ($page > 0 ? $page : 1) : 1;
    } else { //接口
        $page = Request::get($name);
        $page = $page > 0 ? $page : 1;
    }
    return $page;
}


//查询单个字段的值
function get_field($tb, $where = [], $zdName = 'id', $all = false, $order = ['id' => 'desc'])
{
    $fn = $all ? 'column' : 'value';
    return Db::name($tb)->where($where)->order($order)->$fn($zdName);
}

//查询单条数据
function get_find($tb, $where = [], $fields = '*', $order = ['id' => 'desc'])
{
    return Db::name($tb)->where($where)->field($fields)->order($order)->find();
}

//查询数据列表（不分页）
function get_select($tb, $where = [], $fields = '*', $order = ['id' => 'desc'], $limit = 0)
{
    if ($limit) {
        return Db::name($tb)->where($where)->field($fields)->order($order)->limit($limit)->select();
    } else {
        return Db::name($tb)->where($where)->field($fields)->order($order)->select();
    }
}

//查询总数
function get_count($tb, $where)
{
    return Db::name($tb)->where($where)->count();
}

//求某个字段的和
function get_sum($tb, $where, $zdName = 'score')
{
    return Db::name($tb)->where($where)->sum($zdName);
}

//获取数据列表（分页）
function get_page_list($tb, $where = array(), $fields = '*', $order = 'id desc', $pernum = 10, $page_str = true)
{
    $DB = Db::name($tb);
    if ($_REQUEST['page']) {
        $p = $_REQUEST['page'];
    } else {
        $p = isset($_GET['p']) ? (empty($_POST) ? $_GET['p'] : 1) : 1;
    }
    $list = $DB->where($where)->field($fields)->order($order)->page($p . ',' . $pernum)->select();
    if ($page_str) {
        $res['list'] = $list;
        $count = $DB->where($where)->count();
        $Page = new \Think\Page($count, $pernum);
        $res['page'] = $Page->show();
    } else {
        return $list;
    }
    return $res;
}


//通过父id获取所有的子id.
function get_all_child($pid, $pidzdName, $tb)
{
    $ids[] = $pid;
    $DB = Db::name($tb);
    $res = $DB->where($pidzdName, '=', $pid)->field('id')->select();
    if (!empty($res)) {
        foreach ($res as $v) {
            $temp = get_all_child($v['id'], $pidzdName, $tb);
            $ids = array_merge($ids, $temp);
        }
    }
    return $ids;
}

//获取下拉菜单列表
function get_option_list($code = null, $self_in = false, $map = array('status' => 1), $tb = 'sys_category', $fields = "id,pid,code,name,sortnum", $order = "sortnum desc,id")
{
    if ($code != null) { //读取某个栏目下的子栏目
        $curPid = get_field($tb, array('code' => $code));
        if ($curPid) {
            $ids = get_all_child($curPid, 'pid', $tb);
            if (!$self_in) {
                array_shift($ids);
            } else {
                $curPid = 0;
            }
            $map['id'] = $ids;
        } else {
            return array();
        }
    } else {
        if ($tb == 'party_group') {
            global $manage_login;
            $curPid = get_field('party_group', array('id' => $manage_login['manage_group_id']), 'pid');
            $curPid = $curPid > 0 ? $curPid : 0;
        } else {
            $curPid = 0;
        }
    }
    $list = Db::name($tb)->where($map)->field($fields)->order($order)->select();
    $list = list_to_tree($list, $pk = 'id', $pid = 'pid', '_child', $curPid);
    return $list;
}

//取值后unset数组元素
function get_data_unset(&$arr, $zd)
{
    $temp = $arr[$zd];
    unset($arr[$zd]);
    return $temp;
}

//读取微信公众号配置信息
//读取微信公众号配置信息
function get_WX_config(){
    $cacheName = 'auth.wx_config';
    //$wxConfig = Cache::get($cacheName);
    //if(empty($wxConfig)){
        $wxConfig = Config::get('auth.wx_config');
    //}
    $wxConfig['expire_time']=isset($wxConfig['expire_time'])?$wxConfig['expire_time']:0;

    if ($wxConfig['expire_time']<time()){

        $wxConfig = get_WX_access_token($wxConfig);
        my_print($wxConfig);
        Cache::set('auth.wx_config',$cacheName,$wxConfig);
    }else{
        //验证当前access_token有效性
        $url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=".$wxConfig['access_token'];
        $ips = https_request($url);
        $ips = json_decode($ips, true);
        if($ips['errcode']==40001){
            $wxConfig = get_WX_access_token($wxConfig);
            cache_save('auth.wx_config',$cacheName,$wxConfig);
        }
    }
    return $wxConfig;
}
//读取微信公众号access_token
function get_WX_access_token($config)
{
    $appid = $config['app_id'];
    $appsecret = $config['app_secret'];
    $access_token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
    $access_token_json = https_request($access_token_url);
    $access_token_array = json_decode($access_token_json, true);
    my_print($access_token_array);
    $access_token_array['expire_time'] = time() + $access_token_array['expires_in'];
    $config = array_merge($config, $access_token_array);
    return $config;
}

//获取分类列表数据
function get_category_list($code = null, $cacheName, $tb = 'sys_category')
{
    global $cacheOptions;
    $list = cache($cacheName, '', $cacheOptions);
    if (empty($list)) {
        $list = get_option_list($code, true, array(), $tb);
        cache_save($tb, $cacheName, $list);
    }
    return $list;
}

//获取封面图组
function get_img_list($tb, $pid, $num = 1, $order = 'iscover,id desc')
{
    $map = array(
        'tab' => $tb,
        'pid' => $pid
    );
    $list = get_select('imglist', $map, 'url', $order, $num);
    if ($num == 1) {
        return $list[0]['url'];
    } else {
        return $list;
    }
}

//通过当前cate_id获取所有父id的id和name
function get_all_parents($cur_pid, $tb = 'sys_category', $fields = 'id,pid,name')
{
    $pids = array();
    $cur = get_find($tb, array('id' => $cur_pid), $fields);
    if (!empty($cur)) {
        $pids[] = $cur;
        if ($cur['pid'] > 0) {
            $temp = get_all_parents($cur['pid'], $tb, $fields);
            $pids = array_merge($temp, $pids);
        }
    }
    return $pids;
}

//根据两经纬度计算之间的距离
function get_distance($lng1, $lat1, $lng2, $lat2)
{
    $earthRadius = 6367000;
    $lat1 = ($lat1 * pi()) / 180;
    $lng1 = ($lng1 * pi()) / 180;
    $lat2 = ($lat2 * pi()) / 180;
    $lng2 = ($lng2 * pi()) / 180;
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;
    return round($calculatedDistance);
}


/**
 * 图片缩放裁剪
 * @param string $src 原图地址
 * @param integer $width 裁减宽度
 * @param integer $height 裁剪高度
 * @param integer $type 裁减类型：1/IMAGE_THUMB_SCALE:等比例缩放类型，2/IMAGE_THUMB_FILLED:缩放后填充类型，3/IMAGE_THUMB_CENTER:居中裁剪类型,4/IMAGE_THUMB_NORTHWEST:左上角裁剪类型,5/IMAGE_THUMB_SOUTHEAST:右下角裁剪类型，6/IMAGE_THUMB_FIXED:固定尺寸缩放
 * @param boolean $reuse 是否复用之前生成的
 * @return string
 */
function get_thumb_img($src, $width, $height, $type = 3, $reuse = true)
{
    $src = trim($src, '.');
    if (!$src) {
        return "";
    }
    if (preg_match('/(http:\/\/)|(https:\/\/)/i', $src)) {
        return $src;
    }
    $path = '.' . $src;
    if (!file_exists($path)) {
        $path = './static/common/img/default.png';
        $src = trim($path, '.');
    }
    ini_set('memory_limit', '1024M');
    ignore_user_abort(TRUE);
    set_time_limit(0);
    $temp = explode('/', $src);
    $count = count($temp);
    $temp[1] = 'thumb';
    $temp[$count - 1] = preg_replace('/(.jpg)|(.png)|(.jpeg)|(.gif)/i', '/' . $width . 'x' . $height . '${0}', $temp[$count - 1]);
    $thumbSrc = join('/', $temp);
    $thumbPath = '.' . $thumbSrc;
    if (file_exists($thumbPath) && $reuse) {
        return $thumbSrc;
    }
    //$image = new \Think\Image('Imagick');
    //$image=new \think\Image('Imagick');
    //$image->open($path);
    $image = \think\Image::open($path);
    $temp = explode('/', $thumbPath);
    array_pop($temp);
    $dir = join('/', $temp);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    //判断当前尺寸是否需要裁剪
    $cur_w = $image->width();
    $cur_h = $image->height();
    $width = $cur_w > $width ? $width : $cur_w;
    $height = $cur_h > $height && $height > 0 ? $height : $cur_h;
    $image->thumb($width, $height, $type)->save($thumbPath);
    return $thumbSrc;
}


//获取敏感词
function get_sensitive($tb = 'sys_sensitive')
{
    $sensitive = cache($tb);
    if (empty($sensitive)) {
        $sensitive = get_field($tb, array(), 'keyword', true);
        cache($tb, $sensitive);
    }
    return $sensitive;
}


//获取随机字符串
function get_rand_str($len, $chars = null, $tb = null, $field = null)
{
    if (is_null($chars)) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    }
    mt_srand(10000000 * (double)microtime());
    for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
        $str .= $chars[mt_rand(0, $lc)];
    }
    if ($tb && $field) {
        $isExist = get_field($tb, array($field => $str));
        if ($isExist) {
            get_rand_str($len, $chars, $tb, $field);
        } else {
            return $str;
        }
    } else {
        return $str;
    }
}

//获取客户端网速
function get_net_speed()
{
    $fp = fopen("cs.txt", "w");
    for ($i = 0; $i < 170400; $i++) {
        fwrite($fp, "bandwidth");
    }
    fclose($fp);
    $start = get_my_microtime();
    $fsize = filesize("cs.txt") / 1024;

    $stop = get_my_microtime();
    $duration = ($stop - $start);
    $speed = round($fsize / $duration, 2);
    return $speed;
}

//计算时间
function get_my_microtime()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

/**
 * 保存远程图片到本地
 * @param string $url 远程图片地址
 * @param string $savedir 保存目录
 * @return bool|string 生成的路径
 */
function get_remote_img($url = "", $savedir = "./remote/", $filename = null)
{
    if (!$url) return false;
    ignore_user_abort(TRUE);
    set_time_limit(0);
    $type = exif_imagetype($url); //返回值为整型
    if (!$type) {//针对比如微信头像没有后缀名不能获取其类型的处理
        //保存目录
        if (!is_dir($savedir)) mkdir($savedir, 0777, true);
        if (!is_readable($savedir)) chmod($savedir, 0777);
        //文件名拼接
        if ($filename) {
            $path = $savedir . $filename;
        } else {
            $path = $savedir . date('ymdHi') . mt_rand(100000, 999999) . '.png';
        }
        $http = new \Org\Net\Http();
        $http::curlDownload($url, $path);
        //获取的是svg图片
        if ($filename && preg_match('/(.svg)/i', $filename)) {
            return trim($path, '.');
        }
        $type = exif_imagetype($path);
        if (!$type) {
            del_file_and_dir($path);
            return false;
        }
        return trim($path, '.');
    } else {
        $exifArr = array(
            1 => '.gif',
            2 => '.jpg',
            3 => '.png',
            4 => '.swf',
            5 => '.psd',
            6 => '.bmp',
            17 => '.icon'
        );
        $ext = $exifArr[$type]; //后缀名
        if (!$exifArr[$type]) return false; //不存在这里的值时返回
        //保存目录
        if (!is_dir($savedir)) mkdir($savedir, 0777, true);
        if (!is_readable($savedir)) chmod($savedir, 0777);
        //文件名拼接
        if ($filename) {
            $path = $savedir . $filename;
        } else {
            $path = $savedir . date('ymdHi') . mt_rand(100000, 999999) . $ext;
        }
        //图片保存
        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
        $fp2 = @fopen($path, "a");
        fwrite($fp2, $img);
        fclose($fp2);
        return trim($path, '.');
    }
}

//------------------------
// 字母H
//------------------------

/**构建url地址请求
 * @param string $url 请求地址
 * @param null $data 请求参数
 * @param bool $json 是否以json字符传递并返回
 * @param bool $isXML 是否返回XML
 * @return array|mixed 返回值
 */
function https_request($url, $data = NULL, $json = false, $isXML = false)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)) {
        if ($json && is_array($data)) {
            $data = json_encode($data);
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        if ($json) { //发送JSON数据
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
    if ($isXML) {
        $xmlObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        $xmlStr = json_encode($xmlObj);
        $res = json_decode($xmlStr, true);
    }
    if ($json) {
        return json_decode($res, true);
    } else {
        return $res;
    }
}

//构建url地址请求[旧的]
function https_request_20161215($url, $post_data = '', $timeout = 5)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);
    if ($post_data != '') {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    }
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        return 'ERROR ' . curl_error($curl);
    }
    curl_close($curl);
    return $data;
}

//------------------------
// 字母I
//------------------------

/**
 * 检测是否为app
 * @return bool
 */
function is_app()
{
    return strpos($_SERVER['HTTP_USER_AGENT'], 'pandapia_app') !== false ? 1 : false;
}


/**
 * 检测是否为json字符串
 * @param $string
 * @return bool
 */
function is_json($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * 判断是否为ajax请求
 * @return bool
 */
function is_ajax()
{
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
        return true;
    } else {
        return false;
    }
}

/**
 * 检测是否为空目录
 * @param string $path 目录名称
 * @return bool 判断结果
 */
function is_empty_dir($path)
{
    $res = true;
    if (is_dir($path) && ($handle = opendir($path)) !== false) {
        while (($file = readdir($handle)) !== false) {// 遍历文件夹
            if ($file != '.' && $file != '..') {
                $res = false;
                break;
            }
        }
        closedir($handle);
    } else {
        $res = false;
    }
    return $res;
}

//------------------------
// 字母J
//------------------------
/**
 *跳转到错误页
 *
 */
function jump_error($msg = "页面不存在~")
{
    global $http_type;
    $msg = 'o(╯□╰)o　' . $msg;
    $url = $http_type . $_SERVER['SERVER_NAME'] . '/error.html?msg=' . $msg;
    header("location:" . $url);
}

/**
 * @param $msg 要发送的消息
 * @param string $uids 用户id
 * @param array $extras 扩展字段
 * @return bool
 */
function jpush_msg($msg, $uids = 'all', $extras = array())
{
    $config = Config::get('auth.jg_config');
    $app_key = $config['app_key'];
    $master_secret = $config['secret'];
    $client = new \JPush\Client($app_key, $master_secret, null);
    //发送所有用户
    if ($uids == 'all') {
        $client->push()
            ->setPlatform('all')
            ->addAllAudience()
            ->setNotificationAlert($msg)
            ->send();
        return true;
    }
    //发送指定用户
    if (!is_array($uids) && $uids) {
        $uids = array(
            0 => $uids
        );
    }
    foreach ($uids as $v) {
        $cacheName = 'jpush_reg_uid_' . $v;
        global $cacheOptions;
        $info = cache($cacheName, '', $cacheOptions);
        if (empty($info)) {
            $info = get_find('admin_token', array('user_id' => $v, 'is_push' => 1), 'jpush_reg_id,device_type', 'user_id');
            if ($info['jpush_reg_id']) {
                cache_save('admin_token', $cacheName, $info);
            }
        }
        if ($info['jpush_reg_id']) {
            if ($info['device_type'] == 1) {
                jpush_ios($client, $msg, array($info['jpush_reg_id']), $extras);
            } else {
                jpush_android($client, $msg, array($info['jpush_reg_id']), $extras);
            }
        }
    }
}

/**
 * 推送给ios用户
 * @param $client
 * @param $msg
 * @param $pushID
 * @return mixed
 */
function jpush_ios($client, $msg, $pushID, $extras)
{
    $config = array(
        'sound' => 'default',
        'content-available' => false,
        'category' => 'message',
        'alert' => $msg,
        'extras' => $extras
    );
    $options = array(
        'apns_production' => true //是否为生产模式（即蒲公英或者appstore下载的）
    );
    $result = $client
        ->push()
        ->setPlatform('ios')
        ->addRegistrationId($pushID)
        ->iosNotification($msg, $config)
        ->options($options)
        ->send();
    return $result;
}

/**
 * 推送给android用户
 * @param $client
 * @param $msg
 * @param $pushID
 * @return mixed
 */
function jpush_android($client, $msg, $pushID, $extras)
{
    $config = array(
        'title' => '系统消息',
        'extras' => $extras
    );
    $result = $client
        ->push()
        ->setPlatform('android')
        ->addRegistrationId($pushID)
        ->androidNotification($msg, $config)
        ->send();
    return $result;
}


//------------------------
// 字母L
//------------------------
//读取级联菜单
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $startPid = 0)
{
    $tree = array();
    if (is_array($list)) {
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            $parentId = $data[$pid];
            if ($startPid == $parentId) {
                $tree[] =& $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    $tree = list_tree_islast($tree);
    return $tree;
}

//判断级联菜单在同一级中是否为最后一个
function list_tree_islast($list, $level = 0)
{
    foreach ($list as $k => &$v) {
        $v['level'] = $level + 1;
        $count = count($list) - 1;
        $v['islast'] = $k == $count ? "Y" : 'N';
        if (isset($v['_child'])) {
            list_tree_islast($v['_child'], $v['level']);
        }
    }
    return $list;
}

//------------------------
// 字母M
//------------------------

/**
 * 删除垃圾文件验证是否有其他数据引用查询条件处理
 * @param $data
 * @param $val
 * @return array
 */
function del_file_map_format($data, $val)
{
    $str = "";
    $map = array();
    if (empty(!$data)) {
        foreach ($data as $k => $v) {
            switch ($v) {
                case 'eq':
                    $str .= ' (`' . $k . '` = "' . $val . '") OR';
                    break;
                case 'like':
                    $str .= ' (`' . $k . '` LIKE "%' . $val . '" OR `' . $k . '` LIKE "%' . $val . '%" OR `' . $k . '` LIKE "' . $val . '%") OR';
                    break;
            }
        }
    }
    $str = trim($str, 'OR');
    if ($str != "") {
        $map['_string'] = $str;
    }
    return $map;
}

/**
 * 后台ajax查询条件处理
 * @param array $map
 */
function map_format(&$map = [])
{
    $post = Request::post();
    if (!empty($post['key'])) {
        foreach ($post['key'] as $k => $v) {
            if ($v['value'] !== "") {
                foreach ($map as $k2 => $v2) {
                    if ($v2[0] === $k) {
                        unset($map[$k2]);
                    }
                }
                switch ($v['type']) {
                    case 'eq':
                        $map[] = [$k, '=', $v['value']];
                        break;
                    case 'neq':
                        $map[] = [$k, '<>', $v['value']];
                        break;
                    case 'in':
                        $map[] = [$k, 'in', $v['value']];
                        break;
                    case 'notin':
                        $map[] = [$k, 'notin', $v['value']];
                        break;
                    case 'trim':
                        $map[] = [$k, '=', trim($v['value'])];
                        break;
                    case 'like':
                        $val = trim($v['value']);
                        $map[] = [$k, 'like', ['%' . $val, '%' . $val . '%', $val . '%'], 'OR'];
                        break;
                    case 'between':
                        $startEnd = explode(' ~ ', $v['value']);
                        $start = $startEnd[0] == '' ? 0 : strtotime($startEnd[0]);
                        $end = $startEnd[1] == '' ? time() : strtotime($startEnd[1]);
                        $map[] = [$k, '=', [$start, $end]];
                        break;
                }
            }
        }
    }
    $map = array_values($map);
}

//打印数组(调试使用)
function my_print($data, $isdump = false)
{
    echo '<meta charset="utf-8"><pre>';
    if (!$isdump) {
        print_r($data);
    } else {
        var_dump($data);
    }
    exit;
}

//二维数组按某个键名的值排序
function multiSort(&$array, $key_name, $sort_order = 'SORT_ASC', $sort_type = 'SORT_REGULAR')
{
    if (!is_array($array)) {
        return $array;
    }
    // Get args number.
    $arg_count = func_num_args();
    // Get keys to sort by and put them to SortRule array.
    $key_name_list = array();
    $sort_rule = array();
    for ($i = 1; $i < $arg_count; $i++) {
        $arg = func_get_arg($i);
        if (!preg_match('/SORT/', $arg)) {
            $key_name_list[] = $arg;
            $sort_rule[] = '$' . $arg;
        } else {
            $sort_rule[] = $arg;
        }
    }
    // Get the values according to the keys and put them to array.
    foreach ($array as $key => $info) {
        foreach ($key_name_list as $key_name) {
            ${$key_name}[$key] = $info[$key_name];
        }
    }
    // Create the eval string and eval it.
    $eval_str = 'array_multisort(' . implode(',', $sort_rule) . ', $array);';
    eval($eval_str);
    return $array;
}


//------------------------
// 字母N
//------------------------
/**
 * 将数字转化为万
 * @param $num
 * @return string
 */
function number_to_wan($num)
{
    return $num >= 10000 ? number_format(($num / 10000), 2) . '万' : $num;
}

/**
 * 数字字符串以逗号分隔
 * @param $num
 * @return array|bool|string
 */
function num_format($num)
{
    if (!is_numeric($num)) {
        return false;
    }
    $num = explode('.', $num);//把整数和小数分开
    $rl = $num[1];//小数部分的值
    $j = strlen($num[0]) % 3;//整数有多少位
    $sl = substr($num[0], 0, $j);//前面不满三位的数取出来
    $sr = substr($num[0], $j);//后面的满三位的数取出来
    $i = 0;
    $rvalue = '';
    while ($i <= strlen($sr)) {
        $rvalue = $rvalue . ',' . substr($sr, $i, 3);//三位三位取出再合并，按逗号隔开
        $i = $i + 3;
    }
    $rvalue = $sl . $rvalue;
    $rvalue = substr($rvalue, 0, strlen($rvalue) - 1);//去掉最后一个逗号
    $rvalue = explode(',', $rvalue);//分解成数组
    if ($rvalue[0] == 0) {
        array_shift($rvalue);//如果第一个元素为0，删除第一个元素
    }
    $rv = $rvalue[0];//前面不满三位的数
    for ($i = 1; $i < count($rvalue); $i++) {
        $rv = $rv . ',' . $rvalue[$i];
    }
    if (!empty($rl)) {
        $rvalue = $rv . '.' . $rl;//小数不为空，整数和小数合并
    } else {
        $rvalue = $rv;//小数为空，只有整数
    }
    return $rvalue;
}


//------------------------
// 字母O
//------------------------

//php对象转换为数组
function object_to_array($obj)
{
    $arr = array();
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val)) || is_object($val) ? object_to_array($val) : $val;
        $arr[$key] = $val;
    }
    return $arr;
}


//------------------------
// 字母P
//------------------------
//增加pv
function pv_insert($id, $tab)
{
    Db::name($tab)->where(array('id' => array('in', $id)))->setInc('pv');
}


//------------------------
// 字母Q
//------------------------
//七牛云自适应码率生成
function qn_adapt($url, $video_id)
{
    $urlArr = explode('/', $url);
    if ($urlArr[2] == 'v.sctengsen.com') {
        do_del('study_hls', array('video_id' => $video_id));
        $key = urldecode(array_pop($urlArr));

        //文件源信息
        $info = https_request($url . '?avinfo', null, true);
        $width = $info['streams'][1]['width'];
        $height = $info['streams'][1]['height'];
        $old_w = $width > 0 ? $width : 1920;
        $old_h = $height > 0 ? $height : 1080;

        //配置信息
        $qn_config = Config::get('auth.qiniu_config');
        $accessKey = $qn_config['access_key'];
        $secretKey = $qn_config['secrect_key'];
        $bucket = $qn_config['video_bucket']; //要转码的文件所在的空间名。
        $auth = new \Qiniu\Auth($accessKey, $secretKey);

        //转码是使用的队列名称。 https://portal.qiniu.com/mps/pipeline
        $pipeline = 'sctengsen_ppline';

        //转码完成后通知到你的业务服务器。
        $notifyUrl = API_URL . '/notify/qnNotify';
        $pfop = new \Qiniu\Processing\PersistentFop($auth, $bucket, $pipeline, $notifyUrl);

        //要进行转码的转码操作。 http://developer.qiniu.com/docs/v6/api/reference/fop/av/avthumb.html
        $fileArr = explode('.', $key);
        $fileName = base64_encode($fileArr[0]) . mt_rand(0, 10);

        //h5转码名称
        $h5Name = 'h5_' . $fileName;

        //h5转码前缀
        $multiPrefix1 = base64_encode("hls/" . $h5Name . '_1');
        $multiPrefix2 = base64_encode("hls/" . $h5Name . '_2');
        $multiPrefix = $multiPrefix1 . ',' . $multiPrefix2;

        //h5转码尺寸
        $bili1 = "320:" . intval(320 / ($old_w / $old_h)) . ',';
        $bili2 = "640:" . intval(640 / ($old_w / $old_h));
        $h5bl = $bili1 . $bili2;

        //h5转码命令
        $fops = "adapt/m3u8/multiResolution/" . $h5bl . "/multiPrefix/" . $multiPrefix . "/envBandWidth/200000,800000/MultiAb/200k,1000k/hlstime/60";
        $fops .= "|saveas/" . base64_encode($bucket . ":hls/" . $h5Name . '.m3u8');

        //h5转码结果
        list($id, $err) = $pfop->execute($key, $fops);
        $res = array();
        if (!$err) {
            $res[] = array(
                'video_id' => $video_id,
                'res_id' => $id,
                'type' => 1,
                'hls_time' => date('YmdH')
            );
        }

        //pc转码名称
        $pcName = 'pc_' . $fileName;

        //pc转码前缀
        $multiPrefix3 = base64_encode("hls/" . $pcName . '_1');
        $multiPrefix4 = base64_encode("hls/" . $pcName . '_2');
        $multiPrefix = $multiPrefix3 . ',' . $multiPrefix4;

        //pc转码尺寸
        $bili3 = "800:" . intval(800 / ($old_w / $old_h)) . ',';
        $bili4 = "960:" . intval(960 / ($old_w / $old_h));
        $pcbl = $bili3 . $bili4;

        //pc转码命令
        $fops = "adapt/m3u8/multiResolution/" . $pcbl . "/multiPrefix/" . $multiPrefix . "/envBandWidth/1000000,3000000/MultiAb/200k,1000k/hlstime/60";
        $fops .= "|saveas/" . base64_encode($bucket . ":hls/" . $pcName . '.m3u8');

        //pc转码结果
        list($id, $err) = $pfop->execute($key, $fops);
        if (!$err) {
            $res[] = array(
                'video_id' => $video_id,
                'res_id' => $id,
                'type' => 2,
                'hls_time' => date('YmdH')
            );
        }
        if (!empty($res)) {
            Db::name('study_hls')->addAll($res);
        }
    }
}


//------------------------
// 字母R
//------------------------

/**
 * 遍历某个文件夹下的所有文件
 * @param $dir
 * @return array
 */
function read_all_dir($dir)
{
    $result = array();
    $handle = opendir($dir);
    if ($handle) {
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                $cur_path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($cur_path)) {
                    $result['dir'][$cur_path] = read_all_dir($cur_path);
                    //$result[] = read_all_dir($cur_path);
                } else {
                    $result['file'][] = $cur_path;
                    //$result[] = $cur_path;
                }
            }
        }
        closedir($handle);
    }
    return $result;
}

//删除所有空目录
//@param String $path 目录路径
function rm_empty_dir($path)
{
    if (is_dir($path) && ($handle = opendir($path)) !== false) {
        while (($file = readdir($handle)) !== false) {// 遍历文件夹
            if ($file != '.' && $file != '..') {
                $curfile = $path . '/' . $file;// 当前目录
                if (is_dir($curfile)) {// 目录
                    rm_empty_dir($curfile);// 如果是目录则继续遍历
                    if (count(scandir($curfile)) == 2) {//目录为空,=2是因为.和..存在
                        rmdir($curfile);// 删除空目录
                    }
                }
            }
        }
        closedir($handle);
    }
}

//读日志文件到txt文件
function read_log_txt($logName = 'test_log')
{
    if (isset($_GET['clean']) && $_GET['clean'] == 1) {
        cache($logName, null);
        my_print('日志已清空！');
    }
    $logData = cache($logName);
    if (empty($logData)) {
        my_print('暂无日志内容！');
    } else {
        my_print($logData);
    }
}

/**
 * 返回结果
 * @param $message
 * @param int $error
 * @param array $data
 */
function return_msg($message, $error = 1, $data = array())
{
    ob_clean();
    $message = strtolower($message);
    if (in_array($message, array('ok', 'success'))) {
        $error = 1;
    }
    $result = array(
        'code' => $error,
        'msg' => $message,
        'result' => $data
    );
    echo json_encode($result);
    exit;
}

//返回值给接口
function res_api($msg = null, $data = null)
{
    $res = array();
    if ($msg) {
        $res['msg'] = $msg;
    }
    if ($data) {
        $res['data'] = $data;
    }
    if (isset($_REQUEST['lgz']) == 1) {
        my_print($res);
    }
    echo json_encode($res);
    exit;
}

//支付宝RSA私钥签名
function rsa_private_sign($str)
{
    $privateKey = SITE_PATH . '/lib/RSA/rsa_private_key.pem';
    $prikey = file_get_contents($privateKey);
    $pkeyid = openssl_get_privatekey($prikey);
    openssl_sign($str, $sign, $pkeyid);
    openssl_free_key($pkeyid);
    $sign = base64_encode($sign);
    return $sign;
}

//------------------------
// 字母S
//------------------------

/**
 * 将秒转换成小时：分钟：秒
 * @param $second 秒数
 * @param string $delimiter 分隔符:或者.
 * @return bool|string
 */
function second_to_hour($second, $delimiter = ':')
{
    switch ($delimiter) {
        case ':':
            $h = "";
            if ($second >= 3600) {
                $h = intval($second / 3600);
                $h = $h >= 10 ? $h . ':' : '0' . $h . ':';
                $second = $second % 3600;
            }
            $i = intval($second / 60);
            $i = $i >= 10 ? $i . ':' : '0' . $i . ':';
            $s = $second % 60;
            $s = $s >= 10 ? $s : '0' . $s;
            return $h . $i . $s;
            break;
        case '.':
            return sprintf("%.2f", $second / 3600);
            break;
        default:
            return false;
    }


}

/**
 * 文本转换
 * $str为要进行截取的字符串，$length为截取长度 汉字算一个字
 * @param $str
 * @param $length
 * @return string
 */
function str_cut($str, $length)
{
    $str = trim($str);
    $string = "";
    if (mb_strlen($str) > $length) {
        $string .= mb_substr($str, 0, $length, 'utf-8');
        $string .= "...";
        return $string;
    }
    return $str;
}

//保存操作日志
function save_logs($log, $status = 1)
{
    $user = session('admin_login');
    $data = array(
        'content' => $log,
        'create_time' => time(),
        'user_id' => $user['id'],
        'status' => $status
    );
    Db::name('sys_logs')->insert($data);
}

//设置记录上传日志的文件目录
function set_txt_dir()
{
    if (strpos($_SERVER['HTTP_HOST'], 'admin') !== false) {
        //运营端
        $login_id = session('admin_login.id');
        $logName = 'admin_' . $login_id;
    } else if (strpos($_SERVER['HTTP_HOST'], 'manage') !== false) {
        //管理端
        $login_id = session('manage_login.id');
        $logName = 'manage_' . $login_id;
    } else {
        return false;
    }
    //上传日志目录
    global $txtdir;
    $txtdir = './uploads/log';
    //新上传数据中的附件地址保存文件路径
    global $uptxt;
    $uptxt = $txtdir . '/upload_' . $logName . '.txt';
    //原始数据中的附件地址保存文件路径
    global $oldtxt;
    $oldtxt = $txtdir . '/oldtxt_' . $logName . '.txt';
}

//改变某个字段的值
function set_field_value($tb, $where, $zdName, $type = '+/-/=', $val = 1)
{
    if ($type == '+') {
        $fn = 'setInc';
    } else if ($type == '-') {
        $fn = 'setDec';
    } else if ($type == '=') {
        $fn = 'setField';
    } else {
        $fn = false;
    }
    if ($fn) {
        return Db::name($tb)->where($where)->$fn($zdName, $val);
    }
}

//读取级联菜单
function syListTree($list, $pk = 'id', $pid = 'pid', $child = 'child', $startPid = 0)
{
    $tree = array();
    if (is_array($list)) {
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            $parentId = $data[$pid];
            if ($startPid == $parentId) {
                $tree[] =& $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

//保存用户积分变化日志
function save_member_score($uid, $logText, $score, $act = '+/-/=', $type = 0, $date = '', $pid_str = '')
{
    set_field_value('member_info', array('user_id' => $uid), 'score', $act, $score);
    if ($act == '+') {
        set_field_value('member_info', array('user_id' => $uid), 'total_score', $act, $score);
    }
    $data = array(
        'id' => 0,
        'user_id' => $uid,
        'content' => $logText,
        'score' => $score,
        'act' => $act,
        'type' => $type,
        'date' => $date,
        'pid_str' => $pid_str,
        'create_time' => time()
    );
    return Db::name('member_score_log')->insert($data);
}

//设置用户头像
function set_member_headimg($img, $uid)
{
    if (preg_match('/(http:\/\/)|(https:\/\/)/i', $img) && $uid > 0) {
        $path = './uploads/web/u/' . $uid . '/';
        $headimg = get_remote_img($img, $path);
        $headimg = $headimg ? $headimg : '/static/common/img/default_head.png';
        set_field_value('member', array('id' => $uid), 'headimg', '=', $headimg);
    }
}

//------------------------
// 字母T
//------------------------
//时间处理
function time_fortmat($time, $s = 'i', $f = '-', $showRightNow = false, $hasYear = true)
{
    switch ($s) {
        case 'd':
            $str1 = $hasYear ? 'Y' . $f . 'm' . $f . 'd' : 'm' . $f . 'd';
            $str2 = null;
            break;
        case 'i':
            $str1 = $hasYear ? 'Y' . $f . 'm' . $f . 'd H:i' : 'm' . $f . 'd H:i';
            $str2 = 'H:i';
            break;
        case 'y':
            $str1 = $hasYear ? 'y' . $f . 'm' . $f . 'd H:i' : 'm' . $f . 'd H:i';
            $str2 = 'H:i';
            break;
        default :
            $str1 = 'Y' . $f . 'm' . $f . 'd H:i:s';
            $str2 = 'H:i:s';
    }
    if ($s != 'd') {
        if ($showRightNow) {
            $today = strtotime(date('Y-m-d'));
            $yesterday = strtotime(date('Y-m-d', time() - 24 * 3600));
            $threeDayAgo = strtotime(date('Y-m-d', time() - 3 * 24 * 3600));
            if ($time < 10) {
                $time = '——';
            } else if ($time < $threeDayAgo) {
                $time = date($str1, $time);
            } else if ($time < $yesterday) {
                $p_time = strtotime(date('Y-m-d', $time));
                $time = (int)(($today - $p_time) / (24 * 3600)) . '天前 ';
            } else if ($time < $today) {
                $time = '昨天 ';
            } else if (time() - $time < 60) {
                $minute = intval((time() - $time) / 10);
                $time = $minute > 0 ? $minute . '0秒钟前' : '刚刚';
            } else if (time() - $time < 3600) {
                $minute = intval((time() - $time) / 60);
                $time = $minute . '分钟前';
            } else {
                $time = '今天 ' . date($str2, $time);
            }
        } else {
            $today = strtotime(date('Y-m-d'));
            $yesterday = strtotime(date('Y-m-d', time() - 24 * 3600));
            $threeDayAgo = strtotime(date('Y-m-d', time() - 3 * 24 * 3600));
            if ($time < 10) {
                $time = '——';
            } else if ($time < $threeDayAgo) {
                $time = date($str1, $time);
            } else if ($time < $yesterday) {
                $time = (int)(($today - $time) / (24 * 3600)) . '天前 ';
            } else if ($time < $today) {
                $time = '昨天 ';
            } else {
                $time = '今天 ' . date($str2, $time);
            }
        }
    } else {
        $time = $time > 100 ? date($str1, $time) : '——';
    }
    return $time;
}

//------------------------
// 字母U
//------------------------
function up_img_water($src, $text)
{
    $path = '.' . $src;
    if (preg_match('/(.jpg)|(.png)|(.jpeg)|(.gif)/i', $path)) {
        //$img = new \Think\Image(C('IMAGE_LIB'));
        $img = \think\Image::open($path);
        $imgWidth = $img->width();
        $imgHeight = $img->height();
        if ($imgWidth >= 900) {
            $font = 16; //字体大小
            $fongt_h = 25; //文字边距
            $logo_w = 90; //logo长度
            $logo_h = 60; //logo宽途
        } else if ($imgWidth >= 800) {
            $font = 15;
            $fongt_h = 22;
            $logo_w = 80;
            $logo_h = 53;
        } else if ($imgWidth >= 700) {
            $font = 14;
            $fongt_h = 19;
            $logo_w = 70;
            $logo_h = 47;
        } else if ($imgWidth >= 600) {
            $font = 13;
            $fongt_h = 17;
            $logo_w = 60;
            $logo_h = 40;
        } else if ($imgWidth >= 500) {
            $font = 12;
            $fongt_h = 13;
            $logo_w = 50;
            $logo_h = 33;
        } else if ($imgWidth >= 400) {
            $font = 11;
            $fongt_h = 11;
            $logo_w = 40;
            $logo_h = 27;
        } else if ($imgWidth >= 300) {
            $font = 10;
            $fongt_h = 8;
            $logo_w = 30;
            $logo_h = 20;
        } else {
            $font = 8;
            $fongt_h = 6;
            $logo_w = 20;
            $logo_h = 13;
        }
        //计算文字位置
        $ttf_path = SITE_PATH . '/lib/ttfs/SourceHanSans-Regular.otf';
        $fontarea = imagettfbbox($font, 0, $ttf_path, $text);
        $fontWidth = $fontarea[2] - $fontarea[0];
        $fontHeight = $fontarea[1] - $fontarea[7];
        $font_location = array(
            $imgWidth - $fontWidth - $fongt_h,
            $imgHeight - $fontHeight - $fongt_h
        );
        //计算logo位置
        $logo_location = array(
            $imgWidth - $fontWidth - $logo_w - $fongt_h * 1.2,
            $imgHeight - $logo_h - 5
        );
        $logo_path = SITE_PATH . '/lib/ttfs/logo_' . $logo_w . '.png';
        //$savepath = './Public/test/water/'.$imgWidth.'.png';
        $savepath = $path;
        $img->open($path)->text($text . ' ', $ttf_path, $font, '#ffffff', $font_location)->save($savepath);
        $img->open($savepath)->water($logo_path, $logo_location)->save($savepath);
    }
}


/**
 * 上传图片压缩处理
 * @param string $src 图片路径
 * @param int $maxW 最大宽度
 */
//上传压缩
function up_img_compress($src, $maxW = 960, $backSize = false)
{
    $path = './' . $src;
    if (preg_match('/(.jpg)|(.png)|(.jpeg)|(.gif)/i', $path)) {
        //$img = new \Think\Image('Imagick');
        $img = \think\Image::open($path);
        /*$img = new \Think\Image();
        $img->open($path);*/
        $w = $img->width();
        if ($w > $maxW) {
            $h = $img->height();
            $img->thumb($maxW, $h, 1)->save($path);
        }
        if ($backSize) {
            $img->open($path);
            return $img->width() . 'x' . $img->height();
        }

    }
}

//获取文本中含有的上传文件地址
function up_reg_src($text)
{
    $reg = '/"(\/uploads\/[^"]*?|http:\/\/v.sctengsen.com[^"]*?|https:\/\/v.sctengsen.com[^"]*?)"/';
    preg_match_all($reg, $text, $arr);
    $back = array_unique($arr[1]);
    return $back;
}

//记录信息到文件中
function up_write_txt($file, $data)
{
    global $txtdir;
    if (!is_dir($txtdir)) {
        mkdir($txtdir, 0777, true);
    }
    $ofile = fopen($file, 'a');
    fwrite($ofile, $data);
    fclose($ofile);
}

//读取图片url
function up_read_txt($file)
{
    if (is_file($file)) {
        $url = file($file);
        foreach ($url as &$v) {
            $v = trim($v);
        }
        return $url;
    }
    return array();
}

//将文本中的地址保存到文件【编辑时】
function up_put_old_txt($str)
{
    global $txtdir;
    if (!is_dir($txtdir)) {
        mkdir($txtdir, 0777, true);
    }
    global $oldtxt;
    $text = htmlspecialchars_decode($str);
    if (!empty($text)) {
        is_file($oldtxt) && unlink($oldtxt);
        $oldData = up_reg_src($text);
        if (!empty($oldData)) {
            file_put_contents($oldtxt, join("\n", $oldData));
        }
    }
}

//删除相关的无用的附件
function up_del_nouse($noUse)
{
    $qnDelArr = array();
    foreach ($noUse as $v) {
        if (preg_match('/(http:\/\/v.sctengsen.com|https:\/\/v.sctengsen.com)/i', $v)) {
            $temp = explode('v.sctengsen.com/', $v);
            $qnDelArr[] = $temp[1];
        } else {
            //原始图片
            del_file_and_dir('.' . $v);
            //删除裁减图
            unlink_thumb_img($v);
        }
    }
    if (!empty($qnDelArr)) {
        $qn_config = Config::get('auth.qiniu_config');
        $accessKey = $qn_config['access_key'];
        $secretKey = $qn_config['secrect_key'];
        $bucket = $qn_config['video_bucket'];
        $auth = new \Qiniu\Auth($accessKey, $secretKey);
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
        $ops = $bucketManager->buildBatchDelete($bucket, $qnDelArr);
        $bucketManager->batch($ops);
    }
}

//删除数据库数据后删除附件
function unlink_file_after_del()
{
    global $oldtxt;
    $noUse = up_read_txt($oldtxt);
    if (!empty($noUse)) {
        foreach ($noUse as $v) {
            //原始图片
            del_file_and_dir('.' . $v);
            //删除裁减图
            unlink_thumb_img($v);
        }
    }
    @unlink($oldtxt);
}

/**
 * 删除裁减的图片
 * @param string $src 原图地址
 */
function unlink_thumb_img($src)
{
    $temp = explode('/', $src);
    $count = count($temp);
    if ($count >= 4) {
        $temp[1] = 'thumb';
        $temp2 = $temp[$count - 1];
        $temp3 = explode('.', $temp2);
        array_pop($temp3);
        $temp[$count - 1] = join('.', $temp3);
        $thumbDir = '.' . join('/', $temp);
        del_dir_and_file($thumbDir);
    } else {
        write_log_txt($src);
    }
}

//添加或修改保存之后删除垃圾文件
function up_save_del($id, $texts, $checkTab = array())
{
    global $uptxt;
    global $oldtxt;
    $useData = up_reg_src($texts); //当前保存数据库需要的数据
    $upData = up_read_txt($uptxt); //当前记录上传数据文件中的数据
    if ($id != 0) { //编辑时【读取原始数据，合并数据】
        if (is_file($oldtxt)) {
            $oldData = up_read_txt($oldtxt);
            $upData = array_merge($oldData, $upData);
            unlink($oldtxt);
        }
    }
    $diff = array_diff($upData, $useData); //本次内容中没有用到的数据
    if (!empty($diff)) {
        if (!empty($checkTab)) {
            foreach ($diff as $k => $v) {
                foreach ($checkTab as $val) {
                    $map = del_file_map_format($val['map'], $v);
                    if (!empty($map)) {
                        $res = get_field($val['tab'], $map, $val['field'], false, $val['field']);
                        if ($res) {
                            unset($diff[$k]);
                            break;
                        }
                    }
                }
            }
        }
        up_del_nouse($diff);
    }
    if (file_exists($uptxt)) {
        unlink($uptxt);
    }
}


//云之讯短信发送
function ucpass_send($appId, $templateId, $param, $phone, $allowTime = 100)
{
    $map1 = [
        ['date', '=', date('Ymd')],
        ['ip', '=', Request::ip(0, true)]
    ];
    $map2 = [
        ['date', '=', date('Ymd')],
        ['phone', '=', $phone]
    ];
    $count = Db::name('sys_msglog')->whereOr([$map1, $map2])->count();

    if ($count > $allowTime) {
        return '今日发送次数已超限，请明日再试！';
    }

    //初始化必填
    $options['accountsid'] = Config::get('auth.sms_ucpaas.account_sid');
    $options['token'] = Config::get('auth.sms_ucpaas.token');
    //初始化 $options必填

    $ucpass = new \ucpaas\Ucpaas($options);

    $res = json_decode($ucpass->SendSms($appId, $templateId, $param, $phone), true);

    if ($res['code'] == '000000') {
        $date = date('Ymd');
        $data = array(
            'ip' => Request::ip(0, true),
            'date' => $date,
            'phone' => $phone,
            'create_time' => date('Y-m-d H:i:s'),
            'url' => $_SERVER['HTTP_HOST'] . '/' . request()->controller() . '/' . request()->action(),
        );
        Db::name('sys_msglog')->insert($data);
        return 'ok';
    } else {
        //错误码查看地址：http://docs.ucpaas.com/doku.php?id=error_code
        return '错误码(' . $res['code'] . ')';
    }
}


//------------------------
// 字母V
//------------------------

/**版本号输出
 * @param $arr
 * @param $var
 */
function version()
{
    return APP_VERSION;
}

//生成验证码
function verify_create($yzmName = null)
{
    ob_clean();
    $config = [
        // 验证码字体大小
        'fontSize' => 30,
        // 验证码位数
        'length' => 5,
        // 验证码杂点
        'useNoise' => true,
        // 验证码字体 1-6
        'fontttf' => '4.ttf',
        // 验证码字符集合
        'codeSet' => '1234567890'
    ];
    $captcha = new Captcha($config);
    return $captcha->entry($yzmName);
}

//校验验证码
function verify_check($yzmVal, $yzmName, $isJson = false)
{
    if (!captcha_check($yzmVal, $yzmName)) {
        if ($isJson) {
            echo json_encode(array('flag' => 0, 'error' => 'yzm_error'));
        } else {
            echo 'yzm_error';
        }
        exit;
    }
}

//------------------------
// 字母W
//------------------------
/**
 * 写日志文件到txt文件
 * @param $log
 * @param string $logName
 */
function write_log_txt($log, $logName = 'test_log')
{
    $newLog = array(
        '日志时间' => date('Y-m-d H:i:s'),
        '请求地址' => $_SERVER['HTTP_HOST'] . '/' . request()->controller() . '/' . request()->action(),
        '日志内容' => $log
    );

    $logData = cache($logName);
    if (!empty($logData)) {
        array_unshift($logData, $newLog);
    } else {
        $logData = array($newLog);
    }
    if (count($logData) > 500) {
        array_pop($logData);
    }
    cache($logName, $logData);
}

function get_used_time($use_time)
{
    $used = 0;
    $count = count($use_time);
    foreach ($use_time as $key => $item) {
        if ($key < $count - 1||($key==$count-1&&$item['use_time']>0)) {
            $used += $item['use_time'];
        }else{
            $used += time()-$item['create_time'];
        }
    }
    $used = round($used/3600, 2);
    return $used;
}


?>