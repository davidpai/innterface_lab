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
<link type="text/css" rel="stylesheet" href="css/qtip1/jquery.qtip.css" />
<link type="text/css" href="js/fuelux/css/fuelux.min.css" rel="stylesheet">
<style type="text/css">
body {
    padding: 20px;
}
div.login_block {
    position: absolute;
    top: 0;
    right: 0;
    margin: 10px;
}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/qtip1/jquery.qtip.min.js"></script>
<script type="text/javascript" src="js/fuelux/loader.min.js"></script>
<script type="text/javascript">
var qtip_config = {
		content: { attr: 'alt' }, 
		position: {
			my: 'left center',
			at: 'right center', 
            adjust: {
                x: 10
            }
		}, 
		show: { event: false }, 
		hide: {
			event: 'focus', 
			delay: 500
		}, 
		style: 'ui-tooltip-rounded ui-tooltip-font-small'
	};
$(document).ready(function(){

    $('.dropdown-toggle').dropdown();
    $('.combobox').each(function(){
        var $input = $(this).find('input');
        var $dropdown_list = $(this).find('ul li a');
        $dropdown_list.on('click', function(){
            $input.val($(this).text());
        });
    });
    
	// 設定欄位的錯誤訊息文字
	$('.validate_msg').qtip(qtip_config);
    // Display Status 的 qTip 不是設定在欄位元素上，所以這裡要手動設定消失的 event
    $('input[name="display_status"]').focus(function(e){
        $('#input_display_status').qtip('hide');
    });
    
    $('#input_word').blur(function(e){
        var word = $.trim($(this).val());
        $('#input_display_name').val(word);
        checkWordExist();
    });
    $('#input_language').blur(function(){
        checkWordExist();
    });
    $('#input_profession').blur(function(){
        checkWordExist();
    });
    var checkWordExist = function(){
        var word = $.trim($('#input_word').val());
        var language = $.trim($('#input_language').val());
        var profession = $.trim($('#input_profession').val());
        var data = {
            word : word, 
            language : language, 
            profession : profession
        };
        $.post('<?php echo site_url('synonym_mng/ajaxCheckWordExist'); ?>', data, function(res){
            if ( res.error == 0 ) {
                    qtip_config.content = res.msg;
                    $('#input_word').qtip(qtip_config);
                    $('#input_word').qtip('hide');
            } else {
                if ( res.msg ) {
                    qtip_config.content = res.msg;
                    $('#input_word').qtip(qtip_config);
                    $('#input_word').qtip('show');
                } else {
                    alert('Sorry, system get some problem, please reload page or restart your browser.');
                }
                console.log(res);
            }
        }, 'json');
    };
    
    $('#add_form').submit(function(e){
        var return_value = true;
        var $word = $('#input_word');
        var $language = $('#input_language');
        var $profession = $('#input_profession');
        var $display_name = $('#input_display_name');
        var display_status = $('input[name="display_status"]:checked').val();
        
        if ( $.trim($word.val()) == '' ) {
            $word.parent('div.validate_msg').qtip('show');
            return_value = false;
        }
        if ( $.trim($language.val()) == '' ) {
            $language.parent('div.validate_msg').qtip('show');
            return_value = false;
        }
        //if ( $.trim($profession.val()) == '' ) {
        //    $profession.parent('div.validate_msg').qtip('show');
        //    return_value = false;
        //}
        if ( $.trim($display_name.val()) == '' ) {
             $display_name.parent('div.validate_msg').qtip('show');
            return_value = false;
        }
        if ( !display_status ) {
            $('#input_display_status').qtip('show');
            return_value = false;
        }
        var init = function(){
            location.reload();
            /*
            $word.val('');
            $language.val('');
            $profession.val('');
            $display_name.val('');
            $('#radio_display_status_no').attr('checked', true);
            */
        };
        if ( return_value ) {
            $('#save').text('Saving..');
            var action = $(this).attr('action');
            var data = {
                word : $.trim($word.val()), 
                language : $.trim($language.val()), 
                profession : $.trim($profession.val()), 
                display_name : $.trim($display_name.val()), 
                display_status : display_status
            };
            $.post(action, data, function(res){
                if ( res.error == 0 ) {
                    init();
                    //parent.location.reload();
                    //parent.$.fancybox.close();
                    //parent.closePanel();
                } else {
                    if ( res.msg ) {
                        alert(res.msg);
                    } else {
                        alert('Sorry, system get some problem, please reload page or restart your browser.');
                    }
                    console.log(res);
                }
                $('#save').text('Save');
            }, 'json');
        }

        return false;
    });
    
    $('.cancel').click(function(e){
        e.preventDefault();
        //parent.$.fancybox.close();
        parent.closePanel();
    });
    
    $(document).ajaxError(function(event, request, settings, exception) {
        alert('Sorry, system get some problem, please reload page or restart your browser.');
        console.log(settings);
    });
});
</script>
</head>
<body>
<form id="add_form" class="form-horizontal" method="post" action="<?php echo site_url('synonym_mng/add_word'); ?>">
    <fieldset>
        <legend> Add Word <a href="javascript:void(0)" class="btn btn-small pull-right cancel"><i class="icon-arrow-left"></i></a></legend>
        <div class="control-group">
            <label class="control-label" for="input_word">Word *</label>
            <div class="controls">
                <div class="input-append validate_msg" alt="Please input word">
                    <input class="input-large" type="text" id="input_word" name="word" value="" />
                </div>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="input_language">Language *</label>
            <div class="controls">
                <div class="input-append btn-group combobox validate_msg" alt="Please select a language">
                    <input type="text" class="input-medium" id="input_language" name="language" alt="Please select a language" value="" />
                    <button class="btn dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
<?php foreach ( $language_list as $language ) : ?>
                        <li><a href="javascript:void(0)"><?php echo htmlspecialchars($language); ?></a></li>
<?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="input_profession">Profession(專業領域)</label>
            <div class="controls">
                <div class="input-append btn-group combobox">
                    <input type="text" class="input-medium" id="input_profession" name="profession" alt="Please select a profession" value="" />
                    <button class="btn dropdown-toggle validate_msg" data-toggle="dropdown">
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
<?php foreach ( $profession_list as $profession ) : ?>
                        <li><a href="javascript:void(0)"><?php echo htmlspecialchars($profession); ?></a></li>
<?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="input_display_name">Display Name *</label>
            <div class="controls">
                <div class="input-append validate_msg" alt="Please input display name">
                    <input class="input-large" type="text" id="input_display_name" name="display_name" value="" />
                </div>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="input_display_status">Display? *</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="display_status" id="radio_display_status_yes" value="1"> Yes
                </label>
                <label class="radio inline validate_msg" id="input_display_status" alt="Please choose to display or not">
                    <input type="radio" name="display_status" id="radio_display_status_no" value="0" checked="checked"> No
                </label>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" id="save" class="btn btn-primary">Save</button>
            <button type="button" id="cancel" class="btn cancel">Cancel</button>
        </div>
    </fieldset>
</form>
</body>
</html>