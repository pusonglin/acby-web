$(function(){
    //实例化ueditor
    var ue_content = UE.getEditor('content');

    //添加&编辑
    $('#doSubmit').click(function(){
        var flag = true,
            O = $(this),
            curid = parseInt(O.attr('data-id')),
            code = $('#code'),
            codeTs = '请填写模板标识！';
        flag = checkNull(flag,code,codeTs,1);
        if(isNaN(curid)){curid=0}

        var title = $('#title'),
            titleTs = '请填写模板标题！';
        flag = checkNull(flag,title,titleTs,1);

        if(flag){
            var content = ue_content.getContent();
            if(content.length<1){
                myLayer('请填写内容详情！');
                ue_content.focus();
                flag = false;
            }
        }

        if(flag){
            var data = {
                id         : curid,
                code       : code.val(),
                title      : title.val(),
                content    : content
            };
            var url = hostUrl+'/sysset/doneBlock',
                backurl = hostUrl+'/library/block?p='+O.attr('data-page'),
                notice = curid==0?'添加成功！':'编辑成功!';
            postAjax(O,url,data,notice,backurl,1);
        }
    });
});