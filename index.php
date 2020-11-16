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
		<!-- User-login form -->
		<article class="box">
				<h2 class="box-header"><?= $lang->USER_LOGIN ?></h2>

				<form action="login-handler.php" method="post">
					<!-- Username input field -->
					<label class="compact">
						<span class="label required"><?= $lang->USERNAME_LABEL ?></span>
						<input type="text" name="username" minlength="1" maxlength="190"
							placeholder="<?= $lang->USERNAME_PLACEHOLDER ?>" required>
					</label>

					<!-- Password input field -->
					<label class="compact">
						<span class="label required"><?= $lang->PASSWORD_LABEL ?></span>
						<input type="password" name="password" minlength="1" maxlength="300"
							placeholder="<?= $lang->PASSWORD_PLACEHOLDER ?>" required>
					</label>

					<!-- Hidden fields for server-side processing -->
					<input type="hidden" name="class" value="user">
					<input type="hidden" name="request" value="unified_login">

					<!-- Submit button, not input because <input> does not allow images inside it -->
					<button type="submit" class="button" style="width:100%;">
						<?= $lang->LOGIN_SUBMIT ?>
						<?= file_get_contents('./img/log-in.svg') ?>
					</button>
				</form>
		</article>

		<article class="box">
			<!-- Link to new user creation page -->
			<h2 class="box-header"><?= $lang->CREATE_USER_HEADER ?></h2>
			<a href="create-user.php" class="button">
				<?= $lang->CREATE_NEW_USER_LINK ?>
				<?= file_get_contents('./img/user-plus.svg') ?>
			</a>
		</article>

	<?php else : ?>

		<!-- Create new collection -->
		<article class="box">
			<h2><?= $lang->NEW_COLLECTION ?></h2>
			<a href="create-collection.php" class="button">
				<?= $lang->NEW_COLLECTION ?>
			</a>
		</article>

	<?php endif; ?>

	<!-- Lists of collections -->
	<article class="box">
		<?php if ( $user ) : ?>
			<!-- Link of user's own collections -->
			<section class="" id="my-collections">
				<h2><?= $lang->USER_COLLECTIONS ?></h2>
				<a href="./collections.php?user=<?= $user->random_uid ?>" class="button">
					<?= $lang->VIEW_USER_COLLECTIONS ?>
				</a>
			</section>
			<hr>
		<?php endif; ?>

		<!-- Link to public collections -->
		<section class="" id="public-collections">
			<h2><?= $lang->PUBLIC_COLLECTIONS ?></h2>
			<?php if ( $are_there_any_public_colls ) : ?>
				<a href="./collections.php?public" class="button"><?= $lang->VIEW_PUBLIC_COLLECTIONS ?></a>
			<?php else : ?>
				<!-- If no public collections found, print note, and if user logged in link to create new -->
				<p>
					<?= $lang->NO_PUBLIC_COLL_AVAILABLE ?>
				</p>
			<?php endif; ?>
		</section>
	</article>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
