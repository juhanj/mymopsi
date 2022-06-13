<?php declare(strict_types=1);
require './components/_start.php';
/**
 * @var DBConnection $db
 * @var Language     $lang
 * @var User         $user
 */

if ( $user ) {
	header( "Location: ./collections.php" );
	exit();
}

$feedback = Common::checkFeedbackAndPOST();

// For now, we only care if there are any public collections.
$are_there_any_public_colls = $db->query( "select 1 from mymopsi_collection where public = true limit 1" );
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<!-- Feedback from the server goes here. Any possible prints, successes, failures that the server does. -->
<div class="feedback compact" id="feedback"><?= $feedback ?></div>

<main class="main-body-container">

	<h1 class="title">
		My
		<img src="./img/mopsi128.png" alt="MyMopsi logo" class="fancy-title-img">
		Mopsi
	</h1>

	<!-- User-login form -->
	<article class="box">
		<h2 class="box-header" hidden><?= $lang->USER_LOGIN ?></h2>

		<form action="login-handler.php" method="post">
			<!-- Username input field -->
			<label class="compact">
				<span class="label"><?= $lang->USERNAME_LABEL ?></span>
				<input type="text" name="username" minlength="1" maxlength="190"
				       placeholder="<?= $lang->USERNAME_PLACEHOLDER ?>" required>
			</label>

			<!-- Password input field -->
			<label class="compact">
				<span class="label"><?= $lang->PASSWORD_LABEL ?></span>
				<input type="password" name="password" minlength="1" maxlength="300"
				       placeholder="<?= $lang->PASSWORD_PLACEHOLDER ?>" required>
			</label>

			<!-- Hidden fields for server-side processing -->
			<input type="hidden" name="class" value="user">
			<input type="hidden" name="request" value="unified_login">

			<!-- Submit button, not input because <input> does not allow images inside it -->
			<button type="submit" class="button" style="width:100%;">
				<?= $lang->LOGIN_SUBMIT ?>
				<span class="material-icons">login</span>
			</button>
		</form>

		<hr>

		<p style="center">
			<a href="create-user.php" class="center margins-off">
				<span>
					<?= $lang->CREATE_NEW_USER_LINK ?>
					<span class="material-icons">person_add</span>
				</span>
			</a>
		</p>
	</article>

	<!-- Link to public collections -->
	<article class="box" id="public-collections">
		<h2 class="box-header"><?= $lang->PUBLIC_COLLECTIONS_HEADER ?></h2>
		<?php if ( $are_there_any_public_colls ) : ?>
			<a href="./collections.php?public" class="button"><?= $lang->VIEW_PUBLIC_COLLECTIONS ?></a>
		<?php else : ?>
			<!-- If no public collections found, print note, and if user logged in link to create new -->
			<p>
				<?= $lang->NO_PUBLIC_COLL_AVAILABLE ?>
			</p>
		<?php endif; ?>
	</article>

	<!-- Link to public collections -->
	<article class="box" id="view-single-metadata">
		<h2 class="box-header"><?= $lang->SINGLE_METADATA_HEADER ?></h2>
		<a href="./metadata-reader.php" class="button"><?= $lang->VIEW_SINGLE_METADATA ?></a>
	</article>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
