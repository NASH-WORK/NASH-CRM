<?php
    if ($_COOKIE['accessToken']) {
        $unreadInfo = FCurl::get('user/getUnreadInfo', array('accessToken' => $_COOKIE['accessToken']));
        if (is_array($unreadInfo)) {
            $unreadNotice = $unreadInfo['notice'];
            $unreadLike = $unreadInfo['like'];
        }else {
            $unreadNotice = 0;
            $unreadLike = 0;
        }
    }else {
        $unreadNotice = 0;
        $unreadLike = 0;
    }
?>

<ul id="nav-mobile" class="side-nav">
  <li><i class="mdi-image-camera grey-text left"></i><a href="./quan.php">工作圈</a></li>
  <?php
      if ($unreadNotice) {
          echo '<span class="badge">'.$unreadNotice.'</span>';
      }
  ?>
  <li><i class="mdi-action-assignment-ind grey-text left"></i><a href="./atme.php">&nbsp; @ 我</a>

  </li>
  <?php
      if ($unreadLike) echo '<span class="badge new">'.$unreadLike.'</span>';
  ?>
  <li><i class="mdi-action-thumb-up grey-text left"></i><a href="./likeme.php">&nbsp; 赞 我</a>

  </li>
<!--   <li><i class="mdi-action-event grey-text left"></i><a href="#">日历</a></li> -->
  <li><i class="mdi-action-account-box grey-text left"></i><a href="./contact_index.php">联系人</a></li>
  <li><i class="mdi-action-room grey-text left"></i><a href="./group_room.php">房间</a></li>
  <!--<li><i class="mdi-action-room grey-text left"></i><a href="./room_index.php">房间</a></li>-->
  <li><i class="mdi-action-polymer grey-text left"></i><a href="./contact_hash.php?tagList=">聚合</a></li>
  <li><i class="mdi-action-toc grey-text left"></i><a href="./count.php">信息统计</a></li>
  <li><i class="mdi-action-settings grey-text left"></i><a href="./set.php">设置</a></li>
  <li><i class="mdi-social-group grey-text left"></i><a href="./contact_list.php">内部通讯录</a></li>
  <li><i class="mdi-social-location-city grey-text left"></i><a href="./group_index.php">群组</a></li>
</ul>
