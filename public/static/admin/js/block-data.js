$(function(){
    //点击上移
    $('.prev_btn').click(function(){
        var num = $('.prev_btn').index($(this));
        if(num>0){
            $('.block_list').eq(num-1).before($('.block_list').eq(num));
        }else{
            myLayer('已经是第一个了！');
        }
    });

    //点击下移
    $('.next_btn').click(function(){
        var num = $('.next_btn').index($(this));
        var len = $('.next_btn').length;
        if(num<len-1){
            $('.block_list').eq(num).before($('.block_list').eq(num+1));
        }else{
            myLayer('已经是最后一个了！');
        }
    });


    //上传图片
    $('.data_upload').each(function(){
        var curID = this.id;
        upload_imgs(curID);
    });
});

//封面
function upload_imgs(ID){
    var ue_imgs = UE.getEditor(ID,{
        toolbars: [['insertimage']],
        initialFrameHeight:0,
        initialFrameWidth:0
    });
    ue_imgs.ready(function (){
        ue_imgs.hide();
        ue_imgs.addListener('beforeinsertimage', function (t, arg) {     //侦听图片上传
            var box =$('#'+ID).prev();
            box.val(arg[0]['src']);
        });
    });
}

//弹出图片上传的对话框
function upImage(ID){
    var ue_imgs = UE.getEditor(ID);
    var myImage = ue_imgs.getDialog("insertimage");
    myImage.open();
}
