<?php
    if ($_POST) {
        require_once 'lib/FCurl.php';
        $FCurl = new FCurl();
        $param['phoneNum'] = $_POST['username'];
        $param['password'] = $_POST['password'];
        $data = $FCurl::get('user/login', $param);
        setcookie('accessToken', $_COOKIE['accessToken'], time() - 3600);
        setcookie('accessToken', $data['accessToken'], $data['expireTime']);
        $FCurl::header('quan.php');
    }
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="msapplication-tap-highlight" content="no">
<title>Nash.CRM</title>

<link rel="apple-touch-icon-precomposed" href="images/favicon/apple-touch-icon-152x152.png">
<meta name="msapplication-TileColor" content="#FFFFFF">
<meta name="msapplication-TileImage" content="images/favicon/mstile-144x144.png">
<link rel="icon" href="images/favicon/favicon-32x32.png" sizes="32x32">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<!--  Android 5 Chrome Color-->
<meta name="theme-color" content="#EE6E73">

<link href="statics/css/materialize.min.css" rel="stylesheet">
<script src="statics/js/func.js"></script>
</head>
<body>
<div class="navbar-fixed">
<?php require_once 'blank.php'; ?>
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">登陆</a>
    <ul class="right hide-on-med-and-down">
    </ul>

    <?php require_once 'left1.php';?>
    <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
  </div>
</nav>
</div>

<main>
<div class="container">
<p>&nbsp;<br />&nbsp;</p>
<form action="./login.php" method="post">
    <div class="row">
      <div class="input-field col s10 offset-s1">
        <i class="mdi-action-account-circle prefix"></i>
        <input id="user_name" type="text" class="validate" length="30" name="username" required>
        <label for="user_name">用户名</label>
      </div>
    </div>

    <div class="row">
      <div class="input-field col s10 offset-s1">
        <i class="mdi-action-lock prefix"></i>
        <input id="password" type="password" class="validate" name="password" required>
        <label for="password">密码</label>
      </div>
    </div>

    <div class="center-align">
      <button class="btn waves-effect waves-light deep-orange" type="submit" name="action">登陆
      <i class="mdi-content-send right"></i>
      </button>
    <div>
</form>

</div>
</main>
<?php require_once 'bottom.php';?>