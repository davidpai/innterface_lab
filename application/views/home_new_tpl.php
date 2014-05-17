<!DOCTYPE html>
<html>
<head>
<title>innterface - mobile application design patterns library and user interface (UI) search engine</title>
<base href="<?php echo base_url(); ?>" />
<meta charset="UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" /><!-- to force IE8 to use latest standard -->
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="innterface is a search platform which can give you inspiration and example of user interface (UI) design patterns for mobile application like iPhone and iPad." />
<meta name="keywords" content="user interface,UI, mobile applications,app,apps,design patterns,screenshot" />
<meta name="Copyright" content="Copyright © 2013 SayMac All rights reserved." />
<meta property="og:title" content="innterface - mobile application design patterns library and user interface (UI) search engine" />
<meta property="og:image" content="http://innterface.com/img/logo_m.png" />
<meta property="og:type" content="website" />
<meta property="og:url" content="<?php echo base_url(); ?>" />
<meta property="og:site_name" content="innterface - mobile application design patterns library and user interface (UI) search engine" />
<meta property="og:description" content="innterface is a search platform which can give you inspiration and example of user interface (UI) design patterns for mobile application like iPhone and iPad." />
<link rel="shortcut icon" href="img/favicon.ico" />
<!--[if lt IE 9]>
<script type="text/javascript" src="js/html5shiv.js"></script>
<![endif]-->
<link type="text/css" href="css/bootstrap.min.css" rel="stylesheet" media="screen">
<link type="text/css" rel="stylesheet" href="css/home_new.css?v=20140206002" media="screen" />
<link type="text/css" href="js/fancybox/jquery.fancybox.css?v=2.1.4" rel="stylesheet" media="screen" />
<style type="text/css">
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
    
    $('#keyword').focus();
    
    $(document).pngFix(); 
    
    $("img.lazy").lazyload({
        effect: "fadeIn"
    });
    
    // fancybox to show dialog modal
    $('.fancybox').fancybox({
        margin: 0, 
        padding: [0,15,0,15], 
        closeBtn: false,
        maxWidth: 450, 
        minWidth: 0
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
    <?php if ( isset($user->picture) && $user->picture != '' ) : ?>
        <img src="<?php echo htmlspecialchars($user->picture); ?>" width="48" height="48" />
    <?php endif; ?>
    <?php if ( isset($user->name) && $user->name != '' ) : ?>
        <?php echo htmlspecialchars($user->name); ?>
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
                <li class="func_bar-item is_login"><a id="btn_pinboard" class="highlight" href="pinboard">Pinboard</a></li>
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
            <form action="search" method="get" name="search_form" id="search_form">
            <input class="search_input" type="text" id="keyword" name="q" maxlength="150" placeholder="Patterns: ex: Splash, Maps, About.." />
            <button class="search_button"></button>
            </form>
        </div>
    </div>
    <div class="row-fluid">
        <ul class="thumbnail_wall unstyled">
<?php foreach ( $tag_screenshoot_arr as $tag_screenshot ) : ?>
            <li class="thumb_item">
                <h4><?php echo htmlspecialchars(ucwords(strtolower($tag_screenshot['tag']))); ?></h4>
                <div class="thumb_img_wrapper">
                    <a href="search?q=<?php echo urlencode($tag_screenshot['tag']); ?>&m=tag"><img class="lazy" src="img/thumb_loading.gif" data-original="<?php echo htmlspecialchars($tag_screenshot['screenshot']->url); ?>" alt="<?php echo htmlspecialchars($tag_screenshot['screenshot']->tag_list); ?>" title="<?php echo htmlspecialchars($tag_screenshot['screenshot']->appName); ?>"></a>
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

    <div id="pinboard_login_msg" class="modal_dialog">
        <p>Please Login to have your personal pinboard</p>
        <ul>
            <li class="action">
                <a href="login">Login</a>
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