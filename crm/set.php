<?php require_once 'header.php';?>
<body>
<?php require_once 'blank.php'; ?>
<?php
  if ($_POST) {
    #存在头像更改信息
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
      #存在照片信息&&上传成功
      $timeParam = date('Y/m/');
      if (file_exists(IMG_PATH_BASE.'avatar/'.$timeParam)) {
        ;
      }else {
        mkdir(IMG_PATH_BASE.'avatar/'.$timeParam, 0777, true);
      }
    }

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $userId = $GLOBALS['userId'];
        $filePath = 'avatar/'.$timeParam.$userId.'_'.md5_file($_FILES['photo']['tmp_name']).'.'.end(explode('.', $_FILES['photo']['name']));
        $filePathSmall = 'avatar/'.$timeParam.$userId.'_'.md5_file($_FILES['photo']['tmp_name']).'_s'.'.'.end(explode('.', $_FILES['photo']['name']));
        #上传原图
        file_put_contents(IMG_PATH_BASE.$filePath, file_get_contents($_FILES['photo']['tmp_name']));

        #修正图片大小
        require_once 'lib/photo.class.php';
        $photoTool = new photo();
        $photoInfo = $photoTool->getPhotoInfo(IMG_PATH_BASE.$filePath);
        if ($photoInfo['width'] > 600) {
        $rate = $photoInfo['width'] / 600;
            $photoInfo['width'] = 600;
            $photoInfo['height'] = ceil($photoInfo['height'] / $rate);
        }

        #根据照片exif信息决定是否旋转
        require_once 'lib/exif.php';
        $exifInfo = GetImageInfo(IMG_PATH_BASE.$filePath);
        if ($exifInfo['方向'] == 'right side top') {
          $photoTool->flip(IMG_PATH_BASE.$filePath, IMG_PATH_BASE.$filePath, -90);
                      $widthTmp = $photoInfo['width'];
                $photoInfo['width'] = $photoInfo['height'];
            $photoInfo['height'] = $widthTmp;
        }
        unset($exifInfo);

        $photoTool->copyImageWithSize(IMG_PATH_BASE.$filePath, IMG_PATH_BASE.$filePathSmall, $photoInfo['width'], $photoInfo['height']);

        $photo = $filePathSmall;
    }else {
      $photo = '';
    }

    $param['accessToken'] = $_COOKIE['accessToken'];
    $param['phoneNum'] = $_POST['phoneNum'];
    $param['password'] = $_POST['password'];
    $param['photo'] = $photo;
    FCurl::get('user/updateProfile', $param);

    echo '<script>
            alert("success");
            window.location.href="quan.php";
          </script>
        ';
  }
?>

<div class="navbar-fixed">
  <nav class="teal lighten-1" role="navigation">
    <div class="nav-wrapper">
      <a id="logo-container" class="dropdown-button brand-logo" href="#!" data-activates="dropdown-quan">
      设置
      </a>
      <?php require_once 'left2.php';?>
      <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
    </div>
  </nav>
</div>

<main>
  <div class="section">
    <div class="row">
    <form action="#" method="POST" enctype="multipart/form-data" class="col s12" onsubmit="return check()">

      <div class="row">
        <div class="input-field col s12">
          <input id="phoneNum" type="tel" class="validate" name="phoneNum" required>
          <label for="phoneNum">手机号</label>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="password" type="password" name="password" class="validate" required>
          <label for="password">登陆密码</label>
        </div>
      </div>

      <div class="row">
        <div class="file-field input-field">
          <input class="file-path validate" type="text"/>
          <div class="btn">
            <span>头像</span>
            <input type="file" name="photo" />
          </div>
        </div>
      </div>

      <div class="section center-align">
        <button class="btn waves-effect waves-light deep-orange" type="submit" name="action">
          提交
          <i class="mdi-content-send right"></i>
        </button>
      </div>

    </form>
    </div>
  </div>
</main>
<?php require_once 'bottom.php';?>
<script type="text/javascript">
function check() {
  if (!$('#phoneNum').val()) {
    alert('请填写手机号码');
    return false;
  };

  if (!$('#password').val()) {
    alert('请填写密码');
    return false;
  };

  showLoading();
  return true;
}
</script>