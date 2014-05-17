<!DOCTYPE html>
<html>
<head>
<title>Upload | innterface - mobile application design patterns library and user interface (UI) search engine</title>
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
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" href="css/jquery.tagit.css">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/tag-it.min.js"></script>
<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
<script type="text/javascript" src="js/json2.js"></script>
<style type="text/css">
<!--
ol, ul, li {
  margin: 0;
  padding: 0;
  border: 0;
  outline: 0;
  font-size: 100%;
  vertical-align: baseline;
  background: transparent;
}

ul {
  list-style: none;
}

body {
  background-color: #303540;
  margin-left: 0px;
  margin-top: 0px;
  margin-right: 0px;
  height: 100%;
  width: 100%;
}

img {
  border: 0;
}

.style8 {
  font-family: "Lucida Grande";
  font-size: 14px;
  color: #333333;
  font-weight: bold;
}

a:link {
  color: #FF5500;
  text-decoration: none;
}

a:visited {
  text-decoration: none;
  color: #FF5500;
}

a:hover {
  text-decoration: none;
  color: #FF5500;
}

a:active {
  text-decoration: none;
  color: #FF5500;
}

.style12 {
  font-family: "Lucida Grande";
  font-size: 14px;
  color: #B6B9BF;
}

.style1 {
  font-family: "Lucida Grande";
  font-size: 14px;
  color: #BFBFBF;
}

.style14 {
  font-family: "Lucida Grande";
  font-size: 14px;
  color: #FFFFFF;
}

.style15 {
  font-size: 1.25em;
  font-weight: bold;
  color: #FFFFFF;
}

.thumb_img {
  border-radius: 6px 6px 6px 6px;
  box-shadow: 0 4px 8px #21242C;
  height: 480px;
  overflow: hidden;
  width: 320px;
  border: 2px solid transparent;
}

.thumb_title {
  color: #FFF;
  text-align: center;
  font-family: "Lucida Grande";
  font-size: 1em;
  height: 1em;
  line-height: 1em;
  overflow: hidden;
}

.thumb_delete {
  position: absolute;
  top: 0;
  left: 0;
  margin: -8px 0 0 -8px;
}

.thumb_frame {
  border: 2px solid rgb(255,85,0);
}
.thumb_hover {
  border: 2px solid rgb(255,85,0);
}

#top {
  width: 100%;
  position: fixed;
  z-index: 99;
  box-shadow: 0 6px 12px #21242C;
}

#container {
  position: relative;
  top: 144px;
  left: 48px;
  width: 100%;
  height: 100%;
  clear: both;
}

#wrapRight {
  position: fixed;
  top: 120px;
  right: 0;
  height: 100%;
  background-color: #303540;
}

#wrapLeft {
  width: 70%;
  float: left;
}

#wrapLeft li {
  position: relative;
  width: 324px;
  height: 484px;
  margin: 0 48px 4em 0;
  float: left;
}

#ajax_indicator {
  float: right;
  visibility: hidden;
}

#app_name {
  height: 2.5em;
  line-height: 1.25em;
  overflow: hidden;
}

#btn_upload {
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  border-radius: 5px;
  width: 128px;
  height: 32px;
  line-height: 32px;
  font-family: "Lucida Grande";
  font-size: 14px;
  font-weight: bold;
  color: #FFF;
  border: 0;
  text-align: center;
}

.btn-upload-disable {
  cursor: default;
  opacity: 0.5;
  background-color: #F9926F;
}

.btn-upload-enable {
  cursor: pointer;
  opacity: 1;
  background-color: #FF5D24;
}

.icon_fill {
  border-radius: 18px;
  border: 5px solid #47484F;
}

.text_input {
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  border-radius: 5px;
  width: 94%;
  height: 100%;
  padding: 6px 8px;
  font-size: 1em;
  color: #BFBFC6;
  border: 1px solid #BFBFC6;
}

.ui-autocomplete {
  max-height: 300px;
  max-width: 268px;
  overflow-y: auto;
    /* prevent horizontal scrollbar */
  overflow-x: hidden;
}

/* IE 6 doesn't support max-height
* we use height instead, but this forces the menu to always be this tall
*/
* html .ui-autocomplete {
    height: 300px;
    width: 268px;
}
#search_term.ui-autocomplete-loading {
    background: white url('images/ajax-loader_16x16.gif') right center no-repeat;
}
.ui-menu-item a {
    font-size: 0.82em;
}
.tag-it {
    font-size: 0.82em;
}
-->
</style>
<script type="text/javascript">
// 建立幾個容器
// 點選中的screenshot
var active_user_screenshot = {};
// 畫面上全部的screenshot
var user_screenshot_arr = {};

<?php foreach( $query->result() as $ss ) : ?>
    user_screenshot_arr['<?php echo $ss->id; ?>'] = { id : '<?php echo $ss->id; ?>',
                                                      file_name : '<?php echo $ss->file_name ?>' };
<?php endforeach; ?>

$(document).ready(function(){
    // Lazy Load
    $("img.lazy").lazyload({
        effect: "fadeIn"
    });
    
    // Tag
    var tag_cache = {};
    var init_tag = function() {
        $("#tags").tagit({
            caseSensitive: false,
            allowSpaces: true,
            placeholderText: 'Tag (*Necessary)',
            removeConfirmation: true,
            singleField: true,
            afterTagAdded: function(event, ui){
                var tags = $(this).tagit("assignedTags");
                if ( tags.length > 0 ) {
                    $('#btn_upload').removeClass('btn-upload-disable');
                    $('#btn_upload').addClass('btn-upload-enable');
                } else {
                    $('#btn_upload').removeClass('btn-upload-enable');
                    $('#btn_upload').addClass('btn-upload-disable');
                }
                active_user_screenshot['tags'] = tags.join(',');
            },
            afterTagRemoved: function(event, ui){
                var tags = $(this).tagit("assignedTags");
                if ( tags.length > 0 ) {
                    $('#btn_upload').removeClass('btn-upload-disable');
                    $('#btn_upload').addClass('btn-upload-enable');
                } else {
                    $('#btn_upload').removeClass('btn-upload-enable');
                    $('#btn_upload').addClass('btn-upload-disable');
                }
                active_user_screenshot['tags'] = tags.join(',');
            }, 
            autocomplete: {
                delay: 300,
                minLength: 2,
                source: function(request, response){
                    var term = request.term;
                    if ( term in tag_cache ) {
                        response( tag_cache[ term ] );
                        return;
                    }
                    $.get('api/tag/search', { term: request.term }, function(res){
                        if ( res.error == 0 ) {
                            //console.log(res.source);
                            if ( res.source.length == 0 ) {

                            } else {
                                tag_cache[ term ] = res.source;
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
    }
    init_tag();

    // 使App資訊回復初始狀態
    var init_app_info = function(){
        $('#search_term').val('');
        $('#app_name').text('App Name');
        $('#app_seller').text('Seller');
        $('#app_category').text('Category');
        $('#app_icon').attr({src:'images/icon_app.png'}).removeClass('icon_fill');
    };
    
    // 選第一張screenshot
    var select_first = function() {
        var $this = $('.thumb_img').first();
        $this.addClass('thumb_frame'); // 框起來
        click_screenshot($this);
    };
    
    // 點按screenshot出現橘色框，回填App資訊和Tag
    var click_screenshot = function($this) {
        var screenshot_id = $this.attr('rel');  // screenshot的id記錄在img的rel屬性裡
        active_user_screenshot = user_screenshot_arr[screenshot_id];
        // 填入app資訊
        if ( active_user_screenshot.app ) {
            $('#search_term').val(active_user_screenshot.app.trackName);
            $('#app_name').text(active_user_screenshot.app.trackName);
            $('#app_seller').text(active_user_screenshot.app.artistName);
            $('#app_category').text(active_user_screenshot.app.primaryGenreName);
            $('#app_icon').attr({src:active_user_screenshot.app.iconUrl}).addClass('icon_fill');
        } else {
            init_app_info();
        }
        // 填入Tag
        if ( active_user_screenshot.tags ) {
            var tags_arr = active_user_screenshot.tags.split(',');
            $('#tags').tagit("removeAll");
            for (var i=0; i<tags_arr.length; i++) {
                $('#tags').tagit("createTag", tags_arr[i]);
            }
        } else {
            $('#tags').tagit("removeAll");
        }
    };
    // 頁面一載入自動選第一張screenshot
    select_first();
    
    // 點按出現橘色框，回填App資訊和Tag
    $('.thumb_img')
    .hover(function(){
            $(this).addClass('thumb_hover');
        },function(){
            $(this).removeClass('thumb_hover');
        })
    .click(function(){
        $('.thumb_img').removeClass('thumb_frame');
        $(this).addClass('thumb_frame');
        
        click_screenshot($(this));
    });
    
    // 點下刪除圖示
    $('.thumb_delete').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        if ( confirm('Are you sure to remove this screenshot?') ) {
            var $this = $(this);
            $.post($this.attr('href'), { screenshot_id : $this.attr('id') }, function(res){
                if ( res.error == 0 ) {
                    // 消失的動畫
                    $this.parent('li').animate({ opacity:0.25, height:0, width:0, margin:0 }, 800, function(){
                        $(this).remove();
                        var n = $('.thumb_img').length;
                        if ( n <= 0 ) {
                            window.location.href = 'upload';
                        }
                        select_first();
                    });
                } else {
                    if ( res.msg ) {
                        alert(res.msg);
                    } else {
                        alert('Sorry, system get some problem, please reload page or restart your browser.');
                    }
                    console.log(res);
                }
            }, 'json');

        }
        return false;
    });

    // 搜尋App的autocomplete
    var cache = {};
    $("#search_term").autocomplete({
        delay: 1200,
        minLength: 2,
        // 資料來源
        source: function( request, response ) {
            var term = request.term;
            if ( term in cache ) {
                response( cache[ term ] );
                return;
            }
            var $this = $(this);
            $.get('api/app/search', { term: request.term }, function(res){
                if ( res.error == 0 ) {
                    //console.log(res.source);
                    if ( res.source.length == 0 ) {
                        $("#search_term").removeClass('ui-autocomplete-loading');
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
        },
        response: function(event, ui){
            $(this).removeClass('ui-autocomplete-loading');
        },
        // 點選了選單項目的event
        select: function( event, ui ) {
            var iconUrl = ui.item.artworkUrl100 ? ui.item.artworkUrl100 : ( ui.item.iconUrl100 ? ui.item.iconUrl100 : "" ) ;
            // 填入app資訊到容器
            active_user_screenshot['app'] = {
                trackId: ui.item.trackId,
                trackName: ui.item.trackName,
                artistName: ui.item.artistName,
                primaryGenreName: ui.item.primaryGenreName,
                iconUrl: iconUrl
                };
            user_screenshot_arr[active_user_screenshot.id] = active_user_screenshot;
            // 填到畫面上
            $('#app_name').text(ui.item.trackName);
            $('#app_seller').text(ui.item.artistName);
            $('#app_category').text(ui.item.primaryGenreName);
            $('#app_icon').attr({src:iconUrl}).addClass('icon_fill');
        }
    })
    .data("ui-autocomplete")._renderItem = function( ul, item ) {
        // 自定搜尋結果下拉選單的外觀
        var iconUrl = item.artworkUrl60 ? item.artworkUrl60 : ( item.iconUrl53 ? item.iconUrl53 : "" ) ;
        var html = '<a style="font-size:0.82em"><img style="vertical-align:middle;margin-right:4px;" src="'+iconUrl+'" width="32" height="32" />'+item.trackName+'</a>';
        return $("<li></li>").data("autocomplete", item).css({borderBottom:"1px solid #EFEFEF"}).append(html).appendTo(ul);
    };

    // 按下Upload按鈕: 
    $('#btn_upload').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        if ( active_user_screenshot.tags ) {
            //console.log(active_user_screenshot);

            var screenshot_json = JSON.stringify(active_user_screenshot);
            $.post('api/user/add_screenshot_tag', { screenshot_json : screenshot_json }, function(res){
                if ( res.error == 0 ) {
                    var screenshot_id = res.msg;
                    $("#"+screenshot_id).parent('li').animate({ opacity:0.25, height:0, width:0, margin:0 }, 1200, function(){
                        $(this).remove();

                        var n = $('.thumb_img').length;
                        if ( n <= 0 ) {
                            window.location.href = 'upload';
                        }
                        
                        init_app_info();
                        $('#tags').tagit("removeAll");
                        select_first();

                    });
                } else {
                    if ( res.msg ) {
                        alert(res.msg);
                    } else {
                        alert('Sorry, system get some problem, please reload page or restart your browser.');
                    }
                    console.log(res);
                }
            }, 'json');

        }
    });
    
    $('#cancel').click(function(e){
        var n = $('.thumb_img').length;
        if ( n > 0 ) {
            if ( confirm('You still have screenshots not saved, are you sure to leave?') ) {
                $.get('upload/delete_null', {}, function(res){
                    if ( res.error == 0 ) {
                        history.go(-1);
                    } else {
                        if ( res.msg ) {
                            alert(res.msg);
                        } else {
                            alert('Sorry, system get some problem, please reload page or restart your browser.');
                        }
                        console.log(res);
                    }   
                }, 'json');
            }
        }
        return false;
    });
    
    $(document).ajaxError(function(event, request, settings, exception) {
        alert('Sorry, system get some problem, please reload page or restart your browser.');
        console.log(settings);
    });

});
</script>
</head>

<body>
<?php include_once("analyticstracking.php") ?>
<div id="top">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="192" height="80" bgcolor="#FFFFFF"><a href="home"><img src="images/top_logo.png" width="192" height="80" alt="innterface Logo" /></a></td>
    <td width="120" bgcolor="#FFFFFF"><img src="images/top_upload.png" alt="Upload" width="120" height="80" /></td>
    <td align="right" bgcolor="#FFFFFF"><a href="upload/add" class="style8">Add</a>　|　<a href="javascript:history.go(-1);" class="style8" style="color: #333333;" id="cancel">Cancel</a></td>
    <td width="32" bgcolor="#FFFFFF">&nbsp;</td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="16" bgcolor="#303540" background="images/top_shadow.png">&nbsp;</td>
  </tr>
</table>
</div>
<div id="container">
    <div id="wrapLeft">
        <ul>
            <?php foreach( $query->result() as $screenshot ) : ?>
            <li>
                <a class="thumb_delete" id="<?php echo htmlspecialchars($screenshot->id); ?>" href="api/user/delete_screenshot"><img src="images/button_remove.png" width="25" height="25" /></a>
                <a href="javascript:void(0)"><img rel="<?php echo htmlspecialchars($screenshot->id); ?>" class="thumb_img lazy" src="img/thumb_loading.gif" data-original="webdata/upload_file/<?php echo htmlspecialchars(substr($screenshot->file_name, strrpos($screenshot->file_name,'.')-3,3)); ?>/<?php echo htmlspecialchars($screenshot->file_name); ?>" /></a>
                <div class="thumb_title"><?php //echo htmlspecialchars($screenshot->file_name); ?></div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div id="wrapRight">
        <table width="336" border="0" align="left" cellpadding="0" cellspacing="0">
          <tr>
            <td width="32" height="16" background="images/line_vertital_dark.png">&nbsp;</td>
            <td width="272" colspan="2">&nbsp;</td>
            <td width="32">&nbsp;</td>
          </tr>
          <tr>
            <td height="32" background="images/line_vertital_dark.png">&nbsp;</td>
            <td width="272" colspan="2"><span class="style12">Define screenshot information</span></td>
            <td width="32">&nbsp;</td>
          </tr>
          <tr>
            <td height="32" background="images/line_vertital_dark.png">&nbsp;</td>
            <td colspan="2"><div id="autocomplete_container" class="ui-front"><input type="text" id="search_term" class="text_input" name="search_term" value="" maxlength="255" placeholder="App Name" /></div></td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td height="16" background="images/line_vertital_dark.png">&nbsp;</td>
            <td colspan="2">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td height="96" background="images/line_vertital_dark.png">&nbsp;</td>
            <td colspan="2"><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="96" height="96"><img id="app_icon" src="images/icon_app.png" alt="App icon" width="96" height="96" /></td>
                <td width="16">&nbsp;</td>
                <td>
                    <div id="app_name" class="style1 style15">App Name</div>
                    <div id="app_seller" class="style1">Seller</div>
                    <div id="app_category" class="style1">Category</div>
              </tr>
            </table></td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td height="32" background="images/line_vertital_dark.png">&nbsp;</td>
            <td colspan="2">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td height="32" background="images/line_vertital_dark.png">&nbsp;</td>
            <td colspan="2">
                <input type="text" id="tags" name="tags" value="" maxlength="255" placeholder="Tag (*Necessary)" />
            </td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td height="16" background="images/line_vertital_dark.png">&nbsp;</td>
            <td colspan="2">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td height="32" background="images/line_vertital_dark.png">&nbsp;</td>
            <td width="128" background="images/button_orange_128.png">
                <button id="btn_upload" class="btn-upload-disable">Upload</button>
            </td>
            <td width="144">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td height="32" background="images/line_vertital_dark.png">&nbsp;</td>
            <td width="128">&nbsp;</td>
            <td width="144">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table>
    </div>
    <div style="clear:both;"></div>
</div>
</body>
</html>
