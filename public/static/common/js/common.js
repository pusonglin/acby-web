var hostUrl = document.location.protocol.toLowerCase()+'//'+window.location.host;
var userAgent = navigator.userAgent.toLowerCase();   //将获得的设备数据转换为小写字母
var is_app = !!userAgent.match(/pandapia_app/i);
var is_android = !!userAgent.match(/android/i); //android终端
var is_ios = !!userAgent.match(/iphone/i); //ios终端

//****************************************************加载完成后执行******************************************************
$(function () {
    var BODY = $('body');

    //加载附文本内容
    $(window).scroll(function(){
        setIframeContent();
    });

    //多行显示省略号
    dotellipsis();

    //自动转换中文逗号为英文逗号
    BODY.on('input propertychange,change','.cnToEn',function () {
        var str = $(this).val();
        str = str.replace(/，/g,',');
        $(this).val(str);
    });

    //非负数字提示
    BODY.on('blur','.floatNum',function(){
        var val = $(this).val();
        if((parseFloat(val)!=val || parseFloat(val)<0) && val.length>0){
            myLayer('该值只能为非负数字！');
            $(this).focus();
        }
    });

    //非负整数提示
    BODY.on('blur','.intNum',function(){
        var val = $(this).val();
        if((parseInt(val)!=val || parseInt(val)<0) && val.length>0 ){
            myLayer('该值只能为非负整数！');
            $(this).focus();
        }
    });

    //下载服务器远程图片
    auto_get_upload_img();
});

//******************************************************公共方法*********************************************************
//弹窗提示
function myLayer(msg,msgType,time) {
    if(is_app){
        var func;
        if(msgType==1){
            func = 'success';
        }else if(msgType==2){
            func = 'error';
        }else{
            func = 'tips';
        }
        if(is_android){
            window.NativeApp.postMessage('{"func": "'+func+'","message": "'+msg+'"}');
        }else if(is_ios){
            window.webkit.messageHandlers.NativeApp.postMessage('{"func": "'+func+'","message": "'+msg+'"}');
        }else{
            alert(msg);
        }
    }else{
        msgType = msgType?msgType:0;
        if(typeof(layer.msg)=='function'){
            layer.msg(msg, {icon: msgType});
        }else if(typeof(layer.open)=='function'){
            time = time>1.5?time:1.5;
            layer.open({
                content:msg,
                shadeClose: false,
                time : time
            });
        }else{
            alert(msg);
        }
    }
    return false;
}

//消息提示
function layerNotice(notice){
    layer.open({
        shadeClose:false,
        content: notice,
        btn: '我知道了'
    });
}

//多行显示省略号
function dotellipsis() {
    var dotEllipsis = $('.dot-ellipsis');
    if(typeof(dotEllipsis.dotdotdot) == 'function'){
        dotEllipsis.dotdotdot();

        dotEllipsis.hover(function(){
            var isTruncated = $(this).triggerHandler("isTruncated");
            if ( isTruncated ) {
                $(this).attr('title',$(this).attr('data-text'));
            }
        });
    }
}

//图片居中显示
function img_center(obj,bfb) {
    var O = $(obj).parents('.img_center');
    var frameW = O.width();
    if(bfb>0){
        var h = Math.round(frameW*bfb);
        O.css({'height':h});
    }
    var frameH = O.height();
    var img = $(obj);
    var imgW = img.width();
    var imgH = img.height();
    //alert('frameW:'+frameW+',frameH:'+frameH+',imgW:'+imgW+',imgH:'+imgH);
    var margin_top = 0;
    var margin_left = 0;
    if(imgW>=frameW){
        if(imgH>=frameH){
            var minusW = imgW-frameW;
            var minusH = imgH-frameH;
            if(minusW>=minusH){
                margin_left = parseInt((frameW-((frameH/imgH)*imgW))/2).toFixed(2);
                img.css({'height':frameH+'px','marginLeft':margin_left+'px'});
            }else{
                margin_top = parseInt((frameH-((frameW/imgW)*imgH))/2).toFixed(2);
                img.css({'width':frameW+'px','marginTop':margin_top+'px'});
            }
        }else{
            margin_top = parseFloat((frameH-imgH)/2).toFixed(2);
            margin_left = parseFloat((frameW-imgW)/2).toFixed(2);
            img.css({'marginTop':margin_top+'px','marginLeft':margin_left+'px'});
        }
    }else{
        if(imgH>=frameH){
            margin_top = parseFloat((frameH-imgH)/2).toFixed(2);
            margin_left = parseFloat((frameW-imgW)/2).toFixed(2);
            img.css({'marginTop':margin_top+'px','marginLeft':margin_left+'px'});
        }else{
            margin_top = parseFloat((frameH-imgH)/2).toFixed(2);
            margin_left = parseFloat((frameW-imgW)/2).toFixed(2);
            img.css({'marginTop':margin_top+'px','marginLeft':margin_left+'px'});
        }
    }
}

//移动端图片居中处理
function img_center_after_load(isbfb) {
    $('.img_center').each(function () {
        var O = $(this);
        var frameW = O.width();
        if(isbfb){
            var bfb = +O.attr('data-bfb');
            var h = frameW*bfb;
            O.css({'height':h});
        }
        var frameH = O.height();
        //console.log(frameW+'|'+frameH);
        var img = O.find('img');
        var imgW = img.width();
        var imgH = img.height();
        //console.log('frameW:'+frameW+',frameH:'+frameH+',imgW:'+imgW+',imgH:'+imgH);
        var margin_top = 0;
        var margin_left = 0;
        if(imgW>=frameW){
            if(imgH>=frameH){
                var minusW = imgW-frameW;
                var minusH = imgH-frameH;
                if(minusW>=minusH){
                    margin_left = parseInt((frameW-((frameH/imgH)*imgW))/2).toFixed(2);
                    img.css({'height':frameH+'px','marginLeft':margin_left+'px'});
                }else{
                    margin_top = parseInt((frameH-((frameW/imgW)*imgH))/2).toFixed(2);
                    img.css({'width':frameW+'px','marginTop':margin_top+'px'});
                }
            }else{
                margin_top = parseFloat((frameH-imgH)/2).toFixed(2);
                margin_left = parseFloat((frameW-imgW)/2).toFixed(2);
                img.css({'marginTop':margin_top+'px','marginLeft':margin_left+'px'});
            }
        }else{
            if(imgH>=frameH){
                margin_top = parseFloat((frameH-imgH)/2).toFixed(2);
                margin_left = parseFloat((frameW-imgW)/2).toFixed(2);
                img.css({'marginTop':margin_top+'px','marginLeft':margin_left+'px'});
            }else{
                margin_top = parseFloat((frameH-imgH)/2).toFixed(2);
                margin_left = parseFloat((frameW-imgW)/2).toFixed(2);
                img.css({'marginTop':margin_top+'px','marginLeft':margin_left+'px'});
            }
        }
        $(this).removeClass('img_center');
    });
}

//判断设备类型
function browserRedirectIsPhone() {
    var sUserAgent = navigator.userAgent.toLowerCase();
    var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";
    var bIsIphoneOs = sUserAgent.match(/iphone os/i) == "iphone os";
    var bIsMidp = sUserAgent.match(/midp/i) == "midp";
    var bIsUc7 = sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4";
    var bIsUc = sUserAgent.match(/ucweb/i) == "ucweb";
    var bIsAndroid = sUserAgent.match(/android/i) == "android";
    var bIsCE = sUserAgent.match(/windows ce/i) == "windows ce";
    var bIsWM = sUserAgent.match(/windows mobile/i) == "windows mobile";
    if (bIsIpad || bIsIphoneOs || bIsMidp || bIsUc7 || bIsUc || bIsAndroid || bIsCE || bIsWM) {
        return true;
    } else {
        return false;
    }
}

//后台填写的附文本内容显示到iframe
/*function setIframeContent(){
    setTimeout(function(){
        $('.box_iframe').each(function(){
            var o = $(this);
            var curID = this.id;
            var iframeStr = o.prev('.iframe_str').html();
            if(iframeStr){
                var prent = o.parent('div');
                var PW = prent.width();
                o.css({'width':PW});
                var strBox = o.contents().find('#strbox');
                strBox.css({'width':PW});
                strBox.html(iframeStr);
            }

            //改变iframe高度
            var curH = $(document.getElementById(curID).contentWindow.document).height();
            o.css({'height':curH});
        });
    },200);
}*/

//后台填写的附文本内容显示到iframe
function setIframeContent(cut_h){
    setTimeout(function(){
        if(cut_h>0){
            var window_h = $(window).height();
            $('.box_iframe').each(function(){
                var o = $(this);
                var iframeStr = o.prev('.iframe_str').html();
                if(iframeStr){
                    var prent = o.parent('div');
                    var PW = prent.width();
                    o.css({'width':PW});
                    var strBox = o.contents().find('#strbox');
                    strBox.css({'width':PW,'height':(window_h-cut_h)+'px'});
                    strBox.html(iframeStr);
                }

                //改变iframe高度
                o.css({'height':(window_h-cut_h)+'px'});
            });
        }else{
            $('.box_iframe').each(function(){
                var o = $(this);
                var curID = this.id;
                var iframeStr = o.prev('.iframe_str').html();
                if(iframeStr){
                    var prent = o.parent('div');
                    var PW = prent.width();
                    o.css({'width':PW});
                    var strBox = o.contents().find('#strbox');
                    strBox.css({'width':PW});
                    strBox.html(iframeStr);
                }

                //改变iframe高度
                var curH = $(document.getElementById(curID).contentWindow.document).height();
                o.css({'height':curH});
            });
        }
    },200);
}

//检验是否为空
function checkNull(flag,txts,notice,minLen,maxLen){
    if(flag){
        var val = txts.val();
        val = delKB(val);
        var len = val.length;
        if(len<minLen){
            myLayer(notice);
            txts.focus();
            flag = false;
        }else if(len>maxLen){
            myLayer('您填写的内容过长，不能超过'+maxLen+'个字符！');
            txts.focus();
            flag = false;
        }else{
            if(txts.hasClass('floatNum')){
                if(isNaN(val) || (val<0)){
                    myLayer('该值只能为非负数字！');
                    txts.focus();
                    flag = false;
                }
            }
            if(txts.hasClass('intNum')){
                if(parseInt(val)!=val){
                    myLayer('该值只能为非负整数！');
                    txts.focus();
                    flag = false;
                }
            }
        }
        return flag;
    }
}


//检测是否选中下拉菜单
function checkSelect(flag,txts,notice,num){
    if(flag){
        var curnum = txts.val();
        if(curnum==num){
            myLayer(notice);
            flag = false;
        }
        return flag;
    }
}

//删除空白字符
function delKB(str){
    if(str){
        str = str.replace(/\s/gi,'');
        return str.replace(/\s/gi,'　');
    }else{
        return '';
    }
}

//post传值
function postAjax(O,czurl,data,notice,tzurl,back){
    var able = O.attr('able');
    if(able!=0){
        O.attr('able',0);
        if(back){
            $.post(czurl,data,function(d){
                if(isNaN(d)){
                    myLayer(d,2);
                    O.attr('able',1);
                }else{
                    myLayer(notice,1);
                    setTimeout("location.href ='"+ tzurl+"'",1500);
                    if(O.attr('data-fresh')==1){
                        //刷新左侧菜单
                        parent.freshMenu(5);
                    }
                }
            });
        }else{
            $.post(czurl,data);
            O.attr('able',1);
        }
    }
}

//判断返回的时候是否需要重新加载
function checkReload() {
    var BODY = $('body');
    var flag = BODY.attr('data-flag');
    var name = BODY.attr('data-name');
    if(flag==1){
        $.cookie(name,null,{ path: '/' });
    }else{
        var val = $.cookie(name);
        if(val==1){
            var act = BODY.attr('data-act');
            if(act==1){
                $.cookie(name,null,{ path: '/' });
            }
            var url = BODY.attr('data-url');
            if(url){
                location.href=url;
            }else{
                location.reload();
            }
        }
    }
}

//删除上传的头像
function clearAvater(){
    $('.avatar-form').get(0).reset();
    $('.avatar-wrapper').children('div').remove();
}

//下拉框模糊匹配
function editable_select() {
    jQuery.getScript("/static/common/editable.select/jquery.editable-select.min.js",function () {
        $('.editable_select').each(function () {
            var num = $('.editable_select').index($(this));
            var select_id = $(this).attr('id');
            var value_id = $(this).attr('data-id');
            $('#'+select_id).editableSelect({
                effects: 'slide',
                onShow:function () {
                    $('#'+value_id).val('-1');
                },
                onHide:function () {
                    var val = $('#'+select_id).val();
                    $('.es-list:eq('+num+')>li').each(function () {
                        if(val == $(this).html()){
                            $('#'+value_id).val($(this).attr('value'));
                        }
                    })
                }
            });
        })
    });
}

//复制链接
function zclip_copy() {
    jQuery.getScript("/static/common/jqueryzclip/jquery.zclip.js",function () {
        var tempStr;
        $(".zclip_copy").zclip({
            path: "/static/common/jqueryzclip/ZeroClipboard.swf",
            copy: function(){
                tempStr=$(this).attr('data-link');
                return tempStr;
            },
            afterCopy:function(){
                myLayer('URL地址复制成功！',1);
            }
        });
    });
}

//下载图片
function auto_get_upload_img() {
    //var url = 'http://panda2.com/fsockopen/getImages';
    $('img').on('error',function () {
        var O = $(this);
        O.attr('src','/static/common/img/default.png');
        /*var src = O.attr('src');
        var reg=/^[\/upload\/]/gi;
        if(src.match(reg)){
            $.ajax({
                type:'post',
                async:false,
                url:url,
                data:{url:src,jsonp:1},
                dataType:'jsonp',
                jsonp:'callback',
                success:function (d) {
                    O.attr('src',d);
                },
                error: function(){
                    console.log('非法请求');
                }
            })
        }*/
    });
}

//动态加载css、js文件
var Head = document.getElementsByTagName('head')[0],style = document.createElement('style');

//文件全部加载完成显示DOM
function linkScriptDOMLoaded(parm){
    style.innerHTML = 'body{display:none}';//动态加载文件造成样式表渲染变慢，为了防止DOM结构在样式表渲染完成前显示造成抖动，先隐藏body，样式表读完再显示
    Head.insertBefore(style,Head.firstChild)
    var linkScript, linckScriptCount = parm.length, currentIndex = 0;
    for ( var i = 0 ; i < parm.length; i++ ){
        if(/\.css[^\.]*$/.test(parm[i])) {
            linkScript = document.createElement("link");
            linkScript.type = "text/" + ("css");
            linkScript.rel = "stylesheet";
            linkScript.href = parm[i];
        } else {
            linkScript = document.createElement("script");
            linkScript.type = "text/" + ("javascript");
            linkScript.src = parm[i];
        }
        Head.insertBefore(linkScript, Head.lastChild);
        linkScript.onload = linkScript.onerror = function(){
            currentIndex++;
            if(linckScriptCount == currentIndex){
                style.innerHTML = 'body{display:block}';
                Head.insertBefore(style,Head.lastChild)
            }
        }
    }
}


//异步加载css,js文件
function linkScript(parm, fn) {
    var linkScript;
    if(/\.css[^\.]*$/.test(parm)) {
        linkScript = document.createElement("link");
        linkScript.type = "text/" + ("css");
        linkScript.rel = "stylesheet";
        linkScript.href = parm;
    } else {
        linkScript = document.createElement("script");
        linkScript.type = "text/" + ("javascript");
        linkScript.src = parm;
    }
    Head.insertBefore(linkScript, Head.lastChild);
    linkScript.onload = linkScript.onerror = function() {
        if(fn) fn()
    }
}


//图片放大显示
function fancyimg(needLink) {
    if(needLink===false){
        //放大浏览照片
        $(".imgbox").find("a").attr("rel","group").fancybox();
    }else{
        linkScript('/static/common/fancybox/css/fancybox.css');
        linkScript('/static/common/fancybox/js/jquery.fancybox.pack.js',function () {
            //放大浏览照片
            $(".imgbox").find("a").attr("rel","group").fancybox();
        });
    }
}

//地址动态加参数
function urlAddParam(url,key,val) {
    if(url.indexOf('?') ==-1){
        url = url+'?'+key+'='+val;
    }else{
        url = url+'&'+key+'='+val;
    }
    return url;
}

//替换url地址中的参数值
function changeUrlArg(url, arg, val){
    var pattern = arg+'=([^&]*)';
    var replaceText = arg+'='+val;
    return url.match(pattern) ? url.replace(eval('/('+ arg+'=)([^&]*)/gi'), replaceText) : (url.match('[\?]') ? url+'&'+replaceText : url+'?'+replaceText);
}