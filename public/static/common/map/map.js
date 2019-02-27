addCss('/static/common/map/map.css');
addJs('http://webapi.amap.com/maps?v=1.3&key=3126db922493bc16102948da1100a35d&plugin=AMap.Autocomplete');
function loadMap(obj,ID){
    var box_html = '<div id="container">';
    box_html += 		'<div id="loglat">';
    box_html += 			'经度：<input type="text" id="longitude" readonly="readonly"/>';
    box_html += 			'纬度：<input type="text" id="latitude" readonly="readonly"/>';
    box_html += 		'</div>';
    box_html += 		'<div id="search">';
    box_html += 			'<p>搜索关键字：<input type="text" id="tipinput"/></p>';
    box_html += 		'<div>';
    box_html += 			'<ul id="result"></ul>';
    box_html += 		'</div>';
    box_html += 	'</div>';
    box_html += '</div>';
    $('#'+ID).html(box_html);
    var old_location = $(obj).parent('div').find('input').val();
    old_location = old_location ? old_location : '';
    last_marker = [];
    lnglat = '';
    map = new AMap.Map('container', {
        resizeEnable: true,
        zoom:11
    });
    if(old_location){
        location_arr = old_location.split(',');
        if(location_arr.length == 2){
            addMarker(location_arr[0],location_arr[1]);
        }
    }
    var clickEventListener = map.on('click', function(e) {
        addMarker(e.lnglat.getLng(),e.lnglat.getLat());
    });
    $('#tipinput').keyup(function(){
        var keywords = $(this).val();
        if(keywords.length > 0){
            var autoOptions = {
                city: "成都",
                map:map
            };
            autocomplete= new AMap.Autocomplete(autoOptions);
            autocomplete.search(keywords, function(status, result){
                if(status == 'complete'){
                    var data = result.tips;
                    var str = '';
                    $.each(data,function(i,item){
                        if(item.location){
                            str += '<li class="address_list">';
                            str += '<span class="sname">'+item.name+'</span>' ;
                            str += ' <span class="saddress">'+item.district+'</span>';
                            str += '<input type="hidden" value="'+item.location+'" class="lnglat" />';
                            str += '</li>';
                        }
                    });
                    $('#result').html(str);
                }else{
                    $('#result').html('');
                }
            });
        }else{
            $('#result').html('');
        }
        $('#result').show();
    });
    $('#result').on('click','.address_list',function(){
        $('#result').find('.sname').removeClass('current');
        $(this).find('.sname').addClass('current');
        var lnglat = $(this).find('.lnglat').val();
        lnglat = lnglat.split(',');
        addMarker(lnglat[0],lnglat[1]);
        $('#result').hide();
    });
    function addMarker(lng,lat) {
        if(last_marker){
            map.remove(last_marker);
        }
        var position = [lng, lat];
        marker = new AMap.Marker({
            map:map,
            position : position
        });
        $('#longitude').val(lng);
        $('#latitude').val(lat);
        lnglat = lng+','+lat;
        last_marker = [marker];
        map.setZoomAndCenter(15, position);
    }

    //layer
    layer.open({
        type: 1,
        offset: 't', //顶部弹出
        shade: [0],
        title: '请在地图上标出具体位置',
        content: $('#container'),
        area : ['800px','600px'],
        btn : ['我标好了'],
        btnAlign : 'c',
        yes : function(index){
            if(lnglat.length > 0){
                $(obj).parent('div').find('input').val(lnglat);
                layer.close(index);
            }else{
                layer.confirm('您还没有选择坐标，确认离开吗？',{
                    title: '消息提示',
                    btn: ['确定','取消'], //按钮
                    icon: 3
                },function(index1){
                    layer.closeAll();
                });
            }
        }
    });
}

function addCss(url) {
    var link = document.createElement('link');
    link.type = 'text/css';
    link.rel = 'stylesheet';
    link.href = url;
    document.getElementsByTagName("head")[0].appendChild(link);
}
function addJs(url){
    var body = document.getElementsByTagName('body')[0];
    var script = document.createElement('script');
    script.src = url;
    script.type = 'text/javascript';
    body.appendChild(script);
}
