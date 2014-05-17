<!DOCTYPE html>
<html>
<head>
<title>Login | innterface - mobile application design patterns library and user interface (UI) search engine</title>
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
<link type="text/css" href="js/fancybox/jquery.fancybox.css?v=2.1.4" rel="stylesheet" media="screen" />
<style type="text/css">
<!--
body {
	background-color: #EBEDF2;
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
}
img {
    border: 0;
}
.style1 {
	font-family: "Lucida Grande";
	font-size: 14px;
	color: #BFBFBF;
}
.style3 {font-family: "Lucida Grande"; font-size: 14px; color: #666666; }
.style5 {font-family: "Lucida Grande"; font-size: 14px; color: #333333; }
.style6 {
	color: #FF5500;
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
#login {
    display: block;
    height: 100%;
    font-family: "Lucida Grande";
    font-size: 14px;
    color: #666666;
    text-align: center;
}
li a {
    text-decoration: none;
    color: #0088CC;
}
li a:hover {
    color: #0088CC;
    text-decoration: underline;
}
li a.highlight {
    text-decoration: none;
    color: #FF5522;
}
li a.highlight:hover {
    color: #FF5522;
    text-decoration: underline;
}
/* Modal Dialog */
.modal_dialog {
    display: none;
    font-size: 1em;
}
.modal_dialog p {
    margin: 2em 1em;
    font-size: 1.2em;
    text-align: center;
}
.modal_dialog ul {
    display: block;
    margin: 0;
    height: 3em;
    line-height: 3em;
    border-top: 1px solid #DEDEDE;
    font-size: 1.2em;
}
.modal_dialog li {
    display: block;
    height: 3em;
    line-height: 3em;
}
.modal_dialog li.ok {
    float: right;
    margin: 0 0 0 1em;
}
.modal_dialog li.ok a {
    color: #0088D1;
}
.modal_dialog li.cancel {
    float: left;
    margin: 0 1em 0 0;
}
.modal_dialog li.cancel a {
    color: #AAA;
}
.modal_dialog li.action {
    float: right;
    margin: 0 0 0 1em;
}
.modal_dialog li.action a {
    color: #FF5522;
}
.modal_dialog li a {
    display: block;
    height: 3em;
    line-height: 3em;
}
-->
</style>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.fancybox.pack.js?v=2.1.4"></script>
<script type="text/javascript">
var isLogin = <?php echo (int)$is_login; ?>;

window.fbAsyncInit = function() {
    FB.init({
      appId      : '235044053357566', // App ID
      channelUrl : '//innterface.com/channel.html', // Channel File
      status     : true, // check login status
      cookie     : true, // enable cookies to allow the server to access the session
      xfbml      : true  // parse XFBML
    });
};

var fbLogin = function() {
    // 跳出FB的登入對話框或授權對話框
    FB.login(function(response) {
        if (response.authResponse) {
            // connected
            // 當user在FB的popup視窗點[用Facebook帳號登入]
            //    console.log('Welcome!  Fetching your information.... ');
            FB.api('/me', function(response) {
                var user = {    fb_id : response.id,
                                fb_name : response.name,
                                fb_gender : response.gender,
                                fb_birthday : response.birthday,
                                fb_email : response.email,
                                fb_picture : 'http://graph.facebook.com/'+response.id+'/picture'
                            };
                $.post('api/user/fb_login', user, function(res){
                    if ( res.error == 0 ) {
                        if ( res.from_url ) {
                            window.location.href = res.from_url;
                        } else {
                            window.location.href = 'home';
                        }
                    } else {
                        alert('Sorry, system get some problem, please reload page or restart your browser.');
                        //    console.log(res);
                    }
                }, 'json');
            });
        } else {
            // cancelled
            // 當user在FB的popup視窗內點[取消]的時候
            //    console.log('cancelled');
        }
    }, {scope:'email,user_birthday'});
};

var chkInput = function(user) {
    if ( user.email == '' ) {
        alert('Please input email');
        return false;
    }
    if ( user.password == '' ) {
        alert('Please input password');
        return false;
    }
    return true;
};

$(document).ready(function(){
    // Load FB SDK Asynchronously
    (function(d){
       var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
       if (d.getElementById(id)) {return;}
       js = d.createElement('script'); js.id = id; js.async = true;
       js.src = "//connect.facebook.net/en_US/all.js";
       ref.parentNode.insertBefore(js, ref);
     }(document));

    $('#connect_with_facebook').click(function(){
        fbLogin();
    });
    
    
    $('#login').click(function(){
        $('#login_form').trigger('submit');
    });
    
    $('#login_form').submit(function(){
        var user = {};
        user['email'] = $.trim($('#email').val());
        user['password'] = $.trim($('#password').val());
        if ( chkInput(user) ) {
            var url = $(this).attr('action');
            $.post(url, user, function(res){
                if ( res.error == 0 ) {
                    //alert('Welcome to Innterface');
                    $.fancybox.open({href:"#login_msg"},{
                        margin: 0, 
                        padding: [0,15,0,15], 
                        closeBtn: false,
                        maxWidth: 450, 
                        minWidth: 0, 
                        afterClose: function(){
                            if ( res.from_url ) {
                                window.location.href = res.from_url;
                            } else {
                                window.location.href = 'home';
                            }
                        }
                    });
                    $('#login_msg li.ok a').click(function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        $.fancybox.close();
                    });
                } else {
                    alert(res.msg);
                    //    console.log(res.msg);
                }
            }, 'json');
        }
        return false;
    });
});

$(document).ajaxError(function(event, request, settings, exception) {
    $.fancybox.open({href:"#ajax_error"},{
        margin: 0, 
        padding: [0,15,0,15], 
        closeBtn: false,
        maxWidth: 450, 
        minWidth: 0
    });
    $('#ajax_error li.ok a').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        $.fancybox.close();
    });
    //alert('Sorry, system get some problem, please reload page or restart your browser.');
    //    console.log('Ajax error: '+settings.url);
});
</script>
</head>

<body>
<?php include_once("analyticstracking.php") ?>
<div id="fb-root"></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="192" height="80" bgcolor="#FFFFFF"><a href="home"><img src="images/top_logo.png" width="192" height="80" alt="innterface Logo" /></a></td>
    <td width="120" bgcolor="#FFFFFF"><img src="images/top_login.png" alt="Login" width="120" height="80" /></td>
    <td bgcolor="#FFFFFF">&nbsp;</td>
  </tr>
  <tr>
    <td height="16" colspan="3" background="images/top_shadow.png">&nbsp;</td>
  </tr>
</table>
<p>&nbsp;</p>
<form id="login_form" method="post" action="api/user/login">
<table width="384" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="56" height="48">&nbsp;</td>
    <td width="272" colspan="3"><span class="style3">Login with email</span></td>
    <td width="56">&nbsp;</td>
  </tr>
  <tr>
    <td height="32">&nbsp;</td>
    <td colspan="3"><input type="text" id="email" class="text_input" name="email" value="" maxlength="255" placeholder="Email address" /></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="16">&nbsp;</td>
    <td colspan="3">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="32">&nbsp;</td>
    <td colspan="3"><input type="password" id="password" class="text_input" name="password" value="" maxlength="255" placeholder="Password" /></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="16">&nbsp;</td>
    <td colspan="3">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="32">&nbsp;</td>
    <td width="128" background="images/button_write_128.png"><a id="login" href="javascript:void(0);">Login</a></td>
    <td width="16">&nbsp;</td>
    <td width="128">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="32">&nbsp;</td>
    <td colspan="3">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="48" background="images/line_light.png">&nbsp;</td>
    <td colspan="3" background="images/line_light.png"><span class="style3">Connect with</span></td>
    <td background="images/line_light.png">&nbsp;</td>
  </tr>
  
  <tr>
    <td height="32">&nbsp;</td>
    <td width="128"><a id="connect_with_facebook" href="javascript:void(0)"><img src="images/button_facebook.png" alt="Sign-in with Facebook" width="128" height="32" /></a></td>
    <td width="16">&nbsp;</td>
    <td width="128"><a id="connect_with_twitter" href="login/twitter"><img src="images/button_twitter.png" alt="Sign-in with Twitter" width="128" height="32" /></a></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="32">&nbsp;</td>
    <td colspan="3">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="48" background="images/line_light.png">&nbsp;</td>
    <td colspan="3" background="images/line_light.png"><span class="style5"><span class="style3">No account yet?</span><span class="style6"> <a href="signup">Sign-up</a></span></span></td>
    <td background="images/line_light.png">&nbsp;</td>
  </tr>
</table>
</form>
<p>&nbsp;</p>

<div id="login_msg" class="modal_dialog">
    <p>Welcome to Innterface</p>
    <ul>
        <li class="ok">
            <a href="javascript:void(0)">OK</a>
        </li>
        <li class="clearfix"></li>
    </ul>
</div>
<div id="ajax_error" class="modal_dialog">
    <p>Sorry, system get some problem, please reload page or restart your browser</p>
    <ul>
        <li class="ok">
            <a href="javascript:void(0)">OK</a>
        </li>
        <li class="clearfix"></li>
    </ul>
</div>
</body>
</html>
