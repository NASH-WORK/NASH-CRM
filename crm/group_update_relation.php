<?php error_reporting(0) ?>
<?php require_once 'header.php';?>
<?php 
	$groupId = isset($_GET['id']) ? $_GET['id'] : 0;
	$param['accessToken'] = $_COOKIE['accessToken'];
	$param['id'] = $groupId;
	$groupInfo = FCurl::get('group/get', $param);
?>
<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">修改群组关系</a>
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
      <input id="account_name" type="text" class="validate" length="20" disabled value="<?php echo $groupInfo['name'] ;?>" name="username">
      <label for="account_name">群组名称</label>
    </div>
  </div>
  
  <div class="row">
    <div class="col s12">
      <div class="divider"></div>
      <label>群组成员列表</label>
    </div>
  </div>
  <ul class="collection">
  <?php 
  	foreach ($groupInfo['relation'] as $key => $value) {
  		foreach($value as $valueIndex) {
  			echo '
  				<li class="collection-item dismissable" id=relation_li_'.$valueIndex['userId'].'>
					<div>'.$key.' '.$valueIndex['nickname'].'('.$valueIndex['mobile'].')
					<a href="javascript:deleteRelation('.$valueIndex['userId'].','.$groupId.',\''.$_COOKIE['accessToken'].'\')" class="secondary-content">
					<i class="mdi-navigation-cancel"></i></a></div>
				</li>';
  		}
  	}
  ?>
  </ul>
  
  <div class="row grey lighten-4">
    <div class="col s12">
      <a id="savaData_a" href="#modalItems_addType" class="modal-trigger right btn-floating waves-effect waves-light teal lighten-1"><i id="saveData_a" class="mdi-content-add"></i></a>
      <div id="modalItems_addType" class="modal bottom-sheet">
        <div class="modal-content">
          <h5><i class="mdi-action-dns"></i> 添加种类</h5>
          <?php 
             $tagList = FCurl::get('group/getGroupRelationTypeList');
			 echo '<div class="divider"></div><p>';
             foreach ($tagList as $value) {
                 $name = $value['name'];
                 echo '<a href="group_seach.php?id='.$groupId.'&type='.$name.'" class="group-seach modal-action modal-close btn '.$value['color'].'">'.$name.'</a>';
				 echo '&nbsp';
             }  
			 echo '</p>';          
          ?>
        </div>
      </div>
    </div>
  </div>

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