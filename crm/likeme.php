<?php require_once 'header.php';?>
<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">赞 我</a>
    <ul class="right hide-on-med-and-down">
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
    $param['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
    $param['num'] = getPagingNumByRequestName('user/getLikeEventUserList');
    $likeInfo = FCurl::get('user/getLikeEventUserList', $param + array('accessToken' => $accessToken));
    foreach ($likeInfo as $value) {
        $userId = $value['createUserId'];
        if($value['eventType'] == 'group') {
        		if($value['enable_open']) {
        			echo '<div class="row">
		              <div class="col s12">
		                <h5>'.getUserPhoto($value['createUserPhoto'], 30).$value['createUser'].'</h5>
		                <div class="flow-text">'.eventTranslate($value['eventContent']).'<a href="#">'.$value['noticeUser'].'</a>'.getEventPhoto($value['photo']).'</div>
		                    <div class="row grey lighten-3" onclick="window.location='."'"."group_info.php?from=quan&id=".$userId."'".'" >
		                  <div class="col s2"><i class="small grey-text text-darken-2 mdi-social-location-city prefix"></i></div>';
        		}else {
        			echo '<div class="row">
		              <div class="col s12">
		                <h5>'.getUserPhoto($value['createUserPhoto'], 30).$value['createUser'].'</h5>
		                <div class="flow-text">'.eventTranslate($value['eventContent']).'<a href="#">'.$value['noticeUser'].'</a>'.getEventPhoto($value['photo']).'</div>
		                    <div class="row grey lighten-3">
		                  <div class="col s2"><i class="small grey-text text-darken-2 mdi-action-lock prefix"></i></div>';
        		}
        }else {
        		if($value['enable_open']) {
        			echo '<div class="row">
		              <div class="col s12">
		                <h5>'.getUserPhoto($value['createUserPhoto'], 30).$value['createUser'].'</h5>
		                <div class="flow-text">'.eventTranslate($value['eventContent']).'<a href="#">'.$value['noticeUser'].'</a>'.getEventPhoto($value['photo']).'</div>
		                    <div class="row grey lighten-3" onclick="window.location='."'"."contact_info.php?from=quan&id=".$userId."'".'" >
		                  <div class="col s2"><i class="small grey-text text-darken-2 mdi-action-account-box prefix"></i></div>';
        		}else {
        			echo '<div class="row">
		              <div class="col s12">
		                <h5>'.getUserPhoto($value['createUserPhoto'], 30).$value['createUser'].'</h5>
		                <div class="flow-text">'.eventTranslate($value['eventContent']).'<a href="#">'.$value['noticeUser'].'</a>'.getEventPhoto($value['photo']).'</div>
		                    <div class="row grey lighten-3" >
		                  <div class="col s2"><i class="small grey-text text-darken-2 mdi-action-lock prefix"></i></div>';
        		}
        }
		
        echo '<div class="col s10">'.$value['eventCreateUserNickname'].'</div>';
        echo '<div class="col s10">';         
                    if($value['eventType'] == 'group') {
                    		foreach ($value['relation'] as $key => $relationIndex) {
							$valueIndexArray = array();
							foreach($relationIndex as $valueIndex) {
								$valueIndexArray[] = $valueIndex['nickname'];
							}
							echo $key.':<a href="#">'.implode(',', $valueIndexArray).'</a> ';
				   		}
                    }else {
	                    	foreach ($value['eventOwnInfo'] as $tagIndex) {
	                        echo '<span class="'.$tagIndex['coler'].'">'.$tagIndex['tagName'].'</span> ';
	                    }
                    }
        echo '</div></div>
                <h6 class="grey-text">'.$value['createTime'].'</h6>
              </div>';
        $praiseArray = array();
        foreach ($value['praiseList'] as $praiseIndex) {
            $praiseArray[] = '<a href="">'.$praiseIndex.'</a> ';
        }
                    
        if (!empty($praiseArray)) {
            echo '<div class="col s1">
                <h5><i class="mdi-action-favorite pink-text"></i></h5>
              </div>
              <div class="col s10">
                <div class="card-panel orange lighten-5">
                  <div  class="flow-text">';
            echo implode(' , ', $praiseArray);
        }
        echo '</div>
                </div>
              </div>
              <div class="col s12 divider"></div>
            </div>';
    }
?>

<?php 
  echo paging('user/getLikeEventUserList', $_REQUEST, count($likeInfo), 'likeme.php');
?>

</div>
</main>

<?php require_once 'bottom.php';?>