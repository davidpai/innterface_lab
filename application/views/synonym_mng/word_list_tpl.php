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
<link type="text/css" rel="stylesheet" href="js/fancybox/jquery.fancybox.css?v=2.1.4" media="screen" />
<link type="text/css" href="js/fuelux/css/fuelux.min.css" rel="stylesheet">
<style type="text/css">
#func_bar {
    margin-bottom: 10px;
}
div.login_block {
    position: absolute;
    top: 0;
    right: 0;
    margin: 10px;
}
.radio_label {
    float: left;
    margin-left: 10px;
}
input[type="text"], select {
    margin-bottom: 0;
}
td.modify_time {
    word-break: nowrap;
}
#data_table th a {
    display: block;
    text-decoration: none;
    color: #333;
}
#data_table td div.input-append {
    margin-bottom: 0;
}
#panel {
    position: fixed;
    top: 0;
    left: -640px;
    width: 640px;
    height: 100%;
    background-color: #EFEFEF;
    box-shadow: 1px 0 2px #CCC;
    z-index: 5;
    overflow: hidden;
}
#panel iframe {
    width: 100%;
    height: 100%;
    border: 0;
}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.fancybox.pack.js?v=2.1.4"></script>
<script type="text/javascript" src="js/fuelux/loader.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    var id = <?php echo json_encode($id); ?>;
    var word = <?php echo json_encode($word); ?>;
    var language = <?php echo json_encode($language); ?>;
    var profession = <?php echo json_encode($profession); ?>;
    var display_name = <?php echo json_encode($display_name); ?>;
    var display_status = <?php echo json_encode($display_status); ?>;
    
    // 新增
    /*
    $('#add_form').submit(function(e){
        var word = $('#form_word').val();
        var language = $('#form_language').val();
        var display_name = $('#form_display_name').val();
        if ( $.trim(word) != '' && $.trim(language) != '' && $.trim(display_name) != '' ) {
            return true;
        }
        return false;
    });
    */
    /*
    $('#add_word').fancybox({
        margin: 20, 
        padding: 5,
        width: '100%',
        height: '100%',
        minWidth: 640,
        minHeight: 500,
        maxWidth: 640,
        maxHeight: 500,
        closeBtn: false, 
        modal: true, 
        iframe: {
            scrolling : 'auto',
            preload   : false
        }
    });
    */
    // sorting
    // 預先載入ajax轉轉圖
    var indicator_src = 'images/ajax-loader_16x16.gif';
    var indicator = new Image();
    indicator.src = indicator_src;
    $('#data_table th a').click(function(e){
        e.preventDefault();
        //$(this).find('img').hide();
        //$(this).append($('<img>').attr({width:16,height:16,src:indicator_src}));
        var sort_name = $(this).attr('data-sort-name');
        var sort_order = $(this).attr('data-sort-order');
        $('#form_sort_name').val(sort_name);
        $('#form_sort_order').val(sort_order);
        setTimeout(function(){$('#search_form').trigger('submit');},300);
    });
    
    // search
    $('#form_id').val(id).focus(function(){
        $(this).val('');
    });
    $('#form_word').val(word).focus(function(){
        $(this).val('');
    });
/*
    $('#form_language').change(function(){
        $('#search_form_submit').trigger('click');
    }).find('option').each(function(){
        if ( $(this).val() == language ) {
            $(this).attr('selected', true);
        }
    });
*/
    $('#form_language').change(function(){
        //$('#search_form_submit').trigger('click');
        $('#search_form').trigger('submit');
    }).val(language);
/*
    $('#form_profession').change(function(){
        $('#search_form_submit').trigger('click');
    }).find('option').each(function(){
        if ( $(this).val() == profession ) {
            $(this).attr('selected', true);
        }
    });
*/
    $('#form_profession').change(function(){
        $('#search_form_submit').trigger('click');
    }).val(profession);
    $('#form_display_name').val(display_name).focus(function(){
        $(this).val('');
    });
    $('#form_display_status').change(function(){
        $('#search_form_submit').trigger('click');
    }).find('option').each(function(){
        if ( $(this).val() == display_status ) {
            $(this).attr('selected', true);
        }
    });
    // search submit
    $('#search_form_submit').click(function(e){
        var input = false;
        if ( $.trim($('#form_word').val()) != '' ) {
            return true;
        }
        if ( $.trim($('#form_language').val()) != '' ) {
            return true;
        }
        if ( $.trim($('#form_profession').val()) != '' ) {
            return true;
        }
        if ( $.trim($('#form_display_name').val()) != '' ) {
            return true;
        }
        if ( $.trim($('#form_display_status').val()) != '' ) {
            return true;
        }
        return false;
    });
    // search reset
    $('#search_form_reset').click(function(e){
        $('#form_id').val('');
        $('#form_word').val('');
        $('#form_language').val('');
        $('#form_profession').val('');
        $('#form_display_name').val('');
        $('#form_display_status').val('');
        $('#form_sort_name').val('');
        $('#form_sort_order').val('');
        return true;
    });
    // 編輯
    var data_holder = { 
            word : '', 
            language : '', 
            profession : '', 
            display_name : '', 
            display_status : ''
        };
    var language_list = <?php echo json_encode($language_list); ?>;
    var profession_list = <?php array_unshift($profession_list, ''); echo json_encode($profession_list); ?>;
    var display_status_list = [ { text : '',    value : '' }, 
                                { text : 'Yes', value : '1' },
                                { text : 'No',  value : '0' }
                               ];
    var click_edit = function(e){
        if ( $(this).hasClass('save') ) {
            return false;
        }
        e.preventDefault();
        e.stopPropagation();
        var tr = $(this).parent('td').parent('tr');
        var td_word = tr.find('td.word');
        var td_language = tr.find('td.language');
        var td_profession = tr.find('td.profession');
        var td_display_name = tr.find('td.display_name');
        var td_display_status = tr.find('td.display_status');
        
        var word = td_word.text();
        var language = td_language.text();
        var profession = td_profession.text();
        var display_name = td_display_name.text();
        var display_status = td_display_status.attr('data-value');
        
        data_holder.word = word;
        data_holder.language = language;
        data_holder.profession = profession;
        data_holder.display_name = display_name;
        data_holder.display_status = display_status;
        
        td_word.text('');
        td_language.text('');
        td_profession.text('');
        td_display_name.text('');
        td_display_status.text('');
        
        // word
        $('<input>').attr('type','text')
                    .addClass('input-medium')
                    .val(word)
                    .appendTo(td_word)
                    .on('dblclick', function(e){e.stopPropagation();});
        
        // language
        /*
        var language_select = $('<select>').addClass('input-small');
        for (var i=0; i<language_list.length; i++) {
            var language_option = $('<option>').val(language_list[i]).text(language_list[i]);
            if ( language_list[i] == language ) {
                language_option.attr('selected', 'selected');
            }
            language_option.appendTo(language_select);
        }
        language_select.appendTo(td_language);
        */
        var language_combobox = $('<div>').addClass('input-append btn-group combobox');
        var language_input = $('<input>').attr({type:'text',id:'form_language',name:'language'}).addClass('input-mini').val(language);
        var language_button = $('<button>').attr('data-toggle','dropdown').addClass('btn dropdown-toggle');
        $('<span>').addClass('caret').appendTo(language_button);
        var language_ul = $('<ul>').addClass('dropdown-menu');
        for (var i=0; i<language_list.length; i++) {
            var language_li = $('<li>');
            $('<a>').attr('href','javascript:void(0)').text(language_list[i]).appendTo(language_li);
            language_li.appendTo(language_ul);
        }
        language_input.appendTo(language_combobox);
        language_button.appendTo(language_combobox);
        language_ul.appendTo(language_combobox);
        language_combobox.appendTo(td_language);
        language_button.dropdown();
        language_combobox.on('dblclick', function(e){e.stopPropagation();}); // 防止觸發tr的double click事件
        
        // profession
        /*
        var profession_select = $('<select>').addClass('input-medium');
        for (var i=0; i<profession_list.length; i++) {
            var profession_option = $('<option>').val(profession_list[i]).text(profession_list[i]);
            if ( profession_list[i] == profession ) {
                profession_option.attr('selected', 'selected');
            }
            profession_option.appendTo(profession_select);
        }
        profession_select.appendTo(td_profession);
        */
        var profession_combobox = $('<div>').addClass('input-append btn-group combobox');
        var profession_input = $('<input>').attr({type:'text',id:'form_profession',name:'profession'}).addClass('input-small').val(profession);
        var profession_button = $('<button>').attr('data-toggle','dropdown').addClass('btn dropdown-toggle');
        $('<span>').addClass('caret').appendTo(profession_button);
        var profession_ul = $('<ul>').addClass('dropdown-menu');
        for (var i=0; i<profession_list.length; i++) {
            var profession_li = $('<li>');
            $('<a>').attr('href','javascript:void(0)').text(profession_list[i]).appendTo(profession_li);
            profession_li.appendTo(profession_ul);
        }
        profession_input.appendTo(profession_combobox);
        profession_button.appendTo(profession_combobox);
        profession_ul.appendTo(profession_combobox);
        profession_combobox.appendTo(td_profession);
        profession_button.dropdown();
        profession_combobox.on('dblclick', function(e){e.stopPropagation();}); // 防止觸發tr的double click事件
        
        // display_name
        $('<input>').attr('type','text')
                    .addClass('input-medium')
                    .val(display_name)
                    .appendTo(td_display_name)
                    .on('dblclick', function(e){e.stopPropagation();});
        
        // display_status
        var display_status_select = $('<select>').addClass('input-small');
        for (var i=0; i<display_status_list.length; i++) {
            var display_status_option = $('<option>').val(display_status_list[i].value).text(display_status_list[i].text);
            if ( display_status_list[i].value === display_status ) {
                display_status_option.attr('selected', 'selected');
            }
            display_status_option.appendTo(display_status_select);
        }
        display_status_select.appendTo(td_display_status);
        
        $(this).off('click').removeClass('edit').addClass('save').text('Save');
        $(this).on('click', click_save);
    };
    var click_save = function(e){
        if ( $(this).hasClass('edit') ) {
            return false;
        }
        e.preventDefault();
        e.stopPropagation();
        
        $(this).text('Saving..');
        var tr = $(this).parent('td').parent('tr');
        var td_id = tr.find('td.id');
        var td_word = tr.find('td.word');
        var td_language = tr.find('td.language');
        var td_profession = tr.find('td.profession');
        var td_display_name = tr.find('td.display_name');
        var td_display_status = tr.find('td.display_status');
        var td_modify_time = tr.find('td.modify_time');
        
        var data = { 
            id : parseInt(td_id.text().replace('#',''),10), 
            word : td_word.find('input').val(), 
            language : td_language.find('input').val(), 
            profession : td_profession.find('input').val(), 
            display_name : td_display_name.find('input').val(), 
            display_status : td_display_status.find('select').val()
        };
        
        var error_occur = false;
        var $this = $(this);
        $.post('<?php echo site_url('synonym_mng/ajaxEditWord'); ?>', data, function(res){
            if ( res.error == 0 ) {
            } else {
                if ( res.msg ) {
                    error_occur = true;
                    alert(res.msg);
                } else {
                    alert('Sorry, system get some problem, please reload page or restart your browser.');
                }
                console.log(res);
            }
            
            if ( error_occur ) {
                td_word.html('').text(data_holder.word);
                td_language.html('').text(data_holder.language);
                td_profession.html('').text(data_holder.profession);
                td_display_name.html('').text(data_holder.display_name);
                td_display_status.attr('data-value', data_holder.display_status);
                if ( data_holder.display_status === '1' ) {
                    td_display_status.html('').append($('<span>').addClass('badge badge-success').text('Y'));
                } else {
                    td_display_status.html('').append($('<span>').addClass('badge').text('N'));
                }
            } else {
                td_word.html('').text(data.word);
                td_language.html('').text(data.language);
                td_profession.html('').text(data.profession);
                td_display_name.html('').text(data.display_name);
                td_display_status.attr('data-value', data.display_status);
                if ( data.display_status === '1' ) {
                    td_display_status.html('').append($('<span>').addClass('badge badge-success').text('Y'));
                } else {
                    td_display_status.html('').append($('<span>').addClass('badge').text('N'));
                }
                var dt = new Date();
                var Y = dt.getFullYear();
                var m = dt.getMonth()+1;
                var d = dt.getDate();
                var H = dt.getHours();
                var i = dt.getMinutes();
                var s = dt.getSeconds();
                m = (parseInt(m,10) < 10) ? '0'+m : m;
                d = (parseInt(d,10) < 10) ? '0'+d : d;
                H = (parseInt(H,10) < 10) ? '0'+H : H;
                i = (parseInt(i,10) < 10) ? '0'+i : i;
                s = (parseInt(s,10) < 10) ? '0'+s : s;
                var modify_time = Y + '-' + m + '-' + d + ' ' + H + ':' + i + ':' + s;
                td_modify_time.html('').text(modify_time);
            }
            
            $this.off('click').removeClass('save').addClass('edit').text('Edit');
            $this.on('click', click_edit);
        }, 'json');
    };
    $('a.edit').click(click_edit);
    
    // 刪除
    $('.delete').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        var synonym_count = $(this).attr('data-synonym-count');
        var word_id = $(this).attr('data-word-id');
        var text = '確定刪除嗎?';
        if ( parseInt(synonym_count) > 0 ) {
            text = '這個字詞仍有'+synonym_count+'個同義字，刪除這個字詞將連同它的同義字一併刪除，' + text;
        }
        if ( confirm(text) ) {
            $.post('<?php echo site_url('synonym_mng/ajaxDeleteWord'); ?>', { word_id : word_id }, function(res){
                if ( res.error == 0 ) {
                    location.reload();
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
    
    // double click on row to edit
    var row_edit_status = false;
    $('#data_table tbody tr').dblclick(function(e){
        console.log(e);
        e.preventDefault();
        e.stopPropagation();
        if ( row_edit_status ) {
            $(this).find('a.save').trigger('click');
            row_edit_status = false;
        } else {
            $(this).find('a.edit').trigger('click');
            row_edit_status = true;
        }
        return false;
    });
    
    // panel
    var handlerStatus = false;
    // 這裡故意不用 var openPanel = function() { ...
    // 因為要讓 iframe 裡的 javascript 呼叫，所以讓他變成全域函式
    openPanel = function(){
        $('#panel').show().animate({
            left: "0"
        }, 250,  function(){
            handlerStatus = true;
        });
    };
    // 這裡故意不用 var openPanel = function() { ...
    // 因為要讓 iframe 裡的 javascript 呼叫，所以讓他變成全域函式
    closePanel = function(){
        $('#panel').animate({
            left: "-640px"
        }, 250, function(){
            $(this).hide();
            handlerStatus = false;
        });
    };
    $('#add_word').click(function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        $('#panel_iframe').attr('href', href);
        if ( handlerStatus ) {
            closePanel();
        } else {
            openPanel();
        }
        return false;
    });
    $('#container').click(function(e){
        if ( handlerStatus ) {
            closePanel();
        };
    });
    
    // combobox
    $('.dropdown-toggle').dropdown();
    $('.combobox').each(function(){
        var $input = $(this).find('input');
        var $dropdown_list = $(this).find('ul li a');
        $dropdown_list.on('click', function(){
            $input.val($(this).text());
        });
    });

    $(document).ajaxError(function(event, request, settings, exception) {
        alert('Sorry, system get some problem, please reload page or restart your browser.');
        console.log(settings);
    });
});
</script>
</head>
<body>
<div id="panel">
    <iframe id="panel_iframe" src="<?php echo site_url('synonym_mng/add_word'); ?>"></iframe>
</div>
<div id="container" class="container-fluid">
<div class="login_block"><a href="<?php echo site_url('synonym_mng/logout'); ?>">[Logout]</a></div>
<h3>Word List</h3>
<div id="func_bar"><a id="add_word" data-fancybox-type="iframe" class="btn btn-small" href="<?php echo site_url('synonym_mng/add_word'); ?>">Add Word</a>
<span class="pull-right">Search Result: <?php echo $q->num_rows(); ?></span>
</div>
<table id="data_table" class="table table-striped table-bordered table-hover table-condensed">
    <form id="search_form" name="search_form" method="get" action="<?php echo site_url('synonym_mng/word_list'); ?>" class="form-inline">
    <input type="hidden" id="form_sort_name" name="sort_name" value="<?php echo htmlspecialchars($sort_name); ?>" />
    <input type="hidden" id="form_sort_order" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>" />
    <thead>
    <tr>
        <th nowrap="nowrap"><a href="javascript:void(0)" data-sort-name="id" data-sort-order="<?php echo ($sort_name=='id') ? $active_sort_order : $default_sort_order; ?>">#ID <img src="images/<?php echo ($sort_name=='id') ? $active_sort_order : 'ascdesc'; ?>.gif" width="21" height="4" /></a></th>
        <th nowrap="nowrap"><a href="javascript:void(0)" data-sort-name="word" data-sort-order="<?php echo ($sort_name=='word') ? $active_sort_order : $default_sort_order; ?>">Word <img src="images/<?php echo ($sort_name=='word') ? $active_sort_order : 'ascdesc'; ?>.gif" width="21" height="4" /></a></th>
        <th nowrap="nowrap"><a href="javascript:void(0)" data-sort-name="language" data-sort-order="<?php echo ($sort_name=='language') ? $active_sort_order : $default_sort_order; ?>">Language <img src="images/<?php echo ($sort_name=='language') ? $active_sort_order : 'ascdesc'; ?>.gif" width="21" height="4" /></a></th>
        <th nowrap="nowrap"><a href="javascript:void(0)" data-sort-name="profession" data-sort-order="<?php echo ($sort_name=='profession') ? $active_sort_order : $default_sort_order; ?>">Profession(專業領域) <img src="images/<?php echo ($sort_name=='profession') ? $active_sort_order : 'ascdesc'; ?>.gif" width="21" height="4" /></a></th>
        <th nowrap="nowrap"><a href="javascript:void(0)" data-sort-name="display_name" data-sort-order="<?php echo ($sort_name=='display_name') ? $active_sort_order : $default_sort_order; ?>">Display Name <img src="images/<?php echo ($sort_name=='display_name') ? $active_sort_order : 'ascdesc'; ?>.gif" width="21" height="4" /></a></th>
        <th nowrap="nowrap"><a href="javascript:void(0)" data-sort-name="display_status" data-sort-order="<?php echo ($sort_name=='display_status') ? $active_sort_order : $default_sort_order; ?>">Display? <img src="images/<?php echo ($sort_name=='display_status') ? $active_sort_order : 'ascdesc'; ?>.gif" width="21" height="4" /></a></th>
        <th nowrap="nowrap"><a href="javascript:void(0)" data-sort-name="modify_time" data-sort-order="<?php echo ($sort_name=='modify_time') ? $active_sort_order : $default_sort_order; ?>">Modify Time <img src="images/<?php echo ($sort_name=='modify_time') ? $active_sort_order : 'ascdesc'; ?>.gif" width="21" height="4" /></a></th>
        <th nowrap="nowrap"><a href="javascript:void(0)" data-sort-name="synonym_count" data-sort-order="<?php echo ($sort_name=='synonym_count') ? $active_sort_order : $default_sort_order; ?>">Synonym count <img src="images/<?php echo ($sort_name=='synonym_count') ? $active_sort_order : 'ascdesc'; ?>.gif" width="21" height="4" /></a></th>
        <th nowrap="nowrap"></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td width="5%">
            <input id="form_id" name="id" class="input-mini" type="text" value="" />
        </td>
        <td><input id="form_word" name="word" class="input-medium" type="text" value="" /></td>
        <td width="5%">
            <div class="input-append btn-group combobox">
                <input type="text" class="input-mini" id="form_language" name="language" value="" />
                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
<?php foreach ( $language_list as $language ) : ?>
                    <li><a href="javascript:void(0)"><?php echo htmlspecialchars($language); ?></a></li>
<?php endforeach; ?>
                </ul>
            </div>
        </td>
        <td width="10%">
            <div class="input-append btn-group combobox">
                <input type="text" class="input-small" id="form_profession" name="profession" value="" />
                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
<?php foreach ( $profession_list as $profession ) : ?>
                    <li><a href="javascript:void(0)"><?php echo htmlspecialchars($profession); ?></a></li>
<?php endforeach; ?>
                </ul>
            </div>
        </td>
        <td><input id="form_display_name" name="display_name" class="input-medium" type="text" /></td>
        <td width="5%">
            <select id="form_display_status" name="display_status" class="input-small">
                <option value=""></option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </td>
        <td width="12%"></td>
        <td width="10%"></td>
        <td><input id="search_form_submit" type="submit" class="btn btn-primary" value="Search">
        <input id="search_form_reset" type="submit" class="btn" value="Reset"></td>
    </tr>
    </form>
<?php foreach ( $q->result() as $word ) : ?>
    <tr id="synonym_word_<?php echo htmlspecialchars($word->id); ?>">
        <td class="id">#<?php echo htmlspecialchars($word->id); ?></td>
        <td class="word"><?php echo htmlspecialchars($word->word); ?></td>
        <td class="language"><?php echo htmlspecialchars($word->language); ?></td>
        <td class="profession"><?php echo empty($word->profession) ? '' : htmlspecialchars($word->profession); ?></td>
        <td class="display_name"><?php echo htmlspecialchars($word->display_name); ?></td>
        <td class="display_status" data-value="<?php echo htmlspecialchars($word->display_status); ?>">
        <?php if ($word->display_status) : ?>
            <span class="badge badge-success">Y</span>
        <?php else : ?>
            <span class="badge">N</span>
        <?php endif; ?>
        </td>
        <td class="modify_time"><?php echo htmlspecialchars($word->modify_time); ?></td>
        <td>
    <?php if ( $word->synonym_count > 0 ) : ?>
        <span class="label label-info"><?php echo htmlspecialchars($word->synonym_count); ?></span>
    <?php else : ?>
        <span class="label"><?php echo htmlspecialchars($word->synonym_count); ?></span>
    <?php endif; ?>
        <a class="btn btn-small pull-right" href="<?php echo site_url('synonym_mng/synonym_list_split'); ?>?word_id=<?php echo urlencode($word->id); ?>">Synonym</a>
        </td>
        <td>
        <a class="btn btn-small edit" href="javascript:void(0)">Edit</a>&nbsp;
        <a class="btn btn-small btn-danger delete" data-synonym-count="<?php echo htmlspecialchars($word->synonym_count); ?>" data-word-id="<?php echo htmlspecialchars($word->id); ?>" href="javascript:void(0)">Delete</a></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>
</div>
</body>
</html>