<?
$servername = "localhost";
$username = "particle";
$password = "particle";
$dbname = "particle";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM analytes WHERE room='" . $_GET['room'] ."'";
$result = $conn->query($sql);

function get_nice_device_name($device_id, $conn) {
	$sql = "SELECT * FROM devices WHERE device_id='" . $device_id ."'";
	$result = $conn->query($sql);
	$row = $result -> fetch_assoc();
	$nice_name = $row['name'];
	return $nice_name;
}

function get_nice_room_name($room, $conn) {
	$sql = "SELECT * FROM rooms WHERE room='" . $room ."'";
	$result = $conn->query($sql);
	$row = $result -> fetch_assoc();
	$nice_name = $row['friendly_name'];
	return $nice_name;
}

function create_chart($sql_row) {
?>
			$('#<? echo $sql_row['room'] . "-" . $sql_row['name']; ?>').highcharts("StockChart", {
				chart: { type: 'scatter', zoomType: 'xy'},
				xAxis: { type: 'datetime' },
				yAxis: { title: { text: '<? echo ($sql_row['units'] != "") ? (($sql_row['friendly_name'] != "") ? $sql_row['friendly_name'] : $sql_row['name']) . " / " . $sql_row['units'] : $sql_row['friendly_name'] ?>' } },
				series: [{ name: 'Testing' }, { name: 'Stable' }],
				title: { text: '<? echo ($sql_row['friendly_name'] != "") ? $sql_row['friendly_name'] : $sql_row['name'] ?>' }
			});
<?
}

function get_json($sql_row) {
?>
		$.getJSON('http://109.237.25.161/particle/analyses?room=<?=$sql_row['room']?>&name=<?=$sql_row['name']?>', function(data) {
			var chart = $('#<? echo $sql_row['room'] . "-" . $sql_row['name']; ?>').highcharts();
			$.each(data.measurements, function(key, val) {
				obj = val;
				chart.series[0].addPoint([Date.parse(obj.timestamp), parseFloat(obj.value)], false);
			});
			chart.redraw();
		});
<?
}
?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>View device data</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<link href="css/bootstrap.css" rel="stylesheet">
		<link href="css/bootstrap-theme.css" rel="stylesheet">

		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

		<script type="text/javascript">
		$(document).ready(function() {
<?
		$result = $conn->query($sql);
		while ($row = $result -> fetch_assoc()) {
			create_chart($row);
		}
?>
		});

<?
$result = $conn->query($sql);
while ($row = $result -> fetch_assoc()) {
		get_json($row);
}
?>
		</script>
	</head>

	<body>
		<script src="js/highstock.js"></script>
		<script src="js/modules/exporting.js"></script>

		<div class="container-narrow">
			<div class="content">

				<nav class="navbar navbar-default">
					<div class="container-fluid">
						<!-- Brand and toggle get grouped for better mobile display -->
						<div class="navbar-header">
							<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
							<a class="navbar-brand" href="#">Seguimi</a>
						</div>

						<!-- Collect the nav links, forms, and other content for toggling -->
						<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
							<ul class="nav navbar-nav">
								<li><a href="about.php">About</a></li>
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">View device data<span class="caret"></span></a>
									<ul class="dropdown-menu">
										<?
										$device_menu_sql = "SELECT * FROM devices";
										$device_menu_result = $conn->query($device_menu_sql);
										while ($row = $device_menu_result -> fetch_assoc()) {
											?>
											<li><a href="viewdevice.php?device=<?=$row['device_id']?>"><? echo(get_nice_device_name($row['device_id'], $conn)) ?></a></li>
											<?
										}
										?>
									</ul>
								</li>
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">View analyses<span class="caret"></span></a>
									<ul class="dropdown-menu">
										<?
										$analyses_menu_sql = "SELECT * FROM analytes";
										$analyses_menu_result = $conn->query($analyses_menu_sql);
										while ($row = $analyses_menu_result -> fetch_assoc()) {
											?>
											<li><a href="viewanalysis.php?room=<?=$row['room']?>"><?echo get_nice_room_name($row['room'], $conn);?></a></li>
											<?
										}
										?>
									</ul>
								</li>
							</ul>
							<ul class="nav navbar-nav navbar-right">
								<li><button class="btn btn-primary navbar-btn btn-success btn-disabled" type="button" disabled>All good!</button></li>
							</ul>
						</div><!-- /.navbar-collapse -->
					</div><!-- /.container-fluid -->
				</nav>

				<div class="page-header">
					<h1><?echo get_nice_room_name($_GET['room'], $conn);?></h1>
				</div>

				<div class="row">
<?
$result = $conn->query($sql);
while ($row = $result -> fetch_assoc()) {
?>
					<div class="col-md-6">
						<div id="<? echo $row['room'] . "-" . $row['name']; ?>" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
					</div>
<?
}

$conn->close();
?>
				</div>
			</div>
		</div>
	</body>
</html>
