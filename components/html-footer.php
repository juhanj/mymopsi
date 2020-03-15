<?php declare(strict_types=1); ?>

<footer class="site-footer margins-off" id="site-footer">

	<section class="left">
		<p>MyMopsi</p>
	</section>

	<?php // TODO: Fix this on the front page, looks wrong ?>
	<section class="right">
		<a href="<?= WEB_PATH ?>/settings.php" class="button light settings-link">
			Settings
			<?php echo file_get_contents("img/settings.svg",true); ?>
		</a>
    </section>

</footer>
