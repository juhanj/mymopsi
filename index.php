<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 */

$feedback = check_feedback_POST();
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">

	<!-- Feedback from the server goes here. Any possible prints, successes, failures that the server does. -->
	<div class="feedback" id="feedback"><?= $feedback ?></div>

	<h1 class="title">MyMopsi</h1>

	<div class="box">
		<h2><?= $lang->SEARCH_COLL ?></h2>
		<form action="./view.php" method="get">
			<input type="text" name="id" placeholder="<?= $lang->COLL_PLACEHOLDER ?>" class="text">
			<input type="submit" value="<?= $lang->COLL_SUBMIT ?>" class="submit">
		</form>
    </div>

	<div class="box">
		<h2><?= $lang->NEW_COLLECTION ?></h2>
		<button id="open-modal-new-collection"><?= $lang->NEW_COLL_MODAL ?></button>

		<dialog id="modal-new-collection">
			<header>
				<h1><?= $lang->MODAL_HEADER ?></h1>
				<button id="close-modal-new-collection">❌</button>
			</header>

			<form id="new-collection-form" method="post">
				<label>
					<?= $lang->GIVE_NAME ?><span class="required"></span>
					<input type="text" name="name" value="" placeholder="<?= $lang->GIVE_NAME_PLACEHOLDER ?>" required>
				</label>

				<label>
					<?= $lang->GIVE_EMAIL ?>
					<input type="email" name="email" value="" placeholder="<?= $lang->GIVE_EMAIL_PLACEHOLDER ?>">
				</label>

				<span class="small_note"><span class="required"></span> = required field</span>
				<input type="hidden" name="request" value="createNewCollection">
				<input type="submit" value="<?= $lang->CREATE_NEW ?>">
			</form>

			<footer>
				<?= $lang->MODAL_FOOTER ?>
			</footer>
		</dialog>
	</div>

	<div class="box">
		<h2><?= $lang->ADMIN_LOGIN ?></h2>
		<form>
			<!-- Hidden field for admin username because Chrome wants one. Something about accessiblity. -->
	        <input type="text" placeholder="admin" value="admin" autocomplete="username" hidden aria-hidden="true">
			<input type="password" placeholder="<?= $lang->ADMIN_PW_PLACEHOLDER ?>" autocomplete="current-password" class="text">
			<input type="submit" value="<?= $lang->ADMIN_SUBMIT ?>" class="submit">
		</form>
	</div>

	<div class="box">
		<div class="" id="my-collections">
			<!-- List of user's own collections -->
		</div>

		<div class="" id="local-collections">
			<!-- List of local collections -->
		</div>

		<div class="" id="public-collections">
			<!-- List of public collections -->
		</div>
	</div>

	<div class="box">
		<a href="https://github.com/juhanj/mymopsi">Github page</a>
		<a href="./tests/">Tests</a>
	</div>
</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
