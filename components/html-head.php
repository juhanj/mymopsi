<?php declare(strict_types=1); ?>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title><?= $lang->HTML_TITLE ?></title>

	<!-- Material icons CSS -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

	<!-- Modern-Normalize CSS -->
	<link rel="stylesheet" href="<?= WEB_PATH ?>/css/modern-normalize.css">

	<!-- Main CSS file -->
	<link rel="stylesheet" href="<?= WEB_PATH ?>/css/main.css">
	<!-- Header/footer CSS file -->
	<link rel="stylesheet" href="<?= WEB_PATH ?>/css/header-footer.css">
	<!-- Page specific CSS file -->
	<link rel="stylesheet" href="<?= WEB_PATH ?>/css/<?= CURRENT_PAGE ?>.css">

	<!-- Polyfill for <dialog> -element -->
	<script defer src="https://cdnjs.cloudflare.com/ajax/libs/dialog-polyfill/0.5.0/dialog-polyfill.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dialog-polyfill/0.5.0/dialog-polyfill.min.css" />

	<!-- Day.js library for handling datetimes in javascript -->
	<script defer src="https://unpkg.com/dayjs"></script>

	<!-- Main javascript file -->
	<script defer src="<?= WEB_PATH ?>/js/main.js"></script>
	<!-- Page specific javascript file -->
	<script defer src="<?= WEB_PATH ?>/js/<?= CURRENT_PAGE ?>.js"></script>

</head>
