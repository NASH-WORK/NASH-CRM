<?php require_once 'header.php';?>
<?php 
	$groupId = isset($_GET['id']) ? $_GET['id'] : 0;
	$contactType = isset($_GET['type']) ? $_GET['type'] : '未知';
?>
<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">绑定联系人</a>
    <ul class="left">
      <li><a href="group_update.php?id=<?php echo $groupId; ?>"><i class="mdi-navigation-arrow-back"></i></a></li>
    </ul>
  </div>
</nav>
</div>

<main>
 <nav>
    <div class="nav-wrapper">
      <form>
        <div class="input-field">
        	  <input id="search_type" type="search" value="<?php echo $contactType; ?>" style="display: none;"/>
        	  <input id="seach_group_id" type="search" value="<?php echo $groupId; ?>" style="display: none;"/>
        	  <input id="accessToken" type="text" value="<?php echo $_COOKIE['accessToken']; ?>" style="display: none;"/>
          
          <input id="search" type="search" required onpropertychange="javascript:seachByName()" value="<?php echo isset($_GET['name']) ? $_GET['name'] : '' ?>">
          <label for="search"><i class="mdi-action-search"></i></label>
          <i class="mdi-navigation-close"></i>
        </div>
      </form>
    </div>
  </nav>
  <ul class="collection" id="seach_result_ul"></ul>
  <div class="section">
    <div class="row">
    <div class="col s12 center-align">
      <a href="contact_add.php?callback=group_seach.php&groupId=<?php echo $groupId;?>&addType=<?php echo $contactType; ?>" class="btn-floating waves-effect waves-light"><i class="mdi-content-add"></i></a>
    </div>
  </div>
</main>

<!--  Scripts-->
<script src="statics/js/jquery-2.1.3.min.js"></script>
<script src="statics/js/materialize.min.js"></script>
<script src="statics/js/init.js"></script>
<script type="application/javascript">
	$(document).ready(function(){
		var seachType = $('#search_type').val() == '成员' ? 1 : 0;

		$('#search').bind('input propertychange', function(){
			var name = $('#search').val();
			$.getJSON(getAjaxRequestAddress('contact/seach'), {name:name, type:seachType}, function(result){
				if(result.code == 0) {
					var html = '';
					for(var i in result.data) {
						html += '<li class="collection-item dismissable">';
						html += '<div>'+result.data[i].nickname+'('+result.data[i].phone_num+')';
						html += '<a href="javascript:bindRelation('+result.data[i].userId+')" class="secondary-content">';
						html += '<i class="mdi-content-add-circle-outline"></i></a></div></li>';
					}
					$('#seach_result_ul').html(html);
				}else {
					alert('查询失败');
				}
			});
		});
		
		var seachValue = $("#search").val();
		if(seachValue) {
			$.getJSON(getAjaxRequestAddress('contact/seach'), {name:seachValue, type:seachType}, function(result){
				if(result.code == 0) {
					var html = '';
					for(var i in result.data) {
						html += '<li class="collection-item dismissable">';
						html += '<div>'+result.data[i].nickname+'('+result.data[i].phone_num+')';
						html += '<a href="javascript:bindRelation('+result.data[i].userId+')" class="secondary-content">';
						html += '<i class="mdi-content-add-circle-outline"></i></a></div></li>';
					}
					$('#seach_result_ul').html(html);
				}else {
					alert('查询失败');
				}
			});
		}
		
	});
	
	function bindRelation(userId) {
		var accessToken = $('#accessToken').val();
		var groupId = $('#seach_group_id').val();
		var type = $('#search_type').val();
		
		$.post(getAjaxRequestAddress('group/addRelation'), {accessToken:accessToken, groupId:groupId, userId:userId, type:type}, function(result){
			if (result.code == 0) {
				alert('添加成功');
				window.location.href='group_update_relation.php?id='+groupId;
			} else{
				alert('操作失败');
			}
		});
	}
	
</script>
</body>
</html>
