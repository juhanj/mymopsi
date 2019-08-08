<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 * @var $lang
 * @var $user
 */

/**
 * Get public collections from the database, with name, UID, and number of images.
 * @param \DBConnection $db
 * @return Collection[]
 */
function get_public_collections ( DBConnection $db ) {
	$sql = "select c.name, c.random_uid, count(i.id) as number_of_images
			from mymopsi_collection c
			left join mymopsi_img i on c.id = i.collection_id
			where public = true
			group by c.name";
	return $db->query( $sql, [], FETCH_ALL, 'Collection' );
}

$feedback = check_feedback_POST();

$public_colls = get_public_collections( $db );

if ( $user ) {
	$user->getCollections( $db );
}
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<!-- Feedback from the server goes here. Any possible prints, successes, failures that the server does. -->
<div class="feedback compact" id="feedback"><?= $feedback ?></div>

<main class="main-body-container">

	<h1 class="title">MyMopsi</h1>

	<?php if ( !$user ) : ?>
	<div class="box">
		<h2><?= $lang->USER_LOGIN ?></h2>
		<form action="./login_check.php" method="post">
			<label>
				<span class="label"><?= $lang->LOGIN_NAME ?></span>
				<input type="text" name="user">
			</label>
			<label>
				<span class="label"><?= $lang->LOGIN_PASSWORD ?></span>
				<input type="password" name="password">
			</label>
			<input type="submit" value="<?= $lang->USER_SUBMIT ?>">
		</form>
		<hr>
		<h2><?= $lang->CREATE_USER_HEADER ?></h2>
		<a href="./edit_user.php?new" class="button"><?= $lang->CREATE_NEW_USER_LINK ?></a>
    </div>

	<?php else : ?>

	<div class="box">
		<a href="./edit_collection.php?new"><?= $lang->NEW_COLLECTION ?></a>
	</div>
	<?php endif; ?>

	<div class="box">

		<?php if ( $user ) : ?>
			<div class="" id="my-collections">
				<!-- List of user's own collections -->
				<h2><?= $lang->USER_COLLECTIONS ?></h2>
				<ul>
					<?php foreach ( $user->collections as $collection ) : ?>
						<li><a href="./view.php?id=<?= $collection->random_uid ?>"><?= $collection->name ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<hr>
		<?php endif; ?>

		<div class="" id="local-collections">
			<h2><?= $lang->LOCAL_COLLECTIONS ?></h2>
			<!-- List of local collections -->
		</div>
		<hr>

		<div class="" id="public-collections">
			<h2><?= $lang->PUBLIC_COLLECTIONS ?></h2>
			<ul>
				<?php foreach ( $public_colls as $collection ) : ?>
					<li><a href="./view.php?id=<?= $collection->random_uid ?>"><?= $collection->name ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<div class="box links">
		<ul>
			<li>
				<a href="https://github.com/juhanj/mymopsi">Github page</a>
			</li>
			<li>
				<a href="./tests/">Tests</a>
			</li>
		</ul>
	</div>
</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
