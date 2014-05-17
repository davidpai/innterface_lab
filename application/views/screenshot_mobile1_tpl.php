<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd"><head>
<title>Innterface | Screenshot</title>
<base href="<?php echo base_url(); ?>" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="css/reset.css" rel="stylesheet" media="screen">
<link href="css/iframe_mobile.css" rel="stylesheet" media="screen">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
<style type="text/css">
body {
    overflow-x: hidden;
    background-color: rgb(206,210,217);
}

.thumb_img {
  border-radius: 6px 6px 6px 6px;
}

</style>
<script type="text/javascript">
$(document).ready(function(){

    $("img.lazy").lazyload({
        effect: "fadeIn"
    });
});
</script>
</head>
<body>
<?php include_once("analyticstracking.php") ?>
<div id="slider">
    <div id="back"><a href="search?keyword=<?php echo urlencode($keyword); ?>"><img src="img/back_off.png" alt="back" /></a></div>
    <ul class="tabs">
        <li class="prev"><a href="screenshot?id=<?php echo urlencode($id); ?>&keyword=<?php echo urlencode($keyword); ?>&mode=mobile2"><img src="img/prev_off.png" alt="open" /></a></li>
    </ul>

    <div class="tab_container">
        <ul>
            <li style="left: 0px;">
                <div class="row-fluid"><img class="thumb_img lazy" src="img/thumb_loading.gif" data-original="<?php echo htmlspecialchars($screenshot->url); ?>" /></div>
            </li>
        </ul>
    </div>
</div>
</body>
</html>