<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';

//Utils::debug( $_POST );
//Utils::debug( $_FILES );
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
	.collections-list {
		display: flex;
		flex-flow: row wrap;
		justify-content: space-evenly;
		align-items: flex-start;
		align-content: space-around;
	}

	.collections-list .collection {
		display: flex;
		flex-flow: column nowrap;
		margin: .5rem;
		padding: 0;
		height: 5rem;
	}

	.collections-list .collection:hover,
	.collections-list .collection:focus {
		top: -10px;
		box-shadow: 0 0.3rem 0.3rem 0.1rem var(--text-dark);
	}

	.collections-list .collection .img {
		height: 100%;
	}

	.collections-list .collection p {
		background-color: white;
	}

</style>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">

	<ul class="collections-list">
		<?php for ( $i = 0; $i < 1; $i++ ) : ?>
			<li class="collection box">
				<a href="./image.php">
					<img src="/mopsi_dev/mymopsi/img/img.php?id=dcbb877731f4fbdb7358&thumb" class="img">
				</a>
			</li>
		<?php endfor; ?>
	</ul>


</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
