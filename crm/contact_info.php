<?php require_once 'header.php';?>
<?php
    $_GET['seach'] = isset($_GET['seach']) ? $_GET['seach'] : '';
    $_GET['phoneNum'] = isset($_GET['phoneNum']) ? $_GET['phoneNum'] : '';
    $phoneNum = $_GET['phoneNum'];
    $param['accessToken'] = $_COOKIE['accessToken'];
    $param['phoneNum'] = $phoneNum;
    $param['id'] = $_GET['id'];
    $userProfile = FCurl::get('contact/getContactProfile', $param);
	
	$seeEnable = FALSE;
	if(empty($userProfile)) {
		$seeEnable = FALSE;
	}else {
		$param['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
	    $param['num'] = getPagingNumByRequestName('user/getUserOwnerEventList');
	    $eventList = FCurl::get('user/getUserOwnerEventList', $param);
	    $eventList = $eventList['returnData'];
		$seeEnable = TRUE;
	}
	
    
?>

<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">联系人信息</a>
    <ul class="right hide-on-med-and-down">
    </ul>

    <ul class="left">
      <?php
        if (isset($_GET['from']) && $_GET['from'] == 'quan') {
           ?><li><a href="javascript:history.back(1)"><i class="mdi-navigation-arrow-back"></i></a></li><?php
         }elseif (isset($_GET['from']) && $_GET['from']) {
            ?><li><a href="<?php echo $_GET['from']?>.php"><i class="mdi-navigation-arrow-back"></i></a></li><?php
        }else {
            ?><li><a href="contact_index.php?phoneNum=<?php echo $_GET['seach'];?>"><i class="mdi-navigation-arrow-back"></i></a></li><?php
        }
      ?>
    </ul>
    
    <?php 
    		if($seeEnable) {
    ?>
    <ul class="right">
      <li><a href="contact_update.php?phoneNum=<?php echo $phoneNum;?>&id=<?php echo $userProfile['id']?>"><i class="mdi-editor-mode-edit"></i></a></li>
    </ul>
    <?php			
    		}
    ?>
  </div>
</nav>
</div>

<?php 
	if(!$seeEnable) exit('无权限查看该联系人信息');
?>

<main>
<div id="namecard-bar" class="row z-depth-1 light-green lighten-5">
  <div class="row">
    <div class="col s2">
      <i class="small mdi-action-account-box grey-text text-darken-2"></i>
    </div>
    <div class="col s10">
      <h5><?php echo $userProfile['username'];?></h5>
    </div>
    <div class="col s2">
      <i class="small mdi-communication-phone grey-text text-darken-2"></i>
    </div>
    <div class="col s8">
      <h5>
        <!-- <a href=""><?php echo $userProfile['phoneNum']?></a> -->

        <?php
          $userProfile['phoneNum'] = str_replace('，', ',', $userProfile['phoneNum']);
          $userProfile['phoneNum'] = explode(',', $userProfile['phoneNum']);
          foreach ($userProfile['phoneNum'] as $key => $phoneNumIndex) {
        ?>
          <a href="tel://<?php echo trim($phoneNumIndex);?>"><?php echo showMobileStyle($phoneNumIndex);?></a>
        <?
          }
        ?>
      </h5>
    </div>
    <div class="col s2 right-align">
      <h5><a onclick="$('#contactDetails').toggle()"><i class="grey-text text-darken-3 mdi-navigation-unfold-more"></i></a ></h5>
    </div>

    <div id="contactDetails" class="row" style="display:none">
      <div class="col s12">
        <div class="divider"></div>
      </div>
      <?php
        $showMessageNum = 0;
        $loop = count($userProfile['userProfile']);
        foreach ($userProfile['userProfile'] as $key => $userProfileIndex) {
          $showMessageNum++;
            ?>
                <div class="col s2 grey-text text-darken-2"><?php echo $userProfileIndex[$key]?></div>
                <div class="col s4"><?php echo $userProfileIndex['value']?></div>
            <?php
            if ($showMessageNum && !($showMessageNum%2) && $showMessageNum != $loop) {
                echo '<div class="row"></div>';
            }
        }
      ?>
    </div>

    <div class="col s12">
      <div class="divider"></div>
    </div>
  </div>
  <div class="row">
    <div class="col s1">
      <i class="mdi-maps-local-offer grey-text text-darken-2"></i>
    </div>
    <div class="col s5">
      <?php echo $userProfile['tagList']?>
    </div>
    <div class="col s1">
      <i class="mdi-action-assignment-turned-in grey-text text-darken-2"></i>
    </div>
    <div class="col s5">
      <?php echo $userProfile['acctionList']?>
    </div>
  </div>
</div>

<div class="section">
    <div class="row">
    <div class="col s12 center-align">
      <a href="contact_remark.php?phoneNum=<?php echo $phoneNum;?>&id=<?php echo $userProfile['id']?>" class="btn-floating waves-effect waves-light"><i class="mdi-content-add"></i></a>
    </div>
</div>
<?php
    foreach ($eventList as $value) {
      // print_r($value);exit();
        ?>
      <div class="row" id="event_<?php echo $value['eventId'];?>" >
        <div class="col s3 teal-text text-lighten-1">
          <h6>
            <i class="mdi-editor-mode-comment right"></i>
            <a href=""><?php echo $value['createUserName'];?></a>
          </h6>
        </div>
        <div class="col s9">

          <div class="flow-text"><?php echo eventTranslate($value['content']);?>
          <?php
            //$value['noticeUser'] = explode(',', $value['noticeUser']);
            $value['noticeUser'] = isset($value['noticeUser']) ? $value['noticeUser'] : array();
            foreach ($value['noticeUser'] as $noticeUserIndex) {
                echo attranslate($noticeUserIndex);
            }
            if ($value['photo']) {
              echo getEventPhoto($value['photo'], 100);
            }
            echo '</div>';
          ?>
          <?php
            if ($value['createUserId'] == $value['currentUserId']) {
          ?>
              <h6 class="grey-text right">
              <a href="javascript:callbackEvent(<?php echo $value['eventId'] ?>)">
                <i class="small deep-orange-text mdi-action-highlight-remove"></i>
              </a>
              </h6>
          <?php
            }
          ?>
          <h6 class="grey-text"><?php echo $value['createTime'];?></h6>
        </div>
        <div class="col s12 divider"></div>
      </div>
      <?php
    }
?>
<?php
  echo paging('user/getUserOwnerEventList', $_REQUEST, count($eventList), 'contact_info.php');
?>

</div>

</main>

<!--  Scripts-->
<script src="statics/js/jquery-2.1.3.min.js"></script>
<script src="statics/js/materialize.min.js"></script>

<script src="statics/js/init.js"></script>
<script type="text/javascript">
$(document).ready(function(){
  //$('#namecard-bar').pushpin({ top: $('#namecard-bar').offset().top });
  $('.modal-trigger').leanModal();
});

function callbackEvent(id) {
  if (confirm('确定撤销该事件吗?')) {
    var host = window.location.host;
    var accessToken = getCookie('accessToken');
    $.post(getAjaxRequestAddress('user/callbackEvent'), {accessToken:accessToken, id:id}, function(data) {
      if (data.code == 0) {
        //撤销成功
        $('#event_' + id).css('display', 'none');
      }else {
        //撤销失败
        alert('操作失败');
      }
    });
  };
}

function getCookie(name)
{
    var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
    if(arr=document.cookie.match(reg))
        return unescape(arr[2]);
    else
        return null;
}

</script>
</body>
</html>