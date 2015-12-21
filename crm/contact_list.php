<?php require_once 'header.php';?>
<body>
<?php require_once 'blank.php'; ?>

<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">通讯录</a>
    <ul class="right hide-on-med-and-down">
    </ul>

    <?php require_once 'left2.php';?>
    <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
  </div>
</nav>
</div>

<main>
<div>
<div class="section">
  <?php
    $contactLsit = FCurl::get('user/getUserProfileList', array('accessToken' => $_COOKIE['accessToken']));
  ?>
  <table class="hoverable">
    <thead>
      <tr>
          <th data-field="photo">头像</th>
          <th data-field="name">姓名</th>
          <th data-field="tel">电话</th>
      </tr>
    </thead>

    <tbody>
      <?php
        foreach ($contactLsit as $value) {
      ?>
      <tr>
        <td><?php echo getUserPhoto($value['photo']);?></td>
        <td><?php echo $value['nickname']; ?></td>
        <td>
      <?php
        $value['phoneNum'] = explode(',', $value['phoneNum']);
        foreach($value['phoneNum'] as $phoneIndex) {
      ?>
      <a href="tel://<?php echo trim($phoneIndex); ?>"><?php echo trim($phoneIndex); ?></a><br>
      <?php
        }
      ?>
        </td>
      </tr>
      <?php
        }
      ?>
    </tbody>
  </table>
</div>
</div>
</main>
<?php require_once 'bottom.php';?>