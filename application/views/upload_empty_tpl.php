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
<link type="text/css" rel="stylesheet" href="uploader/uploadify.css">
<link type="text/css" rel="stylesheet" href="js/fancybox/jquery.fancybox.css?v=2.1.4" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="uploader/jquery.uploadify.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.fancybox.pack.js?v=2.1.4"></script>
<style type="text/css">
body {
    background-color: #303540;
    margin-left: 0px;
    margin-top: 0px;
    margin-right: 0px;
}
img {
    border: 0;
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
.style10 {font-family: "Lucida Grande"; font-size: 14px; color: #FFFFFF; font-weight: bold; }
.style12 {font-family: "Lucida Grande"; font-size: 14px; color: #B6B9BF; }

#top {
  width: 100%;
  position: fixed;
  z-index: 99;
  box-shadow: 0 6px 12px #21242C;
  top: 0;
}

#container {
    position: relative;
    top: 144px;
    margin: 0 auto;
    text-align: center;
}

#uploader_box {
    position: relative;
    top: 50%;
    left: 50%;
    width: 320px;
}
#uploader_box_inner {
    position: relative;
    top: -50%;
    left: -50%;
}
#uploader_box_inner h3 {
    color: #FFF;
    font-size: medium;
    white-space: nowrap;
}

#file_upload {
    margin: 0 auto;
}

#btn_history {
    display: block;
    width: 192px;
    height: 32px;
    line-height: 32px;
    background: transparent url('images/button_dark_272_disable.png') top left no-repeat;
    color: #FFF;
}
#btn_history:hover {
    background: transparent url('images/button_dark_272.png') top left no-repeat;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
    $('#upload_form').submit(function(e){
        e.preventDefault();
        e.stopPropagation();
        var url = $(this).attr('action');
        var file_name = $('#file_name').val();
        var origin_file_name = $('#origin_file_name').val();
        $.post(url, {file_name: file_name, origin_file_name: origin_file_name}, function(res){
            if ( res.error == 0 ) {
                location.href = 'upload/tag';
            } else {
                if ( res.msg ) {
                    alert(res.msg);
                } else {
                    alert('Sorry, system get some problem, please reload page or restart your browser.');
                }
                console.log(res);
            }
        }, 'json');
    });
    
    // upload component
    var file_name = {};
    var origin_file_name = {};
    var file_size = '10MB';
    var uploadError = function(fileObj, errorCode, errorMsg, errorString) {
        alert(errorMsg+' '+errorString);
    };
    var uploadSuccess = function(fileObj, data, response) {
        if ( $.trim($('#file_name').val()) != '' ) {
            $('#file_name').val( $('#file_name').val() + '::' + data );
        } else {
            $('#file_name').val(data);
        }
        if ( $.trim($('#origin_file_name').val()) != '' ) {
            $('#origin_file_name').val( $('#origin_file_name').val() + '::' + fileObj.name );
        } else {
            $('#origin_file_name').val(fileObj.name);
        }
    };
    var queueComplete = function(queueData) {
        $('#upload_form').trigger('submit');
        //alert(queueData.uploadsSuccessful + ' files were successfully uploaded.');
    };
    var dialogOpen = function() {
        $.fancybox.open('<div></div>', {
            padding: 0,
            margin: 0, 
            width: 0,
            height: 0, 
            minHeight: 0,
            minWidth: 0,
            maxWidth: 0,
            maxHeight: 0, 
            closeBtn: false, 
            modal: true
            });
    };
    var dialogClose = function() {
        $.fancybox.close(true);
    };
    <?php $timestamp = time(); ?>
    $('#file_upload').uploadify({
        'swf'      : '<?php echo base_url('uploader/uploadify.swf'); ?>',
        'uploader' : '<?php echo base_url('uploader/uploadify.php'); ?>', 
        'queueID' : 'fileQueue', 
        'queueSizeLimit' : 100, 
        'buttonText' : 'Select Files..', 
        'buttonClass' : 'border_radius', 
        'fileSizeLimit' : file_size, 
        'fileTypeDesc' : 'Images (.png, .jpg, .jpeg, .gif)', 
        'fileTypeExts' : '*.png;*.jpg;*.jpeg;*.gif',
        'formData'     : {
            'timestamp' : '<?php echo $timestamp;?>',
            'token'     : '<?php echo md5('unique_salt' . $timestamp);?>'
        },
        'multi' : true, 
        'auto' : true, 
        'removeCompleted' : true, 
        'removeTimeout' : 1,
        'onDialogOpen' : dialogOpen,
        'onDialogClose' : dialogClose, 
        'onUploadError' : uploadError, 
        'onUploadSuccess' : uploadSuccess,
        'onQueueComplete' : queueComplete
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
    <td align="right" bgcolor="#FFFFFF"><a href="<?php echo ($cancel_url != '') ? htmlspecialchars($cancel_url) : 'upload/history'; ?>" class="style8" style="color: #333;">Cancel</a></td>
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
    <div id="uploader_box">
        <div id="uploader_box_inner">
            <img src="images/icon_drop.png" alt="Drop" width="192" height="192" />
            <h3>
<?php if ( isset($empty) ) : ?>
                You don't have any screenshots uploaded, <br />
<?php endif; ?>
                Click here to upload your screenshots</h3>
            <form id="upload_form" method="post" action="upload/set_session_var">
                <div>
                    <input id="file_upload" name="file_upload" type="file" multiple="true" />
                    <input type="hidden" name="file_name" id="file_name" value="">
                    <input type="hidden" name="origin_file_name" id="origin_file_name" value="">
                    <div id="fileQueue"></div>
                </div>
            </form>
        </div>
    </div>
</div>
<p>&nbsp;</p>
<div id="fake_modal"></div>
</body>
</html>
