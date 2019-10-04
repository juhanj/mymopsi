<?php ?>
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

	<input type="hidden" name="type" value="new">

	<!-- Cancel & Save -->
	<div class="buttons margins-off">
		<!-- Cancel -->
		<button class="button light"><?= $lang->CANCEL ?></button>
		<!-- Save -->
		<input type="submit" value="<?= $lang->SUBMIT ?>" class="button">
	</div>
</form>

<script>
	let form = document.getElementById( 'create' );
	let password = document.getElementById( 'pw' );
	let pwConfirm = document.getElementById( 'confirm-pw' );
	let error = document.getElementById( 'error' );

	form.onsubmit = (event) => {
		if ( password.value !== pwConfirm.value ) {
			event.preventDefault();
			error.innerHTML = "Password confirmation does not match."
		}
	}
</script>