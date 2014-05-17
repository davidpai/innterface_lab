<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd"><head>
<title>innterface | Screenshot</title>
<base href="<?php echo base_url(); ?>" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
<link href="css/reset.css" rel="stylesheet" media="screen">
<link href="css/iframe_mobile.css" rel="stylesheet" media="screen">
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" href="css/jquery.tagit.css">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
<script type="text/javascript" src="js/jquery.raty.min.js"></script>
<script type="text/javascript" src="js/tag-it.min.js"></script>
<script type="text/javascript" src="js/json2.js"></script>
<style type="text/css">
body {
    overflow-x: hidden;
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
#tag_wrapper ul.tagit li.tagit-choice a {
    margin-top: -8px;
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
.thumb_img {
  border-radius: 6px 6px 6px 6px;
}

</style>
<script type="text/javascript">
$(document).ready(function(){
    
    $('#rating_star').raty({
        path: 'img/',
        score: <?php echo (float)$screenshot->averageUserRating; ?>,
        readOnly: true
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
                $('#btn_save').attr('disabled',false).text('Add Tag').removeClass('done').addClass('save');
                console.log('Tag is added.');
            },
            afterTagRemoved: function(event, ui){
                enter_num = 0;
                $('#btn_save').attr('disabled',false).text('Add Tag').removeClass('done').addClass('save');
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
});
</script>
</head>
<body>
<?php include_once("analyticstracking.php") ?>
<div id="slider">
    <ul class="tabs">
        <li class="next"><a id="screenshot" href="screenshot?id=<?php echo urlencode($id); ?>&keyword=<?php echo urlencode($keyword); ?>&mode=mobile1" rel="<?php echo htmlspecialchars($id); ?>"><img src="img/prev_off.png" alt="back" /></a></li>
    </ul>

    <div class="tab_container">
        <ul>
            <li style="left: 0px; overflow-x:hidden;">
                <div class="app_info">
                    <a href="<?php echo htmlspecialchars($screenshot->trackViewUrl); ?>" target="_blank"><div class="app_iconbox"></div></a>
                    <div class="app_icon"><a href="<?php echo htmlspecialchars($screenshot->trackViewUrl); ?>" target="_blank"><img src="<?php echo ($screenshot->artworkUrl100 != '') ? htmlspecialchars($screenshot->artworkUrl100) : htmlspecialchars($screenshot->iconUrl100); ?>" /></a>
                    </div>
                    <p class="app_name"><?php echo htmlspecialchars($screenshot->trackName); ?></p>
                    <p class="app_inc"><?php echo htmlspecialchars($screenshot->artistName); ?></p>
                    <div class="app_etc">
                    
                    <span>Category: <?php echo htmlspecialchars($screenshot->primaryGenreName); ?></span>
                    <span>Version: <?php echo htmlspecialchars($screenshot->version); ?></span>
                    <span>Rated: <?php echo htmlspecialchars($screenshot->averageUserRating); ?></span>
                    
                    <ul>
                        <li><div id="rating_star"></div>(<?php echo htmlspecialchars(number_format((int)$screenshot->userRatingCount)); ?>)
                        </li>
                    </ul>
                    
                    <p  class="my_btn"><a href="<?php echo htmlspecialchars($screenshot->trackViewUrl); ?>" target="_blank" id="itunes_button">View in iTunes</a></p>
                    </div>
                    
<?php if ( $is_login ) : ?>
                <div id="tag_wrapper">
                    <p>Tags</p>
                    <input type="text" id="tags" name="tags" value="" maxlength="255" placeholder="Enter tags separated by commas" />
                    <button id="btn_save" class="save">Add Tag</button>
                    <div style="clear:right;"></div>
                </div>
<?php endif; ?>

                    <p class="app_inc2">Relate Screenshot</p>
                    <?php foreach ( $relate_screenshot_arr as $relate_screenshot ) : ?>
                    <p class="sc"><a class="sc" href="screenshot?id=<?php echo urlencode($relate_screenshot->id); ?>&tag=<?php echo urlencode($relate_screenshot->tag); ?>&mode=mobile1"><img class="thumb_img" src="<?php echo htmlspecialchars($relate_screenshot->url); ?>" /></a></p>
                    <?php endforeach; ?>
                </div>
            </li>
        </ul>
    </div>
</div>
</body>
</html>