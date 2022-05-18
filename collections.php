<?php declare(strict_types=1);
require './components/_start.php';
/**
 * @var DBConnection $db
 * @var Language     $lang
 * @var User         $user
 */

$feedback = Common::checkFeedbackAndPOST();

// For now, we only care if there are any public collections.
$public_colls_nro = $db->query(
	"select count(id) as count from mymopsi_collection where public = true",
	[],
	false
)->count;
$all_colls_nro = $db->query(
	"select count(id) as count from mymopsi_collection",
	[],
	false
)->count;

$user_id = $_GET[ 'user' ] ?? null;

/**
 * @var Collection[] $collections
 */
$collections = [];

if ( !$user or $user_id === 'public' ) {
	$collections = Collection::fetchPublicCollections( $db );
}
else {
	if ( $user->admin and $user_id === 'all' ) {
		$collections = Collection::fetchAllCollections( $db );
	}
	else if ( $user->admin and !empty($user_id) ) {
		$temp_user = User::fetchUserByRUID( $db, $user_id );
		$temp_user->getCollections($db);
		$collections = $temp_user->collections;
	}
	else {
		$user->getCollections( $db );
		$collections = $user->collections;
	}
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

	<form>
		<label>
			<select name="user" id="userSelect" onchange="this.form.submit()">
				<?php if ($user) : ?>
					<option value="">You (<?= $user->number_of_collections ?>)</option>
				<?php endif; ?>
				<option value="public" <?= $user_id !== 'public' ?: 'selected'?> <?= $public_colls_nro ?: 'disabled' ?>>
					<?= $lang->PUBLIC ?> (<?= $public_colls_nro ?>)
				</option>
				<option value="all" <?= ($user != null and $user->admin) ?: 'disabled' ?>>
					<?= $lang->ALL ?> (<?= $all_colls_nro ?>)
				</option>
			</select>
		</label>
	</form>

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
						<h3 class="name margins-off">
							<span><?= $c->name ?: substr( $c->random_uid, 0, 4 ) ?></span>
						</h3>
						<p class="description"><?= $c->description ?? '' ?></p>
						<img
							class="image"
							src="./img/img.php?collection=<?= $c->random_uid ?>&random&thumb"
							onerror="this.onerror=null;this.src='';">
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
	let userName = "<?= ($user) ? $user->username : $lang->HTML_TITLE ?>";
	let userRUID = "<?= ($user) ? $user->random_uid : null ?>";
</script>

</body>
</html>
