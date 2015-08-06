<?
$servername = "localhost";
$username = "particle";
$password = "particle";
$dbname = "particle";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM measurands WHERE device='" . $_GET['device'] ."'";
$result = $conn->query($sql);
// $conn->close();
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

		<script type="text/javascript">
		$(document).ready(function() {
<?
		$result = $conn->query($sql);
		while ($row = $result -> fetch_assoc()) {
?>
			$('#<? echo $row['device'] . "-" . $row['name']; ?>').highcharts("StockChart", {
				chart: { type: 'scatter', zoomType: 'xy'},
				xAxis: { type: 'datetime' },
				yAxis: { title: { text: '<? echo ($row['units'] != "") ? (($row['friendly_name'] != "") ? $row['friendly_name'] : $row['name']) . " / " . $row['units'] : $row['friendly_name'] ?>' } },
				series: [{ name: 'Testing' }, { name: 'Stable' }],
				title: { text: '<? echo ($row['friendly_name'] != "") ? $row['friendly_name'] : $row['name'] ?>' }
			});
<?
}
?>

		});

<?
$result = $conn->query($sql);
while ($row = $result -> fetch_assoc()) {
?>
		$.getJSON('http://109.237.25.161/particle/measurements?device=<?=$row['device']?>&measurement=<?=$row['name']?>', function(data) {
			var chart = $('#<? echo $row['device'] . "-" . $row['name']; ?>').highcharts();
			$.each(data.measurements, function(key, val) {
				obj = val;
				chart.series[0].addPoint([Date.parse(obj.timestamp), parseFloat(obj.value)], false);
			});
			chart.redraw();
		});
<?
}
?>

		</script>
	</head>

	<body>
		<script src="js/highstock.js"></script>
		<script src="js/modules/exporting.js"></script>

		<div class="container-narrow">
			<div class="content">
				<div class="page-header">
					<h1>Device (<? echo($_GET['device']); ?>)</h1>
				</div>

				<div class="row">
<?
$result = $conn->query($sql);
while ($row = $result -> fetch_assoc()) {
?>
					<div class="col-md-6">
						<div id="<? echo $row['device'] . "-" . $row['name']; ?>" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
					</div>
<?
}
?>
				</div>
			</div>
		</div>
	</body>
</html>
