
$(function () {
    var BODY = $('body');
    //编辑时判断是否需要隐藏上传按钮
    $(".imgbox").each(function(){
        var len = $(this).children('li').length;
        var max = $(this).attr('data-max');
        if(len>=max){
            $(this).next('.btnup').hide();
        }
    });

    //点击删除上传的图片
    BODY.on('click','.up_del_btn',function () {
        var box = $(this).parents('.imgbox');
        box.next('.btnup').show();
        $(this).parent('li').remove();
        fresh_hiden_value(box,'img');
        h_auto();
    });

    //鼠标移上去的时候显示按钮
    BODY.on('mouseenter','.imgbox>li',function () {
        $(this).children('.on_show').css({'display':'block'})
    }).on('mouseleave','.imgbox>li',function () {
        $(this).children('.on_show').css({'display':'none'})
    });

    //点击前移
    BODY.on('click','.prev_btn',function () {
        var num = $('.prev_btn').index($(this));
        if(num>0){
            $('.imgbox>li').eq(num-1).before($('.imgbox>li').eq(num));
        }else{
            myLayer('已经是第一个了！');
        }
        $('input[name=is_cover]:eq(0)').click();
    });

    //点击后移
    BODY.on('click','.next_btn',function () {
        var num = $('.next_btn').index($(this));
        var len = $('.next_btn').length;
        if(num<len-1){
            $('.imgbox>li').eq(num).before($('.imgbox>li').eq(num+1));
        }else{
            myLayer('已经是最后一个了！');
        }
        $('input[name=is_cover]:eq(0)').click();
    });

    //设置封面
    BODY.on('click','input[name=is_cover]',function () {
        $(this).parent('label').parent('li').insertBefore($('.imgbox>li:eq(0)'));
    });

    //删除附件
    BODY.on('click','.file-list>i',function () {
        var box = $(this).parent('.file-list').parent('.file-box');
        $(this).parent('.file-list').remove();
        fresh_hiden_value(box,'file');
    });
});

//封面上传、多图上传公共方法
function upload_imgs(ID,descName){
    var ue_imgs = UE.getEditor(ID,{
        toolbars: [['insertimage']],
        initialFrameHeight:0,
        initialFrameWidth:0
    });
    ue_imgs.ready(function (){
        ue_imgs.hide();
        ue_imgs.addListener('beforeinsertimage', function (t, arg) {     //侦听图片上传
            var box = $('#'+ID).parents('.uploadImg').find('.imgbox');
            var maxNum = box.attr('data-max');
            var setCover = box.attr('data-setcover');
            $.each(arg,function(i, item){
                var len = box.children('li').length;
                if(len<maxNum){
                    var str  = '<li>';
                    str +=      '<a href="' + item['src'] + '" rel="group">';
                    str +=          '<img src="' + item['src'] + '"/>';
                    str +=      '</a>';
                    str +=      '<span class="fa fa-times up_del_btn on_show" title="删除"></span>';
                    if(maxNum>1){
                        str +=      '<span class="fa fa-arrow-circle-left prev_btn on_show" title="前移"></span>';
                        str +=      '<span class="fa fa-arrow-circle-right next_btn on_show" title="后移"></span>';
                    }
                    if(setCover==1){
                        str +=  '<label class="on_show"><input type="radio" name="is_cover" value="' + item['src'] + '"/>设封面</label>';
                    }
                    if(descName){
                        str += '<p><input type="text" class="img_desc" placeholder="'+descName+'"></p>'
                    }
                    str += '</li>';
                    box.append(str);
                    if((len+1)>=maxNum){
                        $('#'+ID).parent('.btnup').hide();
                    }
                }else{
                    $('#'+ID).parent('.btnup').hide();
                }
            });

            fresh_hiden_value(box,'img');

            h_auto();
        });
    });
}

//刷新隐藏域图片列表的值
function fresh_hiden_value(obj,type) {
    switch (type){
        case 'img':
            var imgs = [];
            obj.children('li').each(function () {
                var src = $(this).find('img').attr('src');
                if(src){
                    imgs.push(src);
                }
            });
            var res = '';
            if(imgs.length>0){
                var maxNum = obj.attr('data-max');
                if(maxNum==1){
                    res = imgs[0];
                }else{
                    res = JSON.stringify(imgs);
                }
            }
            obj.children('.layui-input').val(res);
            break;
        case 'file':
            var attachment = [];
            obj.children('.file-list').each(function () {
                var title = $(this).attr('data-title');
                var url = $(this).attr('data-url');
                var size = $(this).attr('data-size');
                var type = $(this).attr('data-type');
                if(title && url){
                    attachment.push({title:title,url:url,size:size,type:type})
                }
            });
            obj.children('.layui-input').val(JSON.stringify(attachment));
            break;
        default:console.log('参数错误');
    }

}


//弹出图片上传的对话框
function upImage(ID){
    var ue_imgs = UE.getEditor(ID);
    var myImage = ue_imgs.getDialog("insertimage");
    myImage.open();
}

//视频上传公共方法
function upload_video(ID){
    var ue_video = UE.getEditor(ID,{
        toolbars: [['insertvideo']],
        initialFrameHeight:0,
        initialFrameWidth:0
    });
    ue_video.ready(function (){
        ue_video.hide();
        ue_video.addListener('afterupvideo', function (t,arg) {     //侦听视频上传
            var box =$('#'+ID).parents('li').find('input');
            box.val(arg[0]['url']);
        });
    });
}

//弹出视频上传的对话框
function upVideo(ID){
    var ue_video = UE.getEditor(ID);
    var myVideo = ue_video.getDialog("insertvideo");
    myVideo.open();
}

//附件上传公共方法
function upload_file(ID){
    var ue_file = UE.getEditor(ID,{
        toolbars: [['attachment']],
        initialFrameHeight:0,
        initialFrameWidth:0
    });
    ue_file.ready(function (){
        ue_file.hide();
        ue_file.addListener('afterupfile', function (t,arg) {     //侦听文件上传
            var box = $('#'+ID).parent('.file-box');
            $.each(arg,function (i,item) {
                box.children('.file-list').each(function () {
                    if(item['title']==$(this).attr("data-title")){
                        $(this).remove();
                    }
                });
                var str = '<span class="file-list" data-title="'+item['title']+'" data-url="'+item['url']+'" data-size="0">'+item['title']+'<i title="删除">x</i></span>';
                $('#'+ID).before(str);
            });

            fresh_hiden_value(box,'file');
        });
    });
}

//弹出附件上传的对话框
function upFile(ID){
    var ue_file = UE.getEditor(ID);
    var myFile = ue_file.getDialog("attachment");
    myFile.open();
}