<?php 
  require_once 'header.php';
?>

<?php
  if ($_POST) {
    #更新or创建账户信息
    $param['phoneNum'] = $_POST['phoneNum'];
    $param['password'] = $_POST['password'];
    $param['nickname'] = $_POST['nickname'];
    $param['project'] = $_POST['project'];
    $userId = FCurl::get('user/createUserAccount', $param);

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
      #存在上传头像文件且上传成功
      $filePath = 'avatar/'.$userId.'_'.md5_file($_FILES['photo']['tmp_name']).'.'.end(explode('.', $_FILES['photo']['name']));
      #修正图片大小
      require_once 'lib/photo.class.php';
      $photoTool = new photo();
      $photoTool->copyImageWithSize($_FILES['photo']['tmp_name'], IMG_PATH_BASE.$filePath, 120, 120);

      $param = array();
      $param['userId'] = $userId;
      $param['photo'] = $filePath;
      FCurl::get('user/updateUserPhoto', $param);

      FCurl::header('user_add.php');
    }
  }
?>

<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" class="dropdown-button brand-logo" href="#!" data-activates="dropdown-quan">添加用户</a>
    <?php require_once 'left.php' ?>    
  </div>
</nav>

<main>
<div class="section">

<div class="row">
<!-- <form action="../../vstone/app/?r=user/create" method="post" enctype="multipart/form-data"> -->
<form action="#" method="post" enctype="multipart/form-data">
  <div class="row">
    <div class="col s12 input-field">
      <input id="account_name" type="text" class="validate" length="20" required name="nickname">
      <label for="account_name">姓名</label>
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <input id="mobile" type="text" class="validate" length="20" required name="phoneNum">
      <label for="mobile">手机号</label>
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <input id="password" type="password" class="validate" length="20" required name="password">
      <label for="password">登陆密码</label>
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <input id="photo" type="file" class="validate" length="20" required name="photo">
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <input id="project" type="text" class="validate" length="20" required name="project">
      <label for="project">管理项目信息</label>
    </div>
  </div>  

  <div class="section center-align">
    <button class="btn waves-effect waves-light deep-orange" type="submit">
      提交
      <i class="mdi-content-send right"></i>
    </button>
  <div>

</form>
</div>
</div>
</main>

<!--  Scripts-->
<script src="statics/js/jquery-2.1.3.min.js"></script>
<script src="statics/js/materialize.min.js"></script>

<script src="statics/js/init.js"></script>
</body>
</html>