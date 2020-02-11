<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

$feedback = Utils::checkFeedbackAndPOST();

$collection = Collection::fetchCollectionByRUID( $db, $_GET['id'] );

if ( !$collection ) {
	$_SESSION['feedback'] = "<p class='error'>No collection found with given ID.</p>";
	header( "Location:index.php" );
	exit();
}

$collection->getImages( $db );
?>

<!DOCTYPE html>
<html lang="fi">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<div class="feedback" id="feedback"><?= $feedback ?></div>

<main class="main-body-container">

	<div class="buttons margins-off">
		<a href="upload.php?id=<?= $collection->random_uid ?>" class="button margins-off">
			<?= $lang->ADD_IMG ?>
			<?= file_get_contents('./img/file-plus.svg') ?>
		</a>

		<a href="map.php?cid=<?= $collection->random_uid ?>" class="button margins-off">
			<?= $lang->TO_MAP ?>
			<?= file_get_contents('./img/map.svg') ?>
		</a>
	</div>

	<ul class="image-list">
		<?php foreach ( $collection->images as $img ) : ?>
			<li class="image">
				<a href="./image.php?id=<?= $img->random_uid ?>" class="link">
					<img src="./img/img.php?id=<?= $img->random_uid ?>&thumb"
					     class="img" alt="<?= $img->name ?>">
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

</main>

<?php require 'html-footer.php'; ?>

<script>
	// These are used in page-specific JS-file, for header-link.
	let collectionName = "<?= $collection->name ?? substr($collection->random_uid,0,5) ?>";
	let collectionRUID = "<?= $collection->random_uid ?>";
</script>

</body>
</html>
