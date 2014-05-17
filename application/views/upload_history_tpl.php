<!DOCTYPE html>
<html>
<head>
<title>Upload History | innterface - mobile application design patterns library and user interface (UI) search engine</title>
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
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
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
}
.style8 {font-family: "Lucida Grande"; font-size: 14px; color: #333333; font-weight: bold; }
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
.style12 {font-family: "Lucida Grande"; font-size: 14px; color: #B6B9BF; }
.style1 {	font-family: "Lucida Grande";
	font-size: 14px;
	color: #BFBFBF;
}
.style14 {font-family: "Lucida Grande"; font-size: 14px; color: #FFFFFF; }
.style15 {
	font-size: 20px;
	font-weight: bold;
	color: #FFFFFF;
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
    margin: 0 auto;
    text-align: center;
}

@media screen and (max-width:744px) {
    .adaptive_width {
        width: 372px;
    }
}
@media screen and (min-width:744px) and (max-width:1116px) {
    .adaptive_width {
        width: 744px;
    }
}
@media screen and (min-width:1116px) and (max-width:1488px) {
    .adaptive_width {
        width: 1116px;
    }
}
@media screen and (min-width:1488px) and (max-width:1860px) {
    .adaptive_width {
        width: 1488px;
    }
}
@media screen and (min-width:1860px) {
    .adaptive_width {
        width: 1860px;
    }
}

#container li {
  position: relative;
  width: 324px;
  height: 484px;
  margin: 0 24px 4em 24px;
  float: left;
}

.thumb_img {
  border-radius: 6px 6px 6px 6px;
  box-shadow: 0 4px 8px #21242C;
  width: 320px;
  height: 480px;
  overflow: hidden;
  border: 2px solid transparent;
  margin: 0 auto;
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

.thumb_over {
    border: 2px solid #FF5500;
}

-->
</style>
<script type="text/javascript">
$(document).ready(function(){
    // Lazy Load
    $("img.lazy").lazyload({
        effect: "fadeIn"
    });
    
    $('.thumb_img').mouseover(function(){
        $(this).addClass('thumb_over');
    }).mouseout(function(){
        $(this).removeClass('thumb_over');
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
                        if (n > 0) {
                        } else {
                            location.reload();
                        }
                    });
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
    <td width="170" bgcolor="#FFFFFF"><img src="images/top_uploadhistory.png" alt="Upload History" width="170" height="80" /></td>
    <td align="right" bgcolor="#FFFFFF"><a href="upload/add" class="style8">Add</a>　|　<a href="<?php echo ($cancel_url != '') ? htmlspecialchars($cancel_url) : 'javascript:history.go(-1);'; ?>" class="style8" style="color: #333;">Cancel</a></td>
    <td width="32" bgcolor="#FFFFFF">&nbsp;</td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="16" bgcolor="#303540" background="images/top_shadow.png">&nbsp;</td>
  </tr>
</table>
</div>
<div id="container" class="adaptive_width" >
    <ul>
        <?php foreach( $query->result() as $screenshot ) : ?>
        <li>
            <a class="thumb_delete" id="<?php echo htmlspecialchars($screenshot->id); ?>" href="api/user/delete_screenshot"><img src="images/button_remove.png" width="25" height="25" /></a>
            <a href="upload/edit?screenshot_id=<?php echo urlencode($screenshot->screenshot_id); ?>"><img rel="<?php echo htmlspecialchars($screenshot->id); ?>" class="thumb_img lazy" src="img/thumb_loading.gif" data-original="webdata/upload_file/<?php echo htmlspecialchars(substr($screenshot->file_name, strrpos($screenshot->file_name,'.')-3,3)); ?>/<?php echo htmlspecialchars($screenshot->file_name); ?>" /></a>
            <div class="thumb_title"><?php echo htmlspecialchars($screenshot->appName); ?></div>
        </li>
        <?php endforeach; ?>
    </ul>
    <div style="clear: both;"></div>
</div>
</body>
</html>
