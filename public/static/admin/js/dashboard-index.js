$(function () {
    $.post(hostUrl+'/dashboard',null,function(data){
        $.each(data,function (i,item) {
            $('#'+i).html(data[i]);
        });
        //console.log(data);
    },'json');
});