<?php ?>

<!-- One single <form> -->
<form method="post" class="box">
	<!-- Name -->
	<label>
		<span class="label required"><?= $lang->NAME ?></span>
		<input type="text" name="name">
	</label>

	<!-- Description -->
	<label>
		<span class="label required"><?= $lang->DESCRIPTION ?></span>
		<input type="text" name="description">
	</label>

	<!-- Public -->
	<label>
		<input type="checkbox" name="public">
		<span class="label"><?= $lang->PUBLIC ?></span>
		<span><?= $lang->PUBLIC_INFO ?></span>
	</label>

	<!-- Editable -->
	<label>
		<input type="checkbox" name="editable">
		<span class="label"><?= $lang->EDITABLE ?></span>
		<span><?= $lang->EDITABLE_INFO ?></span>
	</label>

	<input type="hidden" name="request" value="new">

	<!-- Cancel & Save -->
	<div>
		<!-- Cancel -->
		<button><?= $lang->CANCEL ?></button>
		<!-- Save -->
		<input type="submit" name="<?= $lang->SUBMIT ?>">
	</div>
</form>