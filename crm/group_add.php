<?php require_once 'header.php';?>
<?php 
	if($_POST) {
		$param['accessToken'] = $_COOKIE['accessToken'];
		$param['name'] = $_POST['groupName'];
		$param['groupType'] = $_POST['group_type'];
		$param['groupProject'] = $_POST['group_project'];
		$groupId = FCurl::get('group/create', $param);
		FCurl::header('group_update.php?id='.$groupId);
	}
?>
<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">新群组</a>
    <ul class="right hide-on-med-and-down">
    </ul>

    <ul class="left">
      <li><a href="./group_index.php"><i class="mdi-navigation-arrow-back"></i></a></li>
    </ul>
  </div>
</nav>
</div>

<main>
<p>&nbsp;</p>
<div class="container">
<form action="#" method="post" onsubmit="return create()">
  <div class="row">
    <div class="col s12 input-field">
      <i class="mdi-action-account-circle prefix"></i>
      <input id="group_name" type="text" class="validate" length="20" name="groupName" required>
      <label for="group_name">群组名称</label>
    </div>
    
    <div class="col s12 select-field">
      <label>群组性质</label>
      <input name="group_type" id="group_type" value="1" style="display: none;" />
      <select class="browser-default" id="group_type_select">
        <option value="1" selected >房间</option>
        <option value="0">普通群组</option>
      </select>
      
      <label>所属项目</label>
      <input name="group_project" id="group_project" value="15" style="display: none;" />
      <select class="browser-default" id="group_project_select">
        <?php
        		$param['accessToken'] = $_COOKIE['accessToken'];
			$project = FCurl::get('tag/getALLProjectInfo', $param);
        		foreach($project as $key => $projectIndex) {
        			if($key == 0) {
        				echo '<option value="'.$projectIndex['id'].'" selected >'.$projectIndex['name'].'</option>';
        			}else {
        				echo '<option value="'.$projectIndex['id'].'" >'.$projectIndex['name'].'</option>';
        			}
        		}
        ?>
      </select>
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
</main>

<?php require_once 'bottom.php';?>
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
  
  //保证群组名称惟一
  $('#group_name').bind('change', function(){
  	var groupName = $("#group_name").val();
 	$.getJSON(getAjaxRequestAddress('group/checkGroupName'), {name:groupName}, function(result){
 		if(result.code == 0) {
 			//请求成功
 			if (result.data == false) alert('该群组名称已经存在');
 		}else {
 			//请求失败
 			alert('服务不可用.');
 		}	
 	});
  });    
});

function create() {
	var groupName = $("#group_name").val();
 	$.getJSON(getAjaxRequestAddress('group/checkGroupName'), {name:groupName}, function(result){
 		if(result.code == 0) {
 			//请求成功
 			if (result.data == false) {
 				alert('该群组名称已经存在');
 				return false;
 			}
 		}else {
 			//请求失败
 			alert('服务不可用.');
 			return false;
 		}	
 	});
 	showLoading();
 	return true;
}

function add(tagName, key) {
    var addHtml = '<div class="col s12 input-field">';
    addHtml += '<i class="mdi-action-info prefix active "></i>';
    addHtml += '<input id="'+tagName+'" name="'+key+'" type="text" class="validate" length="20" required>';
    addHtml += '<label for="'+tagName+'" class="active" >'+tagName+'</label></div>';
  $('#group_list').append(addHtml);  
}

function getCookie(name) 
{ 
    var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
    if(arr=document.cookie.match(reg)) return unescape(arr[2]); 
    else return null; 
} 

</script>