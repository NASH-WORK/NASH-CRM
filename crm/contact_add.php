<?php require_once 'header.php';?>
<?php 
    if ($_POST) {
        $param['accessToken'] = $_COOKIE['accessToken'];
        $param['username'] = $_POST['username'];
        $param['phoneNum'] = $_POST['phoneNum'];
        $param['event'] = $_POST['event'];
        $result = FCurl::get('user/createV2', $param);
		
		if($_POST['callback']) {
			#返回seach页面
			FCurl::header('group_seach.php?id='.$_POST['groupId'].'&type='.$_POST['addType'].'&name='.$_POST['username']);
		}else {
			FCurl::header('contact_info.php?phoneNum='.$param['phoneNum'].'&id='.$result['data']);
		}
    }
?>

<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">新联系人</a>
    <ul class="right hide-on-med-and-down">
    </ul>

    <ul class="left">
      <li><a href="./contact_index.php"><i class="mdi-navigation-arrow-back"></i></a></li>
    </ul>
  </div>
</nav>
</div>

<main>
<p>&nbsp;</p>
<div class="container">
<form action="#" method="post" onsubmit="showLoading()">
  <input type="text" name="accessToken" value="<?php echo $_COOKIE['accessToken'];?>" style="display:none">
  
  <!--
  	作者：ruckfull@gmail.com
  	时间：2015-06-23
  	描述：因群组添加更改
  -->
  <input type="text" name="groupId" value="<?php echo isset($_GET['groupId']) ? $_GET['groupId'] : 0; ?>" style="display:none" />
  <input type="text" name="callback" value="<?php echo isset($_GET['callback']) ? $_GET['callback'] : ''; ?>" style="display:none" />
  <input type="text" id="addType" name="addType" value="<?php echo isset($_GET['addType']) ? $_GET['addType'] : ''; ?>" style="display:none" />
  
  <div class="row">
    <div class="col s12 input-field">
      <i class="mdi-action-account-circle prefix"></i>
      <input id="account_name" type="text" class="validate" length="20" name="username" required>
      <label for="account_name">姓名</label>
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <i class="mdi-communication-phone prefix"></i>
      <input id="mobile" type="text" class="validate" length="200" name="phoneNum" required>
      <label for="mobile">手机号</label>
    </div>
  </div>

  <div class="row grey lighten-4">
    <div class="col s12">
      <div class="row">
        <div class="col s12 input-field">
          <i id="active_i" class="mdi-editor-mode-edit prefix"></i>
          <textarea id="eventList" class="materialize-textarea" name="event"></textarea>
          <label id="active_label" for="eventList">跟进记录</label>
        </div>
      </div>
      <a href="#modalTags" class="modal-trigger btn-floating waves-effect waves-light teal lighten-1"><i class="mdi-maps-local-offer"></i></a>

      <div id="modalTags" class="modal bottom-sheet">
        <div class="modal-content">
          <h5><i class="mdi-maps-local-offer"></i> 标签</h5>
          <?php 
             $tagList = FCurl::get('tag/getListBySystem'); 
             foreach ($tagList as $value) {
                 echo '<div class="divider"></div><p>';
                 foreach ($value as $index) {
                     $name = $index['name'];
                     echo '<a id="'.$index['name'].'" href="#!" onclick="add('."'$name'".')" class="uncheck-a-status modal-action text-mid '.$index['color'].'">'.$name.'</a>';
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
  $('#mobile').bind('change', function(event) {
      var host = window.location.host;
      var cookie = getCookie('accessToken');
      $.post(getAjaxRequestAddress('contact/checkContactExistByPhoneNum'), {phoneNum: $('#mobile').val(), accessToken:cookie}, function(result) {
        if (result.code == 0) {
          if (result.data) {
            //联系人已经存在
            alert('联系人已经存在');
            window.location.href = 'contact_info.php?&id='+result.data+'&seach=';
          }
        }else {
          //alert('验证失败');
        }
      });
  });
  
  var addType = $('#addType').val();
  if(addType) add(addType);
  
});

function getCookie(name) 
{ 
    var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
 
    if(arr=document.cookie.match(reg))
 
        return unescape(arr[2]); 
    else 
        return null; 
} 

function add(tagName) {
    $('#active_i').attr('class', 'mdi-editor-mode-edit prefix active');
    $('#active_label').attr('class', 'active');
    $('#eventList').val($("#eventList").val() + ' #'+tagName+' ');
    $('#'+tagName).addClass('check-a-statys');
}
</script>