<?php require_once 'header.php';?>
<?php
    $param['id'] = $_GET['id'];
    $param['phoneNum'] = $_GET['phoneNum'];
    if ($_POST) {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
          #存在照片信息&&上传成功
          $timeParam = date('Y/m/');
          if (file_exists(IMG_PATH_BASE.'contact/'.$timeParam)) {
            ;
          }else {
            mkdir(IMG_PATH_BASE.'contact/'.$timeParam, 0777, true);
          }
        }

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $userId = $param['id'];
            $filePath = 'contact/'.$timeParam.$userId.'_'.md5_file($_FILES['photo']['tmp_name']).'.'.end(explode('.', $_FILES['photo']['name']));
            $filePathSmall = 'contact/'.$timeParam.$userId.'_'.md5_file($_FILES['photo']['tmp_name']).'_s'.'.'.end(explode('.', $_FILES['photo']['name']));
            #上传原图
            file_put_contents(IMG_PATH_BASE.$filePath, file_get_contents($_FILES['photo']['tmp_name']));

            #修正图片大小
            require_once 'lib/photo.class.php';
            $photoTool = new photo();
            $photoInfo = $photoTool->getPhotoInfo(IMG_PATH_BASE.$filePath);
            if ($photoInfo['width'] > 600) {
            $rate = $photoInfo['width'] / 600;
                $photoInfo['width'] = 600;
                $photoInfo['height'] = ceil($photoInfo['height'] / $rate);
            }

            #根据照片exif信息决定是否旋转
            require_once 'lib/exif.php';
            $exifInfo = GetImageInfo(IMG_PATH_BASE.$filePath);
            if ($exifInfo['方向'] == 'right side top') {
              $photoTool->flip(IMG_PATH_BASE.$filePath, IMG_PATH_BASE.$filePath, -90);
                          $widthTmp = $photoInfo['width'];
                    $photoInfo['width'] = $photoInfo['height'];
                $photoInfo['height'] = $widthTmp;
            }
            unset($exifInfo);

            $photoTool->copyImageWithSize(IMG_PATH_BASE.$filePath, IMG_PATH_BASE.$filePathSmall, $photoInfo['width'], $photoInfo['height']);
        }

        //unlink(IMG_PATH_BASE.$filePath);
        $param['event'] = $_POST['event'];
        $param['accessToken'] = $_COOKIE['accessToken'];
        if (isset($filePathSmall) && $filePathSmall) $param['photo'] = $filePathSmall;
        $result = Fcurl::get('user/createEventForContact', $param);
        Fcurl::header('contact_info.php?phoneNum='.$param['phoneNum'].'&id='.$param['id']);
    }else {
        $userProfile = Fcurl::get('contact/getContactProfile', $param);
    }
?>
<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">新增跟进</a>
    <ul class="right hide-on-med-and-down">
    </ul>

    <ul class="left">
      <li><a href="contact_info.php?phoneNum=<?php echo $param['phoneNum'];?>&id=<?php echo trim($param['id']);?>"><i class="mdi-navigation-arrow-back"></i></a></li>
    </ul>

  </div>
</nav>
</div>

<main>
<div class="row">
  <div class="col s2">
    <i class="small mdi-action-account-box grey-text text-darken-2"></i>
  </div>
  <div class="col s10">
    <h5><?php echo $userProfile['username']?></h5>
  </div>
  <div class="col s2">
    <i class="small mdi-communication-phone grey-text text-darken-2"></i>
  </div>
  <div class="col s10">
    <h5><a href=""><?php echo showMobileStyle($userProfile['phoneNum']);?></a></h5>
  </div>
  <div class="col s12">
    <div class="divider"></div>
  </div>
</div>
<div class="row">
  <div class="col s2">
    <i class="tiny mdi-maps-local-offer grey-text text-darken-2"></i>
  </div>
  <div class="col s10">
    <?php echo $userProfile['tagList']?>
  </div>
</div>

<div class="section z-depth-1 amber lighten-5">
<form action="#" method="post" enctype="multipart/form-data" onsubmit="return checkTagLength()">
  <div class="row">
    <div class="col s12">
      <div class="row">
        <div class="col s12 file-field right-align">
          <span class="btn-floating waves-effect waves-light orange lighten-1">
            <i class="mdi-image-photo-camera"></i>
            <input id="photo" type="file" name="photo">
          </span>
        </div>
      </div>
      <div class="row">
        <div class="col s12 input-field">
          <i id="active_i" class="mdi-editor-mode-edit prefix"></i>
          <input name="id" value="<?php echo $_GET['id'];?>" style="display: none">
          <textarea id="eventList" class="materialize-textarea" name="event" ></textarea>
          <label for="eventList" id="active_label">跟进记录</label>
        </div>
      </div>
	  
	  <a href="#modalTags" class="modal-trigger btn-floating waves-effect waves-light teal lighten-1"><i class="mdi-maps-local-offer"></i></a>
	  
	  <div class="row">
	  	<div class="col s12 file-field left-align">
          	<p>
		      <input type="checkbox" class="filled-in" id="filled-in-box" checked="checked" />
		      <label for="filled-in-box">公开</label>
		    </p>
		    <p id="see-list-p" style="display: none;">
		      <label for="see-list" id="active_label">查看范围</label>
		    	  <textarea id="see-list" class="materialize-textarea" name="see-list"></textarea>
		    </p>
      </div>
	 </div>
	 
      <div id="modalTags" class="modal bottom-sheet">
        <div class="modal-content">
          <h5><i class="mdi-maps-local-offer"></i> 标签</h5>
          <?php
             $tagList = Fcurl::get('tag/getListBySystem');
             foreach ($tagList as $value) {
                 echo '<div class="divider"></div><p>';
                 foreach ($value as $index) {
                     $name = $index['name'];
//                   echo '<a href="#!" onclick="add('."'$name'".')" class="modal-action modal-close text-mid '.$index['color'].'">'.$name.'</a>';
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
    <button class="btn waves-effect waves-light deep-orange" type="submit" name="action">
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
  var isOpen = true;
  var host = window.location.host;
  $('.modal-trigger').leanModal();
  $('#modalTags').openModal();
  
  $('#filled-in-box').bind('click', function(){
  	isOpen = isOpen ? false : true;
  	if(isOpen) {
  		$('#see-list-p').css('display', 'none');
  		$('#see-list').val('0');
  	}else {
  		$('#see-list-p').css('display', 'block');
  		$('#see-list').val('');
  	}
  });

  $('#eventList').bind('propertychange input', function(event) {
    var inputVal = $("#eventList").val();
    var last = inputVal.charAt(inputVal.length-1);
    if (last == '@' || last == '@') {
      var keyword = last.replace('@', '');
      $.getJSON(getAjaxRequestAddress('user/getUserList'), {keyword:keyword}, function(result) {
          //todo 如何展示ajax获取数据
      });
    };
  });
});

function checkTagLength() {
  var content = $('#eventList').val();
  var tagList = $('#eventList').val().match(/#[^\s #]+/g);
  for (var i in tagList) {
    if (tagList[i].length > 6) {
      if (confirm('确定添加标签'+tagList[i]+'吗?')) {
        ;
      }else {
        return false;
      }
    }
  }
  showLoading();
  return true;
}

function add(tagName) {
   $('#active_i').attr('class', 'mdi-editor-mode-edit prefix active');
   $('#active_label').attr('class', 'active');
   $('#eventList').val($("#eventList").val() + ' #'+tagName+' ');
   $('#'+tagName).addClass('check-a-statys');
}
</script>
</body>
</html>