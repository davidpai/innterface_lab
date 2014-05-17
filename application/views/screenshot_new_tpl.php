<!DOCTYPE html>
<html>
<head>
<title>Screenshot | innterface - mobile application design patterns library and user interface (UI) search engine</title>
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
<link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" media="screen">
<link type="text/css" rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css">
<link type="text/css" rel="stylesheet" href="css/jquery.tagit.css">
<link type="text/css" rel="stylesheet" href="css/fontello.css" media="screen" />
<link type="text/css" rel="stylesheet" href="css/jquery.qtip.css" media="screen" />
<link type="text/css" href="js/fancybox/jquery.fancybox.css?v=2.1.4" rel="stylesheet" media="screen" />
<!--[if lte IE 7]>
<link type="text/css" rel="stylesheet" href="css/fontello-ie7.css?v=20140108001" media="screen" />
<![endif]-->
<link type="text/css" rel="stylesheet" href="css/screenshot_new.css?v=20140212001" media="screen" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
<script type="text/javascript" src="js/jquery.raty.min.js"></script>
<script type="text/javascript" src="js/tag-it.min.js"></script>
<script type="text/javascript" src="js/json2.js"></script>
<script type="text/javascript" src="js/jquery.qtip.min.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.fancybox.pack.js?v=2.1.4"></script>
<style type="text/css">
</style>
<script type="text/javascript">
$(window).load(function(){
    setTimeout(function(){
        window.scrollTo(0, 1);
    }, 10);
});

$(document).ready(function(){
    var is_login = <?php echo (int)$is_login; ?>;
    var user_id = <?php echo isset($user_id) ? $user_id : 'null'; ?>;
    
    // 橫向圖片，寬度設為480，直向圖片，寬度設為300
    if ( $('#screenshot').width() >= $('#screenshot').height() ) {
        $('.thumb_img_wraper').css('max-width','480px');
        $('.thumb_img').css('max-width','480px');
    } else {
        $('.thumb_img_wraper').css('max-width','300px');
        $('.thumb_img').css('max-width','300px');
    }
    
    
    /* 如果沒有被包在iframe裡，#nav_bar_block的display要block */
    if ( top != self ) {
    } else {
        $('#nav_bar_block').removeClass('hide_block').addClass('show_block');
    }
    
    $("img.lazy").lazyload({
        effect: "fadeIn"
    });
    
    $('div.extras a').qtip({
        position: {
            my: 'top center',
            at: 'bottom center'
        }
    });
    
    $('#rating_star').raty({
        path: 'img/',
        score: <?php echo (float)$screenshot->averageUserRating; ?>,
        readOnly: true
    });
    
    // Like
    var like_it = function($elmt){
        var ss_id = $elmt.attr('data-ss-id');
        $.post('api/user/add_like', { ss_id : ss_id }, function(res){
            if ( res.error == 0 ) {
                var $likeCountElmt = $elmt.children('span.text');
                var nowCount = parseInt($likeCountElmt.text(),10)+1;
                $likeCountElmt.text(nowCount);
                if ( parent != self ) {
                    var $parentFrameElmt = $('a.like_count[data-ss-id="'+ss_id+'"]', window.top.document);
                    $parentFrameElmt.children('span.text').text(nowCount);
                    $parentFrameElmt.removeClass('unlike').addClass('like').attr('title', 'Unlike');
                }
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
                    var nowCount = parseInt($likeCountElmt.text(),10)-1;
                    $likeCountElmt.text(nowCount);
                    if ( parent != self ) {
                        var $parentFrameElmt = $('a.like_count[data-ss-id="'+ss_id+'"]', window.top.document);
                        $parentFrameElmt.children('span.text').text(nowCount);
                        $parentFrameElmt.removeClass('like').addClass('unlike').attr('title', 'Like');
                    }
                }
                $elmt.removeClass('like').addClass('unlike').attr('title', 'Like');
            } else {
                alert_error(res);
            }
        }, 'json');
    };
    
    $('div.extras a.like_count').click(function(e){
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
            if ( parent == self ) {
                window.location.href = 'login?r=<?php echo isset($return_url) ? urlencode($return_url) : ''; ?>';
            } else {
                window.top.location.href = 'login?r=<?php echo isset($return_url) ? urlencode($return_url) : ''; ?>';
            }
        }
    });

    // Dislike
    var dislike_it = function($elmt){
        var ss_id = $elmt.attr('data-ss-id');
        $.post('api/user/add_dislike', { ss_id : ss_id }, function(res){
            if ( res.error == 0 ) {
                var $dislikeCountElmt = $elmt.children('span.text');
                var nowCount = parseInt($dislikeCountElmt.text(),10)+1;
                $dislikeCountElmt.text(nowCount);
                if ( parent != self ) {
                    var $parentFrameElmt = $('a.dislike_count[data-ss-id="'+ss_id+'"]', window.top.document);
                    $parentFrameElmt.children('span.text').text(nowCount);
                    $parentFrameElmt.removeClass('undislike').addClass('dislike').attr('title', 'Undislike');
                }
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
                    var nowCount = parseInt($dislikeCountElmt.text(),10)-1;
                    $dislikeCountElmt.text(nowCount);
                    if ( parent != self ) {
                        var $parentFrameElmt = $('a.dislike_count[data-ss-id="'+ss_id+'"]', window.top.document);
                        $parentFrameElmt.children('span.text').text(nowCount);
                        $parentFrameElmt.removeClass('dislike').addClass('undislike').attr('title', 'Dislike');
                    }
                }
                $elmt.removeClass('dislike').addClass('undislike').attr('title', 'Dislike');
            } else {
                alert_error(res);
            }
        }, 'json');
    };
    
    $('div.extras a.dislike_count').click(function(e){
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
            if ( parent == self ) {
                window.location.href = 'login?r=<?php echo isset($return_url) ? urlencode($return_url) : ''; ?>';
            } else {
                window.top.location.href = 'login?r=<?php echo isset($return_url) ? urlencode($return_url) : ''; ?>';
            }
        }
    });
    
    // Pin
    $('div.extras a.pin_count').click(function(e){
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
                        var nowCount = parseInt($pinCountElmt.text(),10)+1;
                        $pinCountElmt.text(nowCount);
                        if ( parent != self ) {
                            var $parentFrameElmt = $('a.pin_count[data-ss-id="'+ss_id+'"]', window.top.document);
                            $parentFrameElmt.children('span.text').text(nowCount);
                            $parentFrameElmt.removeClass('unpin').addClass('pin').attr('title', 'Unpin');
                        }
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
                            var nowCount = parseInt($pinCountElmt.text(),10)-1;
                            $pinCountElmt.text(nowCount);
                            if ( parent != self ) {
                                var $parentFrameElmt = $('a.pin_count[data-ss-id="'+ss_id+'"]', window.top.document);
                                $parentFrameElmt.children('span.text').text(nowCount);
                                $parentFrameElmt.removeClass('pin').addClass('unpin').attr('title', 'Pin');
                            }
                        }
                        $this.removeClass('pin').addClass('unpin').attr('title', 'Pin');
                    } else {
                        alert_error(res);
                    }
                }, 'json');
            }
        } else {
            if ( parent == self ) {
                window.location.href = 'login?r=<?php echo isset($return_url) ? urlencode($return_url) : ''; ?>';
            } else {
                window.top.location.href = 'login?r=<?php echo isset($return_url) ? urlencode($return_url) : ''; ?>';
            }
        }
    });
    
    // Tag Save
    $('#btn_save').click(function(e){
        var tags = $("#tags").tagit("assignedTags");
        if ( tags.length > 0 ) {
            $(this).text('Saving..');
            console.log(tags.length);
            var screenshot = {  id: $('#screenshot').attr('rel'),
                                tags: tags.join(',')
                                };
            var screenshot_json = JSON.stringify(screenshot);
            var $this = $(this);
            $.post('api/screenshot/add_tags', { screenshot_json : screenshot_json }, function(res){
                if ( res.error == 0 ) {
                    $this.attr('disabled',true).text('Done').removeClass('save').addClass('done');
                } else {
                    if ( res.msg ) {
                        alert(res.msg);
                    } else {
                        alert('Sorry, system get some problem, please reload page or restart your browser.');
                    }
                    console.log(res);
                }
                return false;
            }, 'json');
        }
        return false;
    });
    
    // Tag
    var cache = {};
    var init_tag = function() {
        $("#tags").tagit({
            caseSensitive: false,
            allowSpaces: true,
            placeholderText: 'Enter tags separated by commas',
            removeConfirmation: true,
            singleField: true,
            afterTagAdded: function(event, ui){
                enter_num = 0;
                $('#btn_save').attr('disabled',false).text('Save').removeClass('done').addClass('save');
                console.log('Tag is added.');
            },
            beforeTagRemoved: function(event, ui) {
                //console.log(ui.tagLabel);
                var screenshot = {  id: $('#screenshot').attr('rel'),
                                    tags: ui.tagLabel
                                    };
                var screenshot_json = JSON.stringify(screenshot);
                $.post('api/screenshot/remove_tags', { screenshot_json : screenshot_json }, function(res){
                    if ( res.error == 0 ) {
                    } else {
                        if ( res.msg ) {
                            alert(res.msg);
                        } else {
                            alert('Sorry, system get some problem, please reload page or restart your browser.');
                        }
                        console.log(res);
                    }
                }, 'json');
            }, 
            afterTagRemoved: function(event, ui){
                enter_num = 0;
                $('#btn_save').attr('disabled',false).text('Save').removeClass('done').addClass('save');
                console.log('Tag is removed.');
            }, 
            autocomplete: {
                delay: 300,
                minLength: 2,
                source: function(request, response){
                    var term = request.term;
                    if ( term in cache ) {
                        response( cache[ term ] );
                        return;
                    }
                    $.get('api/tag/search', { term: request.term }, function(res){
                        if ( res.error == 0 ) {
                            //console.log(res.source);
                            if ( res.source.length == 0 ) {

                            } else {
                                cache[ term ] = res.source;
                                response(res.source);
                            }
                        } else {
                            if ( res.msg ) {
                                alert(res.msg);
                            } else {
                                alert('Sorry, system get some problem, please reload page or restart your browser.');
                            }
                            console.log(res);
                        }
                    },'json');
                }
            }
        });
    };
    init_tag();
    // tag & login
    // if not login, clicking tag will popup a login msg, and redirect to login page
    $('div.tag_input ul li.tagit-new input').click(function(){
        if ( !is_login ) {
            $('#login_msg p').text('Please Login to have your tag');
            $('#login_link').trigger('click');
        }
    });
    
    // note & login
    // if not login, clicking note will popup a login msg, and redirect to login page
    var comment = '<?php echo !empty($user_screenshot_comment) ? htmlspecialchars($user_screenshot_comment->comment) : ''; ?>';
    $('#your_comment').focus(function(){
        if ( !is_login ) {
            $('#login_msg p').text('Please Login to have your note');
            $('#login_link').trigger('click');
        }
    }).keydown(function(){
        if ( $.trim($('#your_comment').val()) != '' ) {
            $('#comment_btn_save').attr('disabled',false).text('Save').removeClass('btn-small-done').addClass('btn-small-save');
        }
    });
    
    // note save
    $('#comment_btn_save').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        var screenshot_id = $('#screenshot').attr('rel');
        var your_comment = $('#your_comment').val();
        $(this).text('Saving..');
        var $this = $(this);
        //if ( is_login ) {
            $.post('screenshot/ajaxPostComment', {comment: your_comment, screenshot_id: screenshot_id}, function(res){
                if ( res.error == 0 ) {
                    $this.attr('disabled',true).text('Done').removeClass('btn-small-save').addClass('btn-small-done');
                    console.log(res);
                    console.log('commnet is saved');
                } else {
                    if ( res.msg ) {
                        alert(res.msg);
                    } else {
                        alert('Sorry, system get some problem, please reload page or restart your browser.');
                    }
                    console.log(res);
                }
            }, 'json');
        //}
    });
    
    $('.fancybox').fancybox({
        margin: 0, 
        padding: [0,15,0,15], 
        closeBtn: false,
        maxWidth: 450, 
        minWidth: 0
        });
    $('#login_msg li.cancel a').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        $.fancybox.close();
        });
        
    $(document).ajaxError(function(event, request, settings, exception) {
        alert('Sorry, system get some problem, please reload page or restart your browser.');
        console.log(settings);
    });
});
</script>
</head>
<body>
<div id="nav_bar_block" class="hide_block"><a href="javascript:history.go(-1);">[Back]</a></div>
<div id="screenshot_block">
    <div class="thumb_img_wraper">
        <img id="screenshot" class="thumb_img" src="<?php echo htmlspecialchars($screenshot->url); ?>" rel="<?php echo htmlspecialchars($screenshot->id); ?>" alt="<?php echo htmlspecialchars($screenshot->tag_list); ?>" title="<?php echo htmlspecialchars($screenshot->appName); ?>" width="280" />
        <div class="extras">
            <!-- like -->
            <a href="javascript:void(0)" class="like_count pull-left <?php echo ($screenshot->like == true) ? 'like' : 'unlike'; ?>" title="<?php echo ($is_login) ? (($screenshot->like == true) ? 'Unlike' : 'Like') : 'Login to give this screenshot a thumb-up'; ?>" data-ss-id="<?php echo (int)($screenshot->id); ?>"><i class="iconfont-like"></i><span class="text"><?php echo (int)($screenshot->likeCount); ?></span></a>
            <!-- dislike -->
            <a href="javascript:void(0)" class="dislike_count pull-left <?php echo ($screenshot->dislike == true) ? 'dislike' : 'undislike'; ?>" title="<?php echo ($is_login) ? (($screenshot->dislike == true) ? 'Undislike' : 'Dislike') : 'Login to give this screenshot a thumb-down'; ?>" data-ss-id="<?php echo (int)($screenshot->id); ?>"><i class="iconfont-dislike"></i><span class="text"><?php echo (int)($screenshot->dislikeCount); ?></span></a>
            <!-- pin -->
            <a href="javascript:void(0)" class="pin_count <?php echo ($screenshot->pin == true) ? 'pin' : 'unpin'; ?>" title="<?php echo ($is_login) ? (($screenshot->pin == true) ? 'Unpin' : 'Pin') : 'Login to pin this Screenshot to your Pinboard'; ?>" data-ss-id="<?php echo (int)($screenshot->id); ?>"><i class="iconfont-pin"></i><span class="text"><?php echo (int)($screenshot->pinCount); ?></span></a>
            <!-- view -->
            <a href="javascript:void(0)" class="click_count unpin" title="How many times had this screenshot been viewed" data-ss-id="<?php echo (int)($screenshot->id); ?>"><i class="iconfont-eye"></i><span class="text"><?php echo (int)($screenshot->clickCount); ?></span></a>
        </div>
    </div>
</div>
<div id="related_info_block">
    <div class="info_wraper">
        <div class="app_icon"><a href="<?php echo htmlspecialchars($screenshot->appViewUrl); ?>" target="_blank"><img src="<?php echo htmlspecialchars($screenshot->appIconUrl); ?>" /></a></div>
        <p class="app_name"><?php echo htmlspecialchars($screenshot->appName); ?></p>
        <p class="app_artist"><?php echo htmlspecialchars($screenshot->developer); ?></p>
        <div class="app_misc">
            <ul>
                <li>Category: <?php echo htmlspecialchars($screenshot->category); ?></li>
                <li>Version: <?php echo (trim($screenshot->ss_version)!='') ? htmlspecialchars($screenshot->ss_version) : htmlspecialchars($screenshot->app_version); ?></li>
                <li>Rated: <?php echo htmlspecialchars($screenshot->averageUserRating); ?>
                    <ul>
                        <li><div id="rating_star"></div>(<?php echo htmlspecialchars(number_format((int)$screenshot->userRatingCount)); ?>)
                        </li>
                    </ul>
                </li>
            </ul>
            <p class="itunes_btn">
        <?php if ($screenshot->appPlatform == 'Apple') : ?>
            <a href="<?php echo htmlspecialchars($screenshot->appViewUrl); ?>" target="_blank" id="itunes_button">View in iTunes</a>
        <?php elseif ($screenshot->appPlatform == 'Google') : ?>
            <a href="<?php echo htmlspecialchars($screenshot->appViewUrl); ?>" target="_blank" id="itunes_button">View in Google Play</a>
        <?php else : ?>
            <a href="<?php echo htmlspecialchars($screenshot->appViewUrl); ?>" target="_blank" id="itunes_button">View in Windows Phone Store</a>
        <?php endif; ?>
            </p>
        </div>
<?php //if ( $is_login ) : ?>
        <div id="tag_wrapper">
            <div class="tag_input">
            <p>Tags</p>
            <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($user_screenshot_tag_list); ?>" maxlength="255" placeholder="Enter tags separated by commas" />
            <button id="btn_save" class="save">Save</button>
            <div style="clear:right;"></div>
            </div>
        </div>
        <div id="comment_wrapper">
            <p>Note</p>
            <div><textarea id="your_comment"><?php echo !empty($user_screenshot_comment) ? htmlspecialchars($user_screenshot_comment->comment) : ''; ?></textarea>
            <button id="comment_btn_save" class="btn-small btn-small-save">Save</button>
            <div style="clear:right;"></div>
            </div>
        </div>
<?php //endif; ?>
<?php if ( is_array($relate_screenshot_arr) && count($relate_screenshot_arr) > 0 ) : ?>
        <div id="relate_screenshot_block" class="clearfix">
        <p class="relate_screenshot">Relate Screenshot</p>
        <?php foreach ( $relate_screenshot_arr as $relate_screenshot ) : ?>
        <a class="sc" href="screenshot?id=<?php echo urlencode($relate_screenshot->id); ?>"><img class="lazy" data-original="<?php echo htmlspecialchars($relate_screenshot->url); ?>" src="img/thumb_loading.gif" alt="<?php echo htmlspecialchars($relate_screenshot->tag_list); ?>" title="<?php echo htmlspecialchars($relate_screenshot->appName); ?>" /></a>
        <?php endforeach; ?>
        </div>
<?php endif; ?>
    </div>
</div>
<?php if ( $is_login ) : ?>
<a id="login_link" class="fancybox" href="login"></a>
<?php else : ?>
<a id="login_link" class="fancybox" href="#login_msg"></a>
<?php endif; ?>
<div id="login_msg" class="modal_dialog">
    <p>Please Login to have your note</p>
    <ul>
        <li class="action">
            <a href="login" target="_parent">Login</a>
        </li>
        <li class="cancel">
            <a href="javascript:void(0)">Cancel</a>
        </li>
        <li class="clearfix"></li>
    </ul>
</div>

</body>
</html>