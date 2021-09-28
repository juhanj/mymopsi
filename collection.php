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

/* ************************************
 * Pagination code:
 **************************************/
// Items per page : how many items are shown per page
$ipp = (int)($_GET[ 'ipp' ] ?? $_SESSION['ipp'] ?? $_COOKIE['ipp'] ?? 100); // Items Per Page
// Check IPP validity (number between min<->max)
$ipp = ($ipp < 1 || $ipp > 1000) ? 100 : $ipp;
$ipp = ($collection->number_of_images < $ipp) ? $collection->number_of_images : $ipp;

// Get the GET parameter values:
$page = (int)($_GET[ 'page' ] ?? 1); // Page number
$page = ($page < 1) ? 1 : $page;
// Total number of pages
$total_pages = ceil( $collection->number_of_images / ($ipp ?: 1) );
$page = ( $page > $total_pages ) ? $total_pages : $page;

// Sorting order column : which column are the images sorted by
// Valid columns are listed below
$ord_col = (int)($_GET[ 'col' ] ?? 0); // 0 = name
// Sorting order direction : ascending || descending
$ord_dir = (int)($_GET[ 'dir' ] ?? 1); // 0=ASC || 1=DESC

// Sorting oder columns
$orders = [
	[ 'Name', 'Mediatype', 'Size', 'Location', 'Date created', 'Date added' ],
	[ "Ascending", "Descending" ],
];

// Calculate offset (where to start returning images on a list) based on numbers above
$offset = ($page - 1) * $ipp;

$first_page = "?id={$collection->random_uid}&page=1&ipp={$ipp}&col={$ord_col}&dir={$ord_dir}";
$prev_page = "?id={$collection->random_uid}&page=" . ($page - 1) . "&ipp={$ipp}&col={$ord_col}&dir={$ord_dir}";
$next_page = "?id={$collection->random_uid}&page=" . ($page + 1) . "&ipp={$ipp}&col={$ord_col}&dir={$ord_dir}";
$last_page = "?id={$collection->random_uid}&page={$total_pages}&ipp={$ipp}&col={$ord_col}&dir={$ord_dir}";

$disabled_prev = ($page == 1) ? 'disabled' : '';
$disabled_next = ($page == $total_pages) ? 'disabled' : '';

$collection->getImagesWithPagination( $db, [ $ipp, $offset ], [ $ord_col, $ord_dir ] );
/* ************************************
 * Pagination code END
 **************************************/


?>
<!DOCTYPE html>
<html lang="fi">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<div class="feedback" id="feedback"><?= $feedback ?></div>

<main class="main-body-container width-90">

	<div class="buttons margins-off center">
		<a href="upload.php?id=<?= $collection->random_uid ?>" class="button margins-off">
			<?= $lang->ADD_IMG ?>
			<span class="material-icons">add_photo_alternate</span>
		</a>

		<a href="map.php?cid=<?= $collection->random_uid ?>" class="button margins-off">
			<?= $lang->TO_MAP ?>
			<span class="material-icons">map</span>
		</a>
	</div>

	<hr>

	<?php if ( $collection->number_of_images != 0 ) : ?>
		<!-- Pagination -->
		<div class="pagination">
			<!-- Options form -->
			<form class="options margins-off" method="GET" id="pagination-form">

				<?php if ( $collection->number_of_images > 100 ) :
				// Less than 100 images don't show pagination, just takes space ?>
				<!-- Items Per Page -->
				<label class="items-per-page margins-off">
					<!-- Label for IPP -->
					<span class="label">Items per page</span>
					<!-- Input field for IPP -->
					<input type="number" min="1" max="1000" step="1" onchange="this.form.submit()"
					       name="ipp" value="<?= $ipp ?>"
					       list="suggestedIPPValues" class="option" style="width: 5rem">
				</label>
				<?php endif; ?>

				<!-- Sort column -->
				<label class="sort-by margins-off">
					<!-- Label for sort -->
					<span class="label">Sort <span class="material-icons">sort</span></span>
					<!-- Input field, sort-->
					<select name="col" onchange="this.form.submit()" class="option">
						<?php foreach ( $orders[ 0 ] as $key => $column ) : ?>
							<option value="<?= $key ?>" <?= ($key == $ord_col) ? 'selected' : '' ?>>
								<?= $column ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>

				<!-- Sorting order DESC / ASC -->
				<label class="sort-order margins-off">
					<!-- Label for sorting order -->
					<span class="label material-icons">swap_vert</span>
					<!-- Input field for sorting order -->
					<select name="dir" class="sort-order option" onchange="this.form.submit()">
						<?php foreach ( $orders[ 1 ] as $key => $column ) : ?>
							<option value="<?= $key ?>" <?= ($key == $ord_dir) ? 'selected' : '' ?>>
								<?= $column ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>

				<!-- Server side stuff -->
				<input type="hidden" name="id" value="<?= $collection->random_uid ?>">
			</form>

		<?php if ( $collection->number_of_images > 100 ) :
			// Less than 100 images don't show pagination, just takes space ?>
			<!-- Page navigation (prev / current / next) -->
			<nav class="page-navigation margins-off">
				<!-- Backwards navigation (previous / first page) -->
				<!--				<div class="buttons backward margins-off">-->
				<a class="button <?= $disabled_prev ?>" href="<?= $first_page ?>"> <i class="material-icons">first_page</i> </a>
				<a class="button <?= $disabled_prev ?>" href="<?= $prev_page ?>"> <i class="material-icons">navigate_before</i> </a>
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
				<a class="button <?= $disabled_next ?>" href="<?= $next_page ?>"> <i class="material-icons">navigate_next</i> </a>
				<a class="button <?= $disabled_next ?>" href="<?= $last_page ?>"> <i class="material-icons">last_page</i> </a>
				<!--				</div>-->
			</nav>
		<?php endif; ?>
		</div>

		<hr>

		<!-- Images list -->
		<ul class="image-list" id="imageList">
			<?php foreach ( $collection->images as $index => $img ) : ?>
				<li class="image-list-item openOverlay margins-off"
				    data-index="<?= $index ?>"
				    data-id="<?= $img->random_uid ?>"
				    data-lat="<?= $img->latitude ?>"
				    data-lng="<?= $img->longitude ?>"
					data-name="<?= $img->name ?>">
					<div class="img-name"><?= $img->name ?></div>
					<img src="./img/img.php?id=<?= $img->random_uid ?>&thumb"
					     class="img-thumb"
					     alt="<?= $img->name ?>"
					     onerror="this.onerror=null;this.src='./img/mopsi.ico';">
					<span class="no-location-warning"><?= ($img->latitude) ? '' : '⚠' ?></span>
					<!-- Inline onerror because otherwise it won't trigger
						(image loads before listener is registered) -->
				</li>
			<?php endforeach; ?>
		</ul>

		<p class="current-ipp-value">
			<?= $offset + 1 ?>&ndash;<?= $offset + $ipp ?> / <?= $collection->number_of_images ?>
		</p>

		<hr>
	<?php else : ?>
		<!-- If there are no images in collection, print a short polite message telling
			the user that, because apparently the empty page isn't enough of a message... -->
		<section>
			<?= $lang->NO_IMAGES ?>
		</section>
	<?php endif; ?>

</main>

<?php require 'html-footer.php'; ?>

<!-- Hidden fullscreen overlay code. When image thumbnail is clicked, this is shown -->
<div id="overlay" class="dark-overlay-bg hidden" hidden>
	<div class="overlay-container">
		<section class="overlay-header-container center margins-off">
			<a href="" class="button" id="imageEditLink">
				<?= $lang->EDIT ?>
				<span class="material-icons">edit</span>
			</a>
			<span class="image-name" id="imageName"></span>
			<a href="" class="button" id="imageMapLink">
				<?= $lang->MAP ?>
				<span class="material-icons">place</span>
			</a>
			<button class="button" id="closeOverlay">
				<span class="material-icons">close</span>
			</button>
		</section>

		<section class="overlay-image-container" id="overlayImageContainer">
			<img src="" class="image-full" id="imageFull" alt="">
		</section>
	</div>
</div>

<script>
	// These are used in page-specific JS-file, for header-link.
	let collectionName = "<?= $collection->name ?? substr( $collection->random_uid, 0, 5 ) ?>";
	let collectionRUID = "<?= $collection->random_uid ?>";

	let images = JSON.parse(`<?= $collection->printImagesJSON() ?>`);
</script>

</body>
</html>
