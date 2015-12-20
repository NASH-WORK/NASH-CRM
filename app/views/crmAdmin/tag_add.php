<?php 
  require_once 'header.php';
?>
<?php 
  if ($_POST) {
    $param['name'] = $_POST['tag_name'];
    $param['type'] = $_POST['tag_type'];
    $param['color'] = isset($_POST['color']) ? $_POST['color'] : '';
    $param['keyName'] = isset($_POST['form_name']) ? $_POST['form_name'] : '';

    FCurl::get('tag/createNoAccessToken', $param);
   }
?>

<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" class="dropdown-button brand-logo" href="#!" data-activates="dropdown-quan">添加标签</a>
    <?php require_once 'left.php' ?>    
  </div>
</nav>

<main>
<div class="section">

<div class="row">
<form action="#" method="post" enctype="multipart/form-data">
  <div class="row">
    <div class="col s12 input-field">
      <input id="tag_name" type="text" class="validate" length="20" required name="tag_name">
      <label for="tag_name">标签名称</label> 
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <input id="color" type="text" class="validate" length="50" required name="color">
      <label for="color">标签颜色</label>
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <input id="tag_type" type="text" class="validate" length="50" required name="tag_type">
      <label for="tag_type">标签类型</label>
    </div>
  </div>
  
  <div class="row">
    <div class="col s4">
      <p>
        <input type="checkbox" class="filled-in" id="is_profile_tag" />
        <label for="is_profile_tag">是否是用户信息标签</label>
      </p> 
    </div>

    <div class="col s8 input-field" style="display:none" id="color_div">
      <input id="form_name" type="text" class="validate" length="50" name="form_name">
      <label for="form_name">标签名称(用于程序上传区分使用)</label>
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
</div>
</main>

<!--  Scripts-->
<script src="statics/js/jquery-2.1.3.min.js"></script>
<script src="statics/js/materialize.min.js"></script>
<script src="statics/js/init.js"></script>
<script type="text/javascript">
  var isUserProfileTag = 0;
  $(document).ready(function() {
    $('#is_profile_tag').bind('change', function(event) {
      /* Act on the event */
      isUserProfileTag = isUserProfileTag ? 0 : 1;
      if (isUserProfileTag) {
        $('#color_div').css('display', 'block');
        $('#tag_type').val(10);
      }else {
        $('#color_div').css('display', 'none');
      };
    });
  });
</script>
</body>
</html>