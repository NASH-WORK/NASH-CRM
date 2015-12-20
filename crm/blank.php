<?php 
	if (strpos($_SERVER["HTTP_USER_AGENT"],"MicroMessenger")) {
		
	}elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Android')) {
// 		echo '<div class="navbar-fixed" style="height:20px;">
// <nav role="navigation" style="background-color:#26a69a; height:20px"> 
// </nav>
// </div>';
	}elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')) {
		echo '<div class="navbar-fixed" style="height:20px;">
<nav role="navigation" style="background-color:#FFF; height:20px"> 
</nav>
</div>';
	}else {

	}
?>


