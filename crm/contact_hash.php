<?php
  error_reporting(0);
  require_once 'header.php';
?>
<?php
    $tagList = isset($_GET['tagList']) ? $_GET['tagList'] : '';
    $tagList = explode('^', $tagList);
    array_shift($tagList);
    $param['name'] = implode('&',$tagList);
    $param['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
    $param['num'] = getPagingNumByRequestName('user/getList');
    $userList = FCurl::get('user/getList', $param);
?>
<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">聚合</a>

    <?php require_once 'left2.php';?>
    <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
  </div>
</nav>
</div>

<main>
<div id="search-bar" class="row z-depth-1 teal lighten-5" style="padding-top: .5em;width:100%">
  <div class="col s9">
    <?php
        echo "&nbsp;";
        foreach ($tagList as $tagIndex) {
           if (!$tagIndex && !is_numeric($tagIndex)) continue;
           $param['name'] = $tagIndex;
           $tagInfo = FCurl::get('tag/getStyle', $param);
           $param = array();
    ?>
          <a onclick="del('<?php echo '^'.$tagIndex;?>')"><?php echo '<span class="btn '.$tagInfo.'">'.$tagIndex.'</span>'?></a>
    <?php
        }
    ?>
  </div>
  <div class="col s3 right-align">
    <a href="#modalTags" class="modal-trigger btn-floating waves-effect waves-light deep-orange">
      <i class="mdi-maps-local-offer"></i>
    </a>

    <div id="modalTags" class="modal bottom-sheet left-align">
      <div class="modal-content">
        <h5><i class="mdi-maps-local-offer"></i> 标签</h5>
        <?php
             $tagList = FCurl::get('tag/getListByAllSystem');
             foreach ($tagList as $value) {
                 echo '<div class="divider"></div><p>';
                 foreach ($value as $index) {
                     $name = $index['name'];
                     echo '<a onclick="add('."'$name'".')" class="text-mid '.$index['color'].'">'.$name.'</a>';
                     echo "\n";
                 }
                 echo '</p>';
             }
          ?>
      </div>
    </div>
  </div>
</div>

<div class="section">

<div class="row">
  <?php
    foreach ($userList as $value) {
      //print_r($value);
  ?>
    <div class="col s12">
    <div class="card-panel">
      <h5>
        <i class="small mdi-action-account-box blue-text text-lighten-1"></i>
        <a class="blue-text text-darken-4" href="contact_info.php?from=contact_hash&phoneNum=<?php echo $value['userProfile'][0]['phone_num'];?>&id=<?php echo $value['userId'];?>"><?php echo $value['userProfile'][0]['nickname']?></a>
        <?php
          $value['userProfile'][0]['phone_num'] = str_replace('，', ',', $value['userProfile'][0]['phone_num']);
          $value['userProfile'][0]['phone_num'] = explode(',', $value['userProfile'][0]['phone_num']);
          foreach ($value['userProfile'][0]['phone_num'] as $key => $phoneNumIndex) {
          ?>
            <a href="tel://<?php echo trim($phoneNumIndex);?>"><i class="mdi-communication-phone right"></i></a>
          <?
          }
        ?>

      </h5>
      <div class="flow-text">
        <?php
            foreach ($value['tag'] as $_tagIndex) {
                $tagIndexTmp = $_tagIndex['tagName'];
                echo '<a href="contact_hash.php?tagList=^'.$_tagIndex['tagName'].'" class="'.$_tagIndex['coler'].'">#'.$_tagIndex['tagName'].'</a> ';
            }
        ?>
      </div>
      <div class="divider"></div>
      <p><i class="grey-text text-darken-2 mdi-action-assignment-turned-in"></i>
      <?php
        $acctionListArray = array();
        foreach ($value['acctionList'] as $acctionList) {
            $acctionListArray[] = '<a href="">'.$acctionList.'</a>';
        }
        echo implode(',', $acctionListArray);
      ?>
      </p>
      <blockquote class="grey-text">
        <h6><?php echo eventTranslate($value['lastEventInfo'][0]['content'])?>
            <?php
            $value['lastEventInfo'][0]['noticeUser'] = isset($value['lastEventInfo'][0]['noticeUser']) ? $value['lastEventInfo'][0]['noticeUser'] : array();
            foreach ($value['lastEventInfo'][0]['noticeUser'] as $noticeUserIndex) {
                echo attranslate($noticeUserIndex);
            }
            echo getEventPhoto($value['lastEventInfo'][0]['photo']);
            ?>
        </h6>
        <h6><?php echo $value['lastEventInfo'][0]['createTime']?></h6>
      </blockquote>
    </div>
  </div>
  <?php
    }
  ?>
</div>

<?php
  $_REQUEST['tagList'] = isset($_REQUEST['tagList']) ? $_REQUEST['tagList'] : '';
  echo paging('user/getList', $_REQUEST, count($userList), 'contact_hash.php');
?>

</div>
</main>

<!--  Scripts-->
<script src="statics/js/jquery-2.1.3.min.js"></script>
<script src="statics/js/materialize.min.js"></script>

<script src="statics/js/init.js"></script>
<script type="text/javascript">
function getArgs() {
    var args = {};
    var match = null;
    var search = decodeURIComponent(location.search.substring(1));
    var reg = /(?:([^&]+)=([^&]+))/g;
    while((match = reg.exec(search))!==null){
        args[match[1]] = match[2];
    }
    return args;
}

$(document).ready(function(){
  $('.modal-trigger').leanModal();
  var currentParamTmp = getArgs();

  if (! currentParamTmp['tagList']) {
    $('.mdi-maps-local-offer').click();
  };
});

function del(name) {
    var currentParam = getArgs();
    var param = '';
    for(var i in currentParam) {
        param = currentParam[i];
        param = param.replace(name, "");
        break;
    }

    var url = window.location.href;
    var urlArray = url.split('?');
    window.location.href = urlArray[0]+'?tagList='+param;
}

function get(name) {
	var url = window.location.href;
  var urlArray = url.split('?');
	window.location.href = urlArray[0]+'?tagList=^'+name;
}

function add(name) {
	var currentParam = getArgs();
	for(var i in currentParam) {
    if(currentParam[i] == '^' + name) return false;
  }

  var url = window.location.href;
  if (url.indexOf('?tagList=') <= 0) {
    url = url + '?tagList=';
  }
  url = url + '^' + name;
	window.location.href = url;
}

</script>
</body>
</html>
