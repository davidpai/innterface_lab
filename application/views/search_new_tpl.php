<!DOCTYPE html>
<html>
<head>
<title>Search | innterface - mobile application design patterns library and user interface (UI) search engine</title>
<base href="<?php echo base_url(); ?>" />
<meta charset="UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" /><!-- to force IE8 to use latest standard -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
<link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" media="screen" />
<link type="text/css" rel="stylesheet" href="css/search_new.css?v=20131125001" media="screen" />
<link type="text/css" rel="stylesheet" href="css/fontello.css?v=20140108001" media="screen" />
<link type="text/css" rel="stylesheet" href="css/jquery.qtip.css" media="screen" />
<link type="text/css" rel="stylesheet" href="js/fancybox/jquery.fancybox.css?v=2.1.4" media="screen" />
<!--[if lte IE 7]>
<link type="text/css" rel="stylesheet" href="css/fontello-ie7.css?v=20140108001" media="screen" />
<![endif]-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/css3-mediaqueries.js"></script><!-- make IE6,7,8 to support media query -->
<script type="text/javascript" src="js/matchMedia.js"></script>
<script type="text/javascript" src="js/matchMedia.addListener.js"></script>
<script type="text/javascript" src="js/jquery.pngFix.js"></script>
<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
<script type="text/javascript" src="js/jquery.qtip.min.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.fancybox.pack.js?v=2.1.4"></script>
<script type="text/javascript" src="js/waypoints.min.js"></script>
<script type="text/javascript" src="js/waypoints-infinite.js"></script>
<style type="text/css">
</style>
<script type="text/javascript">
// 頁面load進來就移動1px，以便讓lazy load能動作
$(window).load(function(){
    setTimeout(function(){
        window.scrollTo(0, 1);
    }, 10);
});

$(document).ready(function(){
    var is_login = <?php echo (int)$is_login; ?>;

    $(document).pngFix();
    
    /* matchMedia => 600px以下(不含)用換頁方式載入layer3, 600px以上(含)用modal方式載入layer3 */
    if (matchMedia) {
        var mq = window.matchMedia("only screen and (max-width: 599px)");
        mq.addListener(WidthChange);
        WidthChange(mq);
    }
    function WidthChange(mq) {
        if (mq.matches) {
        } else {
            // fancybox to show layer3 modal
            $('.link-iframe').fancybox({
                margin: 20, 
                padding: 5,
                width: '100%',
                height: '100%',
                minWidth: 480,
                minHeight: 360,
                maxWidth: 960,
                maxHeight: 720,
                closeBtn: true,
                iframe: {
                    scrolling : 'auto',
                    preload   : false
                }
            }); 
        }
    }

    $('.thumb_item div.extras a').qtip({
        position: {
            my: 'top center',
            at: 'bottom center'
        }
    });
    
    // Like
    var like_it = function($elmt){
        var ss_id = $elmt.attr('data-ss-id');
        $.post('api/user/add_like', { ss_id : ss_id }, function(res){
            if ( res.error == 0 ) {
                var $likeCountElmt = $elmt.children('span.text');
                $likeCountElmt.text(parseInt($likeCountElmt.text(),10)+1);
                $elmt.removeClass('unlike').addClass('like').attr('title', 'Unlike');
            } else {
                alert_error(res);
            }
        }, 'json');
    };
    var unlike_it = function($elmt){
        var ss_id = $elmt.attr('data-ss-id');
        $.post('api/user/remove_like', { ss_id : ss_id }, function(res){
            if ( res.error == 0 ) {
                var $likeCountElmt = $elmt.children('span.text');
                var count = parseInt($likeCountElmt.text(),10);
                if ( count > 0 ) {
                    $likeCountElmt.text(parseInt($likeCountElmt.text(),10)-1);
                }
                $elmt.removeClass('like').addClass('unlike').attr('title', 'Like');;
            } else {
                alert_error(res);
            }
        }, 'json');
    };
    
    // 因為我們有用Ajax抓進來成形的DOM元素，所以這裡我們要用on()
    // 而且要寫成$(document).on()不能寫成$('xxx').on()，on()要bind在上層元素
    $(document).on('click', '.thumb_item div.extras a.like_count', function(e){
    //$('.thumb_item div.extras a.like_count').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        if ( is_login == 1 ) {
            $dislikeElmt = $(this).siblings('a.dislike_count');
            if ( $(this).hasClass('unlike') ) {
                // like it
                like_it($(this));
                if ( $dislikeElmt.hasClass('dislike') ) {
                    undislike_it($dislikeElmt);
                }
            } else {
                // unlike it
                unlike_it($(this));
            }
        } else {
            window.location.href = 'login?r=<?php echo isset($return_url) ? urlencode($return_url) : ''; ?>';
        }
        return false;
    });

    // Dislike
    var dislike_it = function($elmt){
        var ss_id = $elmt.attr('data-ss-id');
        $.post('api/user/add_dislike', { ss_id : ss_id }, function(res){
            if ( res.error == 0 ) {
                var $dislikeCountElmt = $elmt.children('span.text');
                $dislikeCountElmt.text(parseInt($dislikeCountElmt.text(),10)+1);
                $elmt.removeClass('undislike').addClass('dislike').attr('title', 'Undislike');
            } else {
                alert_error(res);
            }
        }, 'json');
    };
    
    var undislike_it = function($elmt){
        var ss_id = $elmt.attr('data-ss-id');
        $.post('api/user/remove_dislike', { ss_id : ss_id }, function(res){
            if ( res.error == 0 ) {
                var $dislikeCountElmt = $elmt.children('span.text');
                var count = parseInt($dislikeCountElmt.text(),10);
                if ( count > 0 ) {
                    $dislikeCountElmt.text(parseInt($dislikeCountElmt.text(),10)-1);
                }
                $elmt.removeClass('dislike').addClass('undislike').attr('title', 'Dislike');;
            } else {
                alert_error(res);
            }
        }, 'json');
    };
    
    // 因為我們有用Ajax抓進來成形的DOM元素，所以這裡我們要用on()
    // 而且要寫成$(document).on()不能寫成$('xxx').on()，on()要bind在上層元素
    $(document).on('click', '.thumb_item div.extras a.dislike_count', function(e){
    //$('.thumb_item div.extras a.dislike_count').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        if ( is_login == 1 ) {
            $likeElmt = $(this).siblings('a.like_count');
            if ( $(this).hasClass('undislike') ) {
                // dislike it
                dislike_it($(this));
                if ( $likeElmt.hasClass('like') ) {
                    unlike_it($likeElmt);
                }
            } else {
                // undislike it
                undislike_it($(this));
            }
        } else {
            window.location.href = 'login?r=<?php echo isset($return_url) ? urlencode($return_url) : ''; ?>';
        }
        return false;
    });
    
    // Pin
    // 因為我們有用Ajax抓進來成形的DOM元素，所以這裡我們要用on()
    // 而且要寫成$(document).on()不能寫成$('xxx').on()，on()要bind在上層元素
    $(document).on('click', '.thumb_item div.extras a.pin_count', function(e){
        e.preventDefault();
        e.stopPropagation();
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
            window.location.href = 'login?r=<?php echo isset($return_url) ? urlencode($return_url) : ''; ?>';
        }
        return false;
    });
    
    $("img.lazy").lazyload({
        effect: "fadeIn"
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

    // add a fake <a> from login from_url to trigger layer3 modal
    var trigger_layer3 = <?php echo isset($trigger_layer3) ? (int)$trigger_layer3 : 0 ?>;
    var layer3_url = '<?php echo isset($layer3_url) ? $layer3_url : '' ?>';
    if ( trigger_layer3 ) {
        var $a = $('<a>').attr({'data-fancybox-type':'iframe', href:layer3_url}).addClass('link-iframe');
        $('body').append($a);
    }
    
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
    // if we have the fake <a>, trigger it
    if ( $a ) {
        $a.trigger('click');
    }
    
    // Infinity Scroll
    /*
    var item_num = <?php echo (int)$result_num; ?>;
    $('.more_items').waypoint('infinite', {
        container: '#content_block ul.thumbnail_wall',
        items: 'li.thumb_item',
        more: 'a.infinite-more-link',
        offset: 'bottom-in-view', 
        loadingClass: 'infinite-loading', 
        onBeforePageLoad: function(){
            $('a.infinite-more-link').html('<img src="images/ajax-loader.gif" />');
        }, 
        onAfterPageLoad: function(){
            $('img.lazy[src="img/thumb_loading.gif"]').lazyload({
                effect: "fadeIn"
            });
            if ( $('.thumb_item').length >= item_num ) {
                $('.more_items').hide();
            }
        }
    });
    */
    
    /** progressive load (view xxx of xxx) **/
    var page = <?php echo (int)$p; ?>;
    var total_num = <?php echo (int)$result_num; ?>;
    var per_page = 90; // 第二頁之後，每一頁的顯示數量
    var min_num = 100; // 第一頁的顯示數量，超過這個數才做progressive load
    var make_show_num = function(){
        // 計算應顯示的數量
        show_num = min_num+((page-1)*per_page);
        // 若應顯示數量超過了總數，則顯示總數
        show_num = (show_num >= total_num) ? total_num : show_num;
        // 若總數小於第一頁應顯示的數量，則顯示總數，且不做progressive load
        show_num = (total_num <= min_num) ? total_num : show_num;
        $('#show_num').text(show_num);
    };
    // 頁面第一次載入進來先跑一次
    make_show_num();
    // 預先載入ajax轉轉圖
    var indicator_src = 'images/ajax-loader.gif';
    var indicator = new Image();
    indicator.src = indicator_src;
    // load more按下去
    $('#load_more').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        // 從URL的p參數判斷是第幾頁(通常不會有p參數)，再以p=page+1造出ajax需要的URL
        var pattern1 = /p=[^&]*/i.exec(location.href);
        if ( pattern1 == null ) {
            var url = location.href + '&p=' + (page+1);
        } else {
            var url = location.href.replace(/p=[^&]*/g, 'p='+(page+1));
        }
        var pattern2 = /m=[^&]*/i.exec(url);
        if ( pattern2 == null ) {
            url = url + '&m=ajax';
        } else {
            url = url.replace(/m=[^&]*/g, 'm=ajax');
        }
        //console.log(url);
        // 切換到ajax轉轉圖
        $(this).html($('<img>').attr({width:22, height:22, src:indicator_src}));
        // 送出ajax(此處我們接收整個回傳的頁面，不用json)
        var $this = $(this);
        $.get(url, {}, function(res){
            // parse回傳的頁面原始碼成為可操作的DOM元素
            var html = $.parseHTML(res);
            // 撈出一個個的screenshot容器元素
            var d = $(html).find('#content_block ul.thumbnail_wall li.thumb_item');
            //console.log(d.length);
            // 加到現有的screenshot thumbnail wall下
            d.appendTo('#content_block ul.thumbnail_wall');
            // 變更下方的 view xxx of xxx 顯示數量
            page = page+1;
            make_show_num();
            // 新append進來的要重新設定lazyload的event進去
            $('img.lazy[src="img/thumb_loading.gif"]').lazyload({
                effect: "fadeIn"
            });
            // 切換回向下箭頭圖示
            $this.html('<i class="iconfont-down-open"></i>');
            // 如果顯示數量已達到總數，就隱藏more item列
            if ( show_num >= total_num ) {
                $('.more_items').hide();
            }
        });
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
        alert_error();
    });
    
<?php if ( isset($appkw_url_arr) ) : ?>
/*
    var appkw_url = <?php echo json_encode($appkw_url_arr); ?>;
    $(window).load(function(){
        for (var i=0; i<appkw_url.length; i++) {
            var url = appkw_url[i];
            $('<iframe>').attr({src:url}).css({width:0,height:0}).appendTo('body');
        }
    });
*/
<?php endif; ?>
});
</script>
</head>
<body>
<?php include_once("analyticstracking.php") ?>
<div id="main" class="container-fluid">
    <div id="top" class="row-fluid">
        <div id="logo_block">
            <a href="<?php echo site_url();  ?>"><img class="logo" src="img/logo_m.png" alt="innterface" /></a>
        </div>
        <div id="func_block">
            <ul class="unstyled func_bar">
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
            <li class="func_bar-item is_login"><a id="btn_upload" class="highlight" href="pinboard">Pinboard</a></li>
<?php endif; ?>
                <li class="clearfix"></li>
            </ul>
        </div><!-- end: func_block -->
        <div id="search_block">
            <form action="search" method="get" name="search_form" id="search_form">
            <input class="search_input" type="text" id="keyword" name="q" maxlength="150" value="<?php echo htmlspecialchars($keyword);  ?>" placeholder="Patterns: ex: Splash, Maps, About.." />
            <button class="search_button"></button>
            </form>
        </div><!-- end: search_block -->
        <div class="clearfix" style="background-color: #FFF;"></div>
        <div class="message_bar"><span>Results:</span> <span><?php echo htmlspecialchars($result_num); ?> <?php echo ($result_num > 1) ? 'Screenshots' : 'Screenshot'; ?></span></div>
    </div><!-- end top -->

    <div id="content_block" class="row-fluid">
<?php if ( isset($exception_msg) ) : ?>
        <div class="exception_msg"><?php echo nl2br(htmlspecialchars($exception_msg)); ?></div>
<?php endif; ?>
        <ul class="thumbnail_wall unstyled">
<?php foreach ( $screenshot_arr as $screenshot ) : ?>
            <li class="thumb_item">
                <div class="thumb_img_wrapper thumb">
                    <a class="link-iframe" data-fancybox-type="iframe" href="screenshot?id=<?php echo urlencode($screenshot->id); ?>&keyword=<?php echo urlencode($keyword); ?>"><img class="thumb_img lazy" src="img/thumb_loading.gif" data-original="<?php echo htmlspecialchars($screenshot->url); ?>" alt="<?php echo htmlspecialchars($screenshot->tag_list); ?>" title="<?php echo htmlspecialchars($screenshot->appName); ?>" width="280" /></a>
                </div>
                <h2><?php echo htmlspecialchars($screenshot->appName); ?></h2>
                <div class="extras">
                    <!-- like -->
                    <a href="javascript:void(0)" class="like_count pull-left <?php echo ($screenshot->like == true) ? 'like' : 'unlike'; ?>" title="<?php echo ($is_login) ? (($screenshot->like == true) ? 'Unlike' : 'Like') : 'Login to give this screenshot a thumb-up'; ?>" data-ss-id="<?php echo (int)($screenshot->id); ?>"><i class="iconfont-like"></i><span class="text"><?php echo (int)($screenshot->likeCount); ?></span></a>
                    <!-- dislike -->
                    <?php echo (int)($screenshot->pinCount); ?></span></a>
                    <a href="javascript:void(0)" class="dislike_count pull-left <?php echo ($screenshot->dislike == true) ? 'dislike' : 'undislike'; ?>" title="<?php echo ($is_login) ? (($screenshot->dislike == true) ? 'Undislike' : 'Dislike') : 'Login to give this screenshot a thumb-down'; ?>" data-ss-id="<?php echo (int)($screenshot->id); ?>"><i class="iconfont-dislike"></i><span class="text"><?php echo (int)($screenshot->dislikeCount); ?></span></a>
                    <!-- view -->
                    <a href="javascript:void(0)" class="pull-right click_count unpin" title="How many times had this screenshot been viewed" data-ss-id="<?php echo (int)($screenshot->id); ?>"><i class="iconfont-eye"></i><span class="text"><?php echo (int)($screenshot->clickCount); ?></span></a>
                    <!-- pin -->
                    <a href="javascript:void(0)" class="pull-right pin_count <?php echo ($screenshot->pin == true) ? 'pin' : 'unpin'; ?>" title="<?php echo ($is_login) ? (($screenshot->pin == true) ? 'Unpin' : 'Pin') : 'Login to pin this Screenshot to your Pinboard'; ?>" data-ss-id="<?php echo (int)($screenshot->id); ?>"><i class="iconfont-pin"></i><span class="text">
                    <?php echo (int)($screenshot->pinCount); ?></span></a>
                </div>
            </li>
<?php endforeach; ?>
        </ul>
    <!--    <div class="more_items"><a class="infinite-more-link" href="<?php echo site_url('search'); ?>?q=<?php
    echo urlencode($q); ?>&m=ajax&p=<?php echo (int)$p+1; ?>">More</a></div> -->
    <div class="view_items">View <span id="show_num"></span> of <span id="total_num"><?php echo (int)$result_num; ?></span></div>
<?php if ( isset($have_more) ) : ?>
    <div class="more_items"><a id="load_more" href="javascript:void(0);"><i class="iconfont-down-open"></i></a></div>
<?php endif; ?>
    </div><!-- end: content_block -->
    
    <div id="footer_block" class="row-fluid">
        <div class="span12 footer">Copyright &copy; 2013 SayMac All rights reserved.<br>
        Any questions or suggestions: <a href="mailto:info@saymac.com">info@saymac.com</a>
        <br>
        query in <?php echo $time; ?> seconds
        </div>
    </div>
</div><!-- end main -->

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
</body>
</html>