<!doctype html>
<!--[if lt IE 7 ]> <html class="ie ie6 no-js" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie ie7 no-js" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie ie8 no-js" lang="en"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie ie9 no-js" lang="en"> <![endif]-->
<head>
<title>innterface | Search</title>
<base href="<?php echo base_url(); ?>" />
<meta charset="UTF-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="description" content="imterface" />
<meta name="keywords" content="imterface" />
<meta name="robots" content="all" />
<meta property="og:title" content="innterface" />
<meta property="og:type" content="website" />
<meta property="og:url" content="http://www.innterface.com/" />
<meta property="og:site_name" content="innterface" />
<meta property="fb:app_id" content="innterface" />
<meta property="og:description" content="" />
<meta name="Copyright" content="Copyright © 2013 SayMac All rights reserved." />
<!--[if IE]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!--[if lt IE 7]>
<script defer type="text/javascript" src="js/pngfix.js"></script>
<![endif]-->
<link rel="shortcut icon" href="img/favicon.ico" />
<link href="css/reset.css" rel="stylesheet" media="screen" /> 
<link href="css/style2.css" rel="stylesheet" media="screen" /> 
<link href="css/mobilstyle2.css" rel="stylesheet" media="screen" /> 
<link href="css/nyroModal.css" rel="stylesheet" media="screen" />
<link href="css/fontello.css" rel="stylesheet" media="screen" />
<link href="css/jquery.qtip.css" rel="stylesheet" media="screen" />
<link href="js/fancybox/jquery.fancybox.css?v=2.1.4" rel="stylesheet" media="screen" />
<!--[if lte IE 7]>
<link href="css/fontello-ie7.css" rel="stylesheet" media="screen" />
<![endif]-->
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
<!--<script type="text/javascript" src="js/jquery-1.js"></script>-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery_002.js"></script>
<script type="text/javascript" src="js/matchMedia.js"></script>
<script type="text/javascript" src="js/matchMedia.addListener.js"></script>
<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
<script type="text/javascript" src="js/jquery.qtip.min.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.fancybox.pack.js?v=2.1.4"></script>
<style type="text/css">
/* workaround for WebKit implementation of matchMedia to work */
@media only screen and (max-width:960px) {
    .faake {}
}
div.thumb {
    height: 530px;
    position: relative;
}
div.thumb h2 {
    width: 96%;
    font-size: 1.2em;
    height: 1.2em;
    line-height: 1.2em;
    overflow: hidden;
}
div.extras {
    width: 96%;
    height: 26px;
    text-align: right;
}
div.extras a {
    float: right;
    display: block;
    height: 26px;
    line-height: 26px;
    text-decoration: none;
    margin-left: 0.5em;
}
div.extras i {
    font-size: 24px;
}
div.extras span.text {
    display: block;
    float: right;
    height: 26px;
    line-height: 26px;
}
div.extras a.click_count {
    cursor: default;
}
div.extras a.pin {
    color: #FFF;
}
div.extras a.unpin {
    color: #B3C6EF;
    opacity: .7;
}
.func_bar {
    position: absolute;
    top: 0;
    right:  20px;
    height: 50px;
    line-height: 50px;
    margin: 0;
}
.func_bar-item {
    display: block;
    float: right;
    margin-left: 1em;
}
.func_bar-item a {
    height: 50px;
    line-height: 50px;
    display: block;
    font-size: 1.2em;
}
li a {
    text-decoration: none;
    color: #0088CC;
}
li a:hover {
    color: #0088CC;
    text-decoration: underline;
}
li a.highlight {
    text-decoration: none;
    color: #FF5522;
}
li a.highlight:hover {
    color: #FF5522;
    text-decoration: underline;
}
#profile {
    height: 52px;
    line-height: 52px;
}
#profile img {
    float: left;
    border: 1px solid #999;
    margin-right: 10px;
}
.pinboard_login_msg {
    display: none;
    font-size: 1em;
}
.pinboard_login_msg ul {
    display: block;
    margin: 0;
    height: 3em;
    line-height: 3em;
    border-top: 1px solid #DEDEDE;
    font-size: 1.2em;
}
.pinboard_login_msg li {
    display: block;
    float: right;
    height: 3em;
    line-height: 3em;
    margin: 0 0 0 1em;
}
.pinboard_login_msg li.cancel {
    float: left;
    margin: 0 1em 0 0;
}
li.cancel a {
    color: #AAA;
}
.pinboard_login_msg li a {
    display: block;
    height: 3em;
    line-height: 3em;
}
.pinboard_login_msg p {
    margin: 3em 2em;
    font-size: 1.2em;
    white-space: nowrap;
}
</style>
<script type="text/javascript">
$(window).load(function(){
    setTimeout(function(){
        window.scrollTo(0, 1);
    }, 10);
});

$(document).ready(function(){
    var is_login = <?php echo (int)$is_login; ?>;
    
    if (matchMedia) {
        var mq = window.matchMedia("only screen and (max-width:960px)");
        mq.addListener(WidthChange);
        WidthChange(mq);
    } else {
        console.log('no matchMedia');
    }
    function WidthChange(mq) {
        if (mq.matches) {
            //console.log('window width is less than 960px');
            // 螢幕寬度小於960px的，用換頁方式載入針對mobile設計的第三層頁面
            $('.thumb a.link').addClass('mobile')
            .removeClass('nyroModal')
            .removeAttr('target')
            .unbind()
            .attr('href', function(i, val){
                return $(this).data("mobileUrl"); /* HTML5 data attribute */
            });
        } else {
            //console.log('window width is at least 960px');
            // 螢幕寬度大於960px的，用modal方式載入第三層頁面
            $('.thumb a.link').addClass('nyroModal')
            .removeClass('mobile')
            .attr('target','_blank')
            .nyroModal()
            .attr('href', function(i, val){
                return $(this).data("modalUrl"); /* HTML5 data attribute */
            });
        }
    }
    
    $('.thumb a.link').click(function(e){
        var $clickCountElmt = $(this).siblings('div.extras').children('a.click_count').children('span.text');
        $clickCountElmt.text(parseInt($clickCountElmt.text(),10)+1);
        });

    $('.thumb div.extras a').qtip({
        position: {
            my: 'top center',
            at: 'bottom center'
        }
        })
    $('.thumb div.extras a.pin_count').click(function(e){
        e.preventDefault();
        if ( is_login == 1 ) {
            var ss_id = $(this).attr('data-ss-id');
            var $this = $(this);
            if ( $(this).hasClass('unpin') ) {
                // pin it
                $.post('api/user/add_pin', { ss_id : ss_id }, function(res){
                    if ( res.error == 0 ) {
                        var $pinCountElmt = $this.children('span.text');
                        $pinCountElmt.text(parseInt($pinCountElmt.text(),10)+1);
                        $this.removeClass('unpin').addClass('pin').attr('title', 'Unpin');
                    } else {
                        alert_error(res);
                    }
                }, 'json');
            } else {
                // unpin it
                $.post('api/user/remove_pin', { ss_id : ss_id }, function(res){
                    if ( res.error == 0 ) {
                        var $pinCountElmt = $this.children('span.text');
                        var count = parseInt($pinCountElmt.text(),10);
                        if ( count > 0 ) {
                            $pinCountElmt.text(parseInt($pinCountElmt.text(),10)-1);
                        }
                        $this.removeClass('pin').addClass('unpin').attr('title', 'Pin');;
                    } else {
                        alert_error(res);
                    }
                }, 'json');
            }
        } else {
            window.location.href = 'login';
        }
    });
    
    $("img.lazy").lazyload({
        effect: "fadeIn"
    });
    
    // fancybox to show dialog modal
    $('.fancybox').fancybox({
        margin: 0, 
        padding: [0,15,0,15], 
        closeBtn: false,
        minWidth: '240px'
        });
    $('#pinboard_login_msg li.cancel a').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        $.fancybox.close();
        });
    
    $('#search_button_a').click(function(){
        if ( $.trim($('#keyword').val()) !='' ) {
            $('#search_form').submit();
        }
    });
        
    var alert_error = function(res) {
        if ( res.msg ) {
            alert(res.msg);
        } else {
            alert('Sorry, system get some problem, please reload page or restart your browser.');
        }
        //console.log(res);
    };
    
    $(document).ajaxError(function(event, request, settings, exception) {
        alert('Sorry, system get some problem, please reload page or restart your browser.11');
        //console.log(settings);
    });
});
</script>
</head>
<body>
<?php include_once("analyticstracking.php") ?>
<div class="container-fluid" id="ap_main">
    <div class="input-append">
        <a href="<?php echo site_url();  ?>"><img src="img/logo_m.png" id="logo" alt="imterface"></a>
        <form action="search" method="get" style="margin:0;" name="search_form" id="search_form">
            <div id="search"><input id="keyword" name="q" placeholder="Search" value="<?php echo htmlspecialchars($keyword);  ?>" type="text">
            <a id="search_button_a"><img src="img/searchbutton.png" title="search"></a></div>
        </form>
        <ul class="func_bar">
<?php if ( isset($user) ) : ?>
                <li id="profile" class="func_bar-item is_login">
    <?php if ( isset($user->fb_picture) && $user->fb_picture != '' ) : ?>
        <img src="<?php echo htmlspecialchars($user->fb_picture); ?>" width="50" height="50" />
    <?php endif; ?>
    <?php if ( isset($user->fb_name) && $user->fb_name != '' ) : ?>
        <?php echo htmlspecialchars($user->fb_name); ?>
    <?php endif; ?>
                </li>
<?php endif; ?>
<?php if ( !$is_login ) : ?>
            <li class="func_bar-item not_login"><a id="btn_login" href="login">Login</a></li>
            <li class="func_bar-item not_login"><a id="btn_signup" href="signup">Sign-up</a></li>
            <li class="func_bar-item not_login"><a class="highlight fancybox" href="#pinboard_login_msg">Pinboard</a></li>
<?php else : ?>
            <li class="func_bar-item is_login"><a id="btn_logout" href="logout">Logout</a></li>
            <li class="func_bar-item is_login"><a id="btn_upload" class="highlight" href="upload">Upload</a></li>
            <li class="func_bar-item is_login"><a id="btn_upload" class="highlight" href="pinboard">Pinboard</a></li>
<?php endif; ?>
            <li class="clearfix"></li>
        </ul>
        <div id="message_bar" style="vertical-align: middle; ">
            <span>Results:</span> <span><?php echo htmlspecialchars($result_num); ?> <?php echo ($result_num > 1) ? 'Screenshots' : 'Screenshot'; ?></span>
        </div>
    </div>

    <div id="photo_wall" class="adaptive_width"><!-- -- photo_wall start -->
    
<?php foreach ( $screenshot_arr as $screenshot ) : ?>
        <div class="thumb">
            <a class="link" href="screenshot?id=<?php echo urlencode($screenshot->id); ?>&keyword=<?php echo urlencode($keyword); ?>&mode=modal" target="_blank" class="nyroModal" data-modal-url="screenshot?id=<?php echo urlencode($screenshot->id); ?>&keyword=<?php echo urlencode($keyword); ?>" data-mobile-url="screenshot?id=<?php echo urlencode($screenshot->id); ?>&keyword=<?php echo urlencode($keyword); ?>&mode=mobile1"><img class="thumb_img lazy" src="img/thumb_loading.gif" data-original="<?php echo htmlspecialchars($screenshot->url); ?>" /></a>
            <h2><?php echo htmlspecialchars($screenshot->trackName); ?></h2>
            <div class="extras">
                <a href="javascript:void(0)" class="click_count unpin" title="How many times had this screenshot been viewed" data-ss-id="<?php echo (int)($screenshot->id); ?>"><i class="icon-eye"></i><span class="text"><?php echo (int)($screenshot->clickCount); ?></span></a>
                <a href="javascript:void(0)" class="pin_count <?php echo ($screenshot->pin == true) ? 'pin' : 'unpin'; ?>" title="<?php echo ($is_login) ? (($screenshot->pin == true) ? 'Unpin' : 'Pin') : 'Login to pin this Screenshot to your Pinboard'; ?>" data-ss-id="<?php echo (int)($screenshot->id); ?>"><i class="icon-heart"></i><span class="text"><?php echo (int)($screenshot->pinCount); ?></span></a>
            </div>
        </div>
<?php endforeach; ?>

    </div><!-- photo_wall end -->
</div>

<div id="pinboard_login_msg" class="pinboard_login_msg">
    <p nowrap="nowrap">Please Login to have your personal pinboard!</p>
    <ul>
        <li class="login">
            <a href="login" class="highlight">Login</a>
        </li>
        <li class="cancel">
            <a href="javascript:void(0)">Cancel</a>
        </li>
        <li class="clearfix"></li>
    </ul>
</div>

<script type="text/javascript">
    $(function(){
        $('.thumb img').hover(function(){
            $(this).stop().animate({
                opacity: 0.4
            });
        }, function(){
            $(this).stop().animate({
                opacity: 1
            });
        });
    });
</script>
</body>
</html>