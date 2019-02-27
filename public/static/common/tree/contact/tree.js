$(function(){
    //初始化
    var BODY = $('body');
    initTree();

    //js展开&收起
    BODY.on('click','.tree li.parent_li > span>i',function (e) {
        if($(this).hasClass('disable')) return false;
        var children = $(this).parent("span").parent('li.parent_li').find(' > ul > li');
        if (children.is(":visible")) {
            children.hide('fast');
            $(this).parent("span").attr('title', '展开').find(' > i').addClass('tree-icon-close').removeClass('tree-icon-open');
        } else {
            children.show('fast');
            $(this).parent("span").attr('title', '收起').find(' > i').addClass('tree-icon-open').removeClass('tree-icon-close');
        }
        e.stopPropagation();
    });

    //点击菜单选项
    BODY.on('click','.tree li > span > label',function () {
        var keyword = delKB($('#search-input').val());
        if(keyword.length>0){
            var check = $(this).children('input[type=checkbox]').prop("checked");
            var userId = $(this).children('input').val();
            if(check){
                $(this).children('b').addClass('check_active');
                var userName = $(this).children('input').attr('data-name');
                var str = '<li data-id="'+userId+'">'+userName+'</li>';
                $('.ul-right').append(str);
            }else{
                $(this).children('b').removeClass('check_active');
                $('.ul-right li').each(function () {
                    if(userId==$(this).attr('data-id')){
                        $(this).remove();
                    }
                });
            }
            var userIds = ',';
            $('.ul-right li').each(function () {
                userIds += $(this).attr('data-id')+',';
            });
            userIds = userIds==','?'':userIds;
            $('#user_checked').val(userIds);
        }else{
            changeParent(this,true);
        }
    });

    //点击右侧删除用户
    BODY.on('click','.ul-right>li',function () {
        var id = $(this).attr('data-id');
        var cancelLabel = $('.ul-left #user_'+id).parent('label');
        $(this).remove();
        $('.ul-left #user_'+id).prop("checked", false);
        cancelLabel.children('b').removeClass('check_active');
        changeParent(cancelLabel,true);
    })
});

//改变父元素选择状态
function changeParent(obj,isclick) {
    if($(obj).children('b').hasClass('check_list_unable')){
        return false;
    }
    var check = $(obj).children('input[type=checkbox]').prop("checked");
    var brotherLen,brotherCheckedLen,parentLabel;
    var isend = false;
    if(check){//选中
        $(obj).children('b').addClass('check_active');
        if($(obj).parent('span').parent('li').hasClass('parent_li') && isclick){
            //有子菜单
            $(obj).parent("span").parent('li.parent_li').find('input[type=checkbox]').prop("checked", check);
            $(obj).parent("span").parent('li.parent_li').find('b').addClass('check_active');
        }
        brotherLen = $(obj).parent('span').parent('li').parent('ul').children('li').children('span').children('label').length;
        brotherCheckedLen = $(obj).parent('span').parent('li').parent('ul').children('li').children('span').children('label').children('input[type=checkbox]:checked').length;
        if(brotherLen==brotherCheckedLen){
            //选中父元素
            parentLabel = $(obj).parent('span').parent('li').parent('ul').parent('li').children('span').children('label');
            parentLabel.children('input[type=checkbox]').prop("checked", check);
            changeParent(parentLabel,false);
        }else{
            isend = true;
        }
    }else{ //取消选中
        $(obj).children('b').removeClass('check_active');
        if($(obj).parent('span').parent('li').hasClass('parent_li') && isclick){
            //有子菜单
            $(obj).parent("span").parent('li.parent_li').find('input[type=checkbox]').prop("checked", check);
            $(obj).parent("span").parent('li.parent_li').find('b').removeClass('check_active');
        }
        brotherLen = $(obj).parent('span').parent('li').parent('ul').children('li').children('span').children('label').length;
        brotherCheckedLen = $(obj).parent('span').parent('li').parent('ul').children('li').children('span').children('label').children('input[type=checkbox]:checked').length;
        if(brotherCheckedLen<brotherLen){
            //取消选中父元素
            parentLabel = $(obj).parent('span').parent('li').parent('ul').parent('li').children('span').children('label');
            parentLabel.children('input[type=checkbox]').prop("checked", check);
            changeParent(parentLabel,false);
        }else{
            isend = true;
        }
    }
    if(isend){
        //获取选中用户id
        var userIds = ',';
        $('.ul-right').empty();
        $('.ul-left .tree .user_id:checked').each(function () {
            var id = $(this).val();
            userIds += id+',';
            var str = '<li data-id="'+id+'">'+$(this).attr('data-name')+'</li>';
            $('.ul-right').append(str);
        });
        userIds = userIds==','?'':userIds;
        $('#user_checked').val(userIds);
    }
}

//初始化
function initTree() {
    $('.ul-left .tree li:has(ul)').addClass('parent_li');
    $('.ul-left .tree .tree-icon-close').parent('span').attr('title','展开');
    $('.ul-left .tree .tree-icon-empty').parent('span').attr('title','空支部');
    $('.ul-left .tree .tree-icon-open').parent('span').attr('title','收起').parent('li').show().find('li').show();
    $('.ul-left .tree .group_id').each(function () {
        var userLen = $(this).parent('label').parent('span').parent('li').find('.user_id').length;
        if(userLen==0){
            $(this).parent('label').remove();
        }
    });
    setTimeout(function () {
        $('.ul-left .tree-icon-top').click().removeClass('tree-icon-open').addClass('disable');
    },200);
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