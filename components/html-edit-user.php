<?php ?>

<!-- Form - editing username -->
<form class="box">
	<h2 class="box-header">
		<?php $lang->EDIT_USER_HEADER ?>
	</h2>

	<!-- Username -->
	<label class="compact">
		<span class="label required"><?= $lang->USERNAME ?></span>
		<input type="text" name="name" required maxlength="190" minlength="1">
	</label>
	<!-- Save -->
	<input type="submit" value="<?= $lang->SUBMIT ?>" class="button">
</form>

<!-- Form - password -->
<form class="box" id="password-edit">
	<!-- Password -->
	<label class="compact">
		<span class="label required"><?= $lang->PASSWORD ?></span>
		<input type="password" name="password" required minlength="8" maxlength="300" id="pw">
	</label>

	<!-- Confirm password -->
	<label class="compact">
		<span class="label required"><?= $lang->CONFIRM_PASSWORD ?></span>
		<input type="password" name="password" required minlength="8" maxlength="300" id="confirm-pw">
	</label>

	<p id="error"></p>

	<!-- Save -->
	<input type="submit" value="<?= $lang->SUBMIT ?>" class="button">
</form>

<!-- Form - email -->
<form class="box">
	<!-- Email (this might move) -->
	<label class="compact">
		<span class="label"><?= $lang->EMAIL ?></span>
		<input type="email" name="email" maxlength="190" minlength="1">
	</label>
	<!-- Save -->
	<input type="submit" value="<?= $lang->SUBMIT ?>" class="button">
</form>

<article class="box">
	<!-- Required input explanation -->
	<p class="required-input side-note">
		<span class="required"></span> = <?= $lang->REQUIRED_INPUT ?>
	</p>
</article>

<script>
	let form = document.getElementById( 'password-edit' );
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