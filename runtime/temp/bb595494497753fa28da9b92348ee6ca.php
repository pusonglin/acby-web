<?php /*a:2:{s:74:"D:\phpStudy\PHPTutorial\WWW\acby-web\application\admin\view\rbac\role.html";i:1550457504;s:76:"D:\phpStudy\PHPTutorial\WWW\acby-web\application\admin\view\public\base.html";i:1550823376;}*/ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>乡村振兴</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="/static/common/font-awesome-4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="/static/common/mCustomScrollbar/jquery.mCustomScrollbar.css" />
    <link rel="stylesheet" type="text/css" href="/static/common/layui/css/layui.css" />
    <link rel="stylesheet" type="text/css" href="/static/admin/css/style.css" />
    
    <link rel="icon" href="/static/common/img/favicon.ico" type="image/x-icon"/>
    <script type="text/javascript" src="/static/common/js/jquery-1.12.2.min.js"></script>
    <script type="text/javascript" src="/static/common/js/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="/static/common/js/jquery.pseudo.js"></script><!--before/after-->
    <script type="text/javascript" src="/static/common/mCustomScrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="/static/common/layui/layui.all.js"></script>
    <script type="text/javascript" src="/static/common/js/dotdotdot.js"></script>
    <script type="text/javascript" src="/static/common/js/common.js"></script>
    <script type="text/javascript" src="/static/admin/js/common.js"></script>
    <!--[if lt IE 9]>
    <script type="text/javascript" src="/static/common/html5.js"></script>
    <script type="text/javascript" src="/static/common/css3-mediaqueries.js"></script>
    <![endif]-->
    
</head>
<body>
    

    <div class="body-box">
        
    <section class="rt_wrap content">
        <div class="rt_content">
            <div class="layui-tab">
                <ul class="layui-tab-title">
                    <li class="layui-this">角色管理</li>
                    <?php if($_right['delRole'] == '1'): ?>
                        <button data-href="<?php echo url('delRole'); ?>" class="batch-btn layui-btn layui-btn-danger layui-btn-sm fr">批量删除</button>
                    <?php endif; if($_right['doRole'] == '1'): ?>
                        <button data-href="<?php echo url('doRole'); ?>" class="layer_open_iframe layui-btn layui-btn-normal layui-btn-sm fr">添加角色</button>
                    <?php endif; ?>
                </ul>

                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show">
                        <!--搜索-->
                        <form class="layui-form table-reload">
                            <div class="layui-inline">
                                <input class="layui-input reload-search" data-name="a.name" data-type="like" autocomplete="off" placeholder="角色名称...">
                            </div>
                            <div class="layui-inline">
                                <select class="reload-search" data-name="a.status" data-type="eq" placeholder="全部">
                                    <option value="">全部</option>
                                    <option value="1">启用</option>
                                    <option value="2">停用</option>
                                </select>
                            </div>
                            <button type="button" class="layui-btn reload-btn" data-type="reload">搜索</button>
                        </form>

                        <!--table列表-->
                        <table id="table-list" lay-filter="table-list"></table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--状态-->
    <script type="text/html" id="tpl_status">
        <input type="checkbox" name="status" value="{{d.id}}" data-true="1" data-false="2" data-tb="role" lay-skin="switch" lay-text="是|否" lay-filter="setZdVal" {{ d.status == 1 ? 'checked' : '' }}>
    </script>

    <!--工具栏-->
    <script type="text/html" id="toolbar">
        <?php if($_right['setRight'] == '1'): ?>
            <a lay-event="detail" data-href="<?php echo url('setRight'); ?>?id={{d.id}}&name={{d.name}}" class="layui-btn layui-btn-primary layui-btn-xs">配置</a>
        <?php endif; if($_right['doRole'] == '1'): ?>
            <a lay-event="edit" data-href="<?php echo url('doRole'); ?>?id={{d.id}}" class="layui-btn layui-btn-normal layui-btn-xs">编辑</a>
        <?php endif; if($_right['delRole'] == '1'): ?>
            <a lay-event="del" data-href="<?php echo url('delRole'); ?>" data-id="{{d.id}}" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>
        <?php endif; if($_right['setRight'] != 1 AND $_right['doRole'] != 1 AND $_right['delRole'] != 1): ?>
            暂无权限
        <?php endif; ?>
    </script>

    <script type="text/javascript">
        var tableId = 'table-list';
        var url = '/rbac/role';
        var cols = [[ //表头
            {type:'checkbox',width:38},
            {field: 'id', title: 'ID',width:79},
            {field: 'name', title: '角色名称'},
            {field: 'remark', title: '角色描述',width:357},
            {field: 'status', title: '是否启用',width:79, templet: '#tpl_status', unresize: true},
            {fixed: 'right', title: '操作管理',width:169, align:'center', templet: '#toolbar'}
        ]];

        //初始化
        initTable(tableId,url,cols);
    </script>


    <!--<section class="rt_wrap_head content">
        <div class="rt_content">
            <div class="page_title">
                <h2 class="fl">权限管理 > 角色管理</h2>
                <a href="javascript:;" data-url="<?php echo url('delRole'); ?>" class="fr top_rt_btn del_icon btn-delBatch <?php if($_right['delRole'] != '1'): ?>btn-disable<?php endif; ?>">批量删除</a>
                <a data-href="<?php echo url('doRole'); ?>" class="fr layer_open_iframe top_rt_btn add_icon margin-right-10 <?php if($_right['doRole'] != '1'): ?>btn-disable<?php endif; ?>">添加角色</a>
            </div>
            <table class="table list-table">
                <?php if(!(empty($list) || (($list instanceof \think\Collection || $list instanceof \think\Paginator ) && $list->isEmpty()))): ?>
                    <tr>
                        <th class="bfb5">
                            <label>
                                <input type="checkbox" id='checkAll' title="全选">
                                <span class="check_all"></span>
                            </label>
                        </th>
                        <th class="bfb5">序号</th>
                        <th class="bfb15">角色名称</th>
                        <th class="bfb45">角色描述</th>
                        <th class="bfb10">角色状态</th>
                        <th class="bfb20">操作管理</th>
                    </tr>
                    <?php else: ?>
                    <tr><th class="center bfb100">没有数据！</th></tr>
                <?php endif; ?>
            </table>
        </div>
    </section>

    <?php if(!(empty($list) || (($list instanceof \think\Collection || $list instanceof \think\Paginator ) && $list->isEmpty()))): ?>
        <section class="rt_wrap content">
            <div class="rt_content">
                <table class="table list-table">
                    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                        <tr class="del-remove-<?php echo htmlentities($v['id']); ?>">
                            <td class="center bfb5">
                                <label>
                                    <input type="checkbox" value="<?php echo htmlentities($v['id']); ?>" class="checkIds">
                                    <span class="check_list"></span>
                                </label>
                            </td>
                            <td class="center bfb5"><?php echo htmlentities($i); ?></td>
                            <td class="center bfb15"><?php echo htmlentities($v['name']); ?></td>
                            <td class="center bfb45 ellipsis"><?php echo htmlentities($v['remark']); ?></td>
                            <td class="center bfb10 set_zd_val" data-id="<?php echo htmlentities($v['id']); ?>" data-ask1="确定要设为关闭？" data-ask2="确定要设为开启？" data-title1="开启" data-title2="关闭" data-tb="role" data-zdName="status">
                                <?php if($v['status'] == '1'): ?>
                                    <a title="开启" class="link_icon set_val_btn" data-val="1">&#89;</a>
                                    <?php else: ?>
                                    <a title="关闭" class="link_icon set_val_btn" data-val="2">&#88;</a>
                                <?php endif; ?>
                            </td>
                            <td class="center bfb20">
                                <a title="编辑" data-href="<?php echo url('doRole?id='.$v['id'].'&p='.$p); ?>" class="layer_open_iframe margin-right-10 <?php if($_right['doRole'] != '1'): ?>btn-disable<?php endif; ?>">编辑</a>
                                <a title="配置权限" data-href="<?php echo htmlentities(ADMIN_URL); ?>/rbac/setRight?id=<?php echo htmlentities($v['id']); ?>&p=<?php echo htmlentities($p); ?>&name=<?php echo htmlentities($v['name']); ?>" class="layer_open_iframe margin-right-10 <?php if($_right['setRight'] != '1'): ?>btn-disable<?php endif; ?>">配置权限</a>
                                <a title="删除" href="javascript:void(0)" data-url="<?php echo url('delRole'); ?>" data-id="<?php echo htmlentities($v['id']); ?>" class="btn-del <?php if($_right['delRole'] != '1'): ?>btn-disable<?php endif; ?>">删除</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </table>
                <aside class="paging">
                    <?php echo htmlentities($page); ?>
                </aside>
            </div>
        </section>
    <?php endif; ?>-->

    </div>

    

    

    <DIV class=browser-updator style='HEIGHT: 45px; _top: 924px;clear:both' _ks_data_1427189382133='71'>
        <DIV class=browser-updator-wrapper>
            <P>
                <SPAN>您好，您的浏览器版本过低导致部分功能不能使用，为了方便您的操作，360浏览器请切换至“极速”模式，IE浏览器请升级浏览器：</SPAN>
                <SPAN>点击下载</SPAN>
                <A class='browser-updator-browser browser-updator-ie' href='http://rj.baidu.com/soft/detail/23360.html?ald' target=_blank data-spm-anchor-id='1.7274553.0.0'>升级IE浏览器</A>
            </P>
            <A class=browser-updator-close href='javascript:void(0);'>关闭</A>
        </DIV>
    </DIV>
</body>
</html>