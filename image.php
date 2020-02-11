<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

$feedback = Utils::checkFeedbackAndPOST();

$image = Image::fetchImageByRUID( $db, $_GET['id'] );
if ( !$image ) {
	$_SESSION['feedback'] = "<p class='error'>No Image found with given ID.</p>";
	header( "Location:index.php" );
	exit();
}

$collection = Collection::fetchCollectionByID( $db, $image->collection_id );

if ( ($user->id !== $collection->owner_id) and !$collection->public ) {
	$_SESSION['feedback'] = "<p class='error'>No access to collection.</p>";
	header( "Location:index.php" );
	exit();
}
?>

<!DOCTYPE html>
<html lang="fi">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<div class="feedback" id="feedback"><?= $feedback ?></div>

<main class="main-body-container">

	<form class="box" method="post">
		<label> <span class="label required"><?= $lang->NAME ?></span> <input type="text" name="name" value="<?= $image->name ?>" required> </label>
	</form>

	<form class="box" method="post">
		<label>
			<span class="label required"><?= $lang->DESCRIPTION ?></span>
			<input type="text" name="description" value="<?= $image->description ?>" required>
		</label>
	</form>

	<section class="box">
		<a href="edit-gps.php?id=<?= $image->random_uid ?>" class="button">
			<?= $lang->EDIT_GPS ?>
		</a>
	</section>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
