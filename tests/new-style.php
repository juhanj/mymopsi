<?php declare( strict_types=1 );
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
	.box {
		display: flex;
		flex-direction: column;
		align-content: space-evenly;
		justify-content: space-evenly;
	}
	.button {
		display: flex;
		justify-content: center; /* Horizontal align */
		align-items: center; /* Vertical align */

		padding: 1rem;

		border-radius: .2rem;

		width: auto;
		min-width: 20rem;
		border: 0;

		box-shadow: 0 .2rem 0 0 var(--divider-dark);
	}
	.button:hover {
		/*background-color: var(--primary-darker);*/
	}
</style>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">
	<article class="box">
		<button class="button">New button</button>
		<button class="button">
			<i class="material-icons">check</i>
			Testing icons ✔
		</button>
		<button class="button">Button with super long text that might not fit on the button. I wonder what will happen then. Still need more text. I could be writing my thesis right about, but I kinda want a better style on the site so that it will look nice. If it looks nice, I want to work with it, right?</button>
	</article>
</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
