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
<h3>User Usage</h3>
<table class="table table-striped table-bordered table-hover table-condensed">
    <thead>
    <tr>
        <th>User ID</th>
        <th>Login From</th>
        <th>Name</th>
        <th>E-Mail</th>
        <th>Duration of Login</th>
        <th>Amount of Uploads</th>
        <th>Amount of Tags</th>
        <th>Amount of Clicks</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ( $user_arr as $user ) : ?>
    <tr>
        <td><?php echo htmlspecialchars($user->id); ?></td>
        <td><?php echo htmlspecialchars($user->loginType); ?></td>
        <td><?php echo htmlspecialchars($user->name); ?></td>
        <td><?php echo htmlspecialchars($user->email); ?></td>
        <td><?php echo !empty($user->duration_hours) ? htmlspecialchars($user->duration_hours).' hours' : ''; ?> <?php echo !empty($user->duration_minutes) ? htmlspecialchars($user->duration_minutes).' minutes' : ''; ?> <?php echo !empty($user->duration_seconds) ?  htmlspecialchars($user->duration_seconds).' seconds' : ''; ?></td>
        <td><?php echo empty($user->amount_of_uploads) ? 0 : htmlspecialchars($user->amount_of_uploads); ?></td>
        <td><?php echo empty($user->amount_of_tags) ? 0 : htmlspecialchars($user->amount_of_tags); ?></td>
        <td><?php echo empty($user->amount_of_clicks) ? 0 : htmlspecialchars($user->amount_of_clicks); ?></td>
        <td><a class="btn btn-small" href="<?php echo site_url('usage/user_daily'); ?>?user_id=<?php echo htmlspecialchars($user->id); ?>">Detail</a></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>
</body>
</html>