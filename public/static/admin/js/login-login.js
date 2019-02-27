$(function(){
    //关闭提示
    $('input').keydown(function () {
        layer.closeAll();
    }).click(function () {
        layer.closeAll();
    });

    //用户登录
    $('#sub_btn').off('click').on('click',function () {
        var O = $(this),
            able = O.attr('able');
        if(able!=0){
            var user = $('.login-user'),
                userval = user.val(),
                pwd = $('.login-pwd'),
                pwdval = pwd.val(),
                yzm = $('.login-yzm'),
                yzmval = yzm.val(),
                data = {},
                flag = true;
            //用户名
            if(userval){
                data['user'] = userval;
            }else{
                flag = regError('请输入用户名',user);
            }
            //密码
            if(flag){
                if(pwdval){
                    data['pwd'] = pwdval;
                }else{
                    flag = regError('请输入登录密码',pwd);
                }
            }
            //验证码
            if(flag){
                if(yzmval){
                    data['yzm'] = yzmval;
                }else{
                    flag = regError('请输入验证码',yzm);
                }
            }
            //提交
            if(flag){
                var url = hostUrl+'/login/doLogin.html';
                O.attr('able',0);
                $.post(url,data,function(d){
                    if(d==1){
                        O.html('登录中&nbsp;<i class="layui-icon layui-anim layui-anim-rotate layui-anim-loop layui-icon-loading"></i>');
                        setTimeout(function(){
                            location.href = hostUrl;
                        },1500);
                    }else if(d=='yzm_error'){
                        regError('验证码不正确',yzm);
                    }else if(d==2){
                        regError('密码不匹配，请重新输入',pwd);
                    }else if(d==3){
                        regError('该账户已经被禁止使用，请联系管理员',user);
                    }else if(d==4){
                        regError('该账户已经被冻结，请联系管理员',user);
                    }else if(d==5){
                        regError('用户名不存在。',user);
                    }else{
                        myLayer(d,2);
                    }
                    if(d!=1){
                        O.attr('able',1);
                        yzm.val('');
                        $('.ts_yzm>img').click();
                    }
                })
            }
        }
    });

    //按回车键执行
    $(document).keydown(function(event){
        if(event.keyCode == 13){
            $('#sub_btn').click();
        }
    });


    //针对IE浏览器中placeholder属性无效的处理
    //jQuery placeholder, fix for IE6,7,8,9
    var JPlaceHolder = {
        //检测
        _check : function(){
            return 'placeholder' in document.createElement('input');
        },
        //初始化
        init : function(){
            if(!this._check()){
                this.fix();
            }
        },
        //修复
        fix : function(){
            jQuery(':input[placeholder]').each(function() {
                var self = $(this), txt = self.attr('placeholder'),margintop = self.css('marginTop'),marginLeft = self.css('marginLeft'),w = self.innerWidth(),h = self.innerHeight(),paddingLeft = self.css('paddingLeft');
                self.css('marginTop',0);self.css('marginLeft',0);
                self.wrap($('<div></div>').css({position:'relative', zoom:'1', border:'none',width:w, height:h, background:'none', marginTop:margintop,marginLeft:marginLeft,float:'left'}));
                var holder = $('<p></p>').text(txt).css({position:'absolute', left:0, top:0, height:h+'px',lineHeight:h+'px',paddingLeft:paddingLeft,color:'fff'}).appendTo(self.parent());
                self.focusin(function() {
                    holder.hide();
                }).focusout(function() {
                    if(!self.val()){
                        holder.show();
                    }
                });
                holder.click(function() {
                    holder.hide();
                    self.focus();
                });
            });
        }
    };
    JPlaceHolder.init();
});


//验证报错方法
function regError(errorTs,cur){
    layer.tips(errorTs, cur, {
        tips: [3, '#EC625D'],
        time: 0,
        area:['auto','auto']
    });
    cur.css({'borderColor':'red'});
    cur.attr('data-error',1);
    cur.focus();
    return false;
}