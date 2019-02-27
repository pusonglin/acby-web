$(function () {
    //底部提交按钮显示
    bottom_auto();

    //提交权限
    $('#doSubmit').click(function(){
        var  O = $(this),
            able = O.attr('able');
        if(able!=0){
            O.attr('able',0);
            var checkbox = $('.right:checked'),
                url = hostUrl+'/rbac/doSetRight',
                param = [];
            checkbox.each(function(){
                param.push(this.value);
            });
            var data = {
                right_ids : param,
                role_id   : O.attr('data-id'),
                role_name : O.attr('data-name')
            };
            $.post(url,data,function(d){
                if(isNaN(d)){
                    myLayer(d,2);
                    O.attr('able',1);
                }else{
                    myLayer('权限保存成功！',1);
                    setTimeout(function(){
                        layer_close_iframe();
                    },1000);
                    if(O.attr('data-fresh')==1){
                        //刷新左侧菜单
                        parent.parent.freshMenu(3);
                    }
                }
            })
        }
    });
});


