<?php require_once 'header1.php';?>
<?php require_once 'black_ipad.php';?>
<?php 
    if ($_POST) {
      #创建联系人
      $param['accessToken'] = 'vPoGp4lHm6';
      $param['phoneNum'] = $_POST['phoneNum'];
      $param['username'] = $_POST['username'];
      $userId = FCurl::get('user/createV3', $param);

      #更新联系人性别
      $isFirst = $_POST['group1'];
      if ($isFirst == '#初试') {
        $param = array();
        $param['accessToken'] = 'vPoGp4lHm6';
        $param['username'] = $_POST['username'];
        $param['id'] = $userId;
        $param['sex'] = $_POST['sex'];
        $param['phoneNum'] = $_POST['phoneNum'];
        $param['occupation'] = $_POST['event'];
        FCurl::get('contact/update', $param);
      }

      #追加事件信息
      $param = array();
      $param['id'] = $userId;
      $param['accessToken'] = 'vPoGp4lHm6';
      $isFirst = $_POST['group1'];
      if ($isFirst == '#初试') {
        $param['event'] = $isFirst.' #候选人 #职业 '.$_POST['event'].' @王则琼';
      } else {
        $param['event'] = $isFirst.' @王则琼';
      }
      
      FCurl::get('user/createEventForContact', $param);

      echo '<script type="text/javascript" charset="utf-8" async defer>
            alert("提交成功,请等待.");
            window.location.href="audition_index.php";
            </script>';      
    }
?>

<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <ul class="left">
      <li><a href="audition_index.php"><i class="mdi-image-navigate-before"></i></a></li>
    </ul>
    <a id="logo-container" href="#" class="brand-logo">初试人员信息</a>
  </div>
</nav>
</div>

<main>
<p>&nbsp;</p>
<div class="container">
<form action="#" method="post" onsubmit="return showLoadingForCheck()">
  
  <input type="text" name="accessToken" value="<?php echo $_COOKIE['accessToken'];?>" style="display:none">
  <input name="group1" type="radio" id="first" style="display:none" value="#初试" checked />
  <input id="sex" type="text" class="validate" length="1" style="display:none" name="sex" value="男" required>

  <div class="row">
    <div class="col s12 input-field">
      <i class="mdi-action-account-circle prefix"></i>
      <input id="account_name" type="text" class="validate" length="10" name="username" required>
      <label for="account_name">姓名</label>
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <i class="mdi-communication-phone prefix"></i>
      <input id="mobile" type="tel" class="validate" length="20" name="phoneNum" required>
      <label for="mobile">手机号</label>
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <select id="sex_select">
        <option value="男">男</option>
        <option value="女">女</option>
      </select>
      <label>请选择性别</label>
    </div>
  </div>

  <div class="row ">
    <div class="col s12">
      <div class="row">
        <div class="col s9 input-field grey lighten-4 ">
          <i id="active_i" class="mdi-editor-mode-edit prefix"></i>
          <textarea id="eventList" class="materialize-textarea" name="event" readonly="readonly"></textarea>
          <label id="active_label" for="eventList">应聘职位</label>
        </div>
        <div class="col s3 input-field ">
          <a id="work_select_a" href="#modalTags" class="modal-trigger btn-large waves-effect waves-light"> 职位列表</a>
        </div>
      </div>

      <div id="modalTags" class="modal bottom-sheet">
        <div class="modal-content">
          <h5><i class="mdi-maps-local-offer"></i>应聘职位</h5>
          <?php 
             $tagList = FCurl::get('tag/getAuditionTag'); 
             foreach ($tagList as $value) {
                 echo '<div class="divider"></div><p>';
                 foreach ($value as $index) {
                     $name = $index['name'];
                     echo '<a href="#!" onclick="add('."'$name'".')" class="modal-action modal-close btn  '.$index['color'].'">'.$name.'</a>';
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
  $('select').material_select();

  $('#sex_select').bind('change', function(event) {
    $('#sex').val($('#sex_select').val());
  });
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
   $('#eventList').val(tagName);
}

function showLoadingForCheck() {
  if (!$('#eventList').val()) {
    alert('请选择应试职位');
    $('#work_select_a').click();
    return false;
  };
  return true;
}
</script>