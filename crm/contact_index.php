<?php require_once 'header.php';?>
<?php
    $accessToken = $_COOKIE['accessToken'];
    if (isset($_GET['phoneNum']) && $_GET['phoneNum']) {
        $param['accessToken'] = $accessToken;
        $param['phoneNum'] = $_GET['phoneNum'];
        $param['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
        $param['num'] = getPagingNumByRequestName('user/seachUserList');
        $userList = Fcurl::get('user/seachUserList', $param);
        $phoneNum = $_GET['phoneNum'];
    }else {
        $param['accessToken'] = $accessToken;
        $param['phoneNum'] = '';
        $param['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
        $param['num'] = getPagingNumByRequestName('user/seachUserList');
        $userList = Fcurl::get('user/seachUserList', $param);
        $phoneNum = '';
    }
?>

<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">联系人</a>
    <ul class="right">
      <li><a href="./contact_add.php"><i class="mdi-content-add"></i></a></li>
    </ul>

    <?php require_once 'left2.php';?>
    <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
  </div>
</nav>
</div>

<main>
<div id="search-bar" class="row z-depth-1 teal lighten-5" style="padding-top: .5em;width:100%">
<form action="#" method="get">
  <div class="col s8 flow-text">
    <input id="search" placeholder="手机号码或者联系人姓名" type="text" class="validate" length="20" name="phoneNum" value="<?php echo $phoneNum;?>">
  </div>
  <div class="col s4 right-align">
    <button class="btn waves-effect waves-light deep-orange" type="submit">
      <i class="mdi-action-search"></i>
    </button>
  </div>
</form>
</div>

<?php
  foreach ($userList as $value) {
      //$phone_num = $value['phoneNum'];
      echo '<div class="row">
            <div class="col s12">
              <div class="card-panel flow-text">
                <h5>
                  <i class="small mdi-action-account-box blue-text text-lighten-1"></i>
                  <a class="blue-text text-darken-4" href="contact_info.php?&id='.$value['userId'].'&seach='.$phoneNum.'">'.$value['nickname'].'</a>';
                  $value['phoneNum'] = str_replace('，', ',', $value['phoneNum']);
                  $value['phoneNum'] = explode(',', $value['phoneNum']);
                  foreach ($value['phoneNum'] as $key => $phoneNumIndex) {
                    echo '<a href="tel://'.$phoneNumIndex.'"><i class="mdi-communication-phone right"></i></a>';
                  }
      echo  '</h5>
                  <div class="flow-text">';
                  foreach ($value['eventOwnInfo'] as $tagIndex) {
                      echo '<a href="contact_hash.php?tagList=^'.$tagIndex['tagName'].'" class="'.$tagIndex['coler'].'">#'.$tagIndex['tagName'].'</a> ';
                  }
      echo '</div>
            <div class="divider"></div>
            <p><i class="grey-text text-darken-2 mdi-action-assignment-turned-in"></i>';
              $acctionArray = array();
              foreach ($value['acctionList'] as $acctionIndex) {
                  $acctionArray[] = '<a href="">'.$acctionIndex.'</a>';
              }
              echo implode(',', $acctionArray);
      echo '</p>
            <blockquote class="grey-text">
              <h6>'.eventTranslate($value['lastEventInfo']['content']).'</h6>'.getEventPhoto($value['lastEventInfo']['photo'], 30).'
              <h6>'.$value['lastEventInfo']['createTime'].'</h6>
            </blockquote>
          </div>
        </div>
      </div>';
  }
?>

<div class="section">
<?php
  $_REQUEST['phoneNum'] = isset($_REQUEST['phoneNum']) ? $_REQUEST['phoneNum'] : '';
  echo paging('user/seachUserList', $_REQUEST, count($userList), 'contact_index.php');
?>

</div>
</main>

<!--  Scripts-->
<script src="statics/js/jquery-2.1.3.min.js"></script>
<script src="statics/js/materialize.min.js"></script>

<script src="statics/js/init.js"></script>
</body>
</html>
