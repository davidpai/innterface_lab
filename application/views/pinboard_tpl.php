<!DOCTYPE html>
<html>
<head>
<title>Pinboard | innterface - mobile application design patterns library and user interface (UI) search engine</title>
<base href="<?php echo base_url(); ?>" />
<meta charset="UTF-8" />
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
<link type="text/css" rel="stylesheet" href="js/fancybox/jquery.fancybox.css?v=2.1.4" media="screen" />
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

-->
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
<script type="text/javascript" src="js/matchMedia.js"></script>
<script type="text/javascript" src="js/matchMedia.addListener.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.fancybox.pack.js?v=2.1.4"></script>
<script type="text/javascript">
$(document).ready(function(){
    // Lazy Load
    $("img.lazy").lazyload({
        effect: "fadeIn"
    });
    
    /* matchMedia => 600px以下(不含)用換頁方式載入, 600px以上(含)用modal方式載入 */
    
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
                width: 800,
                height: 600,
                minWidth: 520,
                minHeight: 450,
                maxWidth: 800,
                maxHeight: 600,
                closeBtn: true,
                iframe: {
                    scrolling : 'auto',
                    preload   : false
                }
            }); 
        }
    }
    
    // 點下刪除圖示
    $('.thumb_delete').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        if ( confirm('Are you sure to unpin this screenshot?') ) {
            var $this = $(this);
            $.post($this.attr('href'), { ss_id : $this.attr('id') }, function(res){
                if ( res.error == 0 ) {
                    // 消失的動畫
                    $this.parent('li').animate({ opacity:0.25, height:0, width:0, margin:0 }, 1200, function(){
                        $(this).remove();
                    });
                } else {
                    alert_error(res);
                }
            },'json');
        }
        return false;
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
    <td width="170" bgcolor="#FFFFFF"><img src="images/top_pinboard.png" alt="Upload History" width="170" height="80" /></td>
    <td align="right" bgcolor="#FFFFFF"><span class="style8"><!--<a href="javascript:void(0)" class="style8" style="color: #FF5522;">Share</a>&nbsp;&nbsp;|&nbsp;&nbsp;-->
<?php if ( isset($cancel_url) ) : ?>
    <a href="<?php echo $cancel_url; ?>" class="style8" style="color: #333333;">Cancel</a>
<?php else : ?>
    <a href="javascript:history.go(-1)" class="style8" style="color: #333333;">Cancel</a>
<?php endif; ?>
    </span></td>
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
            <a class="thumb_delete" id="<?php echo htmlspecialchars($screenshot->id); ?>" href="api/user/remove_pin"><img src="images/button_remove.png" width="25" height="25" /></a>
            <a class="link-iframe" data-fancybox-type="iframe" href="screenshot?id=<?php echo urlencode($screenshot->id); ?>"><img rel="<?php echo htmlspecialchars($screenshot->id); ?>" class="thumb_img lazy" src="img/thumb_loading.gif" data-original="<?php echo htmlspecialchars($screenshot->url); ?>" /></a>
            <div class="thumb_title"><?php echo htmlspecialchars($screenshot->appName); ?></div>
        </li>
        <?php endforeach; ?>
    </ul>
    <div style="clear: both;"></div>
</div>
</body>
</html>
