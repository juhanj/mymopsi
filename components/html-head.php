<?php declare(strict_types=1);
$current_page = basename( $_SERVER[ 'SCRIPT_NAME' ], ".php" );
?>

<head>
	<meta charset="UTF-8">
	<title>MyMopsi</title>

	<!-- Material icons CSS -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <!-- Modern-Normalize CSS -->
    <link rel="stylesheet" href="<?= ENV ?>/css/modern-normalize.css">

	<!-- Main CSS file -->
	<link rel="stylesheet" href="<?= ENV ?>/css/main.css">
<!--    <link rel="stylesheet" href="--><?//= ENV ?><!--/css/--><?//= $current_page ?><!--.css">-->

    <!-- Main javascript file -->
	<script defer src="<?= ENV ?>/js/main.js"></script>
<!--    <script src="--><?//= ENV ?><!--/js/--><?//= $current_page ?><!--.js"></script>-->
</head>
