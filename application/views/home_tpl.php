<!DOCTYPE html>
<html>
<head>
<title>innterface Home</title>
<base href="<?php echo base_url(); ?>" />
<meta charset="UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" /><!-- to force IE8 to use latest standard -->
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="innterface" />
<meta name="keywords" content="innterface" />
<meta name="robots" content="all" />
<meta name="Copyright" content="Copyright © 2013 SayMac All rights reserved." />
<meta property="og:title" content="innterface" />
<meta property="og:type" content="website" />
<meta property="og:url" content="http://innterface.com/" />
<meta property="og:site_name" content="innterface" />
<meta property="og:description" content="innterface" />
<link rel="shortcut icon" href="img/favicon.ico" />
<!--[if lt IE 9]>
<script type="text/javascript" src="js/html5shiv.js"></script>
<![endif]-->
<!--<link type="text/css" href="css/reset.css" rel="stylesheet" media="screen" />-->
<link type="text/css" href="css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="js/fancybox/jquery.fancybox.css?v=2.1.4" rel="stylesheet" media="screen" />
<style type="text/css">
body {
    min-width: 320px;
}
.logo {
    margin-top: 2em;
}
.logo img {
    width: 100%;
    height: 100%;
    max-width: 368px;
    min-width: 240px;
    max-height: 72px;
}
.func_bar {
    height: 50px;
    line-height: 50px;
    min-width: 240px;
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
.search_block {
    text-align: center;
    margin: 3em 0;
    /*border: 1px solid #000;*/
}
input.search_input {
    outline: none;
    border: 1px solid #D6D6D6;
    font-size: 1em;
    color: #5F5F5F;
    padding: 0.2em 64px 0.2em 1em;
    text-indent: 0.5em;
    width: 70%;
    height: 2.5em;
    min-width: 168px;
    max-width: 32em;
    border-radius: 2em;
    -moz-border-radius: 2em;
    -webkit-border-radius: 2em;
    background-color: transparent;
    background-image: url('img/searchbutton.png');
    background-repeat: no-repeat;
    background-position: top right;
    background-size: 3em;
}
.search_button {
    position: relative;
    vertical-align: middle;
    width: 4em;
    height: 4em;
    margin-left: -4.5em;
}
ul.thumbnail_wall {
    width: 1020px;
    margin: 0 auto;
}
.thumb_item {
    position: relative;
    border: 1px solid #DFDFDF;
    border-radius: 0.5em;
    -moz-border-radius: 0.5em;
    -webkit-border-radius: 0.5em;
    box-shadow: 0 1px 4px #DFDFDF;
    display: block;
    float: left;
    margin: 16px;
    padding: 6px;
    width: 96%;
    min-width: 254px;
    max-width: 294px;
}
.thumb_item h4 {
    width: 100%;
    min-width: 240px;
    max-width: 280px;
    overflow: hidden;
    margin: 0.5em auto;
}
.thumb_img_wrapper img {
    border: 1px solid #545454;
    display: block;
    border-radius: 0.5em;
    -moz-border-radius: 0.5em;
    -webkit-border-radius: 0.5em;
    width: 100%;
    height: 420px;
    min-width: 240px;
    max-width: 280px;
    margin: 0 auto;
}
.thumb_img_wrapper {
    background-color: #000;
    border-radius: 0.5em;
    -moz-border-radius: 0.5em;
    -webkit-border-radius: 0.5em;
    width: 100%;
    height: 420px;
    min-width: 240px;
    max-width: 280px;
    margin: 0 auto;
}
.thumb_item span {
    margin: 0.5em auto 0 auto;
    display: block;
    width: 100%;
    min-width: 240px;
    max-width: 280px;
    overflow: hidden;
    font-size: 1em;
    height: 3em;
}
.footer {
    color: gray;
    margin: 2em 0;
    text-align: center;
}
.footer a {
    color: #FF5F00;
}
@media only screen and (min-width: 1720px) {
    /* 5 columns */
    ul.thumbnail_wall {
        width: 1700px;
        margin: 0 auto;
    }
}

@media only screen and (min-width: 1360px) and (max-width:1699px) {
    /* 4 columns */
    ul.thumbnail_wall {
        width: 1360px;
        margin: 0 auto;
    }
}

@media only screen and (min-width: 1020px) and (max-width: 1360px) {
    /* 3 columns */
    ul.thumbnail_wall {
        width: 1020px;
        margin: 0 auto;
    }
}

@media only screen and (min-width: 768px) and (max-width: 1019px) {
    /* 2 columns */
    ul.thumbnail_wall {
        width: 680px;
        margin: 0 auto;
    }
}

@media only screen and (min-width: 361px) and (max-width:767px) {
    /* 1 column */
    ul.thumbnail_wall {
        width: 340px;
        margin: 0 auto;
    }
}

@media only screen and (max-width: 360px) {
    /* 1 column and 100% fluid */
    ul.thumbnail_wall {
        width: 100%;
        margin: 0 auto;
    }
    .thumb_item {
        margin: 16px 0;
    }
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
<!--[if lt IE 9]>
<style type="text/css">
/* no support background-size less than IE9 */
input.search_input {
    outline: none;
    border: 1px solid #D6D6D6;
    font-size: 1em;
    color: #5F5F5F;
    padding: 0 64px 0 1em;
    width: 45%;
    min-width: 16em;
    border-radius: 1.5em;
    -moz-border-radius: 1.5em;
    -webkit-border-radius: 1.5em;
    background-color: transparent;
    background-repeat: no-repeat;
    background-position: top right;
    background-image: url('img/searchbutton_42x42.png');
    height: 42px;
}
</style>
<![endif]-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/css3-mediaqueries.js"></script><!-- make IE6,7,8 to support media query -->
<script type="text/javascript" src="js/jquery.pngFix.js"></script>
<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.fancybox.pack.js?v=2.1.4"></script>
<script type="text/javascript">
$(window).load(function(){
    setTimeout(function(){
        window.scrollTo(0, 1);
    }, 10);
});

$(document).ready(function(){
    var isLogin = <?php echo (int)$is_login; ?>;
    
    $(document).pngFix(); 
    
    $("img.lazy").lazyload({
        effect: "fadeIn"
    });
    
    // fancybox to show dialog modal
    $('.fancybox').fancybox({
        padding: [0,15,0,15], 
        closeBtn: false
        });
    $('#pinboard_login_msg li.cancel a').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        $.fancybox.close();
        });
    
    $('.thumb_item img').hover(function(){
        $(this).stop().animate({
            opacity: 0.5
        });
    }, function(){
        $(this).stop().animate({
            opacity: 1
        });
    });
});
</script>
</head>
<body>
<?php include_once("analyticstracking.php") ?>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <ul id="top_bar" class="unstyled func_bar">
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
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12 text-center logo">
            <a href="<?php echo site_url(); ?>"><img src="img/logo.png" width="368" height="72" alt="innterface" /></a>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12 search_block">
            <form action="search" method="get" style="margin:0;" name="search_form" id="search_form">
            <input class="search_input" type="text" id="keyword" name="keyword" maxlength="150" placeholder="Patterns: ex: Splash, Maps, About.." />
            </form>
        </div>
    </div>
    <div class="row-fluid">
        <ul class="thumbnail_wall unstyled">
<?php foreach ( $query->result() as $row ) : ?>
            <li class="thumb_item">
                <h4><?php echo htmlspecialchars(ucwords(strtolower($row->tag))); ?></h4>
                <div class="thumb_img_wrapper">
                    <a href="search?keyword=<?php echo urlencode($row->tag); ?>"><img class="lazy" src="img/thumb_loading.gif" data-original="<?php echo htmlspecialchars($row->url); ?>"></a>
                </div>
            </li>
<?php endforeach; ?>
            <li class="clearfix"></li>
        </ul>
    </div>
    <div class="row-fluid">
        <div class="span12 footer">Copyright &copy; 2013 SayMac All rights reserved.<br>
        Any questions or suggestions: <a href="mailto:info@saymac.com">info@saymac.com</a>
        </div>
    </div>

    <div id="pinboard_login_msg" class="pinboard_login_msg">
        <p nowrap="nowrap">Please Login to have your personal pinboard!</p>
        <ul>
            <li class="login">
                <a href="login?c=pinboard" class="highlight">Login</a>
            </li>
            <li class="cancel">
                <a href="javascript:void(0)">Cancel</a>
            </li>
            <li class="clearfix"></li>
        </ul>
    </div>

</div>
</body>
</html>