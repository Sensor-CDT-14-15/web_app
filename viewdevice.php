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

function create_chart($sql_row) {
?>
			$('#<? echo $sql_row['device'] . "-" . $sql_row['name']; ?>').highcharts("StockChart", {
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
		$.getJSON('http://109.237.25.161/particle/measurements?device=<?=$sql_row['device']?>&measurement=<?=$sql_row['name']?>', function(data) {
			var chart = $('#<? echo $sql_row['device'] . "-" . $sql_row['name']; ?>').highcharts();
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

$conn->close();
?>
				</div>
			</div>
		</div>
	</body>
</html>
