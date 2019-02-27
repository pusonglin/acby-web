$(function(){
    var form = layui.form;
    form.on('radio(first_cate_id)', function(data){
        var typeVal = data.value;
        var coverMust = true;
        var load = layer.load(2);
        $.post(hostUrl+'/index/getNewsCateLists',{pid:typeVal},function (d) {
            var str = "";
            if(d.length>0){
                $.each(d,function (i,item) {
                    var checked = "";
                    if(i==0){
                        checked = 'checked="checked"';
                    }
                    str += '<input type="radio" name="second_cate_id" value="'+item['id']+'" '+checked+' title="'+item['name']+'"  />';
                })
            }
            $('#second_cate_id').html(str);
            form.render();
            if(typeVal==4){
                coverMust = false;
            }
            must_cover(coverMust);
            layer.close(load);
        },'json');
    });

    function must_cover(isMust) {
        if(isMust){
            $('.cover_must').show();
            $('input[name=cover]').attr('lay-verify','required');
        }else{
            $('.cover_must').hide();
            $('input[name=cover]').removeAttr('lay-verify');
        }
    }
});