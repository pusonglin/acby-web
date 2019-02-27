<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

return [
    //路径常量
    'tpl_replace_string'  =>  [
        '__COMMON__' => '/static/common',
        '__IMG__'    => '/static/'.request()->module().'/images',
        '__CSS__'    => '/static/'.request()->module().'/css',
        '__JS__'     => '/static/'.request()->module().'/js',
    ],
    
    //不用校验权限的控制器
    'pass_controller'   => array(
        'Index',
        'User',
        'Plugin'
    ),

    //不用校验权限的方法
    'pass_action'       =>  array(
        'setZdVal',
        'partySet'
    ),
];
