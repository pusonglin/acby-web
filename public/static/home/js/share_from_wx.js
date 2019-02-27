var LoginId = parseInt($('#cur_user_position').attr('data-id'));
var wxConfig = $('#wx_config');
var appid = wxConfig.attr('data-appid');
var timestamp = wxConfig.attr('data-timestamp');
var noncestr = wxConfig.attr('data-noncestr');
var signature = wxConfig.attr('data-signature');
var shareConfig = $('#share_config');
var url = shareConfig.attr('data-url');
var title = shareConfig.attr('data-title');
var desc = shareConfig.attr('data-desc');
var img = shareConfig.attr('data-img');
var from = shareConfig.attr('data-from');
var isTask = $('#task-page').attr('data-istask');

$(function () {
    wx.config({
        debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: appid, // 必填，公众号的唯一标识
        timestamp: timestamp, // 必填，生成签名的时间戳
        nonceStr: noncestr, // 必填，生成签名的随机串
        signature: signature,// 必填，签名，见附录1
        jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
    });
    wx.ready(function(){
        //朋友圈
        wx.onMenuShareTimeline({
            title: title, // 分享标题
            link: url, // 分享链接
            imgUrl: img, // 分享图标
            success: function () {
                if(LoginId>0){
                    if(isTask==1){
                        doTaskFx(); // 用户确认分享后执行的回调函数
                        $(".t_fixed").fadeOut(300);
                    }
                    doFxScore();
                }
            },
            cancel: function () {
                myLayer('取消分享');// 用户取消分享后执行的回调函数
            }
        });
        //微信好友
        wx.onMenuShareAppMessage({
            title: title, // 分享标题
            desc: desc, // 分享描述
            link: url, // 分享链接
            imgUrl: img, // 分享图标
            type: 'link', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                if(LoginId>0){
                    if(isTask==1){
                        doTaskFx(); // 用户确认分享后执行的回调函数
                        $(".t_fixed").fadeOut(300);
                    }
                    doFxScore();
                }
            },
            cancel: function () {
                myLayer('取消分享');// 用户取消分享后执行的回调函数
            }
        });
        //分享到QQ
        wx.onMenuShareQQ({
            title: title, // 分享标题
            desc: desc, // 分享描述
            link: url, // 分享链接
            imgUrl: img, // 分享图标
            success: function () {
                if(LoginId>0){
                    if(isTask==1){
                        doTaskFx(); // 用户确认分享后执行的回调函数
                        $(".t_fixed").fadeOut(300);
                    }
                    doFxScore();
                }
            },
            cancel: function () {
                myLayer('取消分享');// 用户取消分享后执行的回调函数
            }
        });
        //腾讯微博
        wx.onMenuShareWeibo({
            title: title, // 分享标题
            desc: desc, // 分享描述
            link: url, // 分享链接
            imgUrl: img, // 分享图标
            success: function () {
                if(LoginId>0){
                    if(isTask==1){
                        doTaskFx(); // 用户确认分享后执行的回调函数
                        $(".t_fixed").fadeOut(300);
                    }
                    doFxScore();
                }
            },
            cancel: function () {
                myLayer('取消分享');// 用户取消分享后执行的回调函数
            }
        });
        //QQ空间
        wx.onMenuShareQZone({
            title: title, // 分享标题
            desc: desc, // 分享描述
            link: url, // 分享链接
            imgUrl: img, // 分享图标
            success: function () {
                if(LoginId>0){
                    if(isTask==1){
                        doTaskFx(); // 用户确认分享后执行的回调函数
                        $(".t_fixed").fadeOut(300);
                    }
                    doFxScore();
                }
            },
            cancel: function () {
                myLayer('取消分享');// 用户取消分享后执行的回调函数
            }
        });
    });
});


