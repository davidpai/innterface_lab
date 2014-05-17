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
<link rel="shortcut icon" href="img/favicon.ico" />
<!--[if lt IE 9]>
<script type="text/javascript" src="js/html5shiv.js"></script>
<![endif]-->
<link type="text/css" href="css/bootstrap.min.css" rel="stylesheet" media="screen">
<style type="text/css">
#msg_block {
	display: relative;
	margin: 150px auto;
	width: 70%;
	border: 1px solid grey;
}

p {
	line-height: 24px;
	padding: 10px;
}

.msg {
	border-bottom: 1px solid grey;
}

.tip {
	font-size: 13px;
	color: grey;
}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript">
	var pause = 3000;
<?php
if ( isset($pause) && is_numeric($pause) ) {
	echo "var pause = {$pause};";
}
?>
        setTimeout("window.history.go(-1)", pause);
</script>
</head>
<body>
<div id="msg_block">
	<p class="msg"><?php echo $message; ?></p>
	<p class="tip">Please wait, We will back to previous page.</p>
</div>
</body>
</html>