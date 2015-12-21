<?php require_once 'header.php';?>
<body>
<?php require_once 'blank.php'; ?>

<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">信息统计</a>
    <ul class="right hide-on-med-and-down">
    </ul>

    <?php require_once 'left2.php';?>
    <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
  </div>
</nav>
</div>

<main>
<div>
<div class="section">&nbsp;<br /></div>
<div class="section">
  <table class="hoverable">
    <thead>
      <tr>
          <th data-field="rank">排名</th>
          <th data-field="id">姓名</th>
          <th data-field="name">所属项目</th>
          <th data-field="week_num">本周发布信息数</th>
          <th data-field="total_num">累计发布信息数</th>
          <th data-field="new_content_num">本周新增联系人数</th>
          <th data-field="new_group_num">本周新增群组数</th>
      </tr>
    </thead>

    <tbody>
    <?php
      $countResult = FCurl::get('statistics/overview', array('type' => 'event'));
      $rank = 1;
      foreach ($countResult as $key => $value) {
        $projectInfo = array();
        foreach ($value['projectInfo'] as $projectInfoIndex) {
          $projectInfo[] = $projectInfoIndex['name'];
        }
    ?>
      <tr>
        <td><?php echo $rank; ?></td>
        <td><a href="user.php?userId=<?php echo $value['userId']; ?>"><?php echo $value['userInfo'][0]['nickname'];?></a></td>
        <td><?php echo empty($projectInfo) ? '无' : implode(',', $projectInfo); ?></td>
        <td><?php echo $value['weekNum'] ?></td>
        <td><?php echo $value['num'];?></td>
        <td><?php echo $value['createContentNumByWeek']; ?></td>
        <td><?php echo $value['createGroupNumByWeek']; ?></td>
      </tr>
    <?php
      $rank++;
      }
    ?>
    </tbody>
  </table>
</div>
</div>
</main>
<?php require_once 'bottom.php';?>