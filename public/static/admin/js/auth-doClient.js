$(function(){
    var subBtn = $('#button[lay-filter="formSubmit"]'),
        option = $('#pid option'),
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
        form.render();
    });
});