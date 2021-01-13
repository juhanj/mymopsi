<?php declare(strict_types=1); ?>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title><?= $lang->HTML_TITLE ?></title>

	<link rel="icon" href="<?= WEB_PATH ?>/img/mopsi.ico">

	<!-- Serious icon business -->
	<link rel="apple-touch-icon" sizes="180x180" href="<?= WEB_PATH ?>/img/icon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?= WEB_PATH ?>/img/icon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?= WEB_PATH ?>/img/icon/favicon-16x16.png">
	<link rel="manifest" href="<?= WEB_PATH ?>/img/icon/site.webmanifest">
	<link rel="mask-icon" href="<?= WEB_PATH ?>/img/icon/safari-pinned-tab.svg" color="#5bbad5">
	<link rel="shortcut icon" href="<?= WEB_PATH ?>/img/icon/favicon.ico">
	<meta name="msapplication-TileColor" content="#ffc40d">
	<meta name="msapplication-config" content="<?= WEB_PATH ?>/img/icon/browserconfig.xml">
	<meta name="theme-color" content="#ffffff">

	<!-- Material icons CSS -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

	<!-- Modern-Normalize CSS -->
	<link rel="stylesheet" href="<?= WEB_PATH ?>/css/modern-normalize.css">

	<!-- Main CSS file -->
	<link rel="stylesheet" href="<?= WEB_PATH ?>/css/main.css?v=<?= filemtime( DOC_ROOT.WEB_PATH . '/css/main.css' ) ?>">
	<!-- Header/footer CSS file -->
	<link rel="stylesheet" href="<?= WEB_PATH ?>/css/header-footer.css?v=<?= filemtime( DOC_ROOT.WEB_PATH . '/css/header-footer.css' ) ?>">
	<!-- Pagination CSS file (only used in collections and images pages) -->
	<link rel="stylesheet" href="<?= WEB_PATH ?>/css/pagination.css?v=<?= filemtime( DOC_ROOT.WEB_PATH . '/css/pagination.css' ) ?>">
	<!-- Page specific CSS file -->
	<link rel="stylesheet" href="<?= WEB_PATH ?>/css/<?= CURRENT_PAGE ?>.css?v=<?= filemtime( DOC_ROOT.WEB_PATH . '/css/'.CURRENT_PAGE.'.css' ) ?>">

	<!-- Day.js library for handling datetimes in javascript -->
	<script defer src="https://unpkg.com/dayjs"></script>

	<!-- Main javascript file -->
	<script defer src="<?= WEB_PATH ?>/js/main.js"></script>
	<!-- Page specific javascript file -->
	<script type="module" defer src="<?= WEB_PATH ?>/js/<?= CURRENT_PAGE ?>.js"></script>

</head>
