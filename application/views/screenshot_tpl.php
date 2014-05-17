<!doctype html>
<head>
<title>innterface | Screenshot</title>
<base href="<?php echo base_url(); ?>" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
<link href="css/reset.css" rel="stylesheet" media="screen" />
<link href="css/iframe.css" rel="stylesheet" media="screen" />
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" href="css/jquery.tagit.css">
<link rel="stylesheet" href="css/fontello.css" media="screen" />
<link rel="stylesheet" href="css/jquery.qtip.css" media="screen" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
<script type="text/javascript" src="js/jquery.raty.min.js"></script>
<script type="text/javascript" src="js/tag-it.min.js"></script>
<script type="text/javascript" src="js/json2.js"></script>
<script type="text/javascript" src="js/jquery.qtip.min.js"></script>
<style type="text/css">
body {
    width: 940px;
    height: 600px;
}
.row-fluid {
    height: 100%;
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
    color: #333;
}
div.extras a.unpin {
    color: #666;
    opacity: .7;
}
#rating_star {
    float: left;
}
#tag_wrapper {
    margin-top: 15px;
    border-top: 1px solid #CCC;
    color: #7F7F7F;
    font-size: 1.23076923em;
}
#tag_wrapper p {
    margin-top: 15px;
    font-size: 1.125em;
}
#tag_wrapper ul.tagit li.tagit-new {
    width: 18em;
}
#tag_wrapper ul.tag_list li {
    display: block;
    float: left;
    border: 1px solid #D3D3D3;
    font-size: 0.875em;
    font-weight: normal;
    margin: 3px 7px 4px 0;
    border-radius: 4px;
    background: url("css/smoothness/images/ui-bg_glass_75_e6e6e6_1x400.png") repeat-x scroll 50% 50% #E6E6E6;
}
#tag_wrapper ul.tag_list li a {
    vertical-align: baseline;
    padding: 0.3em 0.5em 0.2em 0.5em;
    line-height: 1.2em;
    display: block;
    color: #555;
    text-decoration: none;
}
#tag_wrapper .tag_input {
    margin-top: 3px;
}
#btn_save {
    border: 1px solid #7F7F7F;
    border-radius: 3px;
    font-size: 0.8125em;
    margin-top: 5px;
    float: right;
}
#btn_save.save {
    cursor: pointer;
    background-color: #3F3F3F;
    color: #FFF;
}
#btn_save.done {
    border: 1px solid #FFF;
    cursor: default;
    background-color: #FFF;
    color: #7F7F7F;
}
.app_inc2 {
    margin-top: 15px;
}
</style>
<script>
window.addEventListener("load",function() {
    setTimeout(function(){
        window.scrollTo(0, 1);
    }, 10);
});

$(document).ready(function(){
    var is_login = <?php echo (int)$is_login; ?>;
    
    $("img.lazy").lazyload({
        effect: "fadeIn"
    });
    
    $('#rating_star').raty({
        path: 'img/',
        score: <?php echo (float)$screenshot->averageUserRating; ?>,
        readOnly: true
    });

    $('div.extras a').qtip({
        position: {
            my: 'top center',
            at: 'bottom center'
        }
    });
    $('div.extras a.pin_count').click(function(e){
        e.preventDefault();
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
                window.location.href = 'login';
            } else {
                window.top.location.href = 'login';
            }
        }
    });
    
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
    
    $(document).ajaxError(function(event, request, settings, exception) {
        alert('Sorry, system get some problem, please reload page or restart your browser.');
        console.log(settings);
    });
});
</script>
</head>

<body>
<?php include_once("analyticstracking.php") ?>
    <div class="container-fluid" style="width:940px; height:600px;">
        <div class="left" style="width:600px">
            <div class="row-fluid">
                <img id="screenshot" src="<?php echo htmlspecialchars($screenshot->url); ?>" rel="<?php echo htmlspecialchars($screenshot->id); ?>" />
                <div class="extras">
                    <a href="javascript:void(0)" class="click_count unpin" title="How many times had this screenshot been viewed" data-ss-id="<?php echo (int)($screenshot->id); ?>"><i class="icon-eye"></i><span class="text"><?php echo (int)($screenshot->clickCount); ?></span></a>
                    <a href="javascript:void(0)" class="pin_count <?php echo ($screenshot->pin == true) ? 'pin' : 'unpin'; ?>" title="<?php echo ($is_login) ? (($screenshot->pin == true) ? 'Unpin' : 'Pin') : 'Login to pin this Screenshot to your Pinboard'; ?>" data-ss-id="<?php echo (int)($screenshot->id); ?>"><i class="icon-heart"></i><span class="text"><?php echo (int)($screenshot->pinCount); ?></span></a>
                </div>
            </div>
        </div>
        
        <div class="right" style="width:340px">
            <div class="app_info">
                <div class="app_icon"><a href="<?php echo htmlspecialchars($screenshot->trackViewUrl); ?>" target="_blank"><img src="<?php echo ($screenshot->artworkUrl100 != '') ? htmlspecialchars($screenshot->artworkUrl100) : htmlspecialchars($screenshot->iconUrl100); ?>" /></a></div>
                <p class="app_name"><?php echo htmlspecialchars($screenshot->trackName); ?></p>
                <p class="app_inc"><?php echo htmlspecialchars($screenshot->artistName); ?></p>
                <div class="app_etc">
                    <ul>
                        <li>Category: <?php echo htmlspecialchars($screenshot->primaryGenreName); ?></li>
                        <li>Version: <?php echo htmlspecialchars($screenshot->version); ?></li>
                        <li>Rated: <?php echo htmlspecialchars($screenshot->averageUserRating); ?>
                            <ul>
                                <li><div id="rating_star"></div>(<?php echo htmlspecialchars(number_format((int)$screenshot->userRatingCount)); ?>)
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <p  class="my_btn"><a href="<?php echo htmlspecialchars($screenshot->trackViewUrl); ?>" target="_blank" id="itunes_button">View in iTunes</a></p>
                </div>
                <div id="tag_wrapper">
                    <p>Tags</p>
<?php if ( is_array($tags) && ! empty($tags) ) : ?>
                    <ul class="tag_list">
    <?php foreach ( $tags as $tag ) : ?>
                        <li><a href="search?q=<?php echo urlencode($tag); ?>&m=tag" target="_top"><?php echo htmlspecialchars($tag); ?></a></li>
    <?php endforeach; ?>
                    </ul>
                    <div style="clear:left;"></div>
<?php endif; ?>
<?php if ( $is_login ) : ?>
                    <div class="tag_input">
                    <p>Your Tags</p>
                    <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($user_screenshot_tag_list); ?>" maxlength="255" placeholder="Enter tags separated by commas" />
                    <button id="btn_save" class="save">Save</button>
                    <div style="clear:right;"></div>
                    </div>
<?php endif; ?>
                </div>
                <p class="app_inc2">Relate Screenshot</p>
                <?php foreach ( $relate_screenshot_arr as $relate_screenshot ) : ?>
                <a class="sc" href="screenshot?id=<?php echo urlencode($relate_screenshot->id); ?>"><img class="lazy" data-original="<?php echo htmlspecialchars($relate_screenshot->url); ?>" src="img/thumb_loading.gif" /></a>
                <?php endforeach; ?>
            </div>
        </div><!--right end-->
    </div>
</body>
</html>