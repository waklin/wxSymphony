<!DOCTYPE html>
<?php
	require_once("global.php");
	require_once(DBACCESS_MODULE_PATH . "include.php");
?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>wxSymphony</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="http://cdn.bootcss.com/twitter-bootstrap/3.0.3/css/bootstrap.min.css">
  </head>
  <body>
    <h1>Hello, world!</h1>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="http://cdn.bootcss.com/jquery/1.10.2/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="http://cdn.bootcss.com/twitter-bootstrap/3.0.3/js/bootstrap.min.js"></script>

	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<form role="form">
					<div class="form-group">
						<input type="text" class="form-control" id="nickName" placeholder="type your nick name">
					</div>
					<div class="form-group">
						<select id="city" class="form-control">
							<?php
								$db = new DBAccess();
								$sql = "select id,name from city where id > 0";
								$result = $db->execSql($sql);
								while ($row = $result->fetch_assoc()) {
									$opt = sprintf('<option value="%s">%s</option>', $row["id"], $row["name"]);
									echo($opt);
								}
							?>
						</select>
					</div>
					<div>
						<input type="submit" class="btn btn-default" />
					</div>
				</form>
			</div>
		</div>
		<br/>
		<div class="row">
			<div class="col-md-12">
				<table class="table table-striped">
					<tr>
						<td>线路名称</td>
						<td>方向</td>
						<td>回程</td>
					</tr>
					<tr>
						<td>618</td>
						<td>a-b</td>
						<td>-</td>
					</tr>
					<tr>
						<td>618</td>
						<td>a-b</td>
						<td>-</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
  </body>
</html>
