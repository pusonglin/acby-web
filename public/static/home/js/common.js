var hostUrl = 'http://' + window.location.host;

//公共错误方法
function error( content ){
    layer.open({
        content: '<div style="width: 160px;height:20px;padding: 20px 10px;text-align: center;background-color: #f5f5f5;color: #000">'+content+'</div>'
        ,skin: 'msg'
        ,time: 2 //2秒后自动关闭
        ,anim: 'scale'
    });
}

//公共ajax方法
function getAjax(url,data,type){
    var msgcode = '';
    $.ajax({
        cache: false,
        async: false,
        url:url,
        data:data,
        type:type,
        dataType:"json",
        success:function(res){
            msgcode = res;
        }
    });
    return msgcode;
}

//公共成功方法
function success( content ){
    layer.open({
        content: content
        ,skin: 'msg'
        ,time: 2 //2秒后自动关闭
        ,anim: false
    });
}
