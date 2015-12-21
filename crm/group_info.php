<?php require_once 'header.php';?>
<?php 
	$groupId = isset($_GET['id']) ? $_GET['id'] : 0;
	$pageFrom = isset($_GET['from']) ? $_GET['from'] : 'group_index';
	
	$param['accessToken'] = $_COOKIE['accessToken'];
	$param['id'] = $groupId;
	$groupInfo = FCurl::get('group/get', $param);
	
	$seeEnable = FALSE;
	if(!empty($groupInfo)) {
		$param = array();
		$param['accessToken'] = $_COOKIE['accessToken'];
		$param['id'] = $groupId;
		$param['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
		$param['num'] = isset($_GET['num']) ? $_GET['num'] : DEFAULT_MESSAGE_NUM;
		$groupEventInfo = FCurl::get('group/getEventList', $param);
		$seeEnable = TRUE;
	}
?>
<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">群组信息</a>
    <ul class="right hide-on-med-and-down">
    </ul>

    <ul class="left">
    		<a href="<?php echo $pageFrom; ?>.php"><i class="mdi-navigation-arrow-back"></i></a>
    </ul>
    
    <?php 
    		if($seeEnable) {
    ?>
    <ul class="right">
      <li><a href="group_update.php?id=<?php echo $groupId; ?>"><i class="mdi-editor-mode-edit"></i></a></li>
    </ul>
    <?php			
    		}
    ?>
  </div>
</nav>
</div>

<?php 
	if(!$seeEnable) exit('无权查看该群组信息');
?>

<main>
	<div id="namecard-bar" class="row z-depth-1 light-green lighten-5">
	  <div class="row">
	    <div class="col s2">
	      <i class="small mdi-action-account-box grey-text text-darken-2"></i>
	    </div>
	    <div class="col s10">
	      <h5><?php echo $groupInfo['name']; ?></h5>
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
	      <?php
	      	$groupInfoTagListArray = array(); 
	      	foreach($groupInfo['tagList'] as $groupInfoTagListIndex) {
	      		$groupInfoTagListArray[] = '<span>#'.$groupInfoTagListIndex['name'].'</span>';
	      	}
	      	echo implode(' ', $groupInfoTagListArray);
	      ?>
	    </div>
	    <div class="col s1">
	      <i class="mdi-action-assignment-turned-in grey-text text-darken-2"></i>
	    </div>
	    <div class="col s5">
	      <?php
	      	$groupInfoAcctionListArray = array(); 
	      	foreach ($groupInfo['acctionList'] as $groupInfoAcctionListIndex) {
	      		$groupInfoAcctionListArray[] = '<a href="">'.$groupInfoAcctionListIndex['nickname'].'</a>';
	      	}
			echo implode(', ', $groupInfoAcctionListArray);
	      ?>
	   	</div>
	   	<div class="col s12">
	   		<div class="divider"></div>
	   	</div>
	   	</div>
	   	
	   	<div class="row">
	    <div class="col s1">
	      <i class="mdi-action-assignment-turned-in grey-text text-darken-2"></i>
	    </div>
	   	<div class="col s11">
	   	<?php 
	   		foreach ($groupInfo['relation'] as $key => $value) {
				$valueIndexArray = array();
				foreach($value as $valueIndex) {
					$valueIndexArray[] = $valueIndex['nickname'];
				}
				echo $key.':<a href="#">'.implode(',', $valueIndexArray).'</a> ';
	   		}
	   	?>
	   	</div>
	  </div>
	  <div class="row">
	    <div class="col s12">
	      <a href="group_update_relation.php?id=<?php echo $groupId; ?>" class="right btn-floating waves-effect waves-light"><i class="mdi-content-add"></i></a>
	    </div></div>
	</div>
	
	<div class="section">
	    <div class="row">
	    <div class="col s12 center-align">
	      <a href="group_remark.php?id=<?php echo $groupId; ?>" class="btn-floating waves-effect waves-light"><i class="mdi-content-add"></i></a>
	    </div>
	</div>
	
<?php 
	foreach($groupEventInfo as $groupEventInfoIndex) {
?>
	  <div class="row" id="event_<?php echo $groupEventInfoIndex['id']; ?>">
	    <div class="col s3 teal-text text-lighten-1">
	      <h6>
	        <i class="mdi-editor-mode-comment right"></i>
	        <a href="#"><?php echo $groupEventInfoIndex['createUserInfo']['nickname']; ?></a>
	      </h6>
	    </div>
	    <div class="col s9">
	      <div class="flow-text">
	      	<?php 
	      		echo eventTranslate($groupEventInfoIndex['contact']);
				if ($groupEventInfoIndex['photo']) {
              		echo getEventPhoto($groupEventInfoIndex['photo'], 100);
            		}
	      	?>
	      	
	      </div>                    
	      <h6 class="grey-text"><?php echo $groupEventInfoIndex['createTime']; ?></h6>
	      <!--
          	作者：ruckfull@gmail.com
          	时间：2015-06-23
          	描述：群组事件删除
          -->
	      <?php 
	      	if($groupEventInfoIndex['currentUserId'] == $groupEventInfoIndex['createUserInfo']['userId']) {
	      ?>
	      	  <h6 class="grey-text right">
		          <a href="javascript:callbackEvent(<?php echo $groupEventInfoIndex['id']; ?>)">
		            	<i class="small deep-orange-text mdi-action-highlight-remove"></i>
		          </a>
        	  	  </h6>
	      <?php
	      	}
	      ?>
          
	    </div>
	    <div class="col s12 divider"></div>
	  </div>
<?php		
	}
?>

<?php
  echo paging('group/getEventList', $_REQUEST, count($groupEventInfo), 'group_info.php');
?>
</main>

<!--  Scripts-->
<script src="statics/js/jquery-2.1.3.min.js"></script>
<script src="statics/js/materialize.min.js"></script>

<script src="statics/js/init.js"></script>
<script type="text/javascript">
$(document).ready(function(){
  $('.modal-trigger').leanModal();
});

function callbackEvent(id) {
  if (confirm('确定撤销该事件吗?')) {
    var host = window.location.host;
    var accessToken = getCookie('accessToken');
    $.post(getAjaxRequestAddress('group/callbackEvent'), {accessToken:accessToken, id:id}, function(data) {
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