<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

if ( !empty( $_POST ) ) {
	$controller = new UserController();
	$controller->handleRequest( $db, $user, $_POST );

	if ( $controller->result['success'] ) {
		$_SESSION['feedback'] = "<p class='success'>{$lang->NEW_USER_CREATED}</p>";
		header( "Location: ./index.php" );
		exit();
	}
	elseif ( $controller->result['error'] ) {
		switch ( $controller->result['err'] ) {
			case -2:
				$_SESSION['feedback'] = "<p class='error'>{$lang->USERNAME_NOT_AVAILABLE}</p>";
				break;
			case -1:
				$_SESSION['feedback'] = "<p class='error'>{$lang->STRING_LEN_ERROR}</p>";
				break;
			default:
				$_SESSION['feedback'] = "<p class='error'>Error, unkown error</p>";
		}
	}
}

$feedback = Utils::checkFeedbackAndPOST();
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<!-- Feedback from the server goes here. Any possible prints, successes, failures that the server does. -->
<div class="feedback compact" id="feedback"><?= $feedback ?></div>

<main class="main-body-container">

    <!-- Form - with username & password & email & cancel & save -->
    <form class="box" id="create" method="post">
    	<h2 class="box-header">
    		<?= $lang->NEW_USER_HEADER ?>
    	</h2>

    	<!-- Username -->
    	<label class="compact">
    		<span class="label required"><?= $lang->USERNAME ?></span>
    		<input type="text" name="username" required maxlength="190" minlength="1">
    	</label>

    	<!-- Password -->
    	<label class="compact">
    		<span class="label required"><?= $lang->PASSWORD ?></span>
    		<input type="password" name="password" required minlength="8" maxlength="300" id="pw">
    	</label>

    	<!-- Confirm password -->
    	<label class="compact">
    		<span class="label required"><?= $lang->CONFIRM_PASSWORD ?></span>
    		<input type="password" name="password-confirm" required minlength="8" maxlength="300" id="confirm-pw">
    	</label>

    	<p id="error"></p>

    	<!-- Required input explanation -->
    	<p class="required-input side-note">
    		<span class="required"></span> = <?= $lang->REQUIRED_INPUT ?>
    	</p>

    	<input type="hidden" name="request" value="new">

    	<!-- Cancel & Save -->
    	<div class="buttons margins-off">
    		<!-- Cancel -->
    		<button class="button light"><?= $lang->CANCEL ?></button>
    		<!-- Save -->
    		<input type="submit" value="<?= $lang->SUBMIT ?>" class="button">
    	</div>
    </form>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
