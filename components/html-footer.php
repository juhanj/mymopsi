<?php declare(strict_types=1);
/**
 * @var DBConnection $db
 * @var Language     $lang
 * @var User         $user
 */
?>

<footer class="site-footer margins-off" id="site-footer">

	<section class="left">
	</section>

	<?php // TODO: Fix this on the front page, looks wrong ?>
	<section class="right">
		<a href="<?= WEB_PATH ?>settings.php" class="button light settings-link">
			<?= $lang->SETTINGS ?>
			<span class="material-icons">settings</span>
		</a>
    </section>

</footer>
