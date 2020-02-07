<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

$feedback = Utils::checkFeedbackAndPOST();

// For now, we only care if there are any public collections.
$are_there_any_public_colls = $db->query( "select 1 from mymopsi_collection where public = true limit 1" );

/**
 * @var Collection[] $collections
 */
$collections = [];

if ( $user and $user->admin and !empty($_GET['user']) ) {
	$temp_user = User::fetchUserByRUID( $db, $_GET['user'] );

	$temp_user->getCollections( $db );

	$collections = $temp_user->collections;
}
elseif ( $user and !isset($_GET['public']) ) {
	$user->getCollections( $db );
	$collections = $user->collections;
}
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

<main class="main-body-container">

	<section>
		<h2><?= $lang->USER ?>: <?= $user->username ?? $lang->USER_PUBLIC ?></h2>
	</section>

	<article>
		<ol class="collections-list margins-off">
			<li class="collection box">
				<a href="create-collection.php" class="collection-link">
					<?= $lang->NEW_COLLECTION ?>
					<?= file_get_contents('./img/folder-plus.svg') ?>
				</a>
			</li>
			<?php foreach ( $collections as $c ) : ?>
				<li class="collection box" data-id="<?= $c->random_uid ?>">
					<a href="./collection.php?id=<?= $c->random_uid ?>" class="collection-link">
						<h3><?= $c->name ?: substr($c->random_uid, 0, 5) ?></h3>
						<?php if ( $c->description ) : ?>
							<p class="description"><?= $c->description ?></p>
						<?php endif; ?>
						<p><?= $lang->NRO_OF_IMGS ?>: <?= $c->number_of_images ?></p>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>
	</article>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>

