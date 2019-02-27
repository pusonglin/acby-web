<?php /*a:2:{s:76:"D:\phpStudy\PHPTutorial\WWW\acby-web\application\admin\view\index\index.html";i:1550458370;s:76:"D:\phpStudy\PHPTutorial\WWW\acby-web\application\admin\view\public\base.html";i:1550823376;}*/ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>乡村振兴</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="/static/common/font-awesome-4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="/static/common/mCustomScrollbar/jquery.mCustomScrollbar.css" />
    <link rel="stylesheet" type="text/css" href="/static/common/layui/css/layui.css" />
    <link rel="stylesheet" type="text/css" href="/static/admin/css/style.css" />
    
    <style type="text/css">
        ::-webkit-scrollbar {width: 6px;  height: 6px;  background-color: #F5F5F5;   }
        html{overflow: scroll;height: auto;}
        body{background: #fafafa;overflow: auto;height: auto;}/*heigt:atuo是为了兼容360浏览器scrollTop*/
        /*body{background: #fafafa;overflow: auto;}*/
        .body-box{background: #fafafa;overflow: auto; min-width: 1200px!important;max-width: 1200px!important;}
        #height-line{width: 100%;height: 75px;background: #fafafa;}
        header{position: fixed;top:0;left: 0;z-index: 99999;;
            transition: linear 0.8s;
            -webkit-transition: linear 0.8s;
            transform: translateY(0);
            -webkit-transform: translateY(0);}
        header.active{
            transition: linear 1.5s;
            -webkit-transition: linear 1.5s;
            transform: translateY(-200px);
            -webkit-transform: translateY(-200px);
        }
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
    
    <script type="text/javascript" src="/static/common/js/jquery.cookie.js"></script>
    <script type="text/javascript" src="/static/admin/js/index-index.js"></script>

</head>
<body>
    
    <!--header-->
    <header>
        <div class="header-box">
            <div class="header-logo fl">
                <img src="/static/admin/images/logo.png">
                <span class="headerH"></span>
                <span class="headerH_16">乡村振兴</span>
            </div>
            <ul class="header-nav fl">
                <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                    <li>
                        <a href="javascript:;" data-id="<?php echo htmlentities($v['id']); ?>" data-fresh_id="<?php echo htmlentities($v['fresh_id']); ?>"><?php echo htmlentities($v['name']); ?></a>
                    </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
            <div class="header-logout fr">
                 <ul>
                     <!--消息通知-->
                     <!--<li>
                         <a href="javascript:;">
                             <i class="fa fa-bell-o"></i>
                             <span></span>
                         </a>
                     </li>-->

                     <li>
                        <div class="headerDiv">
                            <div class="headerTx">
                                <img src="<?php echo htmlentities($user['headimg']); ?>"/>
                            </div>
                            <div class="headerArrow"><i class="fa fa-caret-down"></i></div>
                            <!--<span>New</span>-->
                        </div>
                        <!--鼠标以上显示下拉菜单-->
                         <div class="selectMean">
                             <div class="selectarrow"></div>
                             <div class="selectarrow2"></div>
                              <div  class="personName">
                                  <?php echo htmlentities($user['username']); ?><span><?php echo htmlentities($user['phone']); ?></span>
                              </div>

                              <ul>
                                  <li>
                                      <a class="click-jump-btn" data-topnav-id="1" data-fresh-id="19"><i class="fa fa-user-o"></i>个人资料</a>
                                      <a class="click-jump-btn" data-topnav-id="1" data-fresh-id="20"><i class="fa fa-key"></i>修改密码</a>
                                      <a href="<?php echo htmlentities(ADMIN_URL); ?>/user/logout.html"><i class="fa fa-sign-out"></i>退出</a>
                                  </li>
                             </ul>
                         </div><!--/selectMean-->
                     </li>
                 </ul>
            </div>
        </div>
    </header>
    <!--概览-->
    <div id="overview" style="display: none;"></div>


    <div class="body-box">
        
    <div id="height-line"></div>
    <?php if(empty($list) || (($list instanceof \think\Collection || $list instanceof \think\Paginator ) && $list->isEmpty())): ?>
        <div style="padding: 50px 0; height: 600px; text-align: center;">
            <h1>
                <i class="layui-icon" style="line-height: 500px; font-size: 500px; color: #393D50;">&#xe61c;</i>
            </h1>
            <p style="font-size: 20px; font-weight: 300; color: #999;text-align: center;">o(╯□╰)o  您还没有权限哦，请联系管理员~</p>
        </div>
        <?php else: ?>
        <!--aside nav-->
        <aside class="lt_aside_nav content light fl" style="background: #fff">
            <div class="nav_box">
                <ul class="nav_ul"></ul>
            </div>
            <a href="" target="iframe" style="display: none;" id="hide-click-a"></a>
        </aside>
        <iframe id="iframe" name="iframe" frameborder="0" scrolling="no" width="100%" onload="reinitIframe();"></iframe>
    <?php endif; ?>

    </div>

    
    <div class="footer">
        <ul >
            <li><a href="javascript:;" target="_blank">关于我们</a></li>
            <li><a href="javascript:;" target="_blank">服务协议</a></li>
            <li><a href="javascript:;" target="_blank">运营中心</a></li>
            <li><a href="javascript:;" target="_blank">客服中心</a></li>
            <li><a href="javascript:;" target="_blank">联系邮箱</a></li>
            <li><a href="javascript:;" target="_blank">侵权投诉</a></li>
        </ul>
        <p>Copyright © 2014-<?php echo date('Y'); ?> Songlinyun. All Rights Reserved.</p>
    </div>


    

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