<?php /*a:2:{s:79:"D:\phpStudy\PHPTutorial\WWW\acby-web\application\admin\view\document\theme.html";i:1550482155;s:76:"D:\phpStudy\PHPTutorial\WWW\acby-web\application\admin\view\public\base.html";i:1550823376;}*/ ?>
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
                    <li class="layui-this">资讯主题列表</li>
                    <?php if($_right['delTheme'] == '1'): ?>
                        <button data-href="<?php echo url('delTheme'); ?>" class="batch-btn layui-btn layui-btn-danger layui-btn-sm fr">批量删除</button>
                    <?php endif; if($_right['doTheme'] == '1'): ?>
                        <button data-href="<?php echo url('doTheme'); ?>" class="layer_open_iframe layui-btn layui-btn-normal layui-btn-sm fr">添加主题</button>
                    <?php endif; ?>
                </ul>

                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show">
                        <!--搜索-->
                        <form class="layui-form table-reload">
                            <div class="layui-inline">
                                <input class="layui-input reload-search" data-name="name" data-type="like" autocomplete="off" placeholder="主题名称...">
                            </div>
                            <div class="layui-inline">
                                <select class="reload-search" data-name="status" data-type="eq" placeholder="全部">
                                    <option value="">全部</option>
                                    <option value="1">已发布</option>
                                    <option value="2">已删除</option>
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
        {{# if(d.disabled){ }}
        <input type="checkbox" lay-skin="switch" disabled lay-text="是|否" {{ d.status == 1 ? 'checked' : '' }} >
        {{# }else{ }}
        <input type="checkbox" name="status" value="{{d.id}}" data-true="1" data-false="2" data-tb="activity" lay-skin="switch" lay-text="是|否" lay-filter="setZdVal" {{ d.status == 1 ? 'checked' : '' }}>
        {{# } }}
    </script>

    <!--工具栏-->
    <script type="text/html" id="toolbar">
        <?php if($_right['doTheme'] == '1'): ?>
            <a lay-event="edit" data-href="<?php echo url('doTheme'); ?>?id={{d.id}}" class="layui-btn layui-btn-normal layui-btn-xs">编辑</a>
        <?php endif; if($_right['delTheme'] == '1'): ?>
            <a lay-event="del" data-href="<?php echo url('delTheme'); ?>" data-id="{{d.id}}" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>
        <?php endif; if($_right['doTheme'] != 1 AND $_right['delTheme'] != 1): ?>
            暂无操作权限！
        <?php endif; ?>
    </script>

    <script type="text/javascript">
        var tableId = 'table-list';
        var url = '/document/theme';
        var cols = [[ //表头
            {type:'checkbox',width:38},
            {field: 'id', title: 'ID',width:210},
            {field: 'name', title: '活动名称',width:210},
            {field: 'create_time', title: '报名时间',},
            {fixed: 'right', title: '操作管理',width:209, align:'center', templet: '#toolbar'}
        ]];

        //初始化
        initTable(tableId,url,cols);
    </script>

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