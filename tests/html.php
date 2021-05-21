<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';

array_push(
	$breadcrumbs_navigation,
	[ 'Settings', WEB_PATH . '/tests' ],
	[ 'Tests', WEB_PATH . '/tests' ],
);

//debug( $_GET );
//debug( $_POST );
//debug( $_FILES );
//debug( $_COOKIE );
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
	.active {
		border: #000088 1px solid;
	}
</style>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">
	<ul id="testList">
		<li>Item 1</li>
		<li>Item 2</li>
		<li>Item 3</li>
		<li>Item 4</li>
	</ul>
</main>

<?php require 'html-footer.php'; ?>

<script>
	let listItems = document.getElementById("testList").getElementsByTagName("li");
	let activeIndex = null;
	let activeElement = null;

	document.addEventListener( 'keyup', (event) => {
		let key = event.key;

		switch ( key ) {
			case 'ArrowRight':
				if ( activeElement ) {
					activeElement.classList.remove('active');
				}

				if ( activeIndex === null || activeIndex >= (listItems.length - 1) ) {
					activeIndex = 0;
				}
				else ++activeIndex;

				activeElement = listItems.item(activeIndex);
				activeElement.classList.add('active');
				break;
			case 'ArrowLeft':
				if ( activeElement ) {
					activeElement.classList.remove('active');
				}

				if ( activeIndex === null || activeIndex <= 0 ) {
					activeIndex = listItems.length - 1;
				}
				else --activeIndex;

				activeElement = listItems.item(activeIndex);
				activeElement.classList.add('active');
				break;
			case 'Escape':
				if ( activeElement ) {
					activeElement.classList.remove('active');
					activeElement = null;
				}
				break;
		}
		console.log(event);
	} )
</script>

</body>
</html>
