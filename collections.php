<?php declare(strict_types=1);
require './components/_start.php';
/**
 * @var DBConnection $db
 * @var Language     $lang
 * @var User         $user
 */

$feedback = Common::checkFeedbackAndPOST();

// Number of public collections
$public_colls_nro = $db->query(
	"select count(id) as count from mymopsi_collection where public = true",
	[],
	false
)->count;
// Number of all collections (selectable by admin)
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
	foreach ( $collections as $c ) {
		$c->getOwner( $db );
	}
}
else {
	if ( $user->admin and $user_id === 'all' ) {
		$collections = Collection::fetchAllCollections( $db );
		foreach ( $collections as $c ) {
			$c->getOwner( $db );
		}
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

if ($user and $user->admin) {
	$users = User::fetchAllUsers( $db );
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

	<!-- Dropdown selector for own, public, or all collections (admin only) -->
	<form>
		<label>
			<select name="user" id="userSelect" onchange="this.form.submit()">
				<?php if ($user) : ?>
					<option value="">You (<?= $user->number_of_collections ?>)</option>
				<?php endif; ?>
				<option value="public" <?= $user_id !== 'public' ?: 'selected'?>
					<?= $public_colls_nro ?: 'disabled' ?>
				>
					<?= $lang->PUBLIC ?> (<?= $public_colls_nro ?>)
				</option>
				<option value="all"	<?= $user_id !== 'all' ?: 'selected'?>
					<?= ($user != null and $user->admin) ?: 'disabled' ?>
				>
					<?= $lang->ALL ?> (<?= $all_colls_nro ?>)
				</option>
				<?php if ( $user->admin ) : ?>
				<hr>
				<option disabled>----</option>
				<?php foreach ( $users as $u ) : ?>
					<option value="<?= $u->random_uid ?>" <?= $user_id !== $u->random_uid ?: 'selected'?>>
						<?= $u->username ?> (<?= $u->number_of_collections ?>)
					</option>
				<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</label>
	</form>

	<?php if ( $user ) : ?>
	<!-- Create a new collection (link to new page) -->
	<section class="buttons">
		<a href="create-collection.php" class="button">
			<?= $lang->NEW_COLLECTION ?>
			<span class="material-icons">create_new_folder</span>
		</a>
	</section>
	<?php endif; ?>

	<!-- Collections list -->
	<article>
		<ol class="collections-list margins-off">
			<?php foreach ( $collections as $c ) : ?>
				<li class="collection box" data-id="<?= $c->random_uid ?>">
					<a href="./collection.php?id=<?= $c->random_uid ?>" class="collection-link">
						<h3 class="name margins-off">
							<span><?= $c->name ?: substr( $c->random_uid, 0, 4 ) ?></span>
							<?php if ( $user_id == 'public' or $user_id == 'all' ) : ?>
							<span>(<?= $c->owner->username ?>)</span>
							<?php endif; ?>
						</h3>
						<p class="description"><?= $c->description ?? '' ?></p>
						<img
							class="image"
							src="./img/img.php?collection=<?= $c->random_uid ?>&thumb"
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
