<?php declare(strict_types=1);
/**
 * @var User $user
 * @var Language $lang
 * @var array[]  $breadcrumbs_navigation
 */
?>

<header class="site-header margins-off" id="site-header">
	<nav class="breadcrumbs-navigation margins-off">
		<span>
			<a href="<?= WEB_PATH ?>">
				<img src="<?= WEB_PATH ?>/img/mopsi128.png" alt="MyMopsi" style="height: 1rem">
			</a>
		</span>
		<span class="separator">•</span>
		<?php foreach ( $breadcrumbs_navigation as $breadcrumb ) : ?>
			<a href="<?= $breadcrumb[ 1 ] ?>" class="header-page-link">
				<?= $breadcrumb[ 0 ] ?>
			</a>
			<span class="separator"><span class="material-icons">arrow_right</span></span>
		<?php endforeach; ?>
	</nav>
	<h1 class="page-title margins-off">
		<?= $lang->HEADER_TITLE ?>
	</h1>
</header>
