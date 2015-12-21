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
      $param['systemEvent'] = true;
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
    <a id="logo-container" href="#" class="brand-logo">复试人员信息</a>
  </div>
</nav>
</div>

<main>
<p>&nbsp;</p>
<div class="container">
<form action="#" method="post" onsubmit="showLoading()">
  
  <input type="text" name="accessToken" value="<?php echo $_COOKIE['accessToken'];?>" style="display:none">
  <input name="group1" type="radio" id="first" style="display:none" value="#复试" checked />

  <div class="row">
    <div class="col s12 input-field">
      <i class="mdi-action-account-circle prefix"></i>
      <input id="account_name" type="text" class="validate" length="10" name="username" readonly="readonly" style="color:#000">
      <!-- <label for="account_name">姓名</label> -->
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <i class="mdi-communication-phone prefix"></i>
      <input id="mobile" type="tel" class="validate" length="20" name="phoneNum" readonly="readonly" style="color:#000">
      <!-- <label for="mobile">手机号</label> -->
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <i class="mdi-action-face-unlock prefix"></i>
      <input id="sex" type="text" class="validate" length="1" name="sex" readonly="readonly" style="color:#000">
      <!-- <label for="sex">性别</label> -->
    </div>
  </div>

  <div class="row ">
    <div class="col s12">
      <div class="row">
        <div class="col s12 input-field grey lighten-4 ">
          <i id="active_i" class="mdi-editor-mode-edit prefix"></i>
          <textarea id="eventList" class="materialize-textarea" name="event" readonly="readonly" style="color:#000" ></textarea>
          <!-- <label id="active_label" for="eventList">应聘职位</label> -->
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
function GetQueryString(name)
{
     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
     var r = window.location.search.substr(1).match(reg);
     if(r!=null)return  unescape(r[2]); return null;
}

$(document).ready(function(){
  var phoneNum = GetQueryString('phoneNum');
  if (phoneNum) {
    //查找手机号对应信息
    $.getJSON(getAjaxRequestAddress('audition/seach'), {phoneNum:phoneNum}, function(result) {
        if (result.code == 0) {
          //填充相关信息
          $('#account_name').val(result.data.nickname);
          $('#mobile').val(result.data.phoneNum);
          $('#sex').val(result.data.sex);
          $('#eventList').val(result.data.occupation);
        } else{
          //联系人信息不存在
          alert('相关信息不存在. 请输入正确的手机号码');
          window.location.href="audition_second_seach.php";
        };
    });
  } else{
    alert('请输入正确的手机号码');
    window.location.href="audition_second_seach.php";
  };
});
</script>