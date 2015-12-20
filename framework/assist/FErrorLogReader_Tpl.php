<!DOCTYPE html>
<!-- error Log 显示模板 -->
<html>
	<head>
		<title>Error logs</title>
		<style>
			body {font-family: 'Microsoft Yahei', STHeiti, SimSun, Arail, Verdana, Helvetica, sans-serif; font-size:12px;}
			input {width:250px;height:30px;}
			button {width:100px;height:30px;}
			table{width:100%;}
			table, th, tr, td {border: solid #1E569E; border-width: 1px; border-collapse: collapse; font-size:12px; word-break:break-all;}
			th {background-color:#3576CC;padding:8px;color:#fff;}
			td {padding:8px;}
			tr {background-color:#fff;}
			tr:nth-child(2n+1) {background:#EBF1FA;}
		</style>
	</head>
	<body>
		<table>
			<tr>
				<th>Host</th>
				<th>Path</th>
				<th>File</th>
				<th>Line</th>
				<th>Error</th>
			</tr>
			<?php foreach($this->result as $r) {?>
			<tr>
				<td><?php echo $r['host']?></td>
				<td><?php echo htmlspecialchars($r['path'])?></td>
				<td><?php echo $r['file']?></td>
				<td><?php echo $r['line']?></td>
				<td><?php echo $r['error']?></td>
			</tr>
			<?php }?>
		</table>
	</body>
</html>