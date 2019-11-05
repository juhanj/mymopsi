<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

$feedback = check_feedback_POST();

// For now, we only care if there are any public collections.
$are_there_any_public_colls = $db->query( "select 1 from mymopsi_collection where public = true limit 1" );

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

	<section>
		<h2>User: <?= $user->username ?></h2>
	</section>

	<article>
		<ol class="collections-list margins-off">
			<li class="collection box">
				<a href="edit-collection.php?new" class="collection-link">
					<?= $lang->NEW_COLLECTION ?>
					<i class="material-icons">add</i>
				</a>
			</li>
			<?php foreach ( $user->collections as $c ) : ?>
				<li class="collection box" data-id="<?= $c->random_uid ?>">
					<a href="./collection.php?id=<?= $c->random_uid ?>" class="collection-link">
						<h3><?= $c->name ?: substr($c->random_uid, 0, 2) ?></h3>
						<?php if ( $c->description ) : ?>
							<p class="description"><?= $c->description ?></p>
						<?php endif; ?>
						<p>Number of images: <?= $c->number_of_images ?></p>
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

