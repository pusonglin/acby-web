var layerIframe;
var layTable = layui.table;
var layForm = layui.form;
var layUpload = layui.upload;
var layElement = layui.element;
var layDate = layui.laydate;
var layTableConfig = {
    'size': '',
    'limit': 20,
    'none': '没有数据！'
};

$(function () {
    //初始化
    layForm.render();
    layElement.render();
    var BODY = $('body');

    //窗口尺寸自适应屏幕
    $(window).resize(function () {
        if (layerIframe) {
            layer.full(layerIframe);
        }
    });


    //提示没有操作权限
    BODY.on('click', '.layui-btn-disabled,.layui-tab-title .layui-disabled', function () {
        var msg = $(this).attr('data-msg') ? $(this).attr('data-msg') : '抱歉,您没有操作权限,请联系管理员！';
        myLayer(msg, 2);
        return false;
    });

    //浏览器版本提示
    var browser = navigator.appName;
    var b_version = navigator.appVersion;
    var version = b_version.split(";");
    if (version.length >= 2) {
        var trim_Version = navigator.appVersion.split(";")[1].replace(/[ ]/g, "");
        var trim_Version_Num = trim_Version.substring(4);
        if (browser == "Microsoft Internet Explorer" && trim_Version_Num <= 9) {
            $(".browser-updator").show();
        }
    }

    //隐藏浏览器升级提示
    BODY.on('click', '.browser-updator-close', function () {
        $(".browser-updator").hide();
    });

    //弹出iframe层
    BODY.on('click', '.layer_open_iframe', function () {
        if (!$(this).hasClass('btn-disable') && !$(this).hasClass('layui-btn-disabled')) {
            if ($(this).hasClass('checked_users')) {
                $('.checked_users_active').removeClass('checked_users_active');
                $(this).addClass('checked_users_active');
            }
            layer_open_iframe($(this));
        }
    });

    //显示title
    var titleTips;
    BODY.on('mouseenter', '.title-tips', function () {
        var title = delKB($(this).attr("data-title"));
        var flag = $(this).attr('data-flag');
        if (title && title.length > 0) {
            flag = flag > 0 ? flag : 1;
            titleTips = layer.tips(title, $(this), {
                tips: [flag, '#398fea'],
                time: 0
            });
        }
    }).on('mouseleave', '.title-tips', function () {
        layer.close(titleTips);
    });

    //起止时间选择
    $('.start-end').each(function () {
        var min = $(this).attr('data-min');
        min = min ? min : '1970-01-01 08:00:00';
        var max = $(this).attr('data-max');
        max = max ? max : '2099-12-31 23:59:59';
        layDate.render({
            elem: this
            , type: 'datetime'
            , range: '~'
            , format: 'yyyy-MM-dd HH:mm:ss'
            , trigger: 'click'
            , min: min
            , max: max
            , theme: '#398fea'
        });
    });

    //时间选择
    $('.laydate-time').each(function () {
        var min = $(this).attr('data-min');
        min = min ? min : '1970-01-01 08:00:00';
        var max = $(this).attr('data-max');
        max = max ? max : '2099-12-31 23:59:59';
        layDate.render({
            elem: this
            , type: 'datetime'
            , trigger: 'click'
            , min: min
            , max: max
            , theme: '#398fea'
        });
    });

    //监听表单提交
    layForm.on('submit(formSubmit)', function (data) {
        var datas = data.field,
            O = $(this),
            url = O.attr('data-url'),
            back = O.attr('data-back'),
            curid = O.attr('data-id');
        curid = !curid ? 0 : curid;
        datas['id'] = curid;
        var Request = getRequest();
        if (Request['p'] > 0) {
            back = urlAddParam(back, 'p', Request['p']);
        }
        var notice = '操作成功！';
        postAjax(O, hostUrl+url, datas, notice, back, 1);
        //执行相应方法
        var fn = O.attr('data-fn');
        if (fn) {
            eval(fn)();
        }
        return false;
    });

    //删除选择人员
    BODY.on('click', '.checked_users > li', function () {
        var p_ul = $(this).parent('.checked_users');
        var input = p_ul.next('input');
        $(this).remove();
        var ids = ',';
        p_ul.children('li').each(function () {
            var id = $(this).attr('data-id');
            if (id > 0) {
                ids += id + ',';
            }
        });
        if (ids == ',') {
            p_ul.html(input.attr('placeholder'));
            input.val('');
        } else {
            input.val(ids);
        }
        return false;
    });

    //删除分类
    BODY.on('click', '.btn-del', function () {
        var obj=$(this);
        var id=obj.attr('data-id');
        var url=obj.attr('data-url');
        $.post(url, {id:id}, function (d) {
            console.log(d)
            if(d==1){
                myLayer('删除成功', 1);
                location.reload();
            }else{
                myLayer(d.msg, 2);
            }

        });
    });
});


//底部提交按钮自动处理
function bottom_auto() {
    setTimeout(function () {
        var treeH = $('.tree-right').height();
        var btomH = $('.right-bottom').height();
        var mainH = $('.rt_wrap').height();
        if (treeH + btomH > mainH) {
            $('.right-bottom').addClass('right-bottom-fixed');
        } else {
            $('.right-bottom').removeClass('right-bottom-fixed');
        }
    }, 500);
}

//高度自动处理
function h_auto() {
    var h = $('.body-box').height();
    top.reinitIframe(h);
}


//post传值
function postAjax(O, postUrl, data, notice, backUrl, isBack) {
    var able = O.attr('able');
    if (able != 0) {
        O.attr('able', 0);
        if (isBack) {
            $.post(postUrl, data, function (d) {
                if (isNaN(d)) {
                    myLayer(d, 2);
                    O.attr('able', 1);
                } else {
                    myLayer(notice, 1);
                    var open = O.attr('data-open');
                    if (open == 'self') {
                        if (O.attr('data-fresh') == 1) {
                            //刷新左侧菜单
                            parent.freshMenu(5);
                        }
                        if (backUrl) {
                            location.href = backUrl;
                        } else {
                            location.reload();
                        }
                    } else {
                        if (O.attr('data-fresh') == 1) {
                            //刷新左侧菜单
                            parent.parent.freshMenu(5);
                        }
                        if (backUrl) {
                            parent.location.href = backUrl;
                        } else {
                            parent.location.reload();
                        }
                        setTimeout(function () {
                            layer_close_iframe();
                        }, 1500);
                    }
                }
            });
        } else {
            $.post(postUrl, data);
            O.attr('able', 1);
        }
    }
}

//自定义弹出层确认框
function self_layer_confirm(text, funOk, funCancel) {
    top.layer.confirm(text, {icon: 3, title: '消息提示', closeBtn: 0, btnAlign: 'c'}, function (index) {
        if (typeof funOk == 'function') funOk();
        top.layer.close(index);
        return true;
    }, function (index) {
        if (typeof funCancel == 'function') funCancel();
        top.layer.close(index);
        return false;
    });

}

//自定义弹出层成功
function self_layer_msg(text, flag, time) {
    var icon;
    var color;
    switch (flag) {
        case 1: //成功
            icon = '/static/admin/images/success.png';
            color = '#7CC34D';
            break;
        case 2: //失败
            icon = '/static/admin/images/failure.png';
            color = '#F75A53';
            break;
        default: //提示
            icon = '/static/admin/images/tips.png';
            color = '#F8B551';
    }
    time = time > 0 ? time : 2000;
    top.layer.open({
        type: 1,
        title: false,
        closeBtn: 0, //不显示关闭按钮
        shade: [0],
        area: ['100%', '60px'],
        offset: 't', //顶部弹出
        time: time, //2秒后自动关闭
        anim: 5,
        shadeClose: time > 0 ? true : false,
        content: '<div class="top_layer" style="color:' + color + ' "><img src="' + icon + '" />' + text + '</div>'
    })
}

//弹窗提示
function myLayer(msg, msgType, time) {
    msgType = msgType ? msgType : 0;
    if (typeof(layer.msg) == 'function') {
        self_layer_msg(msg, msgType, time);
    } else if (typeof(layer.open) == 'function') {
        var t = time > 2 ? time : 2;
        layer.open({content: msg, shadeClose: false, time: t});
    } else {
        alert(msg);
    }
}

//关闭iframe弹出层
function layer_close_iframe(closeAll, fresh) {
    if (closeAll) {
        parent.layer.closeAll()
    } else {
        var index = parent.layer.getFrameIndex(window.name);
        parent.layer.close(index)
    }
    if (fresh) {
        parent.location.reload();
    }
    top.reinitIframe();
}

//弹出iframe层
function layer_open_iframe(obj, backoldPage) {
    var curPage = $('.layui-laypage-curr>em:eq(1)').html();
    var href = obj.attr('data-href');
    if (curPage > 1 && backoldPage) {
        href = urlAddParam(href, 'p', curPage);
    }
    var area_w = obj.attr('data-w');
    var area_h = obj.attr('data-h');
    var close = obj.attr('data-close') ? 1 : 0;
    var title;
    if (close == 1) {
        title = obj.attr('data-title') ? obj.attr('data-title') : '　'
    } else {
        title = obj.attr('data-title') || close ? obj.attr('data-title') : 0;
    }
    var open = obj.attr('data-open');
    var area = ['100%', '100%'];
    if (area_w && area_h) {
        if (area_w > 0 && area_h > 0) {
            area = [area_w + 'px', area_h + 'px'];
        } else {
            area = [area_w, area_h];
        }
    }
    switch (open) {
        case 'top':
            top.layer.open({
                type: 2,
                title: title,
                shadeClose: false,
                shade: 0.3,
                anim: false,
                area: area,
                content: href,
                closeBtn: close
            });
            break;
        case 'parent':
            var oldNum = parent.layer.getFrameIndex(window.name);
            parent.layer.open({
                type: 2,
                title: title,
                shadeClose: false,
                shade: 0.3,
                anim: false,
                area: area,
                content: href,
                closeBtn: close,
                success: function (layero, index) {
                    try {
                        //关闭之前的iframe窗
                        parent.layer.close(oldNum);

                        //获取iframe的body元素
                        var body = parent.layer.getChildFrame('.body-box', index);
                        console.log(body.height());
                        top.reinitIframe(body.height());
                    } catch (err) {
                        console.log('error');
                    }
                }
            });
            break;
        default:
            layer.open({
                type: 2,
                title: title,
                shadeClose: false,
                shade: 0.3,
                anim: false,
                area: area,
                content: href,
                closeBtn: close,
                success: function (layero, index) {
                    //获取iframe的body元素
                    if (typeof top.reinitIframe === "function") { //是函数
                        var body = layer.getChildFrame('.body-box', index);
                        top.reinitIframe(body.height());
                    }
                }
            });
    }
    return false;
}

//显示地址选择列表
function showAddressList(obj, rank) {
    var address_select_val = $('#address_select_val');
    var province_id = parseInt(address_select_val.attr('data-province_id'));
    var city_id = parseInt(address_select_val.attr('data-city_id'));
    var county_id = parseInt(address_select_val.attr('data-county_id'));
    var town_id = parseInt(address_select_val.attr('data-town_id'));
    var url = hostUrl + '/index/getAddressList';
    rank = rank > 0 ? rank : 3;
    if (obj) { //重新选择后
        var curid = obj.attr('id');
        var pid = parseInt(obj.val());
        address_select_val.attr('data-' + curid, pid);
        var nextid;
        var curRank = 1;
        switch (curid) {
            case 'province_id':
                nextid = 'city_id';
                address_select_val.attr('data-city_id', 0);
                address_select_val.attr('data-county_id', 0);
                address_select_val.attr('data-town_id', 0);
                curRank = 1;
                break;
            case 'city_id':
                nextid = 'county_id';
                address_select_val.attr('data-county_id', 0);
                address_select_val.attr('data-town_id', 0);
                curRank = 2;
                break;
            case 'county_id':
                nextid = 'town_id';
                address_select_val.attr('data-town_id', 0);
                curRank = 3;
                break;
            default:
                nextid = '';
        }
        if (pid > 0 && curRank < rank) {
            var load = layer.load(2);
            $.post(url, {pid: pid}, function (data) {
                if (data) {
                    var str = '<select class="select address_select" id="' + nextid + '">';
                    str += '<option value="-1">请选择</option>';
                    $.each(data, function (i, item) {
                        str += '<option value="' + item['id'] + '">' + item['name'] + '</option>';
                    });
                    str += '</select>';
                    if (nextid == 'province_id') {
                        str += '<select class="select address_select" id="city_id"><option value="-1">请选择</option></select>';
                        str += '<select class="select address_select" id="county_id"><option value="-1">请选择</option></select>';
                    } else if (nextid == 'city_id' && rank > 2) {
                        str += '<select class="select address_select" id="county_id"><option value="-1">请选择</option></select>';
                    }
                    obj.nextAll().remove();
                    $('#address_box').append(str);
                }
                layer.close(load);
            }, 'json')
        } else {
            obj.nextAll().remove();
        }
    } else { //默认显示
        //省份
        $.ajax({
            type: "post",
            url: url,
            data: {pid: 1},
            async: false,
            dataType: 'json',
            success: function (data) {
                var str = '<select class="select address_select" id="province_id">';
                str += '<option value="-1">请选择</option>';
                if (data) {
                    $.each(data, function (i, item) {
                        if (item['id'] == province_id) {
                            str += '<option value="' + item['id'] + '" selected="selected">' + item['name'] + '</option>';
                        } else {
                            str += '<option value="' + item['id'] + '">' + item['name'] + '</option>';
                        }
                    });
                } else {
                    str += '<option value="0">暂无数据！</option>';
                }
                str += '</select>';
                $('#address_box').append(str);
            }
        });

        //城市
        if (rank > 1) {
            if (province_id > 0) {
                $.ajax({
                    type: "post",
                    url: url,
                    data: {pid: province_id},
                    async: false,
                    dataType: 'json',
                    success: function (data) {
                        if (data.length > 0) {
                            var str = '<select class="select address_select" id="city_id">';
                            str += '<option value="-1">请选择</option>';
                            $.each(data, function (i, item) {
                                if (item['id'] == city_id) {
                                    str += '<option value="' + item['id'] + '" selected="selected">' + item['name'] + '</option>';
                                } else {
                                    str += '<option value="' + item['id'] + '">' + item['name'] + '</option>';
                                }
                            });
                            str += '</select>';
                            $('#address_box').append(str);
                        }
                    }
                });
            } else {
                var str = '<select class="select address_select" id="city_id">';
                str += '<option value="-1">请选择</option>';
                str += '</select>';
                $('#address_box').append(str);
            }
        }

        //区县
        if (rank > 2) {
            if (city_id > 0) {
                $.ajax({
                    type: "post",
                    url: url,
                    data: {pid: city_id},
                    async: false,
                    dataType: 'json',
                    success: function (data) {
                        if (data.length > 0) {
                            var str = '<select class="select address_select" id="county_id">';
                            str += '<option value="-1">请选择</option>';
                            $.each(data, function (i, item) {
                                if (item['id'] == county_id) {
                                    str += '<option value="' + item['id'] + '" selected="selected">' + item['name'] + '</option>';
                                } else {
                                    str += '<option value="' + item['id'] + '">' + item['name'] + '</option>';
                                }
                            });
                            str += '</select>';
                            $('#address_box').append(str);
                        }
                    }
                });
            } else {
                var str = '<select class="select address_select" id="county_id">';
                str += '<option value="-1">请选择</option>';
                str += '</select>';
                $('#address_box').append(str);
            }
        }

        //街道
        if (county_id > 0 && rank > 3) {
            $.post(url, {pid: county_id}, function (data) {
                if (data) {
                    var str = '<select class="select address_select" id="town_id">';
                    str += '<option value="-1">请选择</option>';
                    $.each(data, function (i, item) {
                        if (item['id'] == town_id) {
                            str += '<option value="' + item['id'] + '" selected="selected">' + item['name'] + '</option>';
                        } else {
                            str += '<option value="' + item['id'] + '">' + item['name'] + '</option>';
                        }
                    });
                    str += '</select>';
                    $('#address_box').append(str);
                }
            }, 'json');
        }
    }
}

//获取ULR地址参数
function getRequest() {
    var url = location.search; //获取url中"?"符后的字串
    var theRequest = {};
    if (url.indexOf("?") != -1) {
        var str = url.substr(1);
        var strs = str.split("&");
        for (var i = 0; i < strs.length; i++) {
            theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
        }
    }
    return theRequest;
}

//初始化数据表
function initTable(tableId, url, cols, showPage) {
    var curPage = 0;
    var BODY = $('body');
    var page;
    if (showPage === false) {
        page = false;
    } else {
        var Request = getRequest();
        page = {
            curr: Request['p'] > 1 ? Request['p'] : 1
        };
    }

    //初始化
    layTable.render({
        elem: '#' + tableId
        , url: url //数据接口
        , size: layTableConfig['size'] //小字号
        , limit: layTableConfig['limit'] //每页显示的条数
        , method: 'post'
        , cellMinWidth: 30
        , cols: cols
        , skin: 'line'
        , text: {
            none: layTableConfig['none']
        }
        , page: page
        , done: function (res, curr, count) {
            if (res['msg'] == 'token_expire') {
                window.open(hostUrl, '_top');
                return false;
            }
            //禁止选中
            var hasChecked = false;
            $(".layui-table-body.layui-table-main").find("input[name='layTableCheckbox']").each(function (i) {
                if (res.data[i].disabled) {
                    $(this).attr('disabled', true).prop('checked', false);
                    layForm.render('checkbox');
                } else {
                    hasChecked = true;
                }
            });
            $(".layui-table-header").find("input[name=layTableCheckbox][lay-filter='layTableAllChoose']").each(function () {
                $(this).attr('disabled', !hasChecked).prop('checked', false);
                layForm.render('checkbox');
            });
            if (!hasChecked) {
                //$('.batch-btn').addClass('layui-btn-disabled').attr('data-msg','没有可操作的选项!');
            } else {
                $('.batch-btn').each(function () {
                    $(this).removeClass('layui-btn-disabled');
                    var oldmsg = $(this).attr('data-old-msg');
                    if (oldmsg) {
                        $(this).attr('data-msg', oldmsg);
                    }
                });
            }
            //页面高度改变
            if (typeof top.reinitIframe === "function") { //是函数    其中 FunName 为函数名称
                var h = $('.body-box').height();
                top.reinitIframe(h);
            }
            curPage = $('.layui-laypage-curr>em:eq(1)').html();

            //滚动条处理
            $('.layui-table-main').each(function () {
                var childW = $(this).children('table').width();
                var pboxW = $(this).parent('.layui-table-box').width();
                if (childW - pboxW > 10) {
                    layTable.reload(tableId);  //执行重载
                } else {
                    if (childW > 0) {
                        $(this).parent('.layui-table-box').css({'width': childW});
                    }
                }
            })
        }
    });

    //全部选中
    layTable.on('checkbox(' + tableId + ')', function (obj) {
        if (obj.type == 'all' && obj.checked == true) {
            var hasChecked = false;
            var render = false;
            $(".layui-table-body.layui-table-main").find("input[name=layTableCheckbox]").each(function () {
                if ($(this).attr('disabled')) {
                    $(this).prop('checked', false);
                    render = true;
                } else {
                    hasChecked = true;
                }
            });
            if (!hasChecked) {
                $(".layui-table-header").find("input[name=layTableCheckbox][lay-filter='layTableAllChoose']").each(function () {
                    $(this).prop('checked', false);
                    render = true;
                    myLayer('没有可选中的选项！', 0);
                });
            }
            if (render) {
                layForm.render('checkbox');
            }
        }
    });

    //点击搜索重载数据
    BODY.on('click', '.reload-btn', function () {
        var key = {};
        $('.reload-search').each(function () {
            var name = $(this).attr('data-name');
            var type = $(this).attr('data-type');
            var value = $(this).val();
            key[name] = {type: type, value: value}
        });
        //执行重载
        layTable.reload(tableId, {
            page: {
                curr: 1 //重新从第 1 页开始
            }
            , where: {
                key: key
            }
        });
    });

    //监听工具条
    layTable.on('tool(' + tableId + ')', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
        var O = $(this);
        if (!$(this).hasClass('layui-btn-disabled')) {
            switch (obj.event) {
                case 'edit':
                case 'detail':
                    layer_open_iframe(O, true);
                    break;
                case 'del':
                    var msg = O.attr('data-msg') ? O.attr('data-msg') : '确定要删除吗？';
                    self_layer_confirm(msg, function () {
                        var load = layer.load(2);
                        var id = O.attr('data-id'),
                            url = O.attr('data-href'),
                            data = {id: id};
                        $.post(hostUrl+url, data, function (d) {
                            if (isNaN(d)) {
                                myLayer(d, 2);
                            } else {
                                myLayer('操作成功！', 1);
                                var trLen = layTable['cache'][tableId].length; //当前数据的条数
                                var options = {};
                                if (trLen <= 1 && msg == '确定要删除吗？') {
                                    options = {
                                        page: {
                                            curr: curPage > 1 ? (curPage - 1) : 1
                                        }
                                    }
                                }
                                layTable.reload(tableId, options);  //执行重载
                                if (O.attr('data-fresh') == 1) {
                                    //刷新左侧菜单
                                    parent.freshMenu(5);
                                }
                                //删除后执行相应方法
                                var fn = O.attr('data-fn');
                                if (fn) {
                                    eval(fn)();
                                }
                            }
                            layer.close(load);
                        });
                    });
                    break;
                case 'pay'://缴费已收
                    self_layer_confirm('确定已收到' + obj.data.realname + '的费用了吗？', function () {
                        var load = layer.load(2);
                        var id = O.attr('data-id'),
                            url = O.attr('data-href'),
                            data = {id: id};
                        $.post(hostUrl+url, data, function (d) {
                            if (isNaN(d)) {
                                myLayer(d, 2);
                            } else {
                                myLayer('操作成功！', 1);
                                layTable.reload(tableId);  //执行重载
                            }
                            layer.close(load);
                        });
                    });
                    break;
                case 'layer':
                    layer_submit(false, O);
                    break;
                case 'bound':
                    var title = '绑定医嘱ID';
                    var id = O.attr('data-id');
                    layer.open({
                        type: 1,
                        title: title,
                        closeBtn: 1,
                        area: ['500px', 'auto'],
                        shade: 0.3,
                        anim: 5,
                        id: 'LAY_layuipro', //设定一个id，防止重复弹出
                        btn: false,
                        offset: ['200px', '245px'],
                        moveType: 0,//拖拽模式，0或者1
                        content: $('#layer_bound').html(),
                        success: function () {
                            //执行
                            $('body').on('click', '#LAY_layuipro .bound-btn', function () {
                                if ($(this).attr('able') != 0) {
                                    $(this).attr('able', 0);
                                    var advice_id = $('#LAY_layuipro input[name=advice_id]').val();
                                    if (!advice_id) {
                                        myLayer('请填写要绑定的医嘱ID', 2);
                                        $(this).removeAttr('able');
                                        return false;
                                    }
                                    var url = $(this).attr('data-href');
                                    var data = {id: id, advice_id: advice_id};
                                    $.post(hostUrl+url, data, function (d) {
                                        if (isNaN(d)) {
                                            myLayer(d, 2, 3000);
                                        } else {
                                            myLayer('操作成功！', 1);
                                            layTable.reload(tableId);  //执行重载
                                        }
                                        layer.closeAll();
                                        $(this).removeAttr('able');
                                    });
                                }
                            })
                        }
                    });
                    break;
                case 'repair':
                    var title = '处理跟踪';
                    var id = O.attr('data-id');
                    layer.open({
                        type: 1,
                        title: title,
                        closeBtn: 1,
                        area: ['500px', 'auto'],
                        shade: 0.3,
                        anim: 5,
                        id: 'LAY_layuipro', //设定一个id，防止重复弹出
                        btn: false,
                        offset: ['200px', '245px'],
                        moveType: 0,//拖拽模式，0或者1
                        content: $('#layer_repair').html(),
                        success: function () {
                            layForm.render();
                            //执行
                            $('body').on('click', '#LAY_layuipro .repair-btn', function () {
                                if ($(this).attr('able') != 0) {
                                    $(this).attr('able', 0);
                                    var status = $('#LAY_layuipro input[name=status]:checked').val();
                                    var summary = $('#LAY_layuipro textarea[name=summary]').val();
                                    if (!summary) {
                                        myLayer('请填写进度说明！', 2);
                                        $(this).removeAttr('able');
                                        return false;
                                    }

                                    var url = $(this).attr('data-href');
                                    var data = {id: id, status: status, summary: summary};
                                    $.post(hostUrl+url, data, function (d) {
                                        if (isNaN(d)) {
                                            myLayer(d, 2, 3000);
                                        } else {
                                            myLayer('操作成功！', 1);
                                            layTable.reload(tableId);  //执行重载
                                        }
                                        layer.closeAll();
                                        $(this).removeAttr('able');
                                    });
                                }
                            })
                        }
                    });
                    break;
                case 'storeErCode':
                    var title = O.attr('data-title');
                    var id = O.attr('data-id');
                    var url = O.attr('data-url');
                    var str='<div style="text-align: center;height: 330px"><img width="260" height="260" style="margin: 10px auto" src="'+url+'" id="qr_code"><p style="text-align: center;padding-bottom: 10px"><a  class="layui-btn layui-btn-normal" href="'+url+'" download="qr_code'+id+'.png">下载二维码</a></p></div>';
                    layer.open({
                        type: 1,
                        title: title,
                        closeBtn: 1,
                        area: ['300px', 'auto'],
                        shade: 0.3,
                        anim: 5,
                        id: 'LAY_layuipro', //设定一个id，防止重复弹出
                        btn: false,
                        offset: ['100px', '245px'],
                        moveType: 0,//拖拽模式，0或者1
                        content: str,
                        success: function () {
                            layForm.render();
                            //执行
                            $('body').on('click', '#LAY_layuipro .repair-btn', function () {
                                if ($(this).attr('able') != 0) {
                                    $(this).attr('able', 0);
                                    var status = $('#LAY_layuipro input[name=status]:checked').val();
                                    var summary = $('#LAY_layuipro textarea[name=summary]').val();
                                    if (!summary) {
                                        myLayer('请填写进度说明！', 2);
                                        $(this).removeAttr('able');
                                        return false;
                                    }

                                    var url = $(this).attr('data-href');
                                    var data = {id: id, status: status, summary: summary};
                                    $.post(hostUrl+url, data, function (d) {
                                        if (isNaN(d)) {
                                            myLayer(d, 2, 3000);
                                        } else {
                                            myLayer('操作成功！', 1);
                                            layTable.reload(tableId);  //执行重载
                                        }
                                        layer.closeAll();
                                        $(this).removeAttr('able');
                                    });
                                }
                            })
                        }
                    });
                    break;
                default:
                    return false;
            }
        }
    });

    //监听switch改变状态
    layForm.on('switch(setZdVal)', function (obj) {
        setZdVal($(this), obj);
    });

    //监听checkbox改变状态
    layForm.on('checkbox(setZdVal)', function (obj) {
        setZdVal($(this), obj);
    });

    //改变状态
    function setZdVal(O, obj) {
        var id = O.val();
        var name = O.attr('name');
        var tb = O.attr('data-tb');
        var timeZd = O.attr('data-timeZd'); //操作时间（比如置顶时间）
        var url = hostUrl + '/index/setZdVal.html';
        var data = {
            'tb': tb,
            'id': id,
            'zdName': name,
            'timeZd': timeZd
        };
        if (obj.elem.checked) {
            data['zdVal'] = O.attr('data-true');
        } else {
            data['zdVal'] = O.attr('data-false');
        }
        $.post(hostUrl+url, data, function (d) {
            if (isNaN(d)) {
                myLayer(d, 2);
            } else {
                myLayer('操作成功！', 1, 500);
            }
        });
    }

    //点击批量操作
    BODY.on('click', '.batch-btn', function () {
        var O = $(this);
        if (!O.hasClass('layui-btn-disabled')) {
            var checkStatus = layTable.checkStatus(tableId)
                , data = checkStatus.data;
            var msg = O.attr('data-msg') ? O.attr('data-msg') : '确定要删除选中项吗？';
            if (msg == 'check') {
                layer_check(data, O);
                return false;
            } else if (msg == 'import') {
                layer_import(data, O);
                return false;
            } else if (msg == 'layer') {
                layer_submit(data, O);
                return false;
            }
            var ids = "";
            var actNum = 0;
            $.each(data, function (i, item) {
                if (!item['disabled']) {
                    ids += item['id'] + ',';
                    actNum++;
                }
            });
            if (ids.length == 0) {
                myLayer('请选择要操作的选项！');
                return false;
            } else {
                ids = ids.substr(0, ids.length - 1);
            }
            self_layer_confirm(msg, function () {
                var load = layer.load(2);
                var url = O.attr('data-href');
                if (msg == '确定要导出选中的数据吗？') {
                    $.post(hostUrl+url, {id: ids}, function (d) {
                        layer.close(load);
                        window.open(hostUrl + this.url + '?id=' + ids, '_blank');
                    });
                } else {
                    $.post(hostUrl+url, {id: ids}, function (d) {
                        if (isNaN(d)) {
                            myLayer(d, 2);
                        } else {
                            myLayer("操作成功！", 1);
                            if (O.attr('data-fresh') == 1) {
                                //刷新左侧菜单
                                parent.freshMenu(5);
                            }
                            //删除后执行相应方法
                            var fn = O.attr('data-fn');
                            if (fn) {
                                eval(fn)();
                            }
                            var trLen = layTable['cache'][tableId].length; //当前数据的条数
                            var options = {};
                            if (trLen == actNum && msg == '确定要删除选中项吗？') {
                                options = {
                                    page: {
                                        curr: curPage > 1 ? (curPage - 1) : 1
                                    }
                                }
                            }
                            layTable.reload(tableId, options);  //执行重载
                        }
                        layer.close(load);
                    });
                }
            });
        }
    });
}

//弹出审核层
function layer_check(data, O) {
    var ids = "";
    var users = "";
    $.each(data, function (i, item) {
        if (!item['disabled']) {
            ids += item['id'] + ',';
            var name = item['realname'] ? item['realname'] : item['nickname'];
            users += '<span class="checked_user">' + name + '</span>';
        }
    });
    if (ids.length == 0) {
        myLayer('请选择要操作的选项！');
        return false;
    } else {
        ids = ids.substr(0, ids.length - 1);
    }
    $('#users').html(users);
    $('.btn-sub').attr('data-id', ids);
    var title = O.attr('data-title') ? O.attr('data-title') : '　';
    layer.open({
        type: 1,
        title: title,
        closeBtn: 1,
        area: ['70%', '60%'],
        shade: 0.3,
        anim: 5,
        id: 'LAY_layuipro', //设定一个id，防止重复弹出
        btn: false,
        moveType: 0,//拖拽模式，0或者1
        content: $('#layer_check').html()
    });
    layForm.render();
}

//导出导入层
function layer_import(data, O) {
    var title = O.attr('data-title') ? O.attr('data-title') : '　';
    var import_index = layer.open({
        type: 1,
        title: title,
        closeBtn: 1,
        area: ['500px', 'auto'],
        shade: 0.3,
        anim: 5,
        id: 'LAY_layuipro', //设定一个id，防止重复弹出
        btn: false,
        offset: ['200px', '245px'],
        moveType: 0,//拖拽模式，0或者1
        content: $('#layer_import').html(),
        success: function () {
            $('#LAY_layuipro').find('.upload-btn').attr('id', 'upload-btn');
            //执行
            var load;
            layUpload.render({
                elem: '#upload-btn' //绑定元素
                , url: $('#upload-btn').attr('data-url') //上传接口
                , accept: 'file'
                , before: function () {
                    this.url = $('#upload-btn').attr('data-url'); //兼容url地址会动态改变的情况
                    load = top.layer.load(2);
                    layer.close(import_index);
                }
                , done: function (res) {
                    //上传完毕回调
                    if (res.msg == 'ok') {
                        myLayer('导入成功！', 1);
                        initTable(tableId, hostUrl+url, cols);
                    } else {
                        myLayer(res.msg, 2);
                    }
                    top.layer.close(load);
                    eval('hide_file')();
                }
            });
            //执行相应方法
            // var fn = O.attr('data-fn');
            // if(fn){
            //     eval(fn)();
            // }
        }
    });
}

//批量处理提交
function layer_submit(data, O) {
    var ids = "";
    var users = "";
    if (data === false) {
        ids = O.attr('data-id');
        users = '<span class="checked_user">' + O.attr('data-name') + '</span>';
    } else {
        $.each(data, function (i, item) {
            if (!item['disabled']) {
                ids += item['id'] + ',';
                users += '<span class="checked_user">' + item['name'] + '</span>';
            }
        });
        if (ids.length == 0) {
            myLayer('请选择要操作的选项！');
            return false;
        } else {
            ids = ids.substr(0, ids.length - 1);
        }
    }
    $('#users').html(users);
    $('.btn-sub').attr('data-id', ids);
    var title = O.attr('data-title') ? O.attr('data-title') : '　';
    layer.open({
        type: 1,
        title: title,
        closeBtn: 1,
        area: ['70%', '60%'],
        shade: 0.3,
        anim: 5,
        id: 'LAY_layuipro', //设定一个id，防止重复弹出
        btn: false,
        moveType: 0,//拖拽模式，0或者1
        content: $('#layer_sumbit').html()
    });
    layForm.render();
}
