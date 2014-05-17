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
div.login_block {
    position: absolute;
    top: 0;
    right: 0;
    margin: 10px;
}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript">
</script>
</head>
<body>
<div class="login_block"><a href="<?php echo site_url('usage/logout'); ?>">[Logout]</a></div>
<h3>User Usage - Daily Detail</h3>
<p><a class="btn btn-small" href="<?php echo site_url('usage'); ?>">使用者清單</a>&nbsp;&nbsp;<a class="btn btn-small" href="<?php echo site_url('usage/user_daily'); ?>">紀錄總覽</a></p>

<form id="form1" name="form1" method="get" action="">
<div>使用者名稱
    <select name="user_id" id="user_id">
        <option value="">[請選擇]</option>
<?php foreach ( $user_all_arr as $user ) : ?>
        <option value="<?php echo htmlspecialchars($user->id); ?>"><?php echo htmlspecialchars($user->name); ?> (<?php echo htmlspecialchars($user->loginType); ?>)</option>
<?php endforeach; ?>
    </select>
</div>
<div>紀錄日期
    <select name="set_date" id="set_date">
<?php foreach ( $daily_arr as $date => $daily ) : ?>
        <option value="<?php echo htmlspecialchars($date); ?>"><?php echo htmlspecialchars($daily->login_date); ?></option>
<?php endforeach; ?>
    </select>
</div>
</form>

<div class="tabbable"> <!-- Only required for left/right tabs -->
  <ul class="nav nav-tabs">
    <li class="active"><a href="#all" data-toggle="tab">All (<?php echo count($all_arr); ?>)</a></li>
    <li><a href="#uploads" data-toggle="tab">Uploads (<?php echo count($upload_arr); ?>)</a></li>
    <li><a href="#tags" data-toggle="tab">Tags (<?php echo count($tag_arr); ?>)</a></li>
    <li><a href="#clicks" data-toggle="tab">Clicks(<?php echo count($click_arr); ?>)</a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="all">
        <table class="table table-striped table-bordered table-hover table-condensed">
            <thead>
            <tr>
                <th>#</th>
                <th>Time</th>
                <th>Statistics Type</th>
                <th>Uploads(Screenshot #ID)</th>
                <th>Tags</th>
                <th>Clicks(Query Term)</th>
                <th>Screenshot URL</th>
            </tr>
            </thead>
            <tbody>
<?php $i=1; foreach ( $all_arr as $all_item ) : ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo htmlspecialchars($all_item->add_time); ?></td>
                    <td><?php echo htmlspecialchars($all_item->type); ?></td>
                    <td><?php echo empty($all_item->screenshot_id) ? '&nbsp;' : htmlspecialchars($all_item->screenshot_id); ?></td>
                    <td><?php echo empty($all_item->tag) ? '&nbsp;' : htmlspecialchars($all_item->tag); ?></td>
                    <td><?php echo empty($all_item->query_term) ? '&nbsp;' : htmlspecialchars($all_item->query_term); ?></td>
                    <td><a href="<?php echo site_url('screenshot'); ?>?id=<?php echo htmlspecialchars($all_item->screenshot_id); ?>" target="_blank"><?php echo site_url('screenshot'); ?>?id=<?php echo htmlspecialchars($all_item->screenshot_id); ?></a></td>
                </tr>
<?php $i++; endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="tab-pane" id="uploads">
        <table class="table table-striped table-bordered table-hover table-condensed">
            <thead>
            <tr>
                <th>#</th>
                <th>Time</th>
                <th>Screenshot #ID</th>
                <th>Screenshot URL</th>
            </tr>
            </thead>
            <tbody>
<?php $i=1; foreach ( $upload_arr as $upload ) : ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo htmlspecialchars($upload->add_time); ?></td>
                    <td><?php echo htmlspecialchars($upload->screenshot_id); ?></td>
                    <td><a href="<?php echo site_url('screenshot'); ?>?id=<?php echo htmlspecialchars($upload->screenshot_id); ?>" target="_blank"><?php echo site_url('screenshot'); ?>?id=<?php echo htmlspecialchars($upload->screenshot_id); ?></a></td>
                </tr>
<?php $i++; endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="tab-pane" id="tags">
        <table class="table table-striped table-bordered table-hover table-condensed">
            <thead>
            <tr>
                <th>#</th>
                <th>Time</th>
                <th>Tag</th>
                <th>Screenshot URL</th>
            </tr>
            </thead>
            <tbody>
<?php $i=1; foreach ( $tag_arr as $tag ) : ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo htmlspecialchars($tag->add_time); ?></td>
                    <td><?php echo htmlspecialchars($tag->tag); ?></td>
                    <td><a href="<?php echo site_url('screenshot'); ?>?id=<?php echo htmlspecialchars($tag->screenshot_id); ?>" target="_blank"><?php echo site_url('screenshot'); ?>?id=<?php echo htmlspecialchars($tag->screenshot_id); ?></a></td>
                </tr>
<?php $i++; endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="tab-pane" id="clicks">
        <table class="table table-striped table-bordered table-hover table-condensed">
            <thead>
            <tr>
                <th>#</th>
                <th>Time</th>
                <th>Query Term</th>
                <th>Click URL</th>
            </tr>
            </thead>
            <tbody>
<?php $i=1; foreach ( $click_arr as $click ) : ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo htmlspecialchars($click->add_time); ?></td>
                    <td><?php echo htmlspecialchars($click->query_term); ?></td>
                    <td><a href="<?php echo htmlspecialchars($click->url); ?>" target="_blank"><?php echo htmlspecialchars($click->url); ?></a></td>
                </tr>
<?php $i++; endforeach; ?>
            </tbody>
        </table>
    </div>
  </div>
</div>
<script type="text/javascript">
var user_id = '<?php echo htmlspecialchars($user_id); ?>';
var set_date = '<?php echo htmlspecialchars($set_date); ?>';
$('#user_id').change(function(){
    $('#form1').submit();
}).children('option').each(function(){
    if ( $(this).val() == user_id ) {
        $(this).attr('selected', true);
    }
});
$('#set_date').change(function(){
    $('#form1').submit();
}).children('option').each(function(){
    if ( $(this).val() == set_date ) {
        $(this).attr('selected', true);
    }
});
</script>
</body>
</html>