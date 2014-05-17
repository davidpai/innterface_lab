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
<h3>User Usage - Daily</h3>
<p><a class="btn btn-small" href="<?php echo site_url('usage'); ?>">使用者清單</a></p>
<form id="form1" name="form1" method="get" action="">
<div>使用者名稱
    <select name="user_id" id="user_id">
        <option value="">[請選擇]</option>
<?php foreach ( $user_all_arr as $user ) : ?>
        <option value="<?php echo htmlspecialchars($user->id); ?>"><?php echo htmlspecialchars($user->name); ?> (<?php echo htmlspecialchars($user->loginType); ?>)</option>
<?php endforeach; ?>
    </select>
</div>
<div>紀錄月份
    <select name="year_month" id="year_month">
<?php foreach ( $month_arr as $month_key => $month_value ) : ?>
        <option value="<?php echo htmlspecialchars($month_key); ?>"><?php echo htmlspecialchars($month_value); ?></option>
<?php endforeach; ?>
    </select>
</div>
</form>
<table class="table table-striped table-bordered table-hover table-condensed">
    <thead>
    <tr>
        <th>Login Date</th>
        <th>Duration of Login</th>
        <th>Amount of Uploads</th>
        <th>Amount of Tags</th>
        <th>Amount of Clicks</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ( $daily_arr as $date => $daily ) : ?>
    <tr>
        <td><?php echo htmlspecialchars($daily->login_date); ?></td>
        <td><?php echo !empty($daily->duration_hours) ? htmlspecialchars($daily->duration_hours).' hours' : ''; ?> <?php echo !empty($daily->duration_minutes) ? htmlspecialchars($daily->duration_minutes).' minutes' : ''; ?> <?php echo !empty($daily->duration_seconds) ?  htmlspecialchars($daily->duration_seconds).' seconds' : ''; ?></td>
        <td><?php echo htmlspecialchars($daily->amount_of_uploads); ?></td>
        <td><?php echo htmlspecialchars($daily->amount_of_tags); ?></td>
        <td><?php echo htmlspecialchars($daily->amount_of_clicks); ?></td>
        <td><a class="btn btn-small" href="<?php echo site_url('usage/user_daily_detail'); ?>?user_id=<?php echo htmlspecialchars($user_id); ?>&set_date=<?php echo $date; ?>">Detail</a></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>
<script type="text/javascript">
var user_id = '<?php echo htmlspecialchars($user_id); ?>';
var year_month = '<?php echo htmlspecialchars($year_month); ?>';
$('#user_id').change(function(){
    $('#form1').submit();
}).children('option').each(function(){
    if ( $(this).val() == user_id ) {
        $(this).attr('selected', true);
    }
});
$('#year_month').change(function(){
    $('#form1').submit();
}).children('option').each(function(){
    if ( $(this).val() == year_month ) {
        $(this).attr('selected', true);
    }
});
</script>
</body>
</html>