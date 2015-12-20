<?php require_once 'header.php';?>
<?php 
	$seach = isset($_GET['seach']) ? $_GET['seach'] : '';

	$param['accessToken'] = $_COOKIE['accessToken'];
	$param['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
	$param['num'] = DEFAULT_MESSAGE_NUM;
	if($seach) $param['name'] = $seach;
	$seachResult = FCurl::get('group/seachByName', $param);
?>
<body>
<?php require_once 'blank.php'; ?>
<div class="navbar-fixed">
<nav class="teal lighten-1" role="navigation">
  <div class="nav-wrapper">
    <a id="logo-container" href="#" class="brand-logo">群组搜索</a>
    <ul class="right">
      <li><a href="./group_add.php"><i class="mdi-content-add"></i></a></li>
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
	    <input id="search" placeholder="请输入项目房间号" type="text" class="validate" name="seach" required value="<?php echo $seach; ?>" >
	  </div>
	  <div class="col s4 right-align">
	    <button class="btn waves-effect waves-light deep-orange" type="submit">
	      <i class="mdi-action-search"></i>
	    </button>
	  </div>
	</form>
</div>

<?php 
	foreach($seachResult as $seachResultIndex) {
?>
	<div class="row">
		<div class="col s12">
			<div class="card-panel flow-text">
				<h5>
					<i class="small mdi-action-account-box blue-text text-lighten-1"></i>
					<a href="group_info.php?id=<?php echo $seachResultIndex['id']; ?>" class="blue-text text-darken-4"><?php echo $seachResultIndex['name']; ?></a>
				</h5>
				<div class="flow-text">
				<?php 
					foreach($seachResultIndex['tagList'] as $seachResultTagListIndex) {
						echo '<a href="contact_hash.php?tagList=^'.$seachResultTagListIndex['name'].'" class="'.$seachResultTagListIndex['tagClass'].'">#'.$seachResultTagListIndex['name'].'</a>&nbsp';
					}
				?>
				</div>
				<div class="divider"></div>
				<p>
					<i class="grey-text text-darken-2 mdi-action-assignment-turned-in"></i>
				<?php
					$seachResultAcctionListArray = array(); 
					foreach($seachResultIndex['acctionList'] as $seachResultAcctionListIndex) {
						$seachResultAcctionListArray[] = '<a href="#'.$seachResultAcctionListIndex['id'].'">'.$seachResultAcctionListIndex['nickname'].'</a>';
					}
					echo implode(',', $seachResultAcctionListArray);
				?>
				</p>
				<blockquote class="grey-text">
					<?php 
						echo '<h6>'.eventTranslate($seachResultIndex['lastEvent']['contact']).'</h6>'.getEventPhoto($seachResultIndex['lastEvent']['photo'], 30);
					?>
					<h6><?php echo $seachResultIndex['lastEvent']['createTime']; ?></h6>
				</blockquote>
			</div>
		</div>		
	</div>
<?		
	}
?>
<?php
  echo paging('group/seachByName', $_REQUEST, count($seachResult), 'group_index.php');
?>

<div class="section">
</div>
</main>

<!--  Scripts-->
<script src="statics/js/jquery-2.1.3.min.js"></script>
<script src="statics/js/materialize.min.js"></script>

<script src="statics/js/init.js"></script>
</body>
</html>
