<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language     $lang
 * @var User         $user
 */

$feedback = Common::checkFeedbackAndPOST();

$collection = Collection::fetchCollectionByRUID( $db, $_GET[ 'id' ] );

if ( !$collection ) {
	$_SESSION[ 'feedback' ] = "<p class='error'>No collection found with given ID.</p>";
	header( "Location:index.php" );
	exit();
}

/*
 * Pagination code:
 */
$page = (int)($_GET[ 'page' ] ?? 1); // Page number
$items_per_page = (int)($_GET[ 'ipp' ] ?? 50); // Items Per Page
$order_column = (int)($_GET[ 'col' ] ?? 0);
$order_direction = (int)($_GET[ 'dir' ] ?? 1); // ASC || DESC

if ( $page < 1 ) {
	$page = 1;
}
if ( $items_per_page < 1 || $items_per_page > 100 ) {
	$items_per_page = 50;
}
$offset = ($page - 1) * $items_per_page;

$collection->getImagesWithPagination( $db, [$items_per_page,$offset], [$order_column, $order_direction] );

array_push(
	$breadcrumbs_navigation,
	[ 'User', WEB_PATH . '/collections.php' ]
);

// In case there are fewer images than wanted on page
if ( $collection->number_of_images < $items_per_page ) {
	$items_per_page = $collection->number_of_images;
}

$total_pages = ($collection->number_of_images !== 0)
	? ceil( $collection->number_of_images / $items_per_page )
	: 1;

if ( $page > $total_pages ) {
	header( "Location:collection.php?id={$collection->random_uid}&page={$total_pages}&ipp={$items_per_page}&col={$order_column}&dir={$order_direction}" );
	exit();
}

$first_page = "?id={$collection->random_uid}&page=1&ipp={$items_per_page}&col={$order_column}&dir={$order_direction}";
$prev_page = "?id={$collection->random_uid}&page=" . ($page - 1) . "&ipp={$items_per_page}&col={$order_column}&dir={$order_direction}";
$next_page = "?id={$collection->random_uid}&page=" . ($page + 1) . "&ipp={$items_per_page}&col={$order_column}&dir={$order_direction}";
$last_page = "?id={$collection->random_uid}&page={$total_pages}&ipp={$items_per_page}&col={$order_column}&dir={$order_direction}";

$orders = [
	[ 'Name', 'Mediatype', 'Size', 'Location', 'Date created', 'Date added' ],
	[ "Ascending", "Descending" ],
];
?>

<!DOCTYPE html>
<html lang="fi">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<div class="feedback" id="feedback"><?= $feedback ?></div>

<main class="main-body-container">

	<div class="buttons margins-off">
		<a href="upload.php?id=<?= $collection->random_uid ?>" class="button margins-off">
			<?= $lang->ADD_IMG ?>
			<span class="material-icons">add_photo_alternate</span>
		</a>

		<a href="map.php?cid=<?= $collection->random_uid ?>" class="button margins-off">
			<?= $lang->TO_MAP ?>
			<span class="material-icons">map</span>
		</a>
	</div>

	<?php if ( $collection->number_of_images != 0 ) : ?>
		<div class="pagination">
			<form class="options margins-off" method="GET" id="pagination-form">
				<input type="hidden" name="id" value="<?= $collection->random_uid ?>">
				<label class="items-per-page margins-off">
					<span class="label">Items per page</span>
					<select name="ipp" onchange="this.form.submit()">
						<?php $x = 10;
						for ( $i = 0; $i < 5; ++$i ) {
							echo ($x == $items_per_page)
								? "<option value='{$x}' selected>{$x}</option>"
								: "<option value='{$x}'>{$x}</option>";
							$x = ($i % 2 == 0)
								? $x = $x * 5
								: $x = $x * 2;
						} ?>
					</select>
				</label>
				<label class="sort-by margins-off">
					Sort <span class="material-icons">sort</span>
					<select name="col" onchange="this.form.submit()">
						<?php foreach ( $orders[ 0 ] as $key => $column ) : ?>
							<option
								value="<?= $key ?>" <?= ($key == $order_column) ? 'selected' : '' ?>><?= $column ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="sort-order margins-off">
					<span class="material-icons">swap_vert</span>
					<select name="dir" class="sort-order" onchange="this.form.submit()">
						<?php foreach ( $orders[ 1 ] as $key => $column ) : ?>
							<option
								value="<?= $key ?>" <?= ($key == $order_direction) ? 'selected' : '' ?>><?= $column ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</form>

			<!-- Page navigation (prev / current / next) -->
			<nav class="page-navigation margins-off">
				<!-- Backwards navigation (previous / first page) -->
				<div class="buttons backward margins-off">
					<a class="button" href="<?= $first_page ?>"> <i class="material-icons">first_page</i> </a>
					<a class="button" href="<?= $prev_page ?>"> <i class="material-icons">navigate_before</i> </a>
				</div>

				<!-- Page select -->
				<div class="current">
					<input type="number" min="1" max="<?= $total_pages ?>" value="<?= $page ?>"
					       name="page" form="pagination-form" title="Page input" class="page-input"
					       onclick="this.select();">
					<span class="separator">/</span>
					<span class="total-pages"><?= $total_pages ?></span>
				</div>

				<!-- Forwards navigation (next / last page) -->
				<div class="buttons forward margins-off">
					<a class="button" href="<?= $next_page ?>"> <i class="material-icons">navigate_next</i> </a>
					<a class="button" href="<?= $last_page ?>"> <i class="material-icons">last_page</i> </a>
				</div>
			</nav>

			<p class="current-ipp-value">
				<?= $offset ?>&ndash;<?= $offset + $items_per_page ?> / <?= $collection->number_of_images ?>
			</p>
		</div>

		<!-- Images list -->
		<ul class="image-list">
			<?php foreach ( $collection->images as $img ) : ?>
				<li class="image">
					<a href="./edit-image.php?id=<?= $img->random_uid ?>" class="link">
						<img src="./img/img.php?id=<?= $img->random_uid ?>&thumb"
						     class="img" alt="<?= $img->name ?>">
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

	<?php else : ?>
		<!-- If there are no images in collection, print a short, polite messages telling the user that -->
		<!-- Because apparently the empty page isn't enough of a message... -->
		<section>
			<?= $lang->NO_IMAGES ?>
		</section>
	<?php endif; ?>

</main>

<?php require 'html-footer.php'; ?>

<script>
	// These are used in page-specific JS-file, for header-link.
	let collectionName = "<?= $collection->name ?? substr( $collection->random_uid, 0, 5 ) ?>";
	let collectionRUID = "<?= $collection->random_uid ?>";
</script>

</body>
</html>
