<?php declare(strict_types=1);
require '../components/_start.php';

array_push(
	$breadcrumbs_navigation,
	[ 'Settings', WEB_PATH . 'tests' ],
	[ 'Tests', WEB_PATH . 'tests' ],
);

//debug( $_GET );
//debug( $_POST );
//debug( $_FILES );
//debug( $_COOKIE );

$image = new Image();
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
	#container {
		width: 10rem;
		height: 10rem;
		border: 1px solid black;
		display: flex;
		flex-direction: column;
	}

	#elm1, #elm2 {
		pointer-events: none;
	}
</style>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">
	<div id="container">
	</div>
</main>

<?php require 'html-footer.php'; ?>

<script>
	const HOUR = 60;
	const MIN = 60;
	let container = document.getElementById("container");

	container.addEventListener( 'click', (event) => {
	})

	console.log(
		formatGPSDecimalToDMS({lat:62.243424,lng:29.387623784})
	);

	function formatGPSDecimalToDMS ( location ) {
		let latitude = toDegreesMinutesAndSeconds( location.lat );
		let latitudeCardinal = location.lat >= 0 ? "N" : "S";

		let longitude = toDegreesMinutesAndSeconds( location.lng );
		let longitudeCardinal = location.lng >= 0 ? "E" : "W";

		return latitude + " " + latitudeCardinal + ", " + longitude + " " + longitudeCardinal;

		function toDegreesMinutesAndSeconds( coordinate ) {
			let absolute = Math.abs(coordinate);
			let degrees = Math.floor(absolute);
			let minutesNotTruncated = (absolute - degrees) * HOUR;
			let minutes = Math.floor(minutesNotTruncated);
			let seconds = Math.floor((minutesNotTruncated - minutes) * MIN);

			return degrees + "° " + minutes + "′" + seconds + "″";
		}
	}

	function fGPSDecimalToDMS ( location ) {
		let latitude = toDegreesMinutesAndSeconds( location.lat );
		let latitudeCardinal = location.lat >= 0 ? "N" : "S";

		let longitude = toDegreesMinutesAndSeconds( location.lng );
		let longitudeCardinal = location.lng >= 0 ? "E" : "W";

		return latitude + NBSP + latitudeCardinal + ", " + longitude + NBSP + longitudeCardinal;

		function toDegreesMinutesAndSeconds ( coordinate ) {
			let absolute = Math.abs( coordinate );
			let degrees = Math.floor( absolute );
			let minutesNotTruncated = (absolute - degrees) * HOUR;
			let minutes = Math.floor( minutesNotTruncated );
			let seconds = Math.floor( (minutesNotTruncated - minutes) * MIN );

			return degrees + "°" + NBSP + minutes + "′" + seconds + "″";
		}
	}


</script>

</body>
</html>
