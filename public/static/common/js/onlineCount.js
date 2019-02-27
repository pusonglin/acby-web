$(function(){
    //更新直播间人数
    function update_live(){
        var numbox = $('.live_online_number'),
            url = $('#live_online_url').val();
        numbox.each(function(){
            var o = $(this);
            var curid = o.attr('curid');
            var addcur = o.attr('add-cur');
            var data = {id:curid,addCur:addcur};
            $.post(url,data,function(d){
                o.animate({count:d},{
                    duration:500,
                    step:function(){
                        o.html(String(parseInt(this.count)));
                    }
                });
            })
        })
    }

    //获取直播间在线人数
    update_live();
    setInterval(function(){
        update_live();
    },5000); //5秒钟执行一次
});