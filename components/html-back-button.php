<?php declare(strict_types=1);
/**
 * @var Language $lang
 */
?>

<!-- Back button; not sure how semantic or good or clean this is, and I don't care -->
<div class="max-width margins-off" style="width: 100%; justify-self: center;">
	<a href="#" onclick="history.go(-1); return false;" class="button return" style="margin: 0; width: fit-content">
		<span class="material-icons">arrow_back</span>
		<?= $lang->RETURN ?>
	</a>
</div>