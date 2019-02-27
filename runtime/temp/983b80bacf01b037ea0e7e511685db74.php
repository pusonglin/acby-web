<?php /*a:2:{s:79:"D:\phpStudy\PHPTutorial\WWW\acby-web\application\admin\view\document\party.html";i:1550644760;s:76:"D:\phpStudy\PHPTutorial\WWW\acby-web\application\admin\view\public\base.html";i:1550823376;}*/ ?>
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
    
    <style>
        .new_item{
            width: 100%;
            height: 140px;
            border-bottom: 1px solid #ccc;
        }
        .li_item{
            display: flex;
            justify-content: space-around;
        }
        .li_item div{
            height: 100px;
            margin: 20px 0;
        }
        .detail{
            width:69%;
        }
        .detail p{
            line-height: 26px;
            overflow: hidden;
            text-overflow:ellipsis;
            white-space: nowrap;
            color: #666;
        }
        .cover{
            width:15%;
        }
        .cover img{
            height: 90px;
        }
        .oprate{
            width:10%;
        }
        .layui-btn-disabled{
            border: none !important;
        }
        .oprate p{
            height: 28px;
            line-height: 28px;
            text-align: center;
        }
        .oprate font{
            margin-left: 10px;
            font-size: 12px;
        }
        .oprate img{
            height: 14px;
            width:14px;
        }
        .layui-btn-normal{
            background-color: #fff !important;
            color: #096DD9!important;
        }
        .morePos{
            position: fixed;
            border-radius: 3px;
            box-shadow:  0 0 5px #666;
            width: 80px;
            top:0;left: 0;
            background: #fff;
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
            display: none;
        }
        .morePos>a{
            display: inline-block;
            width:100%;
            font-size: 14px;
            text-align: center;
            line-height: 25px;
            padding: 5px 0;
        }
        .morePos>a:hover{
            background: #f2f2f2;
        }
    </style>


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
                    <li class="layui-this">新闻资讯</li>
                    <!--<?php if($_right['delParty'] == '1'): ?>-->
                        <!--<button data-href="<?php echo url('delParty'); ?>" class="batch-btn layui-btn layui-btn-danger layui-btn-sm fr">批量删除</button>-->
                    <!--<?php endif; ?>-->
                    <?php if($_right['doParty'] == '1'): ?>
                        <button data-href="<?php echo url('doParty'); ?>" class="layer_open_iframe layui-btn layui-btn-normal layui-btn-sm fr" style="background-color: #f55b16 !important;color: #fff !important;">添加内容</button>
                    <?php endif; ?>
                </ul>

                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show">
                        <!--搜索-->
                        <form class="layui-form table-reload">
                            <div class="layui-inline">
                                <input class="layui-input reload-search" data-name="a.title" data-type="like" autocomplete="off" placeholder="标题...">
                            </div>
                            <div class="layui-inline">
                                <input class="layui-input reload-search start-end" data-name="a.create_time" data-type="between" autocomplete="off" placeholder="开始时间 ~ 截止时间" readonly>
                            </div>
                            <div class="layui-inline">
                                <select class="reload-search" data-name="a.first_cate_id" data-type="eq" placeholder="全部分类">
                                    <option value="">全部分类</option>
                                    <?php if(is_array($cateList) || $cateList instanceof \think\Collection || $cateList instanceof \think\Paginator): $i = 0; $__LIST__ = $cateList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                        <option value="<?php echo htmlentities($v['id']); ?>"><?php echo htmlentities($v['name']); ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="layui-inline">
                                <select class="reload-search" data-name="a.status" data-type="eq" placeholder="全部状态">
                                    <option value="">全部状态</option>
                                    <option value="1">已发布</option>
                                    <option value="2">未发布</option>
                                </select>
                            </div>
                            <button type="button" class="layui-btn reload-btn" data-type="reload">搜索</button>
                        </form>

                        <!--table列表-->
                        <ul id="table-list" >

                        </ul>
                    </div>
                    <div id="test1"></div>
                </div>
            </div>
        </div>
    </section>

    <!--鼠标移入显示更多-->
    <div class="morePos">

    </div>

    <script type="text/javascript">
        var doParty="<?php echo htmlentities($_right['doParty']); ?>";
        var delParty="<?php echo htmlentities($_right['delParty']); ?>";

        var page=1;
        var key=new Object();
        var tableId = 'table-list';
        var url = '/document/index';
        //初始化
        $(function(){
            initList(tableId,url,1,'');
            $('body').on('click','.layui-btn',function () {
                var curPage = $('.layui-laypage-curr>em:eq(1)').html();
                page=curPage;
                var O = $(this);
                var event=O.attr('lay-event');
                if(!$(this).hasClass('layui-btn-disabled')){
                    switch (event){
                        case 'edit':
                        case 'detail':
                            layer_open_iframe(O,true);
                            break;
                        case 'del':
                            var msg = O.attr('data-msg')?O.attr('data-msg'):'确定要删除吗？';
                            self_layer_confirm(msg,function () {
                                var load = layer.load(2);
                                var id = O.attr('data-id'),
                                    url = O.attr('data-href'),
                                    data = {id:id};
                                $.post(url,data,function(d){
                                    if(isNaN(d)){
                                        myLayer(d,2);
                                    }else{
                                        myLayer('操作成功！',1);
                                        initList(tableId,'/library/newList',curPage,key);
                                        if(O.attr('data-fresh')==1){
                                            //刷新左侧菜单
                                            parent.freshMenu(5);
                                        }
                                        //删除后执行相应方法
                                        var fn = O.attr('data-fn');
                                        if(fn){
                                            eval(fn)();
                                        }
                                    }
                                    layer.close(load);
                                });
                            });
                            break;
                       default:return false;
                    }
                }
            })

            //点击搜索重载数据
            $('body').on('click','.reload-btn',function () {
                var key = {};
                $('.reload-search').each(function () {
                    var name = $(this).attr('data-name');
                    var type = $(this).attr('data-type');
                    var value = $(this).val();
                    key[name] = {type:type,value:value}
                });
                //执行重载

                initList(tableId,url,1,key);
            });

            $('body').on('mouseover','.more',function () {
                var obj=$(this);
                $(".morePos").empty();
                var is_push=obj.attr('data-push');
                var is_hot=obj.attr('data-hot');
                var is_essence=obj.attr('data-essence');
                var status=obj.attr('data-status');
                var has_cover=obj.attr('data-cover');
                var id=obj.attr('data-id');
                if(has_cover==1){
                    if(status==1){
                        $(".morePos").append('<a href="javascript:;" data-id="'+id+'" data-key="status" data-value="2">撤回</a>');
                    }else{
                        $(".morePos").append('<a href="javascript:;" data-id="'+id+'" data-key="status" data-value="1">发布</a>');
                    }
                    if(is_push==1){
                        $(".morePos").append('<a href="javascript:;" data-id="'+id+'" data-key="is_push" data-value="2">取消推荐</a>');
                    }else{
                        $(".morePos").append('<a href="javascript:;" data-id="'+id+'" data-key="is_push" data-value="1">推荐</a>');
                    }
                    if(is_essence==1){
                        $(".morePos").append('<a href="javascript:;" data-id="'+id+'" data-timeZd="essence_time" data-key="is_essence" data-value="2">取消精品</a>');
                    }else{
                        $(".morePos").append('<a href="javascript:;" data-id="'+id+'" data-timeZd="essence_time" data-key="is_essence" data-value="1">精品</a>');
                    }
                    if(is_hot==1){
                        $(".morePos").append('<a href="javascript:;" data-id="'+id+'" data-key="is_hot" data-value="2">取消热点</a>');
                    }else{
                        $(".morePos").append('<a href="javascript:;" data-id="'+id+'" data-key="is_hot" data-value="1">热点</a>');
                    }
                }else{
                    if(status==1){
                        $(".morePos").html('<a href="javascript:;" data-id="'+id+'" data-key="status" data-value="2">撤回</a>');
                    }else{
                        $(".morePos").html('<a href="javascript:;" data-id="'+id+'" data-key="status" data-value="1">发布</a>');
                    }
                    if(is_hot==1){
                        $(".morePos").append('<a href="javascript:;" data-id="'+id+'" data-key="is_hot" data-value="2">取消热点</a>');
                    }else{
                        $(".morePos").append('<a href="javascript:;" data-id="'+id+'" data-key="is_hot" data-value="1">热点</a>');
                    }
                }
                var $top = $(this).offset().top,
                    $left= $(this).offset().left,
                    $aHeight=$(this).outerHeight(),
                    $morePos =$(this).outerWidth()/2,
                    $scroll= $(document).scrollTop();
                $(".morePos").css({'left': $left-$morePos,'top':$top+$aHeight-$scroll})
                $(".morePos").show()
            });

            $('body').on('mouseleave','.more',function () {
                $(".morePos").hide()
            });


            $('body').on('mouseover','.morePos',function () {
                $(this).show();
            });

            $('body').on('mouseleave','.morePos',function () {
                $(this).hide();
            });

            $('body').on('click','.morePos>a',function () {
                var id=$(this).attr('data-id');
                var name=$(this).attr('data-key');
                var timeZd=$(this).attr('data-timeZd');
                var value=$(this).attr('data-value');
                var data={};
                if(timeZd!=''){
                    data={id:id,zdName:name,zdVal:value,tb:'library_news',timeZd:timeZd};
                }else{
                    data={id:id,zdName:name,zdVal:value,tb:'library_news'};
                }
                $.post(hostUrl+'/index/setZdVal.html',data,function (data) {
                    if(data){
                        initList(tableId,url,page,key)
                    }
                })
            });

        });

        function initList(tableId,url,page,key){

            $('#'+tableId).empty();
            $.post(url,{page:page,limit:20,key:key},function (data) {
                if(data){
                    if(data['data']){
                        var str='';
                        $.each(data.data,function (i,v) {
                            str+='<li class="new_item"><div class="li_item"><div class="cover">';
                            str+='<img src="'+v['cover']+'"></div>';
                            str+='<div class="detail"><p style="font-size: 15px;color: #333">'+v['title']+'</p><p style="font-size: 13px">阅读数  '+v['pv']+'  收藏     '+v['collect_num']+'    点赞  '+v['zan_num']+'</p><p style="font-size: 12px">';
                            if(v['status']==1){
                                str+='<font style="color: #f5bb16">已发布</font> ';
                            }else{
                                str+='<font>未发布</font>';
                            }
                            if(v['is_banner']==1){
                                str+='<font style="color: #1278f6;margin-left: 20px">已推荐至轮播</font> ';
                            }
                            if(v['is_home']==1){
                                str+='<font style="color: #d770bc;margin-left: 20px">已推荐至首页</font> ';
                            }
                            str+='<font style="float: right">'+v['category']+'</font></p><p>'+v['create_time']+'   <font style="float: right;font-size: 12px">创建者：'+v['realname']+'</font></p></div>';
                            str+='<div class="oprate">';
                            if(doParty==1){
                                str+='<p><a lay-event="edit" data-href="<?php echo url('doParty'); ?>?id='+v['id']+'" class="layui-btn layui-btn-normal layui-btn-xs"><img src="/static/admin/images/edit.png"><font>修改</font></a></p>';
                            }
                            if(delParty==1){
                                str+='<p><a lay-event="del" data-href="<?php echo url('delParty'); ?>" data-id="'+v['id']+'" class="layui-btn layui-btn-normal layui-btn-xs"><img src="/static/admin/images/del.png"><font>删除</font></a></p>';
                            }
                            str+='<p><a data-essence="'+v['is_essence']+'" data-push="'+v['is_push']+'" data-hot="'+v['is_hot']+'" data-status="'+v['status']+'" data-del="'+delParty+'" data-cover="'+v['has_cover']+'" data-id="'+v['id']+'"   class="more layui-btn layui-btn-normal layui-btn-xs"><img src="/static/admin/images/more.png"><font>更多</font></a></p>';
                            str+='</div></div></li>';
                        });
                        $('#'+tableId).html(str);
                         var h = $('.body-box').height();
                        top.reinitIframe(h+50);
                    }else{
                        $('#'+tableId).html('<p style="width: 100%;line-height: 120px;text-align: center;font-size: 14px">暂无相关数据</p>');
                        data.count=0;
                    }
                    layui.use('laypage', function(){
                        var laypage = layui.laypage;

                        //执行一个laypage实例
                        laypage.render({
                            elem: 'test1' //注意，这里的 test1 是 ID，不用加 # 号
                            ,limit:20
                            ,curr: location.hash.replace('#!fenye=', '') //获取起始页
                            ,hash: 'fenye' //自定义hash值
                            ,count: data.count //数据总数，从服务端得到
                            ,jump: function(obj, first){
                                //首次不执行
                                if(!first){
                                    page=obj.curr;
                                    initList(tableId,url,obj.curr,key)
                                }
                            }
                        });
                    });
                }
            },'json');
        }


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