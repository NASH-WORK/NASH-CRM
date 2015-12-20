<?php require_once 'header.php';?>
<body>

<?php 
  $param['accessToken'] = $_COOKIE['accessToken'];
  $data = FCurl::get('user/getUserGroupInfo', $param);
?>
<ul id="dropdown-quan" class="dropdown-content">
  <li><a href="quan.php?type=all">工作圈</a></li>
  <li><a href="quan.php?tagId=<?php echo $data['tagList']; ?>">项目圈</a></li>
  <li class="divider"></li>
  <li><a href="user.php?userId=<?php echo $data['userId']; ?>">我</a></li>
</ul>

<?php require_once 'blank.php'; ?>

<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" class="dropdown-button brand-logo" href="#!" data-activates="dropdown-quan">
    <?php 
      if(isset($_GET['tagId']) && $_GET['tagId']) echo '项目圈';
      elseif (isset($_GET['userId']) && $_GET['userId']) echo '我';
      else echo '工作圈';
    ?>
    <i class="mdi-navigation-arrow-drop-down right"></i></a>

    <ul class="right">
      <li><a href="#" onclick="window.location.reload()"><i class="mdi-navigation-refresh"></i></a></li>
    </ul>

    <?php require_once 'left2.php';?>
    <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
  </div>
</nav>
</div>
<main>
<div class="section">

<?php 
    $accessToken = $_COOKIE['accessToken'];
    $param['accessToken'] = $accessToken;
    $param['tagId'] = isset($_GET['tagId']) && $_GET['tagId'] ? $_GET['tagId'] : '';
    $param['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
    $param['num'] = DEFAULT_MESSAGE_NUM;
    $callFunctionName = isset($_GET['tagId']) ? 'user/getEventListByGroupId' : 'user/getEventList';
    $eventList = FCurl::get($callFunctionName, $param);
    foreach ($eventList as $value) {
      echo '<div class="row">
            <div class="col s2">';
      echo getUserPhoto($value['createUserPhoto']);
      echo '</div>
            <div class="col s10">
              <h5><a class="blue-text text-darken-4" href="./user.php?userId='.$value['eventCreateUserId'].'">'.$value['createUserName'].'</a></h5>
              <div class="flow-text">'.eventTranslate($value['eventContent']);
      //$value['eventContantUser'] = explode(',', $value['eventContantUser']);
      foreach ($value['eventContantUser'] as $eventContantUserIndex) {
          echo attranslate($eventContantUserIndex);
      }
      echo getEventPhoto($value['photo'], 100);
      $userId = $value['createUserId'];
      if($value['enable_open']) {
      	if($value['eventType'] == 'group') {
	      	echo '</div><div class="row grey lighten-3" onclick="window.location='."'"."group_info.php?from=quan&id=".$userId."'".'">
	                <div class="col s2"><i class="small grey-text text-darken-2 mdi-social-location-city prefix"></i></div>';
	      }else {
	      	echo '</div><div class="row grey lighten-3" onclick="window.location='."'"."contact_info.php?from=quan&id=".$userId."'".'">
	                <div class="col s2"><i class="small grey-text text-darken-2 mdi-action-account-box prefix"></i></div>';
	      }
      }else {
      	if($value['eventType'] == 'group') {
	      	echo '</div><div class="row grey lighten-3">
	                <div class="col s2"><i class="small grey-text text-darken-2 mdi-action-lock prefix"></i></div>';
	      }else {
	      	echo '</div><div class="row grey lighten-3">
	                <div class="col s2"><i class="small grey-text text-darken-2 mdi-action-lock prefix"></i></div>';
	      }
      }
      echo '<div class="col s10">'.$value['eventCreateUserNickname'].'</div>';
      echo '<div class="col s10">';
                if ($value['eventType'] == 'group') {
                		foreach ($value['relation'] as $key => $relationIndex) {
						$valueIndexArray = array();
						foreach($relationIndex as $valueIndex) {
							$valueIndexArray[] = $valueIndex['nickname'];
						}
						echo $key.':<a href="#">'.implode(',', $valueIndexArray).'</a> ';
			   		}
                }else {
	                	foreach ($value['eventOwnInfo'] as $tagIndex) {
	                    $tagName = $tagIndex['tagName'];
	                    echo '<span class="'.$tagIndex['coler'].'">'.$tagIndex['tagName'].'</span> ';
	                }
                }
       echo  '</div></div>
              <h6 class="grey-text">'.$value['time'].'</h6>';
                $praiseArray = array();
                foreach ($value['praise'] as $praiseIndex) {
                    $praiseArray[] = '<a href="">'.$praiseIndex.'</a> ';
                }
                echo empty($praiseArray) ? '<div id=praiselist_'.$value['eventId'].'>' : '<div id=praiselist_'.$value['eventId'].'><i class="mdi-action-favorite-outline"></i>';
                echo implode(' , ', $praiseArray);
                $hadPraise = $value['hadPraised'] ? 'pink-text mdi-action-favorite' : 'pink-text mdi-action-favorite-outline';
       echo  '</div>';
              if($value['hadPraised']) {
                   echo '<h5 class="right-align"><a id=event_a_'.$value['eventId'].' onclick="unlikeEvent('.$value['eventId'].', '."'$accessToken'".')"><i id=event_'.$value['eventId'].' class="'.$hadPraise.'"></i></a></h5>';
              } else {
                  echo '<h5 class="right-align"><a id=event_a_'.$value['eventId'].' onclick="likeEvent('.$value['eventId'].', '."'$accessToken'".')"><i id=event_'.$value['eventId'].' class="'.$hadPraise.'"></i></a></h5>';
              }               
       echo '</div>
            <div class="col s12 divider"></div>
          </div>';

    }
?>

<?php 
  echo paging($callFunctionName, $_REQUEST, count($eventList), 'quan.php');
?>

</div>
</main>
<?php require_once 'bottom.php';?>
<script type="text/javascript">
function likeEvent(eventId, accessToken) {
    $.post(getAjaxRequestAddress('user/likeEvent'), {eventId:eventId, accessToken:accessToken}, function(result) {
         if (result.code == 0) {
            $('#event_' + eventId).attr("class", "pink-text mdi-action-favorite");
            $('#event_a_' + eventId).attr("onclick", "unlikeEvent("+eventId+", "+"'"+accessToken+"'"+")");

            var appendHeml = '';
            if($('#praiselist_' + eventId + ' i').length) {
                ;             
            }else {
              appendHeml += '<i class="mdi-action-favorite-outline"></i>';
            }
            appendHeml += '<a href="">'+result.data+'</a>';
            
            $('#praiselist_' + eventId).append(appendHeml);
//           window.location.reload();
         }else {
            alert(result.message);
         }
    });
}
function unlikeEvent(eventId, accessToken) {
//  var host = window.location.host;
//     $.post('http://'+ host + '/vstone/app/?r=user/unlikeEvent', {eventId:eventId, accessToken:accessToken}, function(result) {
//          if (result.code == 0) {
//             $('#event_' + eventId).attr("class", "pink-text mdi-action-favorite-outline");
//             $('#event_a_' + eventId).attr("onclick", "likeEvent("+eventId+", "+"'"+accessToken+"'"+")");
//             window.location.reload();
//          }else {
//             alert(result.message);
//          }
//     });
}

</script>