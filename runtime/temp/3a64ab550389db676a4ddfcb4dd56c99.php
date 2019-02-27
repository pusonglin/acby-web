<?php /*a:2:{s:76:"D:\phpStudy\PHPTutorial\WWW\acby-web\application\admin\view\login\index.html";i:1551183784;s:76:"D:\phpStudy\PHPTutorial\WWW\acby-web\application\admin\view\public\base.html";i:1550823376;}*/ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>乡村振兴-登录中心</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="/static/common/font-awesome-4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="/static/common/mCustomScrollbar/jquery.mCustomScrollbar.css" />
    <link rel="stylesheet" type="text/css" href="/static/common/layui/css/layui.css" />
    <link rel="stylesheet" type="text/css" href="/static/admin/css/style.css" />
    
    <link rel="stylesheet" type="text/css" href="/static/common/particles/bgStyle.css" />
    <link rel="stylesheet" type="text/css" href="/static/theme/css/login.css" />
    <style type="text/css">
        .body-box{background: none;}
    </style>

    <link rel="icon" href="/static/common/img/favicon.ico" type="image/x-icon"/>
    <script type="text/javascript" src="/static/common/js/jquery-1.12.2.min.js"></script>
    <script type="text/javascript" src="/static/common/js/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="/static/common/js/jquery.pseudo.js"></script><!--before/after-->
    <script type="text/javascript" src="/static/common/mCustomScrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="/static/common/layui/layui.all.js"></script>
    <script type="text/javascript" src="/static/common/js/dotdotdot.js"></script>
    <script type="text/javascript" src="/static/common/js/common.js"></script>
    <script type="text/javascript" src="/static/admin/js/common.js"></script>
    <!--[if lt IE 9]>
    <script type="text/javascript" src="/static/common/html5.js"></script>
    <script type="text/javascript" src="/static/common/css3-mediaqueries.js"></script>
    <![endif]-->
    
</head>
<body>
    

    <div class="body-box">
        
    <div class="container login-container">
        <!--<div id="particles-js"></div>-->
        <!-- 动态云层动画 开始 -->
        <div class="clouds-container">
            <div class="clouds clouds-footer"></div>
            <div class="clouds"></div>
            <div class="clouds clouds-fast"></div>
        </div>
        <!-- 动态云层动画 结束 -->
        <div class="loginCount">
            <div class="login_logo fl">
                <div class="teng_Logo"><!--<img src="/static/admin/images/juva_logo_big.png"/>--></div>
                <div class="teng_font">
                    <h4>爱车保养</h4>
                    <p>后台管理端登录页面</p>
                </div>
                <div class="teng_foot">
                    &#169;www.songlinyun.club
                </div>
            </div><!--login_logo end-->
            <div class="login_inp fr">
                <div class="login_Tx">
                    <img src="/static/admin/images/logo.png"/>
                </div>

                <div class="login_wapper">
                    <div class="login_inputFile">
                        <ul>
                            <li>
                                <div class="width_310">  <input type="text" placeholder="用户名" class="login-user" maxlength="20"></div>
                            </li>
                            <li>
                                <div class="width_310  password"><input type="password" placeholder="登录密码" class="login-pwd" maxlength="20"></div>
                            </li>
                            <li>
                                <div class="width_180 fl"><input type="text" placeholder="验证码" class="login-yzm" maxlength="5"></div>
                                <a href="javascript:;" class="ts_yzm fr">
                                    <img width="100" height="35" src="<?php echo url('login/verify'); ?>" onclick="this.src='<?php echo url('login/verify'); ?>'">
                                </a>
                            </li>
                        </ul>
                    </div><!--login_inputFile end-->

                    <div class="loginBtn">
                        <button id="sub_btn">登录</button>
                    </div>
                </div><!--login_wapper end-->
            </div><!--login_inp end-->
        </div><!--loginCount end-->
    </div>

    </div>

    
    <script type="text/javascript" src="/static/admin/js/login-login.js"></script>
    <script type="text/javascript" src="/static/common/particles/particles.js"></script>
    <script type="text/javascript" src="/static/common/particles/app.js"></script>


    

    <DIV class=browser-updator style='HEIGHT: 45px; _top: 924px;clear:both' _ks_data_1427189382133='71'>
        <DIV class=browser-updator-wrapper>
            <P>
                <SPAN>您好，您的浏览器版本过低导致部分功能不能使用，为了方便您的操作，360浏览器请切换至“极速”模式，IE浏览器请升级浏览器：</SPAN>
                <SPAN>点击下载</SPAN>
                <A class='browser-updator-browser browser-updator-ie' href='http://rj.baidu.com/soft/detail/23360.html?ald' target=_blank data-spm-anchor-id='1.7274553.0.0'>升级IE浏览器</A>
            </P>
            <A class=browser-updator-close href='javascript:void(0);'>关闭</A>
        </DIV>
    </DIV>
</body>
</html>