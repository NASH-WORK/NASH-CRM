<?php require_once 'header.php';?>
<?php 
    if ($_POST) {
        $param['accessToken'] = $_COOKIE['accessToken'];
        foreach ($_POST as $key => $value) {
            $param[$key] = $value;
        }
        $result = FCurl::get('contact/update', $param);
        FCurl::header('contact_info.php?phoneNum='.$param['phoneNum'].'&id='.$param['id'].'&seach='.$_POST['seach']);
    }

    $phoneNum = $_GET['phoneNum'];
    $param['accessToken'] = $_COOKIE['accessToken'];
    $param['phoneNum'] = $phoneNum;
    $param['id'] = $_GET['id'];
    $userProfile = FCurl::get('contact/getContactProfile', $param);
    $phoneNum = $userProfile['phoneNum'];
?>

<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo"><?php echo $userProfile['username']?> 修改</a>
    <ul class="right hide-on-med-and-down">
    </ul>

    <ul class="left">
      <li><a href="contact_info.php?phoneNum=<?php echo $phoneNum;?>&id=<?php echo $userProfile['id']?>"><i class="mdi-navigation-arrow-back"></i></a></li>
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
      <input name='id' value="<?php echo $userProfile['id']?>" style='display: none'>
      <input id="account_name" type="text" class="validate" length="20" required value="<?php echo $userProfile['username']?>" name="username">
      <label for="account_name">姓名</label>
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <i class="mdi-communication-phone prefix"></i>
      <input id="mobile" type="text" class="validate" length="200" required value="<?php echo $phoneNum;?>" name="phoneNum">
      <label for="mobile">手机号</label>
    </div>
  </div>

  <div class="row">
    <div class="col s12">
      <div class="divider"></div>
    </div>
  </div>

  <div class="row grey lighten-4">
    <div id="userProfile">
    <?php 
        $userProfile['userProfile'] = is_array($userProfile['userProfile']) ? $userProfile['userProfile'] : array();
        foreach ($userProfile['userProfile'] as $key => $value) {
            if ($key == 'id') continue;
            if ($key == 'seach') continue;
            ?>
            <div class="col s12 input-field">
              <i class="mdi-action-info prefix"></i>
              <input id="mobile" type="text" class="validate" length="20" required value="<?php echo $value['value']?>" name="<?php echo $key?>">
              <label for="mobile"><?php echo $value[$key]?></label>
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
             $tagList = FCurl::get('tag/getUserProfileList');
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
    <button class="btn waves-effect waves-light deep-orange" type="submit" >
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
});

function add(tagName, key) {
    var addHtml = '<div class="col s12 input-field">';
    addHtml += '<i class="mdi-action-info prefix active "></i>';
    addHtml += '<input id="'+tagName+'" name="'+key+'" type="text" class="validate" length="20" required>';
    addHtml += '<label for="'+tagName+'" class="active" >'+tagName+'</label></div>';
  $('#userProfile').append(addHtml);  
}
</script>
</body>
</html>