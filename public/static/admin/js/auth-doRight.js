$(function(){
    var subBtn = $('#button[lay-filter="formSubmit"]'),
        option = $('#pid option'),
        isnav = $('input[name=isnav]'),
        curid = parseInt(subBtn.attr('data-id')),
        count = 1;
    var form = layui.form;
    form.on('radio(type)', function(data){
        var typeVal = data.value;
        option.attr('disabled',true);
        if(!curid || count!=1){
            option.attr('selected',false);
            if(typeVal==1){
                option.eq(1).attr('selected',true).attr('disabled',false);
            }else{
                option.eq(0).attr('selected',true).attr('disabled',false);
            }
        }
        var typeid = parseInt(typeVal-1);
        $("#pid option[data-type="+typeid+"]").attr('disabled',false);
        count++;
        if(typeVal==1){
            isnav.attr('disabled',false);
            isnav.eq(0).click();
            isnav.attr('disabled',true);
        }else if(typeVal==2||typeVal==3){
            isnav.attr('disabled',false);
            isnav.eq(0).click();
        }else{
            isnav.attr('disabled',false);
            isnav.eq(1).click();
            isnav.attr('disabled',true);
        }
        if(typeVal==2&&$('input[name=isnav]:checked').val()==1){
            $('.icon').slideDown(200);
            $('input[name=icon]').attr('lay-verify','required');
        }else{
            $('.icon').slideUp(200);
            $('input[name=icon]').removeAttr('lay-verify');
        }
        form.render();
    });
});