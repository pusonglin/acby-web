$(function(){
    //添加&编辑分类
    isShowMid();
    $('#doSubmit').click(function(){
        var flag = true,
            O = $(this),
            curid = parseInt(O.attr('data-id'));
        if(isNaN(curid)){curid=0}

        var name = $('#name'),
            nameTs = '栏目名称不能为空！';
        flag = checkNull(flag,name,nameTs,1);

        var parentid = $('#parentid'),
            parentidTs = '请选择所属栏目！';
        flag = checkSelect(flag,parentid,parentidTs,-1);

        var pid = parentid.val();
        if(pid==0&&flag){
            var code = $('#code'),
                codeTs = '请填写栏目编号！';
            flag = checkNull(flag,code,codeTs,1);
        }

        if(flag){
            var data = {
                id      : curid,
                name    : name.val(),
                pid     : parentid.val(),
                summary : $('#summary').val(),
                code    : $('#code').val()
            };
            var url = hostUrl+'/sysset/doneCategory',
                backurl = hostUrl+'/sysset/category?_t='+O.attr('data-time')+'#'+O.attr('data-pid'),
                notice = curid==0?'添加成功！':'编辑成功!';
            postAjax(O,url,data,notice,backurl,1);
        }
    });

    //所属上级分类改变时
    $('#parentid').change(function(){
        isShowMid();
    });

    //是否显示所属板块
    function isShowMid(){
        var parentid = $('#parentid').val();
        if(parentid==0){
            $('.isFirst').show(500);
        }else{
            $('.isFirst').hide(500);
        }
    }
});