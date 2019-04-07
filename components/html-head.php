<?php declare(strict_types=1);
$current_page = basename( $_SERVER[ 'SCRIPT_NAME' ], ".php" );
?>

<head>
	<meta charset="UTF-8">
	<title>MyMopsi</title>

	<!-- Material icons CSS -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <!-- Modern-Normalize CSS -->
    <link rel="stylesheet" href="<?= WEB_PATH ?>/css/modern-normalize.css">

	<!-- Main CSS file -->
	<link rel="stylesheet" href="<?= WEB_PATH ?>/css/main.css">
	<!-- Page specific CSS file -->
    <link rel="stylesheet" href="<?= WEB_PATH ?>/css/<?= $current_page ?>.css">

    <!-- Main javascript file -->
	<script defer src="<?= WEB_PATH ?>/js/main.js"></script>
	<!-- Page specific javascript file -->
    <script src="<?= WEB_PATH ?>/js/<?= $current_page ?>.js"></script>
</head>
