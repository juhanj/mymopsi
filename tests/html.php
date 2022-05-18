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
$file_string = file_get_contents( "./temp/object.json" );
$metadata = json_decode( $file_string );
$json_string = json_encode( $metadata );
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
	dl {
		display: flex;
		flex-flow: row wrap;
		border: solid #666;
		border-width: 1px 1px 0 0;
	}
	dt {
		flex-basis: 20%;
		padding: 2px 4px;
		background: #666;
		text-align: right;
		color: #fff;
		margin-top: ;
	}
	dd {
		flex-basis: 70%;
		flex-grow: 1;
		padding: 2px 4px;
		border-bottom: 1px solid #666;
	}
</style>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">
	<div id="container">
		<?php foreach ( $metadata as $title => $section ) : ?>
		<h2><?= $title ?>:</h2>

		<dl class="margins-off">
			<?php foreach ( $section as $dt => $dd ) : ?>
			<dt><?= $dt ?></dt>
			<dd><?= $dd ?></dd>
			<?php endforeach; ?>
		</dl>
		<?php endforeach; ?>
	</div>
</main>

<?php require 'html-footer.php'; ?>

<script>
	let metadata = JSON.parse( '<?= $json_string ?>' );
</script>

</body>
</html>
