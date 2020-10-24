<?php declare(strict_types=1); ?>

<header class="site-header margins-off" id="site-header">
	<nav class="breadcrumbs-navigation">
		<?php foreach ( $breadcrumbs_navigation as $breadcrumb ) :
			?><a href="<?= $breadcrumb[1] ?>"><?= $breadcrumb[0] ?></a><span class="separator">»</span><?php
		endforeach; ?>
	</nav>
	<h1 class="page-title margins-off">
		<?= $lang->HEADER_TITLE ?>
	</h1>
</header>
