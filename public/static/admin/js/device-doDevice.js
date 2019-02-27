$(function () {
    var form = layui.form;
    var old_model_id = $('select[name=model_id]').attr('data-old');

    //点击切换品牌
    form.on('select(brand_id)',function (data) {
        $.ajax({
            type: "post",
            url: '/device/doDevice',
            data: {brand_id:data.value},
            async: false,
            dataType: 'json',
            success: function (res) {
                var str = '<option value="">选择设备型号</option>';
                if(res.length>0){
                    $.each(res,function (i,item) {
                        if(item['id']==old_model_id){
                            str += '<option value="'+item['id']+'" selected="selected">'+item['name']+'</option>';
                        }else{
                            str += '<option value="'+item['id']+'">'+item['name']+'</option>';
                        }
                    });
                }else{
                    var text = data.value>0?'此品牌暂无型号！':'请先选择品牌！';
                    str += '<option disabled>'+text+'</option>';
                }
                $('select[name=model_id]').html(str);
                form.render();
            }
        });
    });
});