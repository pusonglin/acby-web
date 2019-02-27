$(function () {
    setTimeout(function () {
        $('.tree>ul>li.parent_li > span>i').click();
    },100);

    //底部提交按钮显示
    bottom_auto();

    //提交权限
    $('#doSubmit').click(function(){
        var  O = $(this),
            able = O.attr('able');
        if(able!=0){
            O.attr('able',0);
            //管理端权限
            var checkbox = $('.manage').find('.right:checked'),
                param = [];
            checkbox.each(function(){
                param.push(this.value);
            });
            //客户端权限
            var checkbox2 = $('.client').find('.right:checked'),
                param2 = [];
            checkbox2.each(function(){
                param2.push(this.value);
            });
            var data = {
                manage_ids : param,
                client_ids : param2,
                id   : O.attr('data-id'),
                name : O.attr('data-name')
            };
            var url = hostUrl+'/auth/doSetRight';
            $.post(url,data,function(d){
                if(isNaN(d)){
                    myLayer(d,2);
                    O.attr('able',1);
                }else{
                    setTimeout(function(){
                        layer_close_iframe();
                    },1000);
                    myLayer('权限保存成功！',1);
                }
            })
        }
    });
});


