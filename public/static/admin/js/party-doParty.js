$(function(){
    var form = layui.form;

    //点击切换组织级别
    form.on('radio(rank)', function(data){
        var typeVal = data.value;
        $('.dz_select').each(function () {
            var dataType = $(this).attr('data-type');
            if(dataType>typeVal){
                $(this).hide();
                $(this).children('select').removeAttr('lay-verify').removeAttr('data-val');
                if(dataType==4){
                    $(this).children('select').children('option:gt(1)').remove();
                    $(this).children('select').children('option').attr('selected',false);
                }else{
                    $(this).children('select').children('option:gt(0)').remove();
                }
            }else{
                $(this).show();
                $(this).children('selecet').attr('lay-verify','required');
            }
        });
        form.render();
        showAddressList(1);
    });

    //点击切换城市
    form.on('select(dz_select)',function (data) {
        showAddressList(2,data);
    });

    //初始化
    showAddressList(1);

    //显示地址选择列表
    function showAddressList(flag,obj) {
        var province_id,city_id,county_id,town_id;
        var url = hostUrl+'/index/getAddressList';
        var rank = $('input[name=rank]:checked').val();
        rank = rank>0?rank:3;
        switch (flag){
            case 1: //默认值初始化
                province_id = parseInt($('select[name=province_id]').attr('data-val'));
                city_id = parseInt($('select[name=city_id]').attr('data-val'));
                county_id = parseInt($('select[name=county_id]').attr('data-val'));
                town_id = parseInt($('select[name=town_id]').attr('data-val'));

                //省份
                $.ajax({
                    type: "post",
                    url: url,
                    data: {pid:1},
                    async: false,
                    dataType: 'json',
                    success: function (data) {
                        var str = '<option value="">请选择省</option>';
                        $.each(data,function (i,item) {
                            if(item['id']==province_id){
                                str += '<option value="'+item['id']+'" selected="selected">'+item['name']+'</option>';
                            }else{
                                str += '<option value="'+item['id']+'">'+item['name']+'</option>';
                            }
                        });
                        $('select[name=province_id]').html(str);
                    }
                });

                //城市
                if(rank>1){
                    if(province_id>0){
                        $.ajax({
                            type: "post",
                            url: url,
                            data: {pid:province_id},
                            async: false,
                            dataType: 'json',
                            success: function (data) {
                                if(data.length>0){
                                    var str = '<option value="">请选择市</option>';
                                    $.each(data,function (i,item) {
                                        if(item['id']==city_id){
                                            str += '<option value="'+item['id']+'" selected="selected">'+item['name']+'</option>';
                                        }else{
                                            str += '<option value="'+item['id']+'">'+item['name']+'</option>';
                                        }
                                    });
                                    $('select[name=city_id]').html(str);
                                }
                            }
                        });
                    }
                }else{
                    $('.dz_select:gt(0)').children('select').removeAttr('lay-verify');
                    $('.dz_select:gt(0)').hide();
                    $('input[name=street]').removeAttr('lay-verify').val('');
                    $('.dz_street').hide();
                }

                //区县
                if(rank>2){
                    if(city_id>0){
                        $.ajax({
                            type: "post",
                            url: url,
                            data: {pid:city_id},
                            async: false,
                            dataType: 'json',
                            success: function (data) {
                                if(data.length>0){
                                    var str = '<option value="">请选择县/区</option>';
                                    $.each(data,function (i,item) {
                                        if(item['id']==county_id){
                                            str += '<option value="'+item['id']+'" selected="selected">'+item['name']+'</option>';
                                        }else{
                                            str += '<option value="'+item['id']+'">'+item['name']+'</option>';
                                        }
                                    });
                                    $('select[name=county_id]').html(str);
                                }
                            }
                        });
                    }
                }else{
                    $('.dz_select:gt(1)').children('select').removeAttr('lay-verify');
                    $('.dz_select:gt(1)').hide();
                    $('input[name=street]').removeAttr('lay-verify').val('');
                    $('.dz_street').hide();
                }

                //街道
                if(rank>3){
                    if(county_id>0){
                        $.ajax({
                            type: "post",
                            url: url,
                            data: {pid:county_id},
                            async: false,
                            dataType: 'json',
                            success: function (data) {
                                var str = '<option value="">请选择乡镇/街道</option>';
                                //str += '<option value="0">自定义输入</option>';
                                $.each(data,function (i,item) {
                                    if(item['id']==town_id){
                                        str += '<option value="'+item['id']+'" selected="selected">'+item['name']+'</option>';
                                    }else{
                                        str += '<option value="'+item['id']+'">'+item['name']+'</option>';
                                    }
                                });
                                $('select[name=town_id]').html(str);
                            }
                        });
                    }else{
                        var str = '<option value="">请选择乡镇/街道</option>';
                        //str += '<option value="0">自定义输入</option>';
                        $('select[name=town_id]').html(str);
                    }
                    //详细地址
                    if(town_id===0){
                        $('input[name=street]').attr('lay-verify','required');
                        $('.dz_street').show();
                    }else{
                        $('input[name=street]').removeAttr('lay-verify').val('');
                        $('.dz_street').hide();
                    }
                }else{
                    $('.dz_select:gt(2)').children('select').removeAttr('lay-verify');
                    $('.dz_select:gt(2)').hide();
                    $('input[name=street]').removeAttr('lay-verify').val('');
                    $('.dz_street').hide();
                }
                form.render();
                break;
            case 2: //切换城市
                //隐藏不需要显示的级数和清空数据
                var O = $(obj.elem);
                var curType = O.parent('div').attr('data-type');
                var pid = parseInt(obj.value);
                O.attr('data-val',pid);
                province_id = $('select[name=province_id]').val();
                $('.dz_select').each(function () {
                    var type = $(this).attr('data-type');
                    if(type>curType){
                        if(type==4){
                            $(this).children('select').children('option:gt(1)').remove();
                            $(this).children('select').children('option').attr('selected',false);
                        }else{
                            $(this).children('select').children('option:gt(0)').remove();
                        }
                    }
                    if(type>rank){
                        $(this).children('select').removeAttr('lay-verify');
                        $(this).hide();
                    }else{
                        $(this).children('select').attr('lay-verify','required');
                        $(this).show();
                    }
                });
                form.render();
                if(rank>curType && pid>0){
                    var nextName;
                    var str = '';
                    switch(curType){
                        case '1':
                            nextName = 'city_id';
                            str += '<option value="">请选择市</option>';
                            break;
                        case '2':
                            nextName = 'county_id';
                            str += '<option value="">请选择县/区</option>';
                            break;
                        case '3':
                            nextName = 'town_id';
                            str += '<option value="">请选择乡镇/街道</option>';
                            //str += '<option value="0">自定义输入</option>';
                            break;
                            break;
                        default:return false;
                    }
                    var load = layer.load(2);
                    $.ajax({
                        type: "post",
                        url: url,
                        data: {pid:pid},
                        async: false,
                        dataType: 'json',
                        success: function (data) {
                            if(data.length>0){
                                $.each(data,function (i,item) {
                                    str += '<option value="'+item['id']+'">'+item['name']+'</option>';
                                });
                            }
                            $('select[name='+nextName+']').html(str);
                            form.render();
                            layer.close(load);
                        }
                    });
                }else{
                    if(curType=='4' && pid==0){
                        $('input[name=street]').attr('lay-verify','required');
                        $('.dz_street').show();
                    }else{
                        $('input[name=street]').removeAttr('lay-verify').val('');
                        $('.dz_street').hide();
                    }
                }
                break;
            default:return false;
        }
    }
});