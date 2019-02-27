$(function(){
    //初始化
    $('.tree li:has(ul)').addClass('parent_li');
    $('.fa-angle-double-right').parent('span').attr('title','展开');
    $('.fa-angle-double-down').parent('span').attr('title','隐藏').parent('li').show().find('li').show();

    //js展开&隐藏
    $('.tree li.parent_li > span>i').on('click', function (e) {
        var children = $(this).parent("span").parent('li.parent_li').find(' > ul > li');
        if (children.is(":visible")) {
            children.hide('fast');
            $(this).parent("span").attr('title', '展开').find(' > i').addClass('fa-angle-double-right').removeClass('fa-angle-double-down');
        } else {
            children.show('fast');
            $(this).parent("span").attr('title', '隐藏').find(' > i').addClass('fa-angle-double-down').removeClass('fa-angle-double-right');
        }
        e.stopPropagation();
        bottom_auto();
        setTimeout(function () {
            h_auto();
        },200);
    });

    //终极菜单选项
    $('.tree li > span > label').on('click',function (e) {
        changeParent(this,true);

    });

    function changeParent(obj,isclick) {
        if($(obj).children('b').hasClass('check_list_unable')){
            return false;
        }
        var check = $(obj).children('input[type=checkbox]').prop("checked");
        if(check){//选中
            $(obj).children('b').addClass('check_active');
            if($(obj).parent('span').parent('li').hasClass('parent_li') && isclick){
                //有子菜单
                $(obj).parent("span").parent('li.parent_li').find('input[type=checkbox]').prop("checked", check);
                $(obj).parent("span").parent('li.parent_li').find('b').addClass('check_active');
            }
            if($(obj).parent('span').parent('li').parent('ul').parent().is('.parent_li')){
                var parentSpan = $(obj).parent('span').parent('li').parent('ul').parent('li').children('span');
                //选中父元素
                parentSpan.children('label').children('input[type=checkbox]').prop("checked", check);
                var obj1 = parentSpan.children('label');
                changeParent(obj1,false);
            }
        }else{ //取消选中
            $(obj).children('b').removeClass('check_active');
            if($(obj).parent('span').parent('li').hasClass('parent_li') && isclick){
                //有子菜单
                $(obj).parent("span").parent('li.parent_li').find('input[type=checkbox]').prop("checked", check);
                $(obj).parent("span").parent('li.parent_li').find('b').removeClass('check_active');
            }
            if($(obj).parent('span').parent('li').parent('ul').parent().is('.parent_li')){
                //判断同级是否全部没有选中
                var peerChecked = $(obj).parent('span').parent('li').parent('ul').find('.right:checked');
                if(peerChecked.length==0){
                    var parentSpan2 = $(obj).parent('span').parent('li').parent('ul').parent('li').children('span');
                    //选中父元素
                    parentSpan2.children('label').children('input[type=checkbox]').prop("checked", check);
                    var obj2 = parentSpan2.children('label');
                    changeParent(obj2,false);
                }
            }
        }
    }
});