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
<link rel="shortcut icon" href="img/favicon.ico" />
<!--[if lt IE 9]>
<script type="text/javascript" src="js/html5shiv.js"></script>
<![endif]-->
<link type="text/css" rel="stylesheet" href="css/bootstrap.min.css">
<style type="text/css">
body {
  background-color: #303540;
  color: #FFF;
  margin-left: 0px;
  margin-top: 0px;
  margin-right: 0px;
  height: 100%;
  width: 100%;
}
#container {
    width: 920px;
    margin: 0 auto;
}
.app {
    display: inline-block;
    width: 160px;
    height: 120px;
    border: 2px solid #303540;
    float: left;
    text-align: center;
    margin: 20px 10px 0 10px;
    border-radius: 6px;
}
.app_icon {
    width: 60px;
    height: 60px;
    margin: 5px;
    border-radius: 12px;
    border: 2px solid #333;
}
.app_name {
    height: 3.6em;
    overflow: hidden;
    font-size: 1em;
    line-height: 1.2em;
}
.result {
    text-align: center;
}
.highlight {
    border: 2px solid #FF5500;
}
.clear_both {
    clear: both;
}
#search_box {
    margin: 15px auto;
    text-align: center;
}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    // Lazy Load
    $("img.lazy").lazyload({
        effect: "fadeIn"
    });
    
    $('img.app_icon').mouseover(function(e){
        $(this).css('cursor', 'pointer');
        $(this).addClass('highlight');
    }).mouseout(function(e){
        $(this).removeClass('highlight');
    }).click(function(e){
        var app_id = $(this).parent('div.app').attr('id');
        var type = $(this).parent('div.app').data('type');
        var appPlatform = $(this).parent('div.app').data('appplatform');
        var appPlatformId = $(this).parent('div.app').data('appplatformid');
        var appName = $(this).parent('div.app').data('appname');
        var developer = $(this).parent('div.app').data('developer');
        var category = $(this).parent('div.app').data('category');
        var appIconUrl = $(this).attr('rel');

        // 填入app資訊到parent視窗的容器
        parent.active_user_screenshot['app'] = {
            id : app_id, 
            type : type, 
            appPlatform : appPlatform, 
            appPlatformId : appPlatformId, 
            appName : appName, 
            developer : developer, 
            category : category, 
            appIconUrl : appIconUrl
            };
        parent.user_screenshot_arr[parent.active_user_screenshot.id] = parent.active_user_screenshot;
        // 填到parent視窗畫面上
        parent.$('#app_name').text(appName);
        parent.$('#app_seller').text(developer);
        parent.$('#app_category').text(category);
        parent.$('#app_platform').text(parent.platform_arr[appPlatform]);
        parent.$('#app_icon').attr({src:appIconUrl}).addClass('icon_fill');

        parent.$.fancybox.close();
    });
});
</script>
</head>

<body>
<?php include_once("analyticstracking.php") ?>
<div id="search_box">
    <form id="appname_search_form" class="form-search" method="get" action="">
    <input id="search_term" type="text" class="input-medium search-query" name="q" value="<?php echo htmlspecialchars($term); ?>">
    <button type="submit" class="btn btn-small">Search</button>
    </form>
</div>
<div id="container">
    <div class="result">Search: <?php echo count($source); ?> results</div>
<?php foreach ( $source as $appPlatformId => $app ) : ?>
    <div class="app" id="<?php echo htmlspecialchars($app['id']); ?>" data-type="<?php echo htmlspecialchars($app['type']); ?>" data-appplatform="<?php echo htmlspecialchars($app['appPlatform']); ?>" data-appplatformid="<?php echo htmlspecialchars($app['appPlatformId']); ?>" data-appname="<?php echo htmlspecialchars($app['appName']); ?>" data-developer="<?php echo htmlspecialchars($app['developer']); ?>" data-category="<?php echo htmlspecialchars($app['category']); ?>">
        <img class="app_icon lazy" src="img/thumb_loading.gif" data-original="<?php echo htmlspecialchars($app['appIconUrl']); ?>" width="60" height="60" rel="<?php echo htmlspecialchars($app['appIconUrl']); ?>" />
        <div class="app_name"><?php echo htmlspecialchars($app['appName']); ?></div>
    </div>
<?php endforeach; ?>
<div class="clear_both"></div>
</div>
</body>
</html>