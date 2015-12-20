<?php require_once 'header1.php';?>
<?php require_once 'black_ipad.php';?>
<?php 
	if($_GET['phoneNum']) {
		Fcurl::header('audition_second.php?phoneNum='.$_GET['phoneNum']);
		exit();
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
    <a id="logo-container" href="#" class="brand-logo">面试</a>
    <ul class="right hide-on-med-and-down">
    </ul>
  </div>
</nav>
</div>

<main>
<p>&nbsp;</p>
<div class="container">
	<div id="search-bar" class="row " style="padding-top: .5em;width:100%">
	<form action="#" method="get">
	  <div class="col s12 flow-text">
	    <input id="search" placeholder="请输入手机号码" type="tel" class="validate" length="20" name="phoneNum" required/>
	  </div>
	  <div class="col s12 right-align">
	    <button class="btn waves-effect waves-light deep-orange" type="submit">
	      <i class="mdi-action-search"></i>
	    </button>
	  </div>
	</form>
	</div>
</div>
</main>

<?php require_once 'bottom.php';?>
<script src="statics/js/func.js"></script>