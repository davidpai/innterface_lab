<!DOCTYPE html>
<html>
<head>
<title>Sign-up | innterface - mobile application design patterns library and user interface (UI) search engine</title>
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
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
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
#signup {
    display: block;
    height: 100%;
    font-family: "Lucida Grande";
    font-size: 14px;
    color: #666666;
    text-align: center;
}
-->
</style>
<script type="text/javascript">
    var isLogin = <?php echo (int)$is_login; ?>;
    
    window.fbAsyncInit = function() {
        FB.init({
          appId      : '112923608895883', // App ID
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
                //console.log('Welcome! Fetching information...');
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
                            window.location.href = 'home';
                        } else {
                            alert('login error');
                            console.log(res);
                        }
                    }, 'json');
                });
            } else {
                // cancelled
                // 當user在FB的popup視窗內點[取消]的時候
                //console.log('cancelled');
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
        if ( user.confirm_password == '' ) {
            alert('Please input confirm password');
            return false;
        }
        if ( user.password != user.confirm_password ) {
            alert('Confirm password is not same with password');
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
        
        $('#signup').click(function(){
            $('#signup_form').trigger('submit');
        });
        
        $('#signup_form').submit(function(){
            var user = {};
            user['first_name'] = $.trim($('#first_name').val());
            user['last_name'] = $.trim($('#last_name').val());
            user['email'] = $.trim($('#email').val());
            user['password'] = $.trim($('#password').val());
            user['confirm_password'] = $.trim($('#confirm_password').val());
            if ( chkInput(user) ) {
                var url = $(this).attr('action');
                $.post(url, user, function(res){
                    if ( res.error == 0 ) {
                        alert('Welcome to Innterface');
                        window.location.href = 'home';
                    } else {
                        alert(res.msg);
                        console.log(res.msg);
                    }
                }, 'json');
            }
            return false;
        });
    });
    
    $(document).ajaxError(function(event, request, settings, exception) {
        alert('Sorry, system get some problem, please reload page or restart your browser.');
        console.log('Ajax error: '+exception);
    });
</script>
</head>

<body>
<?php include_once("analyticstracking.php") ?>
<div id="fb-root"></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="192" height="80" bgcolor="#FFFFFF"><a href="home"><img src="images/top_logo.png" width="192" height="80" alt="innterface Logo" /></a></td>
    <td width="120" bgcolor="#FFFFFF"><img src="images/top_signup.png" alt="Sign-up" width="120" height="80" /></td>
    <td bgcolor="#FFFFFF">&nbsp;</td>
  </tr>
  <tr>
    <td height="16" colspan="3" background="images/top_shadow.png">&nbsp;</td>
  </tr>
</table>
<p>&nbsp;</p>
<form id="signup_form" method="post" action="api/user/signup">
<table width="384" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="56" height="48">&nbsp;</td>
    <td width="272" colspan="3"><span class="style3">Sign-up with email</span></td>
    <td width="56">&nbsp;</td>
  </tr>
  <tr>
    <td height="32">&nbsp;</td>
    <td colspan="3"><input type="text" id="first_name" class="text_input" name="first_name" value="" maxlength="255" placeholder="First Name" /></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="16">&nbsp;</td>
    <td colspan="3">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="32">&nbsp;</td>
    <td colspan="3"><input type="text" id="last_name" class="text_input" name="last_name" value="" maxlength="255" placeholder="Last Name" /></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="16">&nbsp;</td>
    <td colspan="3">&nbsp;</td>
    <td>&nbsp;</td>
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
    <td colspan="3"><input type="text" id="password" class="text_input" name="password" value="" maxlength="255" placeholder="Password" /></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="16">&nbsp;</td>
    <td colspan="3">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="32">&nbsp;</td>
    <td colspan="3"><input type="text" id="confirm_password" class="text_input" name="confirm_password" value="" maxlength="255" placeholder="Confirm password" /></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="16">&nbsp;</td>
    <td colspan="3">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="32">&nbsp;</td>
    <td width="128" background="images/button_write_128.png"><a id="signup" href="javascript:void(0);">Sign-up</a></td>
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
    <td width="128"><a id="connect_with_facebook" href="javascript:void(0)"><img src="images/button_facebook.png" alt="Sign-up with Facebook" width="128" height="32" /></a></td>
    <td width="16">&nbsp;</td>
    <td width="128"><a id="connect_with_twitter" href="login/twitter"><img src="images/button_twitter.png" alt="Sign-up with Twitter" width="128" height="32" /></a></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="32">&nbsp;</td>
    <td colspan="3">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="48" background="images/line_light.png">&nbsp;</td>
    <td colspan="3" background="images/line_light.png"><span class="style5"><span class="style3">Already a member?</span><span class="style6"> <a href="login">Login</a></span></span></td>
    <td background="images/line_light.png">&nbsp;</td>
  </tr>
</table>
</form>
<p>&nbsp;</p>
</body>
</html>
