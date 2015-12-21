<!-- <div class="container"> -->
<form action="#" method="post" onsubmit="showLoading()">
  <input type="text" name="accessToken" value="<?php echo $_COOKIE['accessToken'];?>" style="display:none">
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
      <input id="mobile" type="text" class="validate" length="20" name="phoneNum" required>
      <label for="mobile">手机号</label>
    </div>
  </div>

  <div class="row">
    <div class="col s12 input-field">
      <i class="mdi-action-face-unlock prefix"></i>
      <input id="sex" type="text" class="validate" length="1" name="sex" required>
      <label for="sex">性别</label>
    </div>
  </div>
  
<!--   <div class="row">
    <div class="col s12 input-field">
      <i class="mdi-action-verified-user prefix"></i>
      <div class="col s2">&nbsp;</div>
      <div class="col s4">
        <P>
          <input name="group1" type="radio" id="first" value="#初试" checked />
          <label for="first">初试</label>
        </P>
      </div>
      <div class="col s4">
        <P>
          <input name="group1" type="radio" id="second" value="#复试" />
          <label for="second">复试</label>
        </P>
      </div>
    </div>
  </div> -->

  <div class="row grey lighten-4">
    <div class="col s12">
      <div class="row">
        <div class="col s12 input-field">
          <i id="active_i" class="mdi-editor-mode-edit prefix"></i>
          <textarea id="eventList" class="materialize-textarea" name="event" ></textarea>
          <label id="active_label" for="eventList">应聘职位</label>
        </div>
      </div>
      <a href="#modalTags" class="modal-trigger btn-floating waves-effect waves-light teal lighten-1"><i class="mdi-maps-local-offer"></i></a>

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
<!-- </div> -->