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

	<div class="buttons compact">
		<a href="index.php" class="button">
			<?= $lang->BACK ?>
		</a>

		<a href="edit-collection.php" class="button">
			<?= $lang->EDIT_COLL ?>
		</a>

		<a href="upload.php?id=<?= $collection->random_uid ?>" class="button margins-off">
			<?= $lang->ADD_IMG ?>
		</a>

		<a href="map.php?cid=<?= $collection->random_uid ?>" class="button margins-off">
			<?= $lang->TO_MAP ?>
		</a>
	</div>

	<ul class="image-list">
		<?php foreach ( $collection->images as $img ) : ?>
			<li class="image box">
				<a href="./image.php?id=<?= $img->random_uid ?>">
					<img src="./img/img.php?id=<?= $img->random_uid ?>&thumb"
					     class="img" alt="<?= $img->name ?>">
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
