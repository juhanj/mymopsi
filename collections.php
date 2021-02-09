<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

$feedback = Common::checkFeedbackAndPOST();

// For now, we only care if there are any public collections.
$are_there_any_public_colls = $db->query( "select 1 from mymopsi_collection where public = true limit 1" );

/**
 * @var Collection[] $collections
 */
$collections = [];

// If use is admin, get whatever is wanted
if ( $user and $user->admin and !empty($_GET['user']) ) {
	$temp_user = User::fetchUserByRUID( $db, $_GET['user'] );

	$temp_user->getCollections( $db );

	$collections = $temp_user->collections;
}
// Logged in user, get own collections
elseif ( $user and !isset($_GET['public']) ) {
	$user->getCollections( $db );
	$collections = $user->collections;
}
// Public collections (if wanted or not if not logged in)
elseif ( isset($_GET['public']) or !$user ) {
	$collections = $db->query(
		"select * from mymopsi_collection where public = true",
		[],
		true,
		'Collection'
	);
}
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<!-- Feedback from the server goes here. Any possible prints, successes, failures that the server does. -->
<div class="feedback compact" id="feedback"><?= $feedback ?></div>

<main class="main-body-container medium-width">

	<section class="buttons">
		<a href="create-collection.php" class="button">
			<?= $lang->NEW_COLLECTION ?>
			<span class="material-icons">create_new_folder</span>
		</a>
	</section>

	<article>
		<ol class="collections-list margins-off">
			<?php foreach ( $collections as $c ) : ?>
				<li class="collection box" data-id="<?= $c->random_uid ?>">
					<a href="./collection.php?id=<?= $c->random_uid ?>" class="collection-link">
						<h3 class="name">
							<?= $c->name ?: substr($c->random_uid, 0, 5) ?> &mdash;
							<span><span class="material-icons">photo_library</span> <?= $c->number_of_images ?></span>
						</h3>
						<p class="description"><?= $c->description ?? '' ?></p>
						<img class="image" src="./img/img.php?collection=<?= $c->random_uid ?>&random&thumb">
						<span class="count"><?= $c->number_of_images ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>

		<?php if ( !$collections ) : ?>
		<p><?= $lang->NO_COLLECTIONS ?></p>
		<?php endif; ?>
	</article>

</main>

<?php require 'html-footer.php'; ?>

<script>
	// These are used in page-specific JS-file, for header-link.
	let userName = "<?= $user->username ?>";
	let userRUID = "<?= $user->random_uid ?>";
</script>

</body>
</html>
