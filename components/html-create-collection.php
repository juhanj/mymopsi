<?php
declare(strict_types=1); ?>

<!-- One single <form> -->
<form method="post" class="box">
	<!-- Name -->
	<label>
		<span class="label"><?= $lang->NAME ?></span>
		<input type="text" name="name">
	</label>

	<!-- Description -->
	<label>
		<span class="label"><?= $lang->DESCRIPTION ?></span>
		<input type="text" name="description">
	</label>

	<!-- Public -->
	<label>
		<input type="checkbox" name="public">
		<span class="label"><?= $lang->PUBLIC ?></span>
		<span><?= $lang->PUBLIC_INFO ?></span>
	</label>

	<!-- Editable -->
	<label hidden>
		<input type="checkbox" name="editable">
		<span class="label"><?= $lang->EDITABLE ?></span>
		<span><?= $lang->EDITABLE_INFO ?></span>
	</label>

	<input type="hidden" name="request" value="new">

	<!-- Cancel & Save -->
	<div>
		<!-- Save -->
		<input type="submit" name="<?= $lang->SUBMIT ?>" class="button">
	</div>
</form>