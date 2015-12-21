<?php error_reporting(0) ?>
<?php require_once 'header.php';?>
<?php 
	$groupId = isset($_GET['id']) ? $_GET['id'] : 0;
	$param['accessToken'] = $_COOKIE['accessToken'];
	$param['id'] = $groupId;
	$groupInfo = FCurl::get('group/get', $param);
	
	if($_POST) {
		#更新群组信息
		foreach($_POST as $key => $value) $param[$key] = $value;
		$param['accessToken'] = $_COOKIE['accessToken'];
		FCurl::get('group/update', $param);
		FCurl::header('group_info.php?id='.$_POST['id']);
	}
?>
<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">修改</a>
    <ul class="right hide-on-med-and-down">
    </ul>

    <ul class="left">
      <li><a href="group_info.php?id=<?php echo $groupId; ?>"><i class="mdi-navigation-arrow-back"></i></a></li>
    </ul>
  </div>
</nav>
</div>

<main>
<p>&nbsp;</p>
<div class="container">
<form action="#" method="post" onsubmit="showLoading()">
  <div class="row">
    <div class="col s12 input-field">
      <i class="mdi-action-account-circle prefix"></i>
      <input name='id' value="<?php echo $groupId; ?>" style='display: none'>
      <input id="account_name" type="text" class="validate" length="20" required value="<?php echo $groupInfo['name'] ;?>" name="name">
      <label for="account_name">群组名称</label>
    </div>
    
    <div class="col s12 select-field">
      <label>群组性质</label>
      <input name="group_type" id="group_type" value="<?php echo $groupInfo['groupType'];?>" style="display: none;" />
      <select class="browser-default" id="group_type_select">
        <option value="1" <?php if($groupInfo['groupType'] == 1) echo 'selected'; ?> > 房间</option>
        <option value="0" <?php if($groupInfo['groupType'] == 0) echo 'selected'; ?> > 普通群组</option>
      </select>
      
      <label>所属项目</label>
      <input name="group_project" id="group_project" value="<?php echo $groupInfo['groupProject']['id'] ? $groupInfo['groupProject']['id'] : 15;?>" style="display: none;" />
      <select class="browser-default" id="group_project_select">
        <?php
        		$param['accessToken'] = $_COOKIE['accessToken'];
			$project = FCurl::get('tag/getALLProjectInfo', $param);
        		foreach($project as $key => $projectIndex) {
        			if($projectIndex['id'] == $groupInfo['groupProject']['id']) {
        				echo '<option value="'.$projectIndex['id'].'" selected >'.$projectIndex['name'].'</option>';
        			}else {
        				echo '<option value="'.$projectIndex['id'].'" >'.$projectIndex['name'].'</option>';
        			}
        		}
        ?>
      </select>
      
    </div>
    
  </div>
  
  <div class="row grey lighten-4">
    <div id="userProfile">
    <?php 
        $groupInfo['profile'] = is_array($groupInfo['profile']) ? $groupInfo['profile'] : array();
        foreach ($groupInfo['profile'] as $key => $value) {
    ?>
            <div class="col s12 input-field">
              <i class="mdi-action-info prefix"></i>
              <input id="mobile" type="text" class="validate" length="20" required value="<?php echo $value['showValue'];?>" name="<?php echo $value['formKey'];?>">
              <label for="mobile"><?php echo $value['showName'];?></label>
            </div>
            <?php 
        }
    ?>
    </div>
    <div class="col s12">
      <a href="#modalItems" class="modal-trigger btn-floating waves-effect waves-light teal lighten-1"><i class="mdi-content-add"></i></a>
      <div id="modalItems" class="modal bottom-sheet">
        <div class="modal-content">
          <h5><i class="mdi-action-dns"></i> 条目</h5>
          <?php 
             $tagList = FCurl::get('tag/getGroupProfileList');
             foreach ($tagList as $value) {
                 echo '<div class="divider"></div><p>';
                 foreach ($value as $index) {
                     $name = $index['name'];
                     $key = $index['formKey'];
                     echo '<a href="#!" onclick="add('."'$name'".', '."'$key'".')" class="modal-action modal-close btn '.$index['color'].'">'.$name.'</a>';
                     echo "\n";
                 }
                 echo '</p>';
             }            
          ?>
        </div>
      </div>

    </div>
  </div>
  
  <div class="section center-align">
    <button class="btn waves-effect waves-light deep-orange" type="submit" id="commit_butten" <!--style="opacity: 0;"-->
      提交
      <i class="mdi-content-send right"></i>
    </button>
  <div>

</form>
</div>
</main>

<!--  Scripts-->
<script src="statics/js/jquery-2.1.3.min.js"></script>
<script src="statics/js/materialize.min.js"></script>

<script src="statics/js/init.js"></script>
<script src="statics/js/func.js"></script>
<script type="text/javascript">
$(document).ready(function(){
  $('.modal-trigger').leanModal();
  
  //绑定群组类型事件
  $('#group_project_select').bind('change', function(){
  	$('#group_project').val($('#group_project_select').val());
  });
  //绑定群组项目事件
  $('#group_type_select').bind('change', function(){
  	$('#group_type').val($('#group_type_select').val());
  });
  
});

function add(tagName, key) {
    var addHtml = '<div class="col s12 input-field">';
    addHtml += '<i class="mdi-action-info prefix active "></i>';
    addHtml += '<input id="'+tagName+'" name="'+key+'" type="text" class="validate" length="20" required value="">';
    addHtml += '<label for="'+tagName+'" class="active" >'+tagName+'</label></div>';
  $('#userProfile').append(addHtml);  
}

function addEvent(tagName) {
   $('#active_i').attr('class', 'mdi-editor-mode-edit prefix active');
   $('#active_label').attr('class', 'active');
   $('#eventList').val($("#eventList").val() + ' #'+tagName+' ');
   $('#'+tagName).addClass('check-a-statys');
}

function deleteRelation(userId, groupId, accessToken) {
	$.post(getAjaxRequestAddress('group/deleteRelation'), {accessToken:accessToken, userId:userId, groupId:groupId}, function(result){
		if (result.code == 0) {
			alert('操作成功');
			$('#relation_li_'+userId).css('display', 'none');
		} else{
			alert('删除失败');
		}
	});
}

</script>
</body>
</html>