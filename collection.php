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

array_push(
	$breadcrumbs_navigation,
	[ 'User', WEB_PATH . '/collections.php' ]
);

/*
 * Pagination code:
 */
// Get the GET parameter values:
$page = (int)($_GET[ 'page' ] ?? 1); // Page number
$items_per_page = (int)($_GET[ 'ipp' ] ?? 50); // Items Per Page
$order_column = (int)($_GET[ 'col' ] ?? 0);
$order_direction = (int)($_GET[ 'dir' ] ?? 1); // ASC || DESC

// Check that the page number is valid (max page number is checked later)
if ( $page < 1 ) {
	$page = 1;
}
// Check that the IPP is valid
if ( $items_per_page < 1 || $items_per_page > 1000 ) {
	$items_per_page = 50;
}
// Calculate offset (where to start returning images on a list) based on numbers above
$offset = ($page - 1) * $items_per_page;

$collection->getImagesWithPagination( $db, [ $items_per_page, $offset ], [ $order_column, $order_direction ] );

// In case there are fewer images than wanted on page, set IPP to total nro images
if ( $collection->number_of_images < $items_per_page ) {
	$items_per_page = $collection->number_of_images;
}

$total_pages = ($collection->number_of_images !== 0)
	? ceil( $collection->number_of_images / $items_per_page )
	: 1;

// If current page is larger than total pages possible, redirect to max page count
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
				<!--				<div class="buttons backward margins-off">-->
				<a class="button first-page" href="<?= $first_page ?>"> <i class="material-icons">first_page</i> </a>
				<a class="button" href="<?= $prev_page ?>"> <i class="material-icons">navigate_before</i> </a>
				<!--				</div>-->

				<!-- Page select -->
				<div class="current">
					<input type="number" value="<?= $page ?>" step="1"
					       name="page" form="pagination-form" title="Page input" class="page-input"
					       onclick="this.select();">
					<span class="separator">/</span>
					<span class="total-pages"><?= $total_pages ?></span>
				</div>

				<!-- Forwards navigation (next / last page) -->
				<!--				<div class="buttons forward margins-off">-->
				<a class="button" href="<?= $next_page ?>"> <i class="material-icons">navigate_next</i> </a>
				<a class="button last-page" href="<?= $last_page ?>"> <i class="material-icons">last_page</i> </a>
				<!--				</div>-->
			</nav>

			<p class="current-ipp-value">
				<?= $offset + 1 ?>&ndash;<?= $offset + $items_per_page ?> / <?= $collection->number_of_images ?>
			</p>
		</div>

		<!-- Images list -->
		<ul class="image-list" id="imageList">
			<?php foreach ( $collection->images as $index => $img ) : ?>
				<li class="image-list-item openOverlay" data-id="<?= $img->random_uid ?>">
					<!--					<span class="openOverlay">-->
					<img src="./img/img.php?id=<?= $img->random_uid ?>&thumb"
					     class="img-thumb" alt="<?= $img->name ?>"
					     data-id="<?= $img->random_uid ?>"
					     data-name="<?= $img->name ?>">
					<!--					</span>-->
				</li>
			<?php endforeach; ?>
		</ul>

	<?php else : ?>
		<!-- If there are no images in collection, print a short, polite message telling
			the user that, because apparently the empty page isn't enough of a message... -->
		<section>
			<?= $lang->NO_IMAGES ?>
		</section>
	<?php endif; ?>

</main>

<?php require 'html-footer.php'; ?>

<div id="overlay" class="dark-overlay-bg hidden" hidden>
	<div class="overlay-container">
		<section class="overlay-header-container center margins-off">
			<a href="" class="button" id="imageEditLink">
				<?= $lang->EDIT ?>
				<span class="material-icons">edit</span>
			</a>
			<span id="imageName"></span>
			<a href="" class="button" id="imageMapLink">
				<?= $lang->MAP ?>
				<span class="material-icons">place</span>
			</a>
			<button class="button" id="closeOverlay">
				<span class="material-icons">close</span>
			</button>
		</section>

		<section class="overlay-image-container">
			<img src="" class="image-full" id="imageFull" alt="">
		</section>
	</div>
</div>

<script>
	// These are used in page-specific JS-file, for header-link.
	let collectionName = "<?= $collection->name ?? substr( $collection->random_uid, 0, 5 ) ?>";
	let collectionRUID = "<?= $collection->random_uid ?>";
</script>

</body>
</html>
