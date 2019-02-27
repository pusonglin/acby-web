var topNavFreshId = 0;
$(function(){
    var BODY = $('body');
    var hideClickA = $('#hide-click-a');

    //初始化左侧导航栏高度
    initNavHeight();

    //当前菜单样式
    BODY.on('click','.nav_ul a',function(){
        $('.nav_ul a').removeClass('active');
        $(this).addClass('active');
    });

    //展开&隐藏导航子菜单[展开状态时]
    BODY.on('click','.nav_ul dt',function(){
        var O = $(this);
        var dts = $('.nav_ul dt:not(.no_child)');
        var dds = $('.nav_ul dd');
        if(O.hasClass('no_child')){
            dds.hide(500);
            O.parent('dl').children('dd').show(500);
            dts.addClass('hidden');
            dts.attr('data-flag',0);
            O.removeClass('hidden');
            $('.show').removeClass('show');
            dts.removeClass('cur');
            var href = O.attr('data-href');
            hideClickA.attr('href',href);
            hideClickA[0].click();
        }else{
            var flag = O.attr('data-flag');
            if(flag!=1){ //当前隐藏->展开
                O.parent('dl').children('dd').show(500);
                O.removeClass('hidden');
                O.attr('data-flag',1);
            }else{
                O.parent('dl').children('dd').hide(500);
                O.attr('data-flag',0);
            }
            $('.show').removeClass('show');
            dts.removeClass('cur');

            if(O.hasClass('hidden')){
                O.addClass('cur');
            }
        }
        setTimeout(function () {
            reinitIframe();
        },500);
    });


    //快捷菜单点击跳转
    BODY.on('click','.click-jump-btn',function(){
        var topNavId = $(this).attr('data-topnav-id');
        var freshId = $(this).attr('data-fresh-id');
        if(topNavId>0){
            var curTopNavId = $('.nav-active').attr('data-id');
            if(curTopNavId==topNavId){
                //一级导航就是当前展示的一级导航
                var hideClickA = $('#hide-click-a');
                var $click = $('.id_'+freshId);
                $click.click();
                if(!$click.hasClass('no_child')){
                    var href = $click.attr('href');
                    if(hideClickA.attr('href')!=href){
                        hideClickA.attr('href',href);
                        hideClickA[0].click();
                    }
                    var pid = $click.attr('data-pid');
                    if($('.id_'+pid).attr('data-flag')==0){
                        $('.id_'+pid).click();
                    }
                }
                $('#iframe').css("z-index", 0);
                reinitIframe();
            }else{
                $('.header-nav li').each(function () {
                    var id = $(this).children('a').attr('data-id');
                    if(id==topNavId){
                        topNavFreshId = $(this).children('a').attr('data-fresh_id');
                        $(this).children('a').attr('data-fresh_id',freshId);
                        $(this).children('a').click();
                    }
                });
            }
        }
    });

    //顶部导航栏点击效果
    BODY.on('click','.header-nav > li > a',function () {
        if($(this).hasClass('nav-active')){
            return false;
        }
        $('.nav-active').removeClass('nav-active');
        $(this).addClass('nav-active');
        if($(this).attr('data-id')>0){
            if($('#overview').is(':visible')){
                $('#overview').hide();
                $('.body-box').show();
            }
        }else{
            if($('.body-box').is(':visible')){
                $('.body-box').hide();
                $('#overview').show();
                return false;
            }
        }
        var freshId = $(this).attr("data-fresh_id");
        var title=$(this).html();
        if(title=='主页'){
            $('.lt_aside_nav').css('display','none');
            $('#iframe').css('width','100%');
        }else{
            $('.lt_aside_nav').css('display','block');
            $('#iframe').css('width','calc(100% - 225px)');
        }
        if(freshId==0){
            $('#iframe').attr('src',hostUrl+'/error.html');
        }
        if(topNavFreshId>0){
            $(this).attr('data-fresh_id',topNavFreshId);
            topNavFreshId = 0;
        }
        if($(this).attr('data-id')!=$('.nav_ul').attr('data-top-pid') || freshId!=$('.nav_ul').attr('data-fresh_id')){
            freshMenu(freshId);
        }
    });

    //初始化
    $('.header-nav > li:eq(0) >a').click();

    //header右上角用户头像 鼠标移上显示
    $(".headerDiv").hover(function () {
        $(".selectMean").stop().fadeIn();
    },function () {
        $(".selectMean").stop().fadeOut();
    });
    $(".selectMean").hover(function () {
        $(this).stop().fadeIn();
    },function () {
        $(this).stop().fadeOut();
    });

    //窗口发生变化
    $(window).resize(function () {
        initNavHeight();
    });
});

//改变左侧导航栏的高度
function initNavHeight() {
    var winHeight = $(window).height();
    $(".nav_box").height(winHeight - 130);
}

//刷新菜单
function freshMenu(active_id) {
    var top_pid = $('.nav-active').attr('data-id');
    if(!(top_pid>0)){
        return false;
    }
    $('.nav_ul').html('<li class="nodata center"><img src="/static/common/img/loading.gif" /> 加载中</li>');
    var URL = hostUrl;
    $.post(URL+'/index/getRightList',{top_pid:top_pid},function(data){
        $('.nav_ul').remove(); //需要重新初始化滚动条
        $(".clickIcon").attr('data-flag',1);
        var str = '';
        if(data.length>0){
            str += '<ul class="nav_ul" data-top-pid="'+top_pid+'" data-fresh_id="'+active_id+'">';
            $.each(data,function(j,jtem){
                str += '';
                str += '<li>';
                str += '    <dl>';
                if(jtem['_child'].length>0){
                    str += ' <dt class="hidden dtBefor id_'+jtem['id']+'" data-id="'+jtem["id"]+'"><i class="fa '+jtem['icon']+'"></i><em>'+jtem['name']+'</em></dt>';
                    $.each(jtem['_child'],function(k,ktem){
                        str += '<dd><a href="'+URL+'/'+jtem['url']+'/'+ktem['url']+'.html"  class="id_'+ktem['id']+'"  data-name="'+ktem['name']+'" data-id="'+ktem['id']+'" data-pid="'+jtem['id']+'" target="iframe">'+ktem['name']+'</a></dd>';
                    })
                }else{
                    str += '  <dt class="no_child dtBefor id_'+jtem['id']+'" data-href="'+URL+'/'+jtem['url']+'"><i class="fa '+jtem['icon']+'"></i><em>'+jtem['name']+'</em></dt>';
                }
                str += '    </dl>';
                str += '</li>';
            });
            str += '</ul>';
            $('.nav_box').append(str);

            if(active_id>0){
                var hideClickA = $('#hide-click-a');
                var $click = $('.id_'+active_id);
                $click.click();
                if(!$click.hasClass('no_child')){
                    var href = $click.attr('href');
                    hideClickA.attr('href',href);
                    hideClickA[0].click();
                    var pid = $click.attr('data-pid');
                    $('.id_'+pid).click();
                }
                $('#iframe').css("z-index", 0);
            }
        }else{
            str += '<ul class="nav_ul"><li class="nodata center">暂无权限！</li></ul>';
            $('.nav_box').append(str);
        }
        reinitIframe();
    },'json');
}


//改变窗口高度
function reinitIframe(layerH){
    var leftNavH = $('.lt_aside_nav>.nav_box').height();
    if(!(layerH>0)){
        var iframeBodyH = $('#iframe').contents().find('.body-box').height();
    }else{
        iframeBodyH = layerH;
    }
    var realityH = leftNavH>iframeBodyH?leftNavH:iframeBodyH;
    var windH = $(window).height() - 200;
    realityH = realityH>windH?realityH:windH;
    var iframe = document.getElementById("iframe");
    iframe.height = realityH;
    $('.lt_aside_nav').css({'height':realityH});
    if(!layerH){
        $('html,body').animate({scrollTop:0},1);
    }
}


