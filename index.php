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

	<div class="menu-head">
		<form action="./view.php" method="get">
			<input type="text" name="id" placeholder="<?= $lang->COLL_PLACEHOLDER ?>" class="text">
			<input type="submit" value="<?= $lang->COLL_SUBMIT ?>" class="submit">
		</form>
    </div>

	<div class="new-collection">
		<button id="open-modal-new-collection"><?= $lang->CREATE_NEW ?></button>

		<dialog id="modal-new-collection">
			<header>
				<h1>MODAL_HEADER</h1>
				<button id="close-modal-new-collection">❌</button>
			</header>

			<form id="new-collection-form">
				<label><?= $lang->GIVE_NAME ?>
					<input type="text" name="name" value="" placeholder="<?= $lang->GIVE_NAME_PLACEHOLDER ?>" required>
				</label>
				<br>
				<label><?= $lang->GIVE_EMAIL ?>
					<input type="email" value="email" placeholder="<?= $lang->DISABLED ?>" disabled>
				</label>
				<br>
				<input type="hidden" name="request" value="createNewCollection">
				<input type="submit" value="<?= $lang->CREATE_NEW ?>">
			</form>

			<footer>
				<?= $lang->MODAL_FOOTER ?>
			</footer>
		</dialog>
	</div>

	<form class="menu-body">
		<!-- Hidden field for admin username because Chrome wants one. Something about accessiblity. -->
        <input type="text" placeholder="admin" value="admin" autocomplete="username" hidden aria-hidden="true">
		<input type="password" placeholder="<?= $lang->ADMIN_PW_PLACEHOLDER ?>" autocomplete="current-password" class="text">
		<input type="submit" value="<?= $lang->ADMIN_SUBMIT ?>" class="submit">
	</form>

	<div class="" id="my-collections">
		<!-- List of user's own collections -->
	</div>

	<div class="" id="local-collections">
		<!-- List of local collections -->
	</div>

	<div class="" id="public-collections">
		<!-- List of public collections -->
	</div>
</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
